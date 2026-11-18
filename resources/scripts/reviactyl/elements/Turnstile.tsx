import { forwardRef, useEffect, useImperativeHandle, useRef } from 'react';
import useTheme from '@/plugins/useTheme';

export interface TurnstileHandle {
    reset: () => void;
}

interface TurnstileProps {
    siteKey: string;
    onVerify: (token: string) => void;
    onExpire?: () => void;
    onError?: (error: any) => void;
}

declare global {
    interface Window {
        turnstile?: {
            render: (container: HTMLElement, options: TurnstileRenderOptions) => string;
            reset: (widgetId: string) => void;
            remove: (widgetId: string) => void;
        };
    }
}

interface TurnstileRenderOptions {
    sitekey: string;
    callback: (token: string) => void;
    'expired-callback'?: () => void;
    'error-callback'?: (error: any) => void;
    theme?: 'light' | 'dark' | 'auto';
    appearance?: 'always' | 'execute' | 'interaction-only';
    retry?: 'auto' | 'never';
    'retry-interval'?: number;
    'refresh-expired'?: 'auto' | 'manual' | 'never';
}

const Turnstile = forwardRef<TurnstileHandle, TurnstileProps>(({ siteKey, onVerify, onExpire, onError }, ref) => {
    const containerRef = useRef<HTMLDivElement>(null);
    const widgetIdRef = useRef<string | null>(null);
    const { dark } = useTheme();

    useImperativeHandle(ref, () => ({
        reset: () => {
            if (widgetIdRef.current !== null) {
                window.turnstile?.reset(widgetIdRef.current);
            }
        },
    }));

    const onVerifyRef = useRef(onVerify);
    const onExpireRef = useRef(onExpire);
    const onErrorRef = useRef(onError);

    useEffect(() => {
        onVerifyRef.current = onVerify;
        onExpireRef.current = onExpire;
        onErrorRef.current = onError;
    }, [onVerify, onExpire, onError]);

    useEffect(() => {
        let active = true;
        let readinessInterval: ReturnType<typeof setInterval> | undefined;
        let widgetApi: Window['turnstile'];

        const renderWidget = () => {
            if (!active || !window.turnstile || !containerRef.current || widgetIdRef.current !== null) return;

            widgetApi = window.turnstile;
            widgetIdRef.current = widgetApi.render(containerRef.current, {
                sitekey: siteKey,
                callback: (token: string) => active && onVerifyRef.current(token),
                'expired-callback': () => active && onExpireRef.current?.(),
                'error-callback': (error: any) => active && onErrorRef.current?.(error),
                theme: dark ? 'dark' : 'light',
                appearance: 'always',
                retry: 'auto',
                'retry-interval': 5000,
                'refresh-expired': 'auto',
            });
        };

        if (window.turnstile) {
            renderWidget();
        } else {
            if (!document.querySelector('script[src*="challenges.cloudflare.com/turnstile"]')) {
                const script = document.createElement('script');
                script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
            }

            readinessInterval = setInterval(() => {
                if (window.turnstile) {
                    clearInterval(readinessInterval);
                    renderWidget();
                }
            }, 100);
        }

        return () => {
            active = false;
            clearInterval(readinessInterval);
            if (widgetIdRef.current !== null) {
                widgetApi?.remove(widgetIdRef.current);
                widgetIdRef.current = null;
                onExpireRef.current?.();
            }
        };
    }, [siteKey, dark]);

    return <div ref={containerRef} />;
});

Turnstile.displayName = 'Turnstile';

export default Turnstile;

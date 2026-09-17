import { useEffect, useRef } from 'react';

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
        onTurnstileLoad?: () => void;
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

const Turnstile = ({ siteKey, onVerify, onExpire, onError }: TurnstileProps) => {
    const containerRef = useRef<HTMLDivElement>(null);
    const widgetIdRef = useRef<string | null>(null);
    const isRenderedRef = useRef(false);

    // Store callbacks in refs to avoid re-renders
    const onVerifyRef = useRef(onVerify);
    const onExpireRef = useRef(onExpire);
    const onErrorRef = useRef(onError);

    // Update refs when callbacks change
    useEffect(() => {
        onVerifyRef.current = onVerify;
        onExpireRef.current = onExpire;
        onErrorRef.current = onError;
    }, [onVerify, onExpire, onError]);

    useEffect(() => {
        // Prevent double render
        if (isRenderedRef.current) return;

        const renderWidget = () => {
            if (!window.turnstile || !containerRef.current || isRenderedRef.current) return;

            isRenderedRef.current = true;
            widgetIdRef.current = window.turnstile.render(containerRef.current, {
                sitekey: siteKey,
                callback: (token: string) => onVerifyRef.current(token),
                'expired-callback': () => onExpireRef.current?.(),
                'error-callback': (error: any) => onErrorRef.current?.(error),
                theme: 'dark',
                appearance: 'always',
                retry: 'auto',
                'retry-interval': 5000,
                'refresh-expired': 'auto',
            });
        };

        // Check if script is already loaded
        if (window.turnstile) {
            renderWidget();
            return;
        }

        // Check if script is already being loaded
        const existingScript = document.querySelector('script[src*="challenges.cloudflare.com/turnstile"]');
        if (existingScript) {
            // Wait for existing script to load
            const checkInterval = setInterval(() => {
                if (window.turnstile) {
                    clearInterval(checkInterval);
                    renderWidget();
                }
            }, 100);
            return;
        }

        // Load the script
        const script = document.createElement('script');
        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onTurnstileLoad';
        script.async = true;
        script.defer = true;

        window.onTurnstileLoad = () => {
            renderWidget();
        };

        document.head.appendChild(script);

        return () => {
            if (widgetIdRef.current && window.turnstile) {
                window.turnstile.remove(widgetIdRef.current);
                widgetIdRef.current = null;
                isRenderedRef.current = false;
            }
        };
    }, [siteKey]); // Only re-run if siteKey changes

    return <div ref={containerRef} />;
};

export default Turnstile;

import { CSSProperties, MouseEvent, useCallback, useMemo, useState } from 'react';
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';
import { useTranslation } from 'react-i18next';
import { useSWRConfig } from 'swr';
import {
    FaBullhorn,
    FaCircleXmark,
    FaCircleInfo,
    FaTriangleExclamation,
    FaCircleCheck,
    FaRegCircleXmark,
} from 'react-icons/fa6';
import Md2React from '@/reviactyl/ui/Md2React';
import { AlertButton, AlertPlacement, PanelAlert, dismissAlert, trackAlertClick, useAlerts } from '@/api/alerts';

interface Props {
    placement?: AlertPlacement;
    server?: string;
}

const GUEST_STORAGE_KEY = 'reviactyl:dismissed-alerts';
const DAY = 24 * 60 * 60 * 1000;

const accents: Record<string, string> = {
    info: '#3b82f6',
    announcement: 'rgb(var(--color-primary))',
    success: 'rgb(var(--color-success))',
    warning: '#eab308',
    danger: 'rgb(var(--color-danger))',
};

const icons: Record<string, typeof FaCircleInfo> = {
    info: FaCircleInfo,
    announcement: FaBullhorn,
    success: FaCircleCheck,
    warning: FaTriangleExclamation,
    danger: FaCircleXmark,
};

const isSafeUrl = (url: string): boolean => /^(https?:\/\/|mailto:|\/(?!\/))/i.test(url);

type GuestInteraction = 'dismissed' | 'clicked';
type GuestInteractions = Record<string, Partial<Record<GuestInteraction, number>>>;

let unsavedGuestInteractions: GuestInteractions = {};

const readGuestInteractions = (): GuestInteractions => {
    try {
        const parsed = JSON.parse(window.localStorage.getItem(GUEST_STORAGE_KEY) ?? '{}');

        return parsed && typeof parsed === 'object' && !Array.isArray(parsed)
            ? { ...parsed, ...unsavedGuestInteractions }
            : unsavedGuestInteractions;
    } catch {
        return unsavedGuestInteractions;
    }
};

const storeGuestInteraction = (uuid: string, interaction: GuestInteraction) => {
    const stored = readGuestInteractions();
    const previous = typeof stored[uuid] === 'object' && stored[uuid] !== null ? stored[uuid] : {};
    const next = { ...stored, [uuid]: { ...previous, [interaction]: Date.now() } };

    try {
        window.localStorage.setItem(GUEST_STORAGE_KEY, JSON.stringify(next));
        unsavedGuestInteractions = {};
    } catch {
        unsavedGuestInteractions = next;
    }
};

const isHiddenForGuest = (alert: PanelAlert, interactions: GuestInteractions): boolean => {
    const stored = interactions[alert.uuid];
    if (typeof stored !== 'object' || stored === null) return false;

    const hides = (at?: number): boolean => {
        if (typeof at !== 'number') return false;
        if (alert.dismissalsResetAt && at <= alert.dismissalsResetAt.getTime()) return false;

        return alert.redisplayAfterDays === null || at + alert.redisplayAfterDays * DAY > Date.now();
    };

    return (alert.dismissible && hides(stored.dismissed)) || (alert.dismissOnAction && hides(stored.clicked));
};

const isAlertsKey = (key: unknown): boolean => typeof key === 'string' && key.includes('"alerts"');

const Announcement = ({ placement = 'dashboard', server }: Props) => {
    const { t } = useTranslation('strings');
    const reduceMotion = useReducedMotion();
    const { data } = useAlerts(placement, server);
    const { mutate } = useSWRConfig();
    const [guestRevision, setGuestRevision] = useState(0);
    const isGuest = placement === 'auth';

    const alerts = useMemo(() => {
        if (!isGuest) return data ?? [];

        const interactions = readGuestInteractions();

        return (data ?? []).filter((alert) => !isHiddenForGuest(alert, interactions));
    }, [data, isGuest, guestRevision]);

    const hideWhile = useCallback(
        (alert: PanelAlert, request: Promise<void>) => {
            mutate(isAlertsKey, (current?: PanelAlert[]) => current?.filter((cached) => cached.uuid !== alert.uuid), {
                revalidate: false,
            });

            request.catch(() => undefined).then(() => mutate(isAlertsKey));
        },
        [mutate],
    );

    const recordForGuest = (alert: PanelAlert, interaction: GuestInteraction) => {
        storeGuestInteraction(alert.uuid, interaction);
        setGuestRevision((revision) => revision + 1);
    };

    const onDismiss = (alert: PanelAlert) => {
        if (isGuest) {
            recordForGuest(alert, 'dismissed');

            return;
        }

        hideWhile(alert, dismissAlert(alert.uuid));
    };

    const onButtonClick = (event: MouseEvent<HTMLAnchorElement>, alert: PanelAlert, button: AlertButton) => {
        const opensElsewhere = button.newTab || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0;

        if (isGuest) {
            recordForGuest(alert, 'clicked');

            return;
        }

        if (opensElsewhere) {
            const request = trackAlertClick(alert.uuid);

            if (alert.dismissOnAction) {
                hideWhile(alert, request);
            } else {
                request.catch(() => undefined);
            }

            return;
        }

        event.preventDefault();
        trackAlertClick(alert.uuid)
            .catch(() => undefined)
            .then(() => window.location.assign(button.url));
    };

    return (
        <div className={isGuest && alerts.length === 0 ? 'px-2' : 'px-2 my-2'}>
            <AnimatePresence initial={false}>
                {alerts.map((alert) => {
                    const Icon = icons[alert.type] ?? FaCircleInfo;
                    const buttons = alert.buttons.filter((button) => isSafeUrl(button.url));
                    const style = {
                        '--alert-accent': alert.color || accents[alert.type] || accents.info,
                    } as CSSProperties;

                    return (
                        <motion.div
                            key={alert.uuid}
                            layout={!reduceMotion}
                            initial={reduceMotion ? false : { opacity: 0, y: -6 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={reduceMotion ? { opacity: 0 } : { opacity: 0, height: 0, marginTop: 0 }}
                            transition={{ duration: reduceMotion ? 0 : 0.2, ease: 'easeOut' }}
                            className='mx-auto mt-2 w-full max-w-300 overflow-hidden'
                        >
                            <div
                                role={alert.type === 'danger' || alert.type === 'warning' ? 'alert' : 'status'}
                                style={style}
                                className='relative flex items-start gap-x-3 overflow-hidden rounded-ui border border-[color-mix(in_srgb,var(--alert-accent)_35%,transparent)] bg-gray-900/80 py-3 pl-5 pr-3 text-gray-100 backdrop-blur-md sm:items-center'
                            >
                                <span
                                    aria-hidden
                                    className='pointer-events-none absolute inset-0 bg-[var(--alert-accent)] opacity-10'
                                />
                                <span
                                    aria-hidden
                                    className='absolute inset-y-0 left-0 w-1.5 bg-[var(--alert-accent)]'
                                />
                                <Icon
                                    aria-hidden
                                    className='relative mt-0.5 h-5 w-5 shrink-0 text-[var(--alert-accent)] sm:mt-0'
                                />
                                <div className='relative flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center'>
                                    <div className='min-w-0 flex-1 break-words'>
                                        {alert.title && <strong className='mr-1.5 text-gray-50'>{alert.title}</strong>}
                                        <Md2React markdown={alert.message} />
                                    </div>
                                    {buttons.length > 0 && (
                                        <div className='flex shrink-0 flex-wrap items-center gap-2'>
                                            {buttons.map((button, index) => (
                                                <a
                                                    key={`${index}-${button.label}`}
                                                    href={button.url}
                                                    target={button.newTab ? '_blank' : undefined}
                                                    rel={button.newTab ? 'noopener noreferrer' : undefined}
                                                    onClick={(event) => onButtonClick(event, alert, button)}
                                                    className={
                                                        button.style === 'secondary'
                                                            ? 'rounded-full px-3 py-1.5 text-sm font-medium text-gray-200 underline-offset-4 transition-colors hover:text-gray-50 hover:underline focus-visible:outline-2 focus-visible:outline-[var(--alert-accent)]'
                                                            : 'rounded-full border border-gray-100/40 px-4 py-1.5 text-sm font-medium text-gray-50 transition-colors hover:border-gray-100/70 hover:bg-gray-100/10 focus-visible:outline-2 focus-visible:outline-[var(--alert-accent)]'
                                                    }
                                                >
                                                    {button.label}
                                                </a>
                                            ))}
                                        </div>
                                    )}
                                </div>
                                {alert.dismissible && (
                                    <>
                                        <span
                                            aria-hidden
                                            className='relative hidden h-7 w-px bg-gray-100/20 sm:block'
                                        />
                                        <button
                                            type='button'
                                            aria-label={t('dismiss')}
                                            title={t('dismiss')}
                                            onClick={() => onDismiss(alert)}
                                            className='relative shrink-0 rounded-full p-1.5 text-gray-200 transition-colors hover:bg-gray-100/10 hover:text-gray-50 focus-visible:outline-2 focus-visible:outline-[var(--alert-accent)]'
                                        >
                                            <FaRegCircleXmark aria-hidden className='h-5 w-5' />
                                        </button>
                                    </>
                                )}
                            </div>
                        </motion.div>
                    );
                })}
            </AnimatePresence>
        </div>
    );
};

export default Announcement;

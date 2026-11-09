import { useId, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Server } from '@/api/server/getServer';
import { ServerCategory } from '@/api/server/types';
import { LayoutContainer, ServerLayout } from '@/components/dashboard/ServerLayout';
import { ChevronDownIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';
import Card from '@/reviactyl/ui/Card';
import { FaServer } from 'react-icons/fa6';

interface Props {
    category: ServerCategory | null;
    label?: string;
    servers: Server[];
    showOnlyAdmin: boolean;
    showCategory?: boolean;
    onCategoryChanged?: () => void;
}

export default ({ category, label, servers, showOnlyAdmin, showCategory = true, onCategoryChanged }: Props) => {
    const { t } = useTranslation('dashboard/index');
    const contentId = useId();
    const storageKey = `server-category-${category?.uuid ?? 'primary'}`;
    const [open, setOpen] = useState(() => {
        if (typeof window === 'undefined') return true;

        try {
            return window.localStorage.getItem(storageKey) !== 'false';
        } catch {
            return true;
        }
    });

    const toggleOpen = () => {
        setOpen((prev) => {
            const next = !prev;

            try {
                window.localStorage.setItem(storageKey, String(next));
            } catch {
                return next;
            }

            return next;
        });
    };

    if (servers.length === 0) return null;

    const categoryColor = category?.color || 'var(--color-blue-500)';
    const displayColor = category ? categoryColor : 'var(--color-gray-600)';
    return (
        <div className='relative mb-3 overflow-hidden'>
            <button
                type='button'
                aria-expanded={open}
                aria-controls={contentId}
                className={`relative z-10 flex w-full cursor-pointer items-center justify-between px-5 py-4.5 text-left transition-colors duration-150 hover:bg-gray-800 bg-gray-900 border border-gray-800 ${open ? 'rounded-t-ui mb-2' : 'rounded-ui'}`}
                onClick={toggleOpen}
            >
                <div className='flex min-w-0 flex-1 items-center gap-3'>
                    <div className='flex items-center gap-2'>
                        <span
                            className='inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md border'
                            style={{
                                color: displayColor,
                                borderColor: `${displayColor}40`,
                                backgroundColor: `${displayColor}15`,
                            }}
                        >
                            <FaServer className='h-6' />
                        </span>
                        <div className='min-w-0'>
                            <span className='font-medium inline-flex gap-2' style={{ color: displayColor }}>
                                {category ? label || category.name : t('categories.primary')}
                                <span
                                    className='inline-flex h-6 min-w-6 items-center justify-center rounded-full border px-2 text-xs font-semibold tabular-nums'
                                    style={{
                                        color: displayColor,
                                        borderColor: `color-mix(in srgb, ${displayColor} 25%, transparent)`,
                                        backgroundColor: `color-mix(in srgb, ${displayColor} 8%, transparent)`,
                                    }}
                                >
                                    {servers.length}
                                </span>
                            </span>
                            {category?.description && (
                                <p className='mt-0.5 truncate text-xs text-gray-400' title={category.description}>
                                    {category.description}
                                </p>
                            )}
                        </div>
                    </div>
                </div>

                <motion.div animate={{ rotate: open ? 180 : 0 }} transition={{ duration: 0.25, ease: 'easeInOut' }}>
                    <ChevronDownIcon className='h-5 w-5 text-gray-400' />
                </motion.div>
            </button>
            <div id={contentId}>
                <AnimatePresence initial={false}>
                    {open && (
                        <motion.div
                            key='category-content'
                            initial={{ height: 0, opacity: 0, y: -8 }}
                            animate={{ height: 'auto', opacity: 1, y: 0 }}
                            exit={{ height: 0, opacity: 0, y: -8 }}
                            transition={{ duration: 0.3, ease: [0.22, 1, 0.36, 1] }}
                            className='overflow-hidden'
                        >
                            <Card className='overflow-hidden rounded-t-none! p-2!'>
                                <LayoutContainer>
                                    {servers.map((server, index) => (
                                        <ServerLayout
                                            key={server.uuid}
                                            server={server}
                                            className={index > 0 ? 'mt-2' : undefined}
                                            onCategoryChanged={onCategoryChanged}
                                            showCategory={!showOnlyAdmin && showCategory}
                                        />
                                    ))}
                                </LayoutContainer>
                            </Card>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </div>
    );
};

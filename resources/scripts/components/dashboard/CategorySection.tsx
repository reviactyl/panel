import { useState } from 'react';
import { Server } from '@/api/server/getServer';
import { ServerCategory } from '@/api/server/types';
import { LayoutContainer, ServerLayout } from '@/components/dashboard/ServerLayout';
import { ChevronDownIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';
import Card from '@/reviactyl/ui/Card';

interface Props {
    category: ServerCategory | null;
    servers: Server[];
    showOnlyAdmin: boolean;
    showCategory?: boolean;
    onCategoryChanged?: () => void;
}

// Exact styles provided by user
export default ({ category, servers, showOnlyAdmin, showCategory = true, onCategoryChanged }: Props) => {
    const { t } = useTranslation('dashboard/index');
    const [open, setOpen] = useState(true);

    if (servers.length === 0) return null;

    const categoryColor = category?.color || '#3b82f6';
    const displayColor = category ? categoryColor : '#64748b';

    return (
        <Card className='relative mb-5 overflow-hidden p-0! transition'>
            {/* LEFT ACCENT BAR */}
            <div className='absolute top-0 left-0 h-full w-1' style={{ backgroundColor: displayColor }} />

            {/* HEADER */}
            <button
                className='flex w-full cursor-pointer items-center justify-between px-5 py-[18px] text-left transition-all duration-150 hover:bg-gray-700'
                onClick={() => setOpen(!open)}
            >
                <div className='flex min-w-0 flex-1 items-center gap-3'>
                    <div className='min-w-0'>
                        <span className='font-medium' style={{ color: displayColor }}>
                            {category ? category.name : t('categories.primary')}
                        </span>
                        {category?.description && (
                            <p className='mt-0.5 truncate text-xs text-[#94a3b8]' title={category.description}>
                                {category.description}
                            </p>
                        )}
                    </div>
                    <span
                        className='ml-auto shrink-0 rounded-ui border px-2 py-1 text-xs'
                        style={{ color: displayColor, borderColor: displayColor }}
                    >
                        {t('categories.servers-count', { count: servers.length })}
                    </span>
                </div>

                <ChevronDownIcon className={`h-5 w-5 text-gray-400 transition-transform ${open ? 'rotate-180' : ''}`} />
            </button>

            {/* CONTENT */}
            {open && (
                <div className='border-t border-gray-600 p-4'>
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
                </div>
            )}
        </Card>
    );
};

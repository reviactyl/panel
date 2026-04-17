import { useState } from 'react';
import { Server } from '@/api/server/getServer';
import { ServerCategory } from '@/api/server/types';
import ServerRow from '@/components/dashboard/ServerRow';
import tw from 'twin.macro';
import styled from 'styled-components';
import { ChevronDownIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';
import Card from '@/reviactyl/ui/Card';

interface Props {
    category: ServerCategory | null;
    servers: Server[];
    showOnlyAdmin: boolean;
    onCategoryChanged?: () => void;
}

// Exact styles provided by user
const HeaderButton = styled.button`
    width: 100%;
    transition: all 0.15s ease;
    text-align: left;

    list-style: none;
    cursor: pointer;
    padding: 18px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
`;

export default ({ category, servers, showOnlyAdmin, onCategoryChanged }: Props) => {
    const { t } = useTranslation('dashboard/index');
    const [open, setOpen] = useState(true);

    if (servers.length === 0) return null;

    const categoryColor = category?.color || '#3b82f6';
    const displayColor = category ? categoryColor : '#64748b';

    return (
        <Card css={[tw`!p-0 relative overflow-hidden mb-5 transition`]}>
            {/* LEFT ACCENT BAR */}
            <div css={tw`absolute left-0 top-0 h-full w-1`} style={{ backgroundColor: displayColor }} />

            {/* HEADER */}
            <HeaderButton className='hover:bg-gray-600' onClick={() => setOpen(!open)}>
                <div css={tw`flex items-center gap-3 flex-1 min-w-0`}>
                    <div css={tw`min-w-0`}>
                        <span css={tw`font-medium`} style={{ color: displayColor }}>
                            {category ? category.name : t('categories.primary')}
                        </span>
                        {category?.description && (
                            <p css={tw`text-xs text-[#94a3b8] mt-0.5 truncate`} title={category.description}>
                                {category.description}
                            </p>
                        )}
                    </div>
                    <span css={tw`text-xs px-2 py-1 rounded-ui border flex-shrink-0 ml-auto`} style={{ color: displayColor, borderColor: displayColor }}>
                        {t('categories.servers-count', { count: servers.length })}
                    </span>
                </div>

                <ChevronDownIcon css={[tw`w-5 h-5 text-gray-400 transition-transform`, open && tw`rotate-180`]} />
            </HeaderButton>

            {/* CONTENT */}
            {open && (
                <div css={tw`border-t border-gray-500 p-4`}>
                    <div css={tw`grid lg:grid-cols-2 gap-4`}>
                        {servers.map((server, index) => (
                            <ServerRow
                                key={server.uuid}
                                server={server}
                                css={index > 0 ? tw`mt-2` : undefined}
                                onCategoryChanged={onCategoryChanged}
                                showCategory={!showOnlyAdmin}
                            />
                        ))}
                    </div>
                </div>
            )}
        </Card>
    );
};

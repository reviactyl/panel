import React, { useEffect, useState, useMemo, useRef } from 'react';
import { Server } from '@/api/server/getServer';
import getServers from '@/api/getServers';
import Spinner from '@/components/elements/Spinner';
import PageContentBlock from '@/components/elements/PageContentBlock';
import useFlash from '@/plugins/useFlash';
import { useStoreState } from 'easy-peasy';
import { usePersistedState } from '@/plugins/usePersistedState';
import Switch from '@/components/elements/Switch';
import tw from 'twin.macro';
import useSWR from 'swr';
import { PaginatedResult } from '@/api/http';
import Pagination from '@/components/elements/Pagination';
import { useLocation } from 'react-router-dom';
import Card from '@/reviactyl/ui/Card';
import Title from '@/reviactyl/ui/Title';
import { EmojiSadIcon, FilterIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';
import getServerCategories from '@/api/account/getServerCategories';
import getClientEggs from '@/api/getClientEggs';
import CategorySection from '@/components/dashboard/CategorySection';
import CategoryManagerModal from '@/components/dashboard/CategoryManagerModal';
import ServerRow from '@/components/dashboard/ServerRow';

export default () => {
    const { t } = useTranslation('dashboard/index');
    const { search } = useLocation();
    const defaultPage = Number(new URLSearchParams(search).get('page') || '1');

    const [page, setPage] = useState(!isNaN(defaultPage) && defaultPage > 0 ? defaultPage : 1);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const uuid = useStoreState((state) => state.user.data!.uuid);
    const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const [showOnlyAdmin, setShowOnlyAdmin] = usePersistedState(`${uuid}:show_all_servers`, false);

    const [isModalVisible, setModalVisible] = useState(false);
    const [selectedCategory, setSelectedCategory] = useState<string>('all');
    const [selectedEggId, setSelectedEggId] = useState<number | null>(null);
    const [eggFilterOpen, setEggFilterOpen] = useState(false);
    const eggFilterRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (eggFilterRef.current && !eggFilterRef.current.contains(event.target as Node)) {
                setEggFilterOpen(false);
            }
        };
        if (eggFilterOpen) {
            document.addEventListener('mousedown', handleClickOutside);
        }
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, [eggFilterOpen]);

    const { data: eggs } = useSWR('/api/client/eggs', getClientEggs);

    const { data: servers, error, mutate: mutateServers } = useSWR<PaginatedResult<Server>>(
        ['/api/client/servers', showOnlyAdmin && rootAdmin, page, selectedCategory, selectedEggId],
        () => getServers({
            page,
            type: showOnlyAdmin && rootAdmin ? 'admin' : undefined,
            'filter[category_uuid]': selectedCategory === 'all' ? undefined : (selectedCategory === 'primary' ? 'null' : selectedCategory),
            eggId: selectedEggId ?? undefined,
        })
    );

    const { data: categories, mutate: mutateCategories } = useSWR(
        '/api/client/account/categories',
        () => getServerCategories()
    );

    useEffect(() => {
        if (!servers) return;
        if (servers.pagination.currentPage > 1 && !servers.items.length) {
            setPage(1);
        }
    }, [servers?.pagination.currentPage]);

    useEffect(() => {
        setPage(1);
    }, [selectedEggId]);

    useEffect(() => {
        // Don't use react-router to handle changing this part of the URL, otherwise it
        // triggers a needless re-render. We just want to track this in the URL incase the
        // user refreshes the page.
        window.history.replaceState(null, document.title, `/${page <= 1 ? '' : `?page=${page}`}`);
    }, [page]);

    useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'dashboard', error });
        if (!error) clearFlashes('dashboard');
    }, [error]);

    const groupedServers = useMemo(() => {
        if (!servers) return {};

        const groups = (servers.items || []).reduce((acc, server) => {
            const catUuid = server.category?.uuid || 'primary';
            if (!acc[catUuid]) acc[catUuid] = [];
            acc[catUuid].push(server);
            return acc;
        }, {} as Record<string, Server[]>);

        // If a specific category is selected, only keep that group
        if (selectedCategory !== 'all') {
            return { [selectedCategory]: groups[selectedCategory] || [] };
        }

        return groups;
    }, [servers, selectedCategory]);

    const sortedCategorySlugs = useMemo(() => {
        const slugs = Object.keys(groupedServers);
        // Sort by category position if categories are loaded
        if (categories) {
            return slugs.sort((a, b) => {
                if (a === 'primary') return 1; // Primary always last
                if (b === 'primary') return -1;
                const indexA = categories.findIndex(c => c.uuid === a);
                const indexB = categories.findIndex(c => c.uuid === b);
                return indexA - indexB;
            });
        }
        return slugs;
    }, [groupedServers, categories]);

    return (
        <PageContentBlock className='pr-2' title={t('title')} showFlashKey={'dashboard'}>
            <CategoryManagerModal
                visible={isModalVisible}
                onDismissed={() => setModalVisible(false)}
                onCategoryChanged={() => {
                    mutateCategories();
                    mutateServers();
                }}
            />

            <div className='flex items-center justify-between py-4 flex-wrap gap-4'>
                <div>
                    {/* TODO: Needs to be updated.
                    1] Show different subtitle based on $showOnlyAdmin
                    2] It somehow looks odd and doesn't match reviactyl v2 design.
                    <h1 className='text-xl font-semibold text-gray-100'>{t('welcome.title')}</h1>
                    <p className='text-sm text-gray-400'>{t('welcome.subtitle')}</p>
                    */}
                    <Title className='text-4xl'>{t('title')}</Title>
                </div>

                <div className='flex items-center gap-4 flex-wrap'>
                    {!showOnlyAdmin && (
                        <>
                            <select
                                className='bg-[#1e293b] border border-[#334155] text-gray-200 px-3 py-1.5 rounded-lg outline-none focus:border-blue-500 transition'
                                value={selectedCategory}
                                onChange={(e) => setSelectedCategory(e.target.value)}
                            >
                                <option value="all">{t('categories.all-categories')}</option>
                                {categories?.map(cat => (
                                    <option key={cat.uuid} value={cat.uuid}>{cat.name}</option>
                                ))}
                                <option value="primary">{t('categories.primary')}</option>
                            </select>

                            <button
                                onClick={() => setModalVisible(true)}
                                className='bg-[#1e293b] border border-[#334155] hover:border-blue-500 text-gray-200 px-4 py-1.5 rounded-lg transition'
                            >
                                {t('categories.manage')}
                            </button>
                        </>
                    )}
                    {rootAdmin && (
                        <div className='flex items-center space-x-2 border-l border-[#334155] pl-4'>
                            <p className='uppercase text-xs text-gray-400'>
                                {showOnlyAdmin ? t('other-servers') : t('your-servers')}
                            </p>
                            <Switch
                                name={'show_all_servers'}
                                defaultChecked={showOnlyAdmin}
                                onChange={() => setShowOnlyAdmin((s) => !s)}
                            />
                        </div>
                    )}
                    {!showOnlyAdmin && eggs && eggs.length > 0 && (
                        <div className='relative flex items-center border-l border-[#334155] pl-4' ref={eggFilterRef}>
                            <button
                                type='button'
                                onClick={() => setEggFilterOpen((o) => !o)}
                                className={`p-1.5 rounded-lg transition border ${
                                    selectedEggId !== null
                                        ? 'bg-blue-500/20 border-blue-500 text-blue-400'
                                        : 'bg-[#1e293b] border-[#334155] text-gray-400 hover:border-gray-500 hover:text-gray-200'
                                }`}
                                title={t('eggs.filter-label')}
                                aria-label={t('eggs.filter-label')}
                                aria-expanded={eggFilterOpen}
                            >
                                <FilterIcon className='w-5 h-5' />
                            </button>
                            {eggFilterOpen && (
                                <div className='absolute right-0 top-full mt-1.5 z-10 min-w-[180px] py-2 px-2 bg-[#1e293b] border border-[#334155] rounded-lg shadow-lg'>
                                    <p className='text-xs text-gray-400 uppercase px-2 pb-1.5'>
                                        {t('eggs.filter-label')}
                                    </p>
                                    <select
                                        className='w-full bg-[#0f172a] border border-[#334155] text-gray-200 text-sm px-3 py-2 rounded-lg outline-none focus:border-blue-500 transition'
                                        value={selectedEggId ?? ''}
                                        onChange={(e) => {
                                            setSelectedEggId(e.target.value === '' ? null : Number(e.target.value));
                                            setEggFilterOpen(false);
                                        }}
                                        aria-label={t('eggs.filter-label')}
                                    >
                                        <option value=''>{t('eggs.all')}</option>
                                        {eggs.map((egg) => (
                                            <option key={egg.id} value={egg.id}>
                                                {egg.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {!servers ? (
                <Spinner centered size={'large'} />
            ) : (
                <div>
                    <Pagination data={servers} onPageSelect={setPage}>
                        {() => {
                            const items = servers.items || [];
                            if (items.length === 0) {
                                return (
                                    <Card css={tw`col-span-1 lg:col-span-2`}>
                                        <p className='flex justify-center text-center text-sm text-gray-400 py-10'>
                                            <EmojiSadIcon className='w-5 h-5 mr-1' />{' '}
                                            {selectedEggId !== null
                                                ? t('eggs.no-servers-for-egg')
                                                : showOnlyAdmin
                                                  ? t('no-other-servers')
                                                  : t('no-servers')}
                                        </p>
                                    </Card>
                                );
                            }
                            if (showOnlyAdmin) {
                                return (
                                    <div className='grid lg:grid-cols-2 gap-4'>
                                        {items.map((server, index) => (
                                            <ServerRow
                                                key={server.uuid}
                                                server={server}
                                                css={index > 0 ? tw`mt-2` : undefined}
                                                showCategory={false}
                                            />
                                        ))}
                                    </div>
                                );
                            }
                            return sortedCategorySlugs.length > 0 ? (
                                sortedCategorySlugs.map(slug => (
                                    <CategorySection
                                        key={slug}
                                        category={categories?.find(c => c.uuid === slug) || null}
                                        servers={groupedServers[slug] || []}
                                        showOnlyAdmin={showOnlyAdmin || false}
                                        onCategoryChanged={() => mutateServers()}
                                    />
                                ))
                            ) : (
                                <Card css={tw`col-span-1 lg:col-span-2`}>
                                    <p className='flex justify-center text-center text-sm text-gray-400 py-10'>
                                        <EmojiSadIcon className='w-5 h-5 mr-1' />{' '}
                                        {t('no-servers')}
                                    </p>
                                </Card>
                            );
                        }}
                    </Pagination>
                </div>
            )}
        </PageContentBlock>
    );
};

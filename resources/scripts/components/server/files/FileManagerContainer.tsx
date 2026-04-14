import React, { useEffect, useMemo, useRef, useState } from 'react';
import { httpErrorToHuman } from '@/api/http';
import { motion } from 'framer-motion';
import Spinner from '@/reviactyl/elements/Spinner';
import FileObjectRow from '@/components/server/files/FileObjectRow';
import FileManagerBreadcrumbs from '@/components/server/files/FileManagerBreadcrumbs';
import loadDirectory, { FileObject } from '@/api/server/files/loadDirectory';
import NewDirectoryButton from '@/components/server/files/NewDirectoryButton';
import UrlDownloadButton from '@/components/server/files/UrlDownloadButton';
import { NavLink, useLocation } from 'react-router-dom';
import Can from '@/reviactyl/elements/Can';
import { ServerError } from '@/reviactyl/elements/ScreenBlock';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import FileManagerStatus from '@/components/server/files/FileManagerStatus';
import MassActionsBar from '@/components/server/files/MassActionsBar';
import UploadButton from '@/components/server/files/UploadButton';
import ServerContentBlock from '@/reviactyl/elements/ServerContentBlock';
import { useStoreActions } from '@/state/hooks';
import ErrorBoundary from '@/reviactyl/elements/ErrorBoundary';
import { FileActionCheckbox } from '@/components/server/files/SelectFileCheckbox';
import { hashToPath, encodePathSegments } from '@/helpers';
import style from './style.module.css';
import { SearchIcon, FolderIcon, FolderOpenIcon, DocumentIcon } from '@heroicons/react/solid';
import Card from '@/reviactyl/ui/Card';
import { useTranslation } from 'react-i18next';
import ImageViewerModal from '@/components/server/files/ImageViewerModal';
import getFileDownloadUrl from '@/api/server/files/getFileDownloadUrl';
import { join } from 'pathe';
import { bytesToString } from '@/lib/formatters';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import { PlusSmIcon } from '@heroicons/react/solid';
import { ExtensionSlot } from '@/extensions/ExtensionSlot';
import Input from '@/reviactyl/elements/Input';
import {
    FaArrowDown19,
    FaArrowDownAZ,
    FaArrowDownShortWide,
    FaArrowUp19,
    FaArrowUpAZ,
    FaArrowUpShortWide,
} from 'react-icons/fa6';

type SortType = 'name' | 'size' | 'date';
type SortDirection = 'asc' | 'desc';

const sortFiles = (
    files: FileObject[],
    sortType: SortType = 'name',
    sortDirection: SortDirection = 'asc'
): FileObject[] => {
    const sorted = [...files];

    sorted.sort((a, b) => (a.isFile === b.isFile ? 0 : a.isFile ? 1 : -1));

    const multiplier = sortDirection === 'asc' ? 1 : -1;

    if (sortType === 'name') {
        sorted.sort((a, b) => a.name.localeCompare(b.name) * multiplier);
    } else if (sortType === 'size') {
        sorted.sort((a, b) => {
            if (a.isFile && b.isFile) {
                return (a.size - b.size) * multiplier;
            }
            return 0;
        });
    } else if (sortType === 'date') {
        sorted.sort((a, b) => {
            const timeA = a.modifiedAt.getTime();
            const timeB = b.modifiedAt.getTime();
            return (timeA - timeB) * multiplier;
        });
    }

    return sorted.filter((file, index) => index === 0 || file.name !== sorted[index - 1]?.name);
};

type SearchResult = FileObject & { fullPath: string };

const RecursiveFileRow = ({ file, serverId }: { file: SearchResult; serverId: string }) => {
    const to = file.isFile
        ? `/server/${serverId}/files/edit#${encodePathSegments(file.fullPath)}`
        : `/server/${serverId}/files#${encodePathSegments(file.fullPath)}`;
    const Icon = file.isFile ? DocumentIcon : FolderIcon;
    return (
        <div className='flex items-center py-2 px-2 border-b border-gray-700 last:border-0 hover:bg-gray-700 rounded-ui'>
            <Icon className='w-4 h-4 mr-3 flex-none text-gray-400' aria-hidden='true' />
            <NavLink to={to} className='flex-1 text-sm text-gray-200 truncate hover:text-primary-400 min-w-0'>
                {file.fullPath}
            </NavLink>
            {file.isFile && <span className='text-xs text-gray-500 ml-3 flex-none'>{bytesToString(file.size)}</span>}
        </div>
    );
};

export default () => {
    const { t } = useTranslation('server/files');
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { hash } = useLocation();
    const { data: files, error, mutate } = useFileManagerSwr();
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const clearFlashes = useStoreActions((actions) => actions.flashes.clearFlashes);
    const setDirectory = ServerContext.useStoreActions((actions) => actions.files.setDirectory);

    const setSelectedFiles = ServerContext.useStoreActions((actions) => actions.files.setSelectedFiles);
    const selectedFilesLength = ServerContext.useStoreState((state) => state.files.selectedFiles.length);

    // Image viewer state
    const [imageViewerVisible, setImageViewerVisible] = useState(false);
    const [selectedImage, setSelectedImage] = useState<{ url: string; name: string } | null>(null);

    const [sortType, setSortType] = useState<SortType>('name');
    const [sortDirection, setSortDirection] = useState<SortDirection>('asc');

    const [inputValue, setInputValue] = useState('');
    const [searchExpanded, setSearchExpanded] = useState(false);
    const [query, setQuery] = useState('');
    const [recursiveResults, setRecursiveResults] = useState<SearchResult[]>([]);
    const [isSearching, setIsSearching] = useState(false);
    const searchGenRef = useRef(0);
    const searchInputRef = useRef<HTMLInputElement>(null);
    const isSearchExpanded = searchExpanded || !!inputValue;

    useEffect(() => {
        clearFlashes('files');
        setSelectedFiles([]);
        setDirectory(hashToPath(hash));
        setInputValue('');
        setSearchExpanded(false);
    }, [hash]);

    useEffect(() => {
        mutate();
    }, [directory]);

    useEffect(() => {
        const timer = setTimeout(() => setQuery(inputValue), 300);
        return () => clearTimeout(timer);
    }, [inputValue]);

    useEffect(() => {
        if (isSearchExpanded) {
            searchInputRef.current?.focus();
        }
    }, [isSearchExpanded]);

    useEffect(() => {
        if (!query) {
            setRecursiveResults([]);
            setIsSearching(false);
            return;
        }

        const gen = ++searchGenRef.current;
        setIsSearching(true);
        setRecursiveResults([]);

        (async () => {
            const matches: SearchResult[] = [];
            const queue: string[] = ['/'];
            const seen = new Set<string>(['/']);
            const MAX_FILES = 250;
            const CONCURRENCY = 3;

            while (queue.length > 0 && gen === searchGenRef.current && matches.length < MAX_FILES) {
                const batch = queue.splice(0, CONCURRENCY);
                const settled = await Promise.allSettled(
                    batch.map((dir) => loadDirectory(uuid, dir).then((dirFiles) => ({ dir, dirFiles })))
                );

                if (gen !== searchGenRef.current) break;

                for (const result of settled) {
                    if (result.status !== 'fulfilled') continue;
                    const { dir, dirFiles } = result.value;
                    for (const f of dirFiles ?? []) {
                        const fp = `${dir === '/' ? '' : dir}/${f.name}`.replace(/\/+/g, '/');
                        if (!f.isFile && !seen.has(fp)) {
                            seen.add(fp);
                            queue.push(fp);
                        }
                        if (f.name.toLowerCase().includes(query.toLowerCase())) {
                            matches.push({ ...f, fullPath: fp, key: fp });
                        }
                    }
                }

                if (gen === searchGenRef.current) {
                    setRecursiveResults([...matches]);
                }
            }

            if (gen === searchGenRef.current) setIsSearching(false);
        })().catch(() => {
            if (gen === searchGenRef.current) setIsSearching(false);
        });
    }, [query]);

    const filteredFiles = useMemo(() => {
        if (!files) return [];
        return files;
    }, [files]);

    const onSelectAllClick = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSelectedFiles(e.currentTarget.checked ? (query ? [] : filteredFiles.map((file) => file.name)) : []);
    };

    const handleImageClick = (file: FileObject) => {
        const filePath = join(directory, file.name);
        getFileDownloadUrl(uuid, filePath)
            .then((url) => {
                setSelectedImage({ url, name: file.name });
                setImageViewerVisible(true);
            })
            .catch((error) => {
                console.error('Failed to get image URL:', error);
            });
    };

    const handleImageViewerClose = () => {
        setImageViewerVisible(false);
        setSelectedImage(null);
    };

    if (error) {
        return <ServerError message={httpErrorToHuman(error)} onRetry={() => mutate()} />;
    }

    return (
        <ServerContentBlock title={t('title')} showFlashKey={'files'}>
            <ExtensionSlot name={`server:files:above`} />
            <ErrorBoundary>
                <Card className={'flex flex-col mb-1 mt-2 !rounded-b-none !px-2 !py-3'}>
                    <div className='flex flex-wrap md:flex-nowrap items-center gap-2'>
                        <Can action={'file.create'}>
                            <div className={style.manager_actions}>
                                <ExtensionSlot name={`server:files:actions:start`} />
                                <FileManagerStatus className={style.icon_action} />
                                <UrlDownloadButton className={style.icon_action} />
                                <NewDirectoryButton className={style.icon_action} />
                                <UploadButton className={style.icon_action} />
                                <Tooltip content={t('new-file')}>
                                    <NavLink
                                        to={`/server/${id}/files/new${window.location.hash}`}
                                        className={style.icon_action}
                                        aria-label={t('new-file')}
                                    >
                                        <PlusSmIcon className='h-5 w-5' />
                                    </NavLink>
                                </Tooltip>
                                <ExtensionSlot name={`server:files:actions:end`} />
                            </div>
                        </Can>
                        <div className='order-2 md:order-none md:ml-auto flex items-center gap-1 w-full md:w-auto'>
                            <Input
                                ref={searchInputRef}
                                type='text'
                                placeholder={t('search.placeholder')}
                                aria-label={t('search.placeholder')}
                                value={inputValue}
                                onChange={(e) => setInputValue(e.target.value)}
                                onBlur={() => {
                                    if (!inputValue) setSearchExpanded(false);
                                }}
                                onKeyDown={(e) => {
                                    if (e.key === 'Escape') {
                                        if (inputValue) {
                                            setInputValue('');
                                        } else {
                                            setSearchExpanded(false);
                                        }
                                    }
                                }}
                            />
                        </div>
                    </div>
                    <div className='flex flex-wrap md:flex-nowrap items-center gap-2 mt-2'>
                        <div className='order-3 w-full min-w-0 md:order-none md:flex-1 md:w-auto bg-gray-600 rounded-ui py-2'>
                            <FileManagerBreadcrumbs
                                renderLeft={
                                    <FileActionCheckbox
                                        type={'checkbox'}
                                        css={tw`mx-4 block md:hidden`}
                                        checked={selectedFilesLength === (files?.length === 0 ? -1 : files?.length)}
                                        onChange={onSelectAllClick}
                                    />
                                }
                            />
                        </div>
                    </div>
                </Card>
                <Card className={'flex items-center mb-1 !rounded-none !px-2 !py-2 !bg-gray-600 hidden md:block'}>
                    <div className='order-4 md:order-none flex items-center gap-1'>
                        <div className='flex-1 ml-[55px]'>
                            <button
                                onClick={() => {
                                    if (sortType === 'name') {
                                        setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
                                    } else {
                                        setSortType('name');
                                        setSortDirection('asc');
                                    }
                                }}
                                className={'flex items-center gap-x-1 text-sm text-gray-300 !text-gray-200'}
                            >
                                <span css={tw`text-xs font-semibold`}>Name</span>
                                {sortType === 'name' ? (
                                    <FaArrowDownAZ className={sortDirection === 'asc' ? 'rotate-180' : ''} />
                                ) : (
                                    <FaArrowUpAZ />
                                )}
                            </button>
                        </div>
                        <div className='w-1/6 justify-end flex'>
                            <button
                                onClick={() => {
                                    if (sortType === 'size') {
                                        setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
                                    } else {
                                        setSortType('size');
                                        setSortDirection('asc');
                                    }
                                }}
                                className={'flex items-center gap-x-1 text-sm text-gray-300 !text-gray-200'}
                            >
                                <span css={tw`text-xs font-semibold`}>Size</span>
                                {sortType === 'size' ? (
                                    <FaArrowDown19 className={sortDirection === 'asc' ? 'rotate-180' : ''} />
                                ) : (
                                    <FaArrowUp19 />
                                )}
                            </button>
                        </div>
                        <div className='w-1/5 mr-[80px] justify-end flex'>
                            <button
                                onClick={() => {
                                    if (sortType === 'date') {
                                        setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
                                    } else {
                                        setSortType('date');
                                        setSortDirection('asc');
                                    }
                                }}
                                className={'flex items-center gap-x-1 text-sm text-gray-300 !text-gray-200'}
                            >
                                <span css={tw`text-xs font-semibold`}>Date</span>
                                {sortType === 'date' ? (
                                    <FaArrowDownShortWide className={sortDirection === 'asc' ? 'rotate-180' : ''} />
                                ) : (
                                    <FaArrowUpShortWide />
                                )}
                            </button>
                        </div>
                    </div>
                </Card>
            </ErrorBoundary>
            {!files ? (
                <Spinner size={'large'} centered />
            ) : (
                <Card className='!rounded-t-none !p-3'>
                    {query ? (
                        isSearching && recursiveResults.length === 0 ? (
                            <Spinner size={'base'} centered />
                        ) : recursiveResults.length === 0 ? (
                            <div className={'flex flex-col items-center justify-center py-10 text-gray-500'}>
                                <SearchIcon className={'w-10 h-10 mb-2 opacity-40'} />
                                <p className={'text-sm'}>{t('no-results')}</p>
                            </div>
                        ) : (
                            <motion.div
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                transition={{ duration: 0.15, ease: 'easeIn' }}
                            >
                                {isSearching && <p css={tw`text-xs text-gray-500 text-center mb-2`}>Searching...</p>}
                                {recursiveResults.map((file) => (
                                    <RecursiveFileRow key={file.fullPath} file={file} serverId={id} />
                                ))}
                            </motion.div>
                        )
                    ) : !filteredFiles.length ? (
                        <div className={'flex flex-col items-center justify-center py-10 text-gray-500'}>
                            <FolderOpenIcon className={'w-12 h-12 mb-2 opacity-40'} />
                            <p className={'text-sm'}>{t('empty')}</p>
                        </div>
                    ) : (
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            transition={{ duration: 0.15, ease: 'easeIn' }}
                        >
                            {files.length > 250 && (
                                <div css={tw`rounded bg-yellow-400 mb-px p-3`}>
                                    <p css={tw`text-yellow-900 text-sm text-center`}>{t('too-large')}</p>
                                </div>
                            )}
                            {sortFiles(filteredFiles.slice(0, 250), sortType, sortDirection).map((file) => (
                                <FileObjectRow key={file.key} file={file} onImageClick={handleImageClick} />
                            ))}
                            <MassActionsBar />
                        </motion.div>
                    )}
                </Card>
            )}
            {selectedImage && (
                <ImageViewerModal
                    visible={imageViewerVisible}
                    onDismissed={handleImageViewerClose}
                    imageUrl={selectedImage.url}
                    imageName={selectedImage.name}
                    appear
                />
            )}
            <ExtensionSlot name={`server:files:below`} />
        </ServerContentBlock>
    );
};

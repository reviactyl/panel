import React, { useEffect, useMemo, useRef, useState } from 'react';
import { httpErrorToHuman } from '@/api/http';
import { motion } from 'framer-motion';
import Spinner from '@/components/elements/Spinner';
import FileObjectRow from '@/components/server/files/FileObjectRow';
import FileManagerBreadcrumbs from '@/components/server/files/FileManagerBreadcrumbs';
import loadDirectory, { FileObject } from '@/api/server/files/loadDirectory';
import NewDirectoryButton from '@/components/server/files/NewDirectoryButton';
import UrlDownloadButton from '@/components/server/files/UrlDownloadButton';
import { NavLink, useLocation } from 'react-router-dom';
import Can from '@/components/elements/Can';
import { ServerError } from '@/components/elements/ScreenBlock';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { ServerContext } from '@/state/server';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import FileManagerStatus from '@/components/server/files/FileManagerStatus';
import MassActionsBar from '@/components/server/files/MassActionsBar';
import UploadButton from '@/components/server/files/UploadButton';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import { useStoreActions } from '@/state/hooks';
import ErrorBoundary from '@/components/elements/ErrorBoundary';
import { FileActionCheckbox } from '@/components/server/files/SelectFileCheckbox';
import { hashToPath, encodePathSegments } from '@/helpers';
import style from './style.module.css';
import { SearchIcon, XIcon, FolderIcon, FolderOpenIcon, DocumentIcon } from '@heroicons/react/solid';
import Card from '@/reviactyl/ui/Card';
import { useTranslation } from 'react-i18next';
import ImageViewerModal from '@/components/server/files/ImageViewerModal';
import getFileDownloadUrl from '@/api/server/files/getFileDownloadUrl';
import { join } from 'pathe';
import { bytesToString } from '@/lib/formatters';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import { PlusSmIcon } from '@heroicons/react/solid';

const sortFiles = (files: FileObject[]): FileObject[] => {
    const sortedFiles: FileObject[] = files
        .sort((a, b) => a.name.localeCompare(b.name))
        .sort((a, b) => (a.isFile === b.isFile ? 0 : a.isFile ? 1 : -1));
    return sortedFiles.filter((file, index) => index === 0 || file.name !== sortedFiles[index - 1]?.name);
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
            <ErrorBoundary>
                <Card className={'flex flex-col mb-1 mt-2 !rounded-b-none !px-2 !py-3'}>
                    <div className='flex flex-wrap md:flex-nowrap items-center gap-2'>
                        <FileActionCheckbox
                            type={'checkbox'}
                            css={tw`mx-2`}
                            checked={!query && selectedFilesLength > 0 && selectedFilesLength === filteredFiles.length}
                            onChange={onSelectAllClick}
                        />
                        <div className='order-3 w-full min-w-0 md:order-none md:flex-1 md:w-auto'>
                            <FileManagerBreadcrumbs renderLeft={<></>} />
                        </div>
                        <div className='order-2 md:order-none md:ml-auto flex items-center gap-1 w-full md:w-auto'>
                            <div
                                role='search'
                                className={`relative flex items-center min-w-0 transition-all duration-200 ease-in-out ${
                                    isSearchExpanded ? 'flex-1 md:w-72 lg:w-96' : 'w-10'
                                }`}
                            >
                                {isSearchExpanded ? (
                                    <>
                                        <SearchIcon className='absolute left-2 h-4 w-4 text-gray-400 pointer-events-none' />
                                        <input
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
                                            className='w-full bg-gray-700 rounded-ui pl-8 pr-8 py-1.5 text-sm text-gray-100 placeholder-gray-400 border border-gray-600 focus:outline-none focus:border-primary-400'
                                        />
                                        <button
                                            type='button'
                                            aria-label={t('search.clear')}
                                            onClick={() => {
                                                if (inputValue) {
                                                    setInputValue('');
                                                } else {
                                                    setSearchExpanded(false);
                                                }
                                            }}
                                            className='absolute right-2 text-gray-400 hover:text-gray-200'
                                        >
                                            <XIcon className='h-4 w-4' />
                                        </button>
                                    </>
                                ) : (
                                    <Tooltip content={t('search.button')}>
                                        <button
                                            type='button'
                                            aria-label={t('search.button')}
                                            onClick={() => setSearchExpanded(true)}
                                            className='flex items-center justify-center w-10 h-10 rounded-ui bg-gray-700 border border-gray-600 text-gray-300 hover:text-gray-100 hover:border-gray-500 transition-colors'
                                        >
                                            <SearchIcon className='h-4 w-4' />
                                        </button>
                                    </Tooltip>
                                )}
                            </div>
                        </div>
                        <Can action={'file.create'}>
                            <>
                                <div className={style.manager_actions_mobile}>
                                    <FileManagerStatus />
                                    <UrlDownloadButton />
                                    <NewDirectoryButton />
                                    <UploadButton />
                                    <NavLink to={`/server/${id}/files/new${window.location.hash}`}>
                                        <Button>{t('new-file')}</Button>
                                    </NavLink>
                                </div>
                                <div className={style.manager_actions_compact}>
                                    <FileManagerStatus className={style.icon_action} />
                                    <UrlDownloadButton compact className={style.icon_action} />
                                    <NewDirectoryButton compact className={style.icon_action} />
                                    <UploadButton compact className={style.icon_action} />
                                    <Tooltip content={t('new-file')}>
                                        <NavLink
                                            to={`/server/${id}/files/new${window.location.hash}`}
                                            className={style.icon_action}
                                            aria-label={t('new-file')}
                                        >
                                            <PlusSmIcon className='h-5 w-5' />
                                        </NavLink>
                                    </Tooltip>
                                </div>
                            </>
                        </Can>
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
                            {sortFiles(filteredFiles.slice(0, 250)).map((file) => (
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
        </ServerContentBlock>
    );
};

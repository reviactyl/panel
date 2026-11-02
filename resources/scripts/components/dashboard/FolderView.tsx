import { ServerCategory } from '@/api/account/getServerCategories';
import { FaFolder, FaChevronRight, FaPlus, FaHouse, FaArrowRightArrowLeft, FaTrash, FaPen } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';
import Spinner from '@/reviactyl/elements/Spinner';
import Button from '@/reviactyl/elements/Button';
import { folderPath, compareFolders } from './folderTree';

interface Props {
    categories?: ServerCategory[];
    selected: string;
    error?: boolean;
    onRetry: () => void;
    onSelect: (uuid: string) => void;
    onManage: () => void;
    onMoveFolder: (folder: ServerCategory) => void;
    onEditFolder: (folder: ServerCategory) => void;
    onDeleteFolder: (folder: ServerCategory) => void;
}

export default ({
    categories,
    selected,
    onSelect,
    onManage,
    onMoveFolder,
    onDeleteFolder,
    onEditFolder,
    error,
    onRetry,
}: Props) => {
    const { t } = useTranslation('dashboard/index');
    const path = folderPath(categories || [], selected);
    const current = path[path.length - 1];
    const children = categories
        ?.filter((folder) => (folder.parentUuid || null) === (current?.uuid || null))
        .sort(compareFolders);
    return (
        <section className='mb-6' aria-label={t('folders.folders')}>
            <div className='mb-4 flex flex-wrap items-center justify-between gap-3'>
                <nav aria-label={t('folders.folders')} className='flex min-w-0 flex-wrap items-center gap-3 text-sm'>
                    <Button
                        type='button'
                        size='small'
                        isSecondary
                        onClick={() => onSelect('all')}
                        aria-current={!current ? 'page' : undefined}
                        className='[&>span]:inline-flex [&>span]:items-center [&>span]:gap-2'
                    >
                        <FaHouse aria-hidden='true' />
                        {t('folders.home')}
                    </Button>
                    {path.map((folder, index) => (
                        <span key={folder.uuid} className='flex min-w-0 items-center gap-3'>
                            <FaChevronRight className='shrink-0 text-gray-400' aria-hidden='true' />
                            {index === path.length - 1 ? (
                                <span className='break-words text-gray-50' aria-current='page'>
                                    {folder.name}
                                </span>
                            ) : (
                                <Button type='button' size='small' isSecondary onClick={() => onSelect(folder.uuid)}>
                                    {folder.name}
                                </Button>
                            )}
                        </span>
                    ))}
                </nav>
                <Button
                    type='button'
                    size='small'
                    isSecondary
                    onClick={onManage}
                    className='[&>span]:inline-flex [&>span]:items-center [&>span]:gap-2'
                >
                    <FaPlus aria-hidden='true' />
                    {t('folders.create')}
                </Button>
            </div>
            {error && (
                <div role='alert' className='flex flex-wrap items-center gap-3 text-sm text-gray-300'>
                    <span>{t('folders.load-folders-error')}</span>
                    <Button type='button' onClick={onRetry}>
                        {t('folders.retry')}
                    </Button>
                </div>
            )}
            {!error &&
                (!categories ? (
                    <Spinner centered />
                ) : (
                    <div className={children?.length ? 'grid gap-3 sm:grid-cols-2 lg:grid-cols-3' : undefined}>
                        {children?.map((folder) => (
                            <div
                                key={folder.uuid}
                                className='group relative min-w-0 overflow-hidden rounded-ui border border-gray-800 bg-gray-900'
                            >
                                <Button
                                    type='button'
                                    onClick={() => onSelect(folder.uuid)}
                                    className='h-full w-full justify-start! gap-4 rounded-[inherit]! border-0! bg-gray-900! hover:bg-gray-800! p-4! pr-32! text-left [&>span]:flex [&>span]:w-full [&>span]:items-center [&>span]:gap-4'
                                >
                                    <FaFolder
                                        className='h-8 w-8 shrink-0'
                                        style={{ color: folder.color || 'var(--color-gray-200)' }}
                                        aria-hidden='true'
                                    />
                                    <span className='min-w-0 flex-1'>
                                        <span className='block break-words font-medium'>{folder.name}</span>
                                        {folder.description && (
                                            <span className='block truncate text-sm font-normal text-gray-300'>
                                                {folder.description}
                                            </span>
                                        )}
                                    </span>
                                </Button>
                                <div className='absolute right-3 top-1/2 flex -translate-y-1/2 gap-1 opacity-0 transition-opacity duration-150 group-hover:opacity-100 group-focus-within:opacity-100 [@media(hover:none)]:opacity-100'>
                                    <Button
                                        type='button'
                                        isSecondary
                                        size='xsmall'
                                        onClick={() => onEditFolder(folder)}
                                        title={t('categories.edit')}
                                        aria-label={`${t('categories.edit')}: ${folder.name}`}
                                    >
                                        <FaPen aria-hidden='true' />
                                    </Button>
                                    <Button
                                        type='button'
                                        size='small'
                                        isSecondary
                                        onClick={() => onMoveFolder(folder)}
                                        title={t('folders.move-folder')}
                                        aria-label={`${t('folders.move-folder')}: ${folder.name}`}
                                    >
                                        <FaArrowRightArrowLeft aria-hidden='true' />
                                    </Button>
                                    <Button
                                        type='button'
                                        size='small'
                                        isSecondary
                                        onClick={() => onDeleteFolder(folder)}
                                        title={t('categories.delete')}
                                        aria-label={`${t('categories.delete')}: ${folder.name}`}
                                    >
                                        <FaTrash aria-hidden='true' />
                                    </Button>
                                </div>
                            </div>
                        ))}
                    </div>
                ))}
        </section>
    );
};

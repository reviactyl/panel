import { useLocation, useNavigate } from 'react-router-dom';
import { encodePathSegments, hashToPath } from '@/helpers';
import { recycleStoragePath } from './recyclePaths';
import loadDirectory from '@/api/server/files/loadDirectory';
import getFileDownloadUrl from '@/api/server/files/getFileDownloadUrl';
import { isImageFile } from '@/api/server/files/isImageFile';
import { isVideoFile } from '@/api/server/files/isVideoFile';
import ImageViewerModal from './ImageViewerModal';
import VideoViewerModal from './VideoViewerModal';
import FileManagerBreadcrumbs from './FileManagerBreadcrumbs';
import { FileActionCheckbox } from './SelectFileCheckbox';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import { usePermissions } from '@/plugins/usePermissions';
import { Row } from './FileDropdownMenu';
import { FaFileArrowDown, FaTrash } from 'react-icons/fa6';
import { useEffect, useId, useRef, useState } from 'react';
import useSWR from 'swr';
import { useTranslation } from 'react-i18next';
import http, { httpErrorToHuman } from '@/api/http';
import { ServerContext } from '@/state/server';
import { Button } from '@/reviactyl/components/button';
import { Dialog } from '@/reviactyl/elements/dialog';
import Can from '@/reviactyl/elements/Can';
import Spinner from '@/reviactyl/elements/Spinner';
import Card from '@/reviactyl/ui/Card';
import DeleteFilesDialog from './DeleteFilesDialog';
import { bytesToString } from '@/lib/formatters';
import { FaTrashCan, FaArrowRotateLeft } from 'react-icons/fa6';
import type { FileObject } from '@/api/server/files/loadDirectory';
import styles from './style.module.css';
import DropdownMenu from '@/reviactyl/elements/DropdownMenu';
import Input from '@/reviactyl/elements/Input';
import { formatDistanceToNow } from 'date-fns';
import { FaEllipsis, FaFolder, FaFileLines, FaArrowDownAZ } from 'react-icons/fa6';

type Entry = {
    id: string;
    original_path: string;
    created_at: string;
    size: number | null;
    is_file: boolean;
    path?: string;
    entry_id?: string;
    relative_path?: string;
};
type Listing = { data: Entry[]; size: number };

export function RecycleBinRow({ grid, files, onOpen }: { grid: boolean; files: FileObject[]; onOpen: () => void }) {
    const { t } = useTranslation('server/recycle');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const menu = useRef<DropdownMenu>(null);
    const menuId = useId();
    const [emptying, setEmptying] = useState(false);
    const [failure, setFailure] = useState('');
    const [busy, setBusy] = useState(false);
    const { data, mutate } = useSWR<Listing>(
        `/api/client/servers/${uuid}/files/recycle-bin`,
        async (url: string) => (await http.get(url)).data,
    );
    useEffect(() => {
        mutate();
    }, [files, mutate]);
    return (
        <div
            className={`${styles.file_row} ${grid ? styles.file_tile : ''}`}
            onContextMenu={(event) => {
                event.preventDefault();
                event.stopPropagation();
                menu.current?.triggerMenu(event.clientX);
            }}
        >
            <button
                type='button'
                onClick={onOpen}
                className={`${styles.details} text-left focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400`}
            >
                <span
                    className={grid ? 'text-gray-400 text-4xl mb-4' : 'flex-none text-gray-400 ml-6 mr-4 text-lg pl-3'}
                >
                    <FaTrashCan />
                </span>
                <span className={grid ? 'w-full truncate text-center' : 'flex-1 truncate'}>{t('title')}</span>
                <span className={grid ? 'mt-2 text-xs text-gray-400' : 'w-1/6 text-right mr-4'}>
                    {data ? bytesToString(data.size) : '—'}
                </span>
                {!grid && (
                    <span className='w-1/5 text-right mr-4 hidden md:block'>
                        {data?.data[0]
                            ? formatDistanceToNow(new Date(data.data[0].created_at), { addSuffix: true })
                            : ''}
                    </span>
                )}
            </button>
            <DropdownMenu
                ref={menu}
                id={menuId}
                manageFocus
                renderToggle={(onClick, visible) => (
                    <button
                        type='button'
                        aria-label={t('title')}
                        aria-expanded={visible}
                        onClick={onClick}
                        className='cursor-pointer rounded-ui px-4 py-2 hover:text-white'
                    >
                        <FaEllipsis />
                    </button>
                )}
            >
                <Can action='file.delete'>
                    <Row
                        icon={FaTrash}
                        title={t('empty-bin')}
                        danger
                        disabled={busy || !data?.data.length}
                        onClick={() => {
                            menu.current?.closeMenu();
                            setEmptying(true);
                        }}
                    />
                </Can>
            </DropdownMenu>
            {failure && <p role='alert'>{failure}</p>}
            {emptying && (
                <DeleteFilesDialog
                    files={[t('title')]}
                    permanentOnly
                    onClose={() => setEmptying(false)}
                    onConfirm={async () => {
                        setEmptying(false);
                        setBusy(true);
                        setFailure('');
                        try {
                            await http.post(`/api/client/servers/${uuid}/files/recycle-bin/empty`);
                        } catch (error) {
                            setFailure(httpErrorToHuman(error));
                        } finally {
                            await mutate().catch(() => undefined);
                            setBusy(false);
                        }
                    }}
                />
            )}
        </div>
    );
}

function BinFileRow({
    entry,
    selected,
    busy,
    onSelect,
    onRestore,
    onDelete,
    onOpen,
    onDownload,
}: {
    entry: Entry;
    selected: boolean;
    busy: boolean;
    onSelect: (checked: boolean) => void;
    onRestore: () => void;
    onDelete: () => void;
    onOpen: () => void;
    onDownload: () => void;
}) {
    const { t } = useTranslation(['server/recycle', 'strings']);
    const menu = useRef<DropdownMenu>(null);
    const menuId = useId();
    const name = entry.original_path.split('/').pop()!;
    const icon = entry.is_file ? <FaFileLines /> : <FaFolder />;
    const [canReadContents] = usePermissions(['file.read-content']);
    return (
        <div
            className={styles.file_row}
            onContextMenu={(event) => {
                event.preventDefault();
                if (!busy) menu.current?.triggerMenu(event.clientX);
            }}
        >
            <>
                <label className='absolute z-30 flex-none cursor-pointer self-center px-4 py-2'>
                    <Input
                        type='checkbox'
                        aria-label={entry.original_path}
                        checked={selected}
                        disabled={busy}
                        onChange={(event) => onSelect(event.currentTarget.checked)}
                    />
                </label>
            </>
            <button
                type='button'
                disabled={busy || (entry.is_file && !canReadContents)}
                onClick={onOpen}
                className={`${styles.details} text-left`}
            >
                <span className='flex-none text-gray-400 ml-6 mr-4 text-lg pl-3'>{icon}</span>
                <span className='flex-1 truncate' title={entry.original_path}>
                    {name}
                </span>
                <span className='w-1/6 text-right mr-4 hidden sm:block'>
                    {entry.size === null ? '—' : bytesToString(entry.size)}
                </span>
                <span
                    className='w-1/5 text-right mr-4 hidden md:block'
                    title={new Date(entry.created_at).toLocaleString()}
                >
                    {formatDistanceToNow(new Date(entry.created_at), { addSuffix: true })}
                </span>
            </button>
            <DropdownMenu
                ref={menu}
                id={menuId}
                manageFocus
                renderToggle={(onClick, visible) => (
                    <button
                        type='button'
                        disabled={busy}
                        aria-label={`${t('strings:action')}: ${name}`}
                        aria-expanded={visible}
                        aria-controls={menuId}
                        onClick={onClick}
                        className='cursor-pointer rounded-ui px-4 py-2 hover:text-white focus-visible:outline-2 focus-visible:outline-primary-400'
                    >
                        <FaEllipsis />
                    </button>
                )}
            >
                {entry.is_file && (
                    <Can action='file.read-content'>
                        <Row
                            icon={FaFileArrowDown}
                            title={t('server/files:dropdown.download')}
                            disabled={busy}
                            onClick={() => {
                                menu.current?.closeMenu();
                                onDownload();
                            }}
                        />
                    </Can>
                )}
                <>
                    <Can action={['file.create', 'file.update', 'file.delete']}>
                        <Row
                            icon={FaArrowRotateLeft}
                            title={t('restore')}
                            disabled={busy}
                            onClick={() => {
                                menu.current?.closeMenu();
                                onRestore();
                            }}
                        />
                    </Can>
                    <Can action='file.delete'>
                        <Row
                            icon={FaTrash}
                            title={t('server/files:delete')}
                            danger
                            disabled={busy}
                            onClick={() => {
                                menu.current?.closeMenu();
                                onDelete();
                            }}
                        />
                    </Can>
                </>
            </DropdownMenu>
        </div>
    );
}

export default function RecycleBin() {
    const { t } = useTranslation(['server/recycle', 'server/files', 'strings']);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const navigate = useNavigate();
    const { hash } = useLocation();
    const path = hashToPath(hash);
    const nested = path !== '/.recycle-bin';
    const [media, setMedia] = useState<{ url: string; name: string; video: boolean } | null>(null);
    const {
        data: children,
        error: browseError,
        mutate: refreshChildren,
    } = useSWR(nested ? [uuid, path] : null, () => loadDirectory(uuid, recycleStoragePath(path)));
    const url = `/api/client/servers/${uuid}/files/recycle-bin`;
    const { data, error, mutate } = useSWR<Listing>(url, async () => (await http.get(url)).data);
    const [busy, setBusy] = useState(false);
    const [failure, setFailure] = useState('');
    const [query, setQuery] = useState('');
    const [sort, setSort] = useState<'name' | 'size' | 'date'>('name');
    const [descending, setDescending] = useState(false);
    const [selected, setSelected] = useState<string[]>([]);
    const [restoring, setRestoring] = useState<{ entries: Entry[]; conflicts: string[] } | null>(null);
    const [deleting, setDeleting] = useState<Entry[] | null>(null);
    useEffect(() => {
        setSelected([]);
        setQuery('');
    }, [path]);
    const parent = data?.data.find((entry) => entry.id === path.split('/')[2]);
    const relativeDirectory = path.split('/').slice(4);
    const entries: Entry[] = nested
        ? (parent ? (children ?? []) : []).map((file) => ({
              id: file.name,
              entry_id: parent!.id,
              relative_path: [...relativeDirectory, file.name].join('/'),
              original_path: `${parent!.original_path}/${[...relativeDirectory, file.name].join('/')}`,
              created_at: file.modifiedAt.toISOString(),
              size: file.size,
              is_file: file.isFile,
              path: `${path}/${file.name}`,
          }))
        : (data?.data ?? []);
    const entryPath = (entry: Entry) =>
        entry.path ?? `/.recycle-bin/${entry.id}/${entry.original_path.split('/').pop()}`;
    const download = async (entry: Entry) => {
        try {
            window.location.href = await getFileDownloadUrl(uuid, recycleStoragePath(entryPath(entry)));
        } catch (error) {
            setFailure(httpErrorToHuman(error));
        }
    };
    const open = async (entry: Entry) => {
        const target = entryPath(entry);
        if (!entry.is_file) {
            navigate(`#${encodePathSegments(target)}`);
            return;
        }
        setBusy(true);
        setFailure('');
        try {
            const actual = recycleStoragePath(target);
            const files = await loadDirectory(uuid, actual.slice(0, actual.lastIndexOf('/')));
            const file = files.find((file) => file.name === actual.split('/').pop());
            if (!file) throw new Error('File missing');
            if (isImageFile(file) || isVideoFile(file)) {
                setMedia({
                    url: await getFileDownloadUrl(uuid, actual),
                    name: entry.original_path.split('/').pop()!,
                    video: isVideoFile(file),
                });
            } else if (file.isEditable()) navigate(`/server/${id}/files/edit#${encodePathSegments(target)}`);
            else await download(entry);
        } catch (error) {
            setFailure(httpErrorToHuman(error));
        } finally {
            setBusy(false);
        }
    };
    const visible = entries
        .filter((entry) => entry.original_path.toLowerCase().includes(query.toLowerCase()))
        .sort((left, right) => {
            const difference =
                sort === 'name'
                    ? left.original_path.split('/').pop()!.localeCompare(right.original_path.split('/').pop()!)
                    : sort === 'size'
                      ? (left.size ?? 0) - (right.size ?? 0)
                      : new Date(left.created_at).getTime() - new Date(right.created_at).getTime();
            return descending ? -difference : difference;
        });
    const sortBy = (column: 'name' | 'size' | 'date') => {
        setDescending(sort === column ? !descending : false);
        setSort(column);
    };
    const selectedEntries = visible.filter((entry) => selected.includes(entry.id));

    const payload = (entry: Entry) => ({
        id: entry.entry_id ?? entry.id,
        ...(entry.relative_path ? { path: entry.relative_path } : {}),
    });
    const refresh = async () => {
        await mutate().catch(() => undefined);
        if (nested) await refreshChildren().catch(() => undefined);
    };

    const prepareRestore = async (entries: Entry[]) => {
        setBusy(true);
        setFailure('');
        try {
            const conflicts: string[] = [];
            for (const entry of entries) {
                const response = await http.post(`${url}/restore`, { ...payload(entry), preview: true });
                if (response.data.conflict) conflicts.push(entry.id);
            }
            setRestoring({ entries, conflicts });
        } catch (error) {
            setFailure(httpErrorToHuman(error));
        } finally {
            setBusy(false);
        }
    };

    const restore = async (replace: boolean) => {
        if (!restoring) return;
        setBusy(true);
        setFailure('');
        try {
            for (const entry of restoring.entries) {
                await http.post(`${url}/restore`, {
                    ...payload(entry),
                    replace: replace && restoring.conflicts.includes(entry.id),
                });
                setSelected((ids) => ids.filter((id) => id !== entry.id));
            }
        } catch (error) {
            setFailure(httpErrorToHuman(error));
        } finally {
            setRestoring(null);
            await refresh();
            setBusy(false);
        }
    };

    const purge = async (entries: Entry[]) => {
        setDeleting(null);
        setBusy(true);
        setFailure('');
        try {
            for (const entry of entries) {
                await http.post(`${url}/delete`, payload(entry));
                setSelected((ids) => ids.filter((id) => id !== entry.id));
            }
        } catch (error) {
            setFailure(httpErrorToHuman(error));
        } finally {
            await refresh();
            setBusy(false);
        }
    };

    return (
        <>
            <Card className='flex flex-col mb-1 mt-2 rounded-b-none! px-2! py-3!'>
                <div className='flex flex-wrap md:flex-nowrap items-center gap-2'>
                    {selectedEntries.length > 0 && (
                        <>
                            <Can action={['file.create', 'file.update', 'file.delete']}>
                                <Tooltip content={t('restore')}>
                                    <Button.Text
                                        aria-label={t('restore')}
                                        disabled={busy}
                                        onClick={() => prepareRestore(selectedEntries)}
                                    >
                                        <FaArrowRotateLeft className='h-5 w-5' />
                                    </Button.Text>
                                </Tooltip>
                            </Can>
                            <Can action='file.delete'>
                                <Tooltip content={t('server/files:delete')}>
                                    <Button.Danger
                                        aria-label={t('server/files:delete')}
                                        disabled={busy}
                                        onClick={() => setDeleting(selectedEntries)}
                                    >
                                        <FaTrash className='h-5 w-5' />
                                    </Button.Danger>
                                </Tooltip>
                            </Can>
                        </>
                    )}
                    {busy && <Spinner size='small' />}
                    <div className='order-2 md:order-0 md:ml-auto flex items-center gap-1 w-full md:w-auto'>
                        <Input
                            type='search'
                            aria-label={t('server/files:search.placeholder')}
                            placeholder={t('server/files:search.placeholder')}
                            value={query}
                            onChange={(event) => {
                                setQuery(event.currentTarget.value);
                                setSelected([]);
                            }}
                        />
                    </div>
                </div>
                <div className='flex flex-wrap md:flex-nowrap items-center gap-2 mt-2'>
                    <div className='order-3 w-full min-w-0 md:order-0 md:flex-1 md:w-auto bg-gray-800 rounded-ui py-2'>
                        <FileManagerBreadcrumbs
                            renderLeft={
                                <FileActionCheckbox
                                    type='checkbox'
                                    className='mx-4 block'
                                    aria-label={t('strings:select_all')}
                                    disabled={busy || visible.length === 0}
                                    checked={
                                        visible.length > 0 && visible.every((entry) => selected.includes(entry.id))
                                    }
                                    onChange={(event) =>
                                        setSelected(event.currentTarget.checked ? visible.map((entry) => entry.id) : [])
                                    }
                                />
                            }
                        />
                    </div>
                </div>
            </Card>
            <Card className='mb-1 rounded-none! px-2! py-2! bg-gray-800! hidden md:block'>
                <div className='flex items-center gap-1 text-xs font-semibold text-gray-300'>
                    <button
                        type='button'
                        onClick={() => sortBy('name')}
                        className='cursor-pointer flex-1 ml-13.75 text-left'
                    >
                        {t('server/files:name')}{' '}
                        {sort === 'name' && (
                            <FaArrowDownAZ className={`inline ml-1 ${descending ? '' : 'rotate-180'}`} />
                        )}
                    </button>
                    <button type='button' onClick={() => sortBy('size')} className='cursor-pointer w-1/6 text-right'>
                        {t('server/files:size')}{' '}
                        {sort === 'size' && (
                            <FaArrowDownAZ className={`inline ml-1 ${descending ? '' : 'rotate-180'}`} />
                        )}
                    </button>
                    <button
                        type='button'
                        onClick={() => sortBy('date')}
                        className='cursor-pointer w-1/5 mr-20 text-right'
                    >
                        {t('server/files:date')}{' '}
                        {sort === 'date' && (
                            <FaArrowDownAZ className={`inline ml-1 ${descending ? '' : 'rotate-180'}`} />
                        )}
                    </button>
                </div>
            </Card>
            <Card className='rounded-t-none! p-3!'>
                {(failure || error || browseError) && (
                    <p role='alert' className='mb-4 text-red-200'>
                        {failure || httpErrorToHuman(error || browseError)}
                    </p>
                )}
                {(error || browseError) && (
                    <Button onClick={() => (nested ? refreshChildren() : mutate())}>{t('retry')}</Button>
                )}
                {((nested && !children && !browseError) || (!nested && !data && !error)) && <Spinner centered />}
                {(nested ? children : data) && visible.length === 0 && (
                    <p className='py-8 text-center text-gray-300'>
                        {t(query ? 'server/files:no-results' : nested ? 'server/files:move-modal.empty' : 'empty')}
                    </p>
                )}
                {visible.map((entry) => (
                    <BinFileRow
                        key={entry.id}
                        entry={entry}
                        selected={selected.includes(entry.id)}
                        busy={busy}
                        onSelect={(checked) =>
                            setSelected((ids) => (checked ? [...ids, entry.id] : ids.filter((id) => id !== entry.id)))
                        }
                        onOpen={() => open(entry)}
                        onDownload={() => download(entry)}
                        onRestore={() => prepareRestore(selected.includes(entry.id) ? selectedEntries : [entry])}
                        onDelete={() => setDeleting(selected.includes(entry.id) ? selectedEntries : [entry])}
                    />
                ))}
            </Card>
            {media &&
                (media.video ? (
                    <VideoViewerModal
                        visible
                        appear
                        onDismissed={() => setMedia(null)}
                        videoUrl={media.url}
                        videoName={media.name}
                    />
                ) : (
                    <ImageViewerModal
                        visible
                        appear
                        onDismissed={() => setMedia(null)}
                        imageUrl={media.url}
                        imageName={media.name}
                    />
                ))}
            {restoring && (
                <Dialog
                    open
                    onClose={() => {
                        if (!busy) setRestoring(null);
                    }}
                    preventExternalClose={busy}
                    title={t(restoring.conflicts.length ? 'replace-title' : 'restore')}
                >
                    {restoring.conflicts.length > 0 && (
                        <p className='mb-4 text-sm text-gray-300'>{t('replace-warning')}</p>
                    )}
                    <ul className='max-h-64 overflow-y-auto'>
                        {restoring.entries.map((entry) => (
                            <li key={entry.id} className='break-all py-1 text-sm text-gray-300'>
                                {entry.original_path}
                            </li>
                        ))}
                    </ul>
                    <Dialog.Footer>
                        <div className='flex w-full flex-wrap justify-end gap-2'>
                            <Button.Text disabled={busy} onClick={() => setRestoring(null)}>
                                {t('cancel')}
                            </Button.Text>
                            {restoring.conflicts.length > 0 ? (
                                <Button.Danger disabled={busy} onClick={() => restore(true)}>
                                    {t('replace')}
                                </Button.Danger>
                            ) : (
                                <Button disabled={busy} onClick={() => restore(false)}>
                                    {t('restore')}
                                </Button>
                            )}
                        </div>
                    </Dialog.Footer>
                </Dialog>
            )}
            {deleting && (
                <DeleteFilesDialog
                    files={deleting.map((entry) => entry.original_path)}
                    permanentOnly
                    onClose={() => setDeleting(null)}
                    onConfirm={() => purge(deleting)}
                />
            )}
        </>
    );
}

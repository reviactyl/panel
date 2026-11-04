import { DocumentIcon, FolderIcon } from '@heroicons/react/solid';
import { dirname } from 'pathe';
import { NavLink } from 'react-router-dom';
import { FileObject } from '@/api/server/files/loadDirectory';
import { isImageFile } from '@/api/server/files/isImageFile';
import { isVideoFile } from '@/api/server/files/isVideoFile';
import { encodePathSegments } from '@/helpers';
import { bytesToString } from '@/lib/formatters';
import { usePermissions } from '@/plugins/usePermissions';

export type SearchResult = FileObject & { fullPath: string };

interface Props {
    file: SearchResult;
    serverId: string;
    onMediaClick: (file: FileObject, filePath: string) => void;
    onNavigate: () => void;
}

export default function RecursiveFileRow({ file, serverId, onMediaClick, onNavigate }: Props) {
    const [canRead] = usePermissions(['file.read']);
    const [canReadContents] = usePermissions(['file.read-content']);
    const media = isImageFile(file) || isVideoFile(file);
    const editable = file.isFile && !media && file.isEditable();
    const canNavigate = editable ? canReadContents : canRead;
    const path = file.isFile && !editable ? dirname(file.fullPath) : file.fullPath;
    const className = 'flex-1 text-left text-sm text-gray-200 truncate hover:text-primary-400 min-w-0';
    const Icon = file.isFile ? DocumentIcon : FolderIcon;

    return (
        <div className='flex items-center py-2 px-2 border-b border-gray-800 last:border-0 hover:bg-gray-900 rounded-ui'>
            <Icon className='w-4 h-4 mr-3 flex-none text-gray-400' aria-hidden='true' />
            {media && canReadContents ? (
                <button type='button' className={className} onClick={() => onMediaClick(file, file.fullPath)}>
                    {file.fullPath}
                </button>
            ) : canNavigate ? (
                <NavLink
                    to={`/server/${serverId}/files${editable ? '/edit' : ''}#${encodePathSegments(path)}`}
                    className={className}
                    onClick={(event) => {
                        if (
                            !event.ctrlKey &&
                            !event.metaKey &&
                            !event.shiftKey &&
                            !event.altKey &&
                            event.button === 0
                        ) {
                            onNavigate();
                        }
                    }}
                >
                    {file.fullPath}
                </NavLink>
            ) : (
                <span className={className}>{file.fullPath}</span>
            )}
            {file.isFile && <span className='text-xs text-muted ml-3 flex-none'>{bytesToString(file.size)}</span>}
        </div>
    );
}

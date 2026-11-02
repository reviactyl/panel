import { FaHouse, FaFolder, FaCheck } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';
import { ServerCategory } from '@/api/account/getServerCategories';
import Button from '@/reviactyl/elements/Button';
import { folderTree } from './folderTree';

interface Props {
    folders: ServerCategory[];
    current: string;
    selected: string;
    onSelect: (uuid: string) => void;
    excluded?: Set<string>;
    disabled?: boolean;
}

export default ({ folders, current, selected, onSelect, excluded, disabled }: Props) => {
    const { t } = useTranslation('dashboard/index');
    const rows = [
        { uuid: '', name: t('folders.home'), depth: 0, path: t('folders.home'), color: undefined },
        ...folderTree(folders)
            .filter(({ folder }) => !excluded?.has(folder.uuid))
            .map(({ folder, depth, path }) => ({ ...folder, depth: depth + 1, path })),
    ];
    return (
        <div
            className='max-h-72 overflow-y-auto overscroll-contain rounded-ui bg-gray-800 p-2'
            aria-label={t('folders.destination')}
        >
            {rows.map((folder) => (
                <Button
                    key={folder.uuid}
                    type='button'
                    isSecondary
                    disabled={disabled}
                    aria-pressed={selected === folder.uuid}
                    onClick={() => onSelect(folder.uuid)}
                    title={folder.path}
                    className={`[&>span]:flex [&>span]:w-full [&>span]:items-center [&>span]:gap-3 flex w-full items-center gap-3 rounded-ui px-3 py-3 text-left text-gray-100 focus-visible:outline-2 focus-visible:outline-primary-500 ${selected === folder.uuid ? 'bg-gray-700' : 'bg-transparent hover:bg-gray-700/60'}`}
                >
                    <span
                        style={{
                            marginInlineStart: `${Math.min(folder.depth, 6) * 12}px`,
                            color: folder.color || undefined,
                        }}
                        className='shrink-0'
                    >
                        {folder.uuid ? <FaFolder aria-hidden='true' /> : <FaHouse aria-hidden='true' />}
                    </span>
                    <span className='min-w-0 flex-1 break-words'>{folder.name}</span>
                    {current === folder.uuid && (
                        <span className='shrink-0 rounded bg-gray-600 px-2 py-1 text-xs'>{t('folders.current')}</span>
                    )}
                    {selected === folder.uuid && <FaCheck className='shrink-0' aria-hidden='true' />}
                </Button>
            ))}
        </div>
    );
};

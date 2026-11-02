import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ServerCategory } from '@/api/account/getServerCategories';
import updateServerCategory from '@/api/account/updateServerCategory';
import { Dialog } from '@/reviactyl/elements/dialog';
import Button from '@/reviactyl/elements/Button';
import useFlash from '@/plugins/useFlash';
import FlashMessageRender from '@/components/FlashMessageRender';
import FolderDestinationPicker from './FolderDestinationPicker';
import { descendantUuids } from './folderTree';

export default ({
    folder,
    folders,
    onDismissed,
    onSaved,
}: {
    folder: ServerCategory;
    folders: ServerCategory[];
    onDismissed: () => void;
    onSaved: () => Promise<unknown>;
}) => {
    const { t } = useTranslation('dashboard/index');
    const [destination, setDestination] = useState(folder.parentUuid || '');
    const [saving, setSaving] = useState(false);
    const { clearAndAddHttpError, clearFlashes } = useFlash();
    useEffect(() => {
        clearFlashes('move-folder');
    }, [clearFlashes]);
    const submit = async () => {
        clearFlashes('move-folder');
        setSaving(true);
        try {
            await updateServerCategory(folder.uuid, { parentUuid: destination || null });
        } catch (error) {
            clearAndAddHttpError({ key: 'move-folder', error });
            setSaving(false);
            return;
        }
        setSaving(false);
        onDismissed();
        try {
            await onSaved();
        } catch {
            clearAndAddHttpError({ key: 'dashboard', error: new Error(t('folders.refresh-error')) });
        }
    };
    return (
        <Dialog
            open
            hideCloseIcon
            preventExternalClose={saving}
            onClose={() => {
                if (!saving) onDismissed();
            }}
            title={t('folders.move-folder')}
        >
            <p className='mb-4 break-words text-gray-300'>{folder.name}</p>
            <FlashMessageRender byKey='move-folder' />
            <FolderDestinationPicker
                folders={folders}
                current={folder.parentUuid || ''}
                selected={destination}
                onSelect={setDestination}
                excluded={descendantUuids(folders, folder.uuid)}
                disabled={saving}
            />
            <Dialog.Footer>
                <Button disabled={saving} isSecondary onClick={onDismissed}>
                    {t('folders.close')}
                </Button>
                <Button
                    isLoading={saving}
                    disabled={saving || destination === (folder.parentUuid || '')}
                    onClick={submit}
                >
                    {t('folders.confirm-move')}
                </Button>
            </Dialog.Footer>
        </Dialog>
    );
};

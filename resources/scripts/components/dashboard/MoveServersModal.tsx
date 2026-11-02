import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ServerCategory } from '@/api/account/getServerCategories';
import { Server } from '@/api/server/getServer';
import updateServerCategory from '@/api/server/updateServerCategory';
import { Dialog } from '@/reviactyl/elements/dialog';
import Button from '@/reviactyl/elements/Button';
import Spinner from '@/reviactyl/elements/Spinner';
import useFlash from '@/plugins/useFlash';
import FlashMessageRender from '@/components/FlashMessageRender';
import FolderDestinationPicker from './FolderDestinationPicker';

interface Props {
    categories?: ServerCategory[];
    server: Server;
    onDismissed: () => void;
    onSaved: () => Promise<unknown>;
}

export default ({ categories, server, onDismissed, onSaved }: Props) => {
    const { t } = useTranslation('dashboard/index');
    const current = server.category?.uuid || '';
    const [destination, setDestination] = useState(current);
    const [saving, setSaving] = useState(false);
    const { clearAndAddHttpError, clearFlashes } = useFlash();
    useEffect(() => {
        clearFlashes('folder-move');
    }, [clearFlashes]);
    const submit = async () => {
        setSaving(true);
        clearFlashes('folder-move');
        try {
            await updateServerCategory(server.uuid, destination || null);
        } catch (error) {
            clearAndAddHttpError({ key: 'folder-move', error });
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
            title={t('folders.move-server')}
        >
            <FlashMessageRender byKey='folder-move' />
            {categories ? (
                <FolderDestinationPicker
                    folders={categories}
                    current={current}
                    selected={destination}
                    onSelect={setDestination}
                    disabled={saving}
                />
            ) : (
                <Spinner centered />
            )}
            <Dialog.Footer>
                <Button disabled={saving} isSecondary onClick={onDismissed}>
                    {t('folders.close')}
                </Button>
                <Button isLoading={saving} disabled={saving || !categories || destination === current} onClick={submit}>
                    {t('folders.confirm-move')}
                </Button>
            </Dialog.Footer>
        </Dialog>
    );
};

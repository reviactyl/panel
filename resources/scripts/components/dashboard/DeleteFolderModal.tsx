import { useEffect, useState } from 'react';
import { Dialog } from '@/reviactyl/elements/dialog';
import Button from '@/reviactyl/elements/Button';
import { ServerCategory } from '@/api/account/getServerCategories';
import deleteServerCategory from '@/api/account/deleteServerCategory';
import useFlash from '@/plugins/useFlash';
import FlashMessageRender from '@/components/FlashMessageRender';
import { useTranslation } from 'react-i18next';

export default ({
    folder,
    onDismissed,
    onDeleted,
    folders = true,
}: {
    folder: ServerCategory;
    onDismissed: () => void;
    onDeleted: () => Promise<unknown>;
    folders?: boolean;
}) => {
    const { t } = useTranslation(['dashboard/index', 'strings']);
    const [loading, setLoading] = useState(false);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    useEffect(() => {
        clearFlashes('folder-delete');
    }, [clearFlashes]);
    const remove = async () => {
        setLoading(true);
        clearFlashes('folder-delete');
        try {
            await deleteServerCategory(folder.uuid);
        } catch (error) {
            clearAndAddHttpError({ key: 'folder-delete', error });
            setLoading(false);
            return;
        }
        setLoading(false);
        onDismissed();
        try {
            await onDeleted();
        } catch (error) {
            clearAndAddHttpError({ key: 'dashboard', error });
        }
    };
    return (
        <Dialog
            open
            hideCloseIcon
            preventExternalClose={loading}
            onClose={() => {
                if (!loading) onDismissed();
            }}
            title={folders ? t('folders.delete-title') : `${t('categories.delete')}: ${folder.name}`}
        >
            <FlashMessageRender byKey='folder-delete' />
            {folders ? (
                <>
                    {t('folders.delete-message-start')}&nbsp;
                    <span className='font-semibold text-gray-50'>{folder.name}</span> {t('folders.delete-message-end')}
                    <p className='mt-2 text-gray-300'>{t('folders.delete-consequences')}</p>
                </>
            ) : (
                <p className='text-gray-300'>{t('categories.delete-confirm')}</p>
            )}
            <Dialog.Footer>
                <Button isSecondary disabled={loading} onClick={onDismissed}>
                    {t('cancel', { ns: 'strings' })}
                </Button>
                <Button color='red' isLoading={loading} disabled={loading} onClick={remove}>
                    {t('categories.delete')}
                </Button>
            </Dialog.Footer>
        </Dialog>
    );
};

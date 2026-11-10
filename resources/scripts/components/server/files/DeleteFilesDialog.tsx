import { useStoreState } from '@/state/hooks';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Dialog } from '@/reviactyl/elements/dialog';
import { Button } from '@/reviactyl/components/button';

export default function DeleteFilesDialog({
    files,
    onClose,
    onConfirm,
    permanentOnly = false,
}: {
    files: string[];
    onClose: () => void;
    onConfirm: (permanent: boolean) => void;
    permanentOnly?: boolean;
}) {
    const { t } = useTranslation(['server/recycle', 'server/files']);
    const retentionDays = useStoreState((state) => state.settings?.data?.recycleBinRetentionDays ?? 31);
    const [permanent, setPermanent] = useState(false);
    return (
        <Dialog open onClose={onClose} title={t(permanent ? 'permanent' : 'delete')}>
            <p className='mb-4'>
                {t(permanent ? 'irreversible' : permanentOnly ? 'delete-question' : 'delete-message', {
                    days: retentionDays,
                })}
            </p>
            <ul className='max-h-48 overflow-y-auto break-all list-disc pl-5'>
                {files.map((file) => (
                    <li key={file}>{file}</li>
                ))}
            </ul>
            <Dialog.Footer>
                <div className='flex w-full flex-wrap justify-end gap-2'>
                    <Button.Text onClick={permanent ? () => setPermanent(false) : onClose}>
                        {t(permanent ? 'back' : 'cancel')}
                    </Button.Text>
                    {permanent ? (
                        <Button.Danger onClick={() => onConfirm(true)}>{t('permanent')}</Button.Danger>
                    ) : (
                        <>
                            {!permanentOnly && (
                                <Button onClick={() => onConfirm(false)}>{t('server/files:delete')}</Button>
                            )}
                            <Button.Danger onClick={() => setPermanent(true)}>{t('permanent')}</Button.Danger>
                        </>
                    )}
                </div>
            </Dialog.Footer>
        </Dialog>
    );
}

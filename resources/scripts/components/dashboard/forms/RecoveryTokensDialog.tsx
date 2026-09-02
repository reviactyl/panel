import { Dialog, DialogProps } from '@/reviactyl/elements/dialog';
import { Button } from '@/reviactyl/components/button/index';
import CopyOnClick from '@/reviactyl/elements/CopyOnClick';
import { Alert } from '@/reviactyl/elements/alert';
import { useTranslation } from 'react-i18next';

interface RecoveryTokenDialogProps extends DialogProps {
    tokens: string[];
}

export default ({ tokens, open, onClose }: RecoveryTokenDialogProps) => {
    const { t } = useTranslation('dashboard/account');
    const grouped = [] as [string, string][];
    tokens.forEach((token, index) => {
        if (index % 2 === 0) {
            grouped.push([token, tokens[index + 1] || '']);
        }
    });

    return (
        <Dialog
            open={open}
            onClose={onClose}
            title={t('2fa.recovery.title')}
            description={t('2fa.recovery.description')}
            hideCloseIcon
            preventExternalClose
        >
            <Dialog.Icon position={'container'} type={'success'} />
            <CopyOnClick text={tokens.join('\n')} showInNotification={false}>
                <pre className={'bg-gray-900 rounded p-2 mt-6'}>
                    {grouped.map((value) => (
                        <span key={value.join('_')} className={'block'}>
                            {value[0]}
                            <span className={'mx-2 selection:bg-gray-900'}>&nbsp;</span>
                            {value[1]}
                            <span className={'selection:bg-gray-900'}>&nbsp;</span>
                        </span>
                    ))}
                </pre>
            </CopyOnClick>
            <Alert type={'danger'} className={'mt-3'}>
                {t('2fa.recovery.alert')}
            </Alert>
            <Dialog.Footer>
                <Button.Text onClick={onClose}>{t('2fa.recovery.done')}</Button.Text>
            </Dialog.Footer>
        </Dialog>
    );
};

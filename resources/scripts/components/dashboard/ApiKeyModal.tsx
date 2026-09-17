import { useContext } from 'react';
import Button from '@/reviactyl/elements/Button';
import asModal from '@/hoc/asModal';
import ModalContext from '@/context/ModalContext';
import CopyOnClick from '@/reviactyl/elements/CopyOnClick';
import { useTranslation } from 'react-i18next';

interface Props {
    apiKey: string;
}

const ApiKeyModal = ({ apiKey }: Props) => {
    const { t } = useTranslation('dashboard/account');
    const { dismiss } = useContext(ModalContext);

    return (
        <>
            <h3 className='mb-6 text-2xl'>{t('api.modal.your-api-key')}</h3>
            <p className='mb-6 text-sm'>{t('api.modal.message')}</p>
            <pre className='rounded-ui border border-gray-800 bg-gray-900 px-4 py-2 font-mono text-sm'>
                <CopyOnClick text={apiKey}>
                    <code className='font-mono'>{apiKey}</code>
                </CopyOnClick>
            </pre>
            <div className='mt-6 flex justify-end'>
                <Button type={'button'} onClick={() => dismiss()}>
                    {t('api.modal.close')}
                </Button>
            </div>
        </>
    );
};

ApiKeyModal.displayName = 'ApiKeyModal';

export default asModal<Props>({
    closeOnEscape: false,
    closeOnBackground: false,
})(ApiKeyModal);

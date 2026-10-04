import { useState } from 'react';
import classNames from 'classnames';
import { FaFileExport } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';
import Modal from '@/reviactyl/elements/Modal';
import Button from '@/reviactyl/elements/Button';
import Switch from '@/reviactyl/elements/Switch';
import { ServerContext } from '@/state/server';
import { ServerDatabase } from '@/api/server/databases/getServerDatabases';
import useFlash from '@/plugins/useFlash';

interface Props {
    database: ServerDatabase;
    visible: boolean;
    onDismissed: () => void;
}

export default ({ database, visible, onDismissed }: Props) => {
    const { t } = useTranslation('server/databases');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useFlash();
    const [compress, setCompress] = useState(false);
    const [format, setFormat] = useState<'gz' | 'zip'>('gz');

    const download = () => {
        clearFlashes('databases');

        const frame = document.createElement('iframe');
        frame.style.display = 'none';
        frame.onload = () => {
            let message: string | null = null;
            try {
                message = JSON.parse(frame.contentDocument?.body?.textContent || '').errors[0].detail;
                message = message || t('export-error');
            } catch {
                message = null;
            }

            if (message) {
                addError({ key: 'databases', message });
            }

            frame.remove();
        };
        setTimeout(() => frame.remove(), 5 * 60 * 1000);
        frame.src = `/api/client/servers/${uuid}/databases/${database.id}/export${compress ? `?compress=${format}` : ''}`;
        document.body.appendChild(frame);

        onDismissed();
    };

    return (
        <Modal visible={visible} onDismissed={onDismissed}>
            <h2 className='text-2xl'>{t('export-title')}</h2>
            <p className='mt-2 text-sm text-gray-300'>{t('export-description', { name: database.name })}</p>
            <div className='mt-6 rounded-ui border border-gray-800 bg-gray-900 p-4'>
                <Switch
                    name='compress'
                    label={t('export-compress')}
                    description={t('export-compress-description')}
                    defaultChecked={compress}
                    onChange={(e) => setCompress(e.target.checked)}
                />
                {compress && (
                    <div className='mt-4 border-t border-gray-800 pt-4'>
                        <p className='mb-2 text-sm text-gray-200' id='export_format_label'>
                            {t('export-format')}
                        </p>
                        <div className='grid grid-cols-2 gap-2' role='radiogroup' aria-labelledby='export_format_label'>
                            {(['gz', 'zip'] as const).map((option) => (
                                <button
                                    key={option}
                                    type='button'
                                    role='radio'
                                    aria-checked={format === option}
                                    className={classNames(
                                        'rounded-ui border px-4 py-2 text-sm font-semibold transition-colors duration-150',
                                        format === option
                                            ? 'border-reviactyl bg-reviactyl/10 text-gray-100'
                                            : 'border-gray-700 text-gray-300 hover:border-gray-500',
                                    )}
                                    onClick={() => setFormat(option)}
                                >
                                    .sql.{option}
                                </button>
                            ))}
                        </div>
                    </div>
                )}
            </div>
            <div className='mt-6 flex flex-wrap justify-end'>
                <Button type='button' isSecondary className='w-full sm:mr-2 sm:w-auto' onClick={onDismissed}>
                    {t('cancel')}
                </Button>
                <Button type='button' className='mt-4 w-full sm:mt-0 sm:w-auto' onClick={download}>
                    <FaFileExport className='mr-2 inline-block w-[1.25em]' />
                    {t('export-download')}
                </Button>
            </div>
        </Modal>
    );
};

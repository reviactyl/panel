import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { useTranslation } from 'react-i18next';
import { FaTriangleExclamation } from 'react-icons/fa6';

const MaintenanceAlert = () => {
    const { t } = useTranslation('strings');
    const isUnderMaintenance = useStoreState((state: ApplicationStore) => state.designify.data!.isUnderMaintenance);
    return (
        <>
            {isUnderMaintenance ? (
                <div className='px-2'>
                    <div className='mx-auto mt-2 flex w-full max-w-[1200px] items-center gap-x-3 rounded-ui border border-yellow-500/60 bg-yellow-800/10 p-3 text-gray-100'>
                        <div>
                            <FaTriangleExclamation className='h-5 w-5 font-bold !text-yellow-500' />
                        </div>
                        <div>
                            <b>{t('under_maintenance')}</b> {t('maintenance-mode-warning')}
                        </div>
                    </div>
                </div>
            ) : (
                ''
            )}
        </>
    );
};

export default MaintenanceAlert;

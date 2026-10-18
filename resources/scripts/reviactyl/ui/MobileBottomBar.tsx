import { NavLink } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { FaBars, FaHouse, FaUser, FaXmark } from 'react-icons/fa6';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';

interface Props {
    onShowSidebar: () => void;
    sidebarOpen: boolean;
}

const itemClass = ({ isActive }: { isActive: boolean }) =>
    `flex flex-col items-center justify-center gap-1 text-[11px] font-medium transition-colors ${
        isActive ? 'text-reviactyl' : 'text-gray-400 hover:text-gray-100'
    }`;

const MobileBottomBar = ({ onShowSidebar, sidebarOpen }: Props) => {
    const { t } = useTranslation('dashboard/account');
    const { session } = useSubuserPreview();

    return (
        <nav
            aria-label={t('mobile-navigation.navigation')}
            className='fixed inset-x-0 bottom-0 z-50 border-t border-gray-800 bg-gray-950/95 px-3 pt-2 pb-[max(env(safe-area-inset-bottom),0.5rem)] shadow-[0_-8px_24px_rgba(0,0,0,0.2)] backdrop-blur-lg lg:hidden'
        >
            <div className='mx-auto grid h-14 max-w-lg grid-cols-3 items-center'>
                <NavLink to='/' end className={itemClass}>
                    <FaHouse className='h-5 w-5' aria-hidden='true' />
                    <span>{t('mobile-navigation.home')}</span>
                </NavLink>
                <button
                    type='button'
                    onClick={onShowSidebar}
                    aria-label={
                        sidebarOpen ? t('mobile-navigation.close-sidebar') : t('mobile-navigation.show-sidebar')
                    }
                    aria-expanded={sidebarOpen}
                    className='-mt-5 flex flex-col items-center justify-center gap-1 text-[11px] font-semibold text-gray-100'
                >
                    <span className='flex h-12 w-12 items-center justify-center rounded-full bg-reviactyl text-white shadow-lg ring-4 ring-gray-950 transition-transform active:scale-95'>
                        {sidebarOpen ? <FaXmark className='h-5 w-5' /> : <FaBars className='h-5 w-5' />}
                    </span>
                    <span>{sidebarOpen ? t('mobile-navigation.close') : t('mobile-navigation.menu')}</span>
                </button>
                {session ? (
                    <button
                        type='button'
                        disabled
                        aria-label={t('mobile-navigation.account')}
                        className='flex cursor-not-allowed flex-col items-center justify-center gap-1 text-[11px] font-medium text-gray-600 opacity-40 grayscale'
                    >
                        <FaUser className='h-5 w-5' aria-hidden='true' />
                        <span>{t('mobile-navigation.account')}</span>
                    </button>
                ) : (
                    <NavLink to='/account' className={itemClass}>
                        <FaUser className='h-5 w-5' aria-hidden='true' />
                        <span>{t('mobile-navigation.account')}</span>
                    </NavLink>
                )}
            </div>
        </nav>
    );
};

export default MobileBottomBar;

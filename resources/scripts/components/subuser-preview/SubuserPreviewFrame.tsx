import { PropsWithChildren, useState } from 'react';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';
import { FaEye, FaTriangleExclamation } from 'react-icons/fa6';
import { Button } from '@/reviactyl/components/button';
import Spinner from '@/reviactyl/elements/Spinner';
import { useTranslation } from 'react-i18next';

export const SubuserPreviewFrame = ({ children }: PropsWithChildren) => {
    const { t } = useTranslation('server/users');
    const { loading, session, exit } = useSubuserPreview();
    const [exiting, setExiting] = useState(false);

    const onExit = async () => {
        if (!session || exiting) return;

        setExiting(true);
        const destination = `/server/${session.serverIdentifier}/users`;
        await exit();
        window.location.assign(destination);
    };

    if (loading) {
        return (
            <div className='flex min-h-screen items-center justify-center bg-gray-950'>
                <Spinner size='large' />
            </div>
        );
    }

    if (!session) return children;

    return (
        <div className='min-h-screen bg-gray-950'>
            <div aria-hidden='true' className='pointer-events-none fixed inset-0 z-[70] border-2 border-amber-500/80' />
            <aside
                aria-label={t('preview.session-label')}
                className='fixed inset-x-0 top-0 z-[60] flex h-16 items-center border-b border-amber-500 bg-gray-950 px-3 text-gray-100 shadow-lg sm:px-5'
            >
                <div className='flex min-w-0 flex-1 items-center gap-3'>
                    <span className='flex h-9 w-9 shrink-0 items-center justify-center rounded-ui bg-amber-500/15 text-amber-400'>
                        <FaEye className='h-5 w-5' />
                    </span>
                    <div className='min-w-0'>
                        <div className='flex items-center gap-2'>
                            <p className='truncate text-sm font-semibold sm:text-base'>{t('preview.title')}</p>
                            <span className='hidden rounded-ui border border-gray-700 px-2 py-0.5 text-xs text-gray-300 md:inline'>
                                {t('preview.permission-count', { count: session.permissionCount })}
                            </span>
                        </div>
                        <p className='truncate text-xs text-gray-300'>{session.subuserEmail}</p>
                    </div>
                </div>

                <div className='hidden items-center gap-2 px-4 text-sm text-amber-300 lg:flex'>
                    <FaTriangleExclamation className='h-4 w-4' />
                    {t('preview.safety-message')}
                </div>

                <Button.Text
                    size={Button.Sizes.Small}
                    disabled={exiting}
                    onClick={onExit}
                    className='shrink-0 border border-amber-500/40 !text-amber-200 hover:!bg-amber-500/10'
                >
                    {exiting ? t('preview.exiting') : t('preview.exit')}
                </Button.Text>
            </aside>
            <div className='min-h-screen pt-16'>{children}</div>
        </div>
    );
};

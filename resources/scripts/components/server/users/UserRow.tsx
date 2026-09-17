import { useState } from 'react';
import { Subuser } from '@/state/server/subusers';
import { FaPen, FaUnlock, FaUserLock } from 'react-icons/fa6';
import RemoveSubuserButton from '@/components/server/users/RemoveSubuserButton';
import EditSubuserModal from '@/components/server/users/EditSubuserModal';
import Can from '@/reviactyl/elements/Can';
import { useStoreState } from 'easy-peasy';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import { useTranslation } from 'react-i18next';
import PreviewSubuserButton from '@/components/server/users/PreviewSubuserButton';
import { ServerContext } from '@/state/server';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';

interface Props {
    subuser: Subuser;
}

export default ({ subuser }: Props) => {
    const accountUuid = useStoreState((state) => state.user!.data!.uuid);
    const { session } = useSubuserPreview();
    const uuid = session?.subuserUuid ?? accountUuid;
    const isServerOwner = ServerContext.useStoreState((state) => state.server.data?.isOwner === true);
    const [visible, setVisible] = useState(false);
    const { t } = useTranslation('server/users');

    return (
        <GreyRowBox className='mb-2'>
            <EditSubuserModal subuser={subuser} visible={visible} onModalDismissed={() => setVisible(false)} />
            <div className='hidden h-10 w-10 overflow-hidden rounded-full border-2 border-gray-900 bg-white md:block'>
                <img className='h-full w-full' src={`${subuser.image}?s=400`} />
            </div>
            <div className='ml-4 flex-1 overflow-hidden'>
                <p className='truncate text-sm'>{subuser.email}</p>
            </div>
            <div className='ml-4'>
                <p className='text-center font-medium'>
                    &nbsp;
                    {subuser.twoFactorEnabled ? (
                        <FaUserLock className={'inline-block w-[1.25em]'} />
                    ) : (
                        <FaUnlock className={'inline-block w-[1.25em] text-red-400'} />
                    )}
                    &nbsp;
                </p>
                <p className='hidden text-2xs uppercase text-muted md:block'>{t('two-factor-enabled')}</p>
            </div>
            <div className='ml-4 hidden md:block'>
                <p className='text-center font-medium'>
                    {subuser.permissions.filter((permission) => permission !== 'websocket.connect').length}
                </p>
                <p className='text-2xs uppercase text-muted'>{t('permissions-label')}</p>
            </div>
            {subuser.uuid !== uuid && (
                <>
                    {isServerOwner && <PreviewSubuserButton subuser={subuser} />}
                    <Can action={'user.update'}>
                        <button
                            type={'button'}
                            aria-label={t('edit-subuser')}
                            className='mx-4 block p-1 text-sm text-gray-600 transition-colors duration-150 hover:text-gray-100 md:p-2'
                            onClick={() => setVisible(true)}
                        >
                            <FaPen />
                        </button>
                    </Can>
                    <Can action={'user.delete'}>
                        <RemoveSubuserButton subuser={subuser} />
                    </Can>
                </>
            )}
        </GreyRowBox>
    );
};

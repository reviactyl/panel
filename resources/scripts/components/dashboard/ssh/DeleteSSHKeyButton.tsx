import tw from 'twin.macro';
import { FaTrash } from 'react-icons/fa6';
import { useState } from 'react';
import { useFlashKey } from '@/plugins/useFlash';
import { deleteSSHKey, useSSHKeys } from '@/api/account/ssh-keys';
import { Dialog } from '@/components/elements/dialog';
import Code from '@/components/elements/Code';
import { useTranslation } from 'react-i18next';

export default ({ name, fingerprint }: { name: string; fingerprint: string }) => {
    const { t } = useTranslation('dashboard/account');
    const { clearAndAddHttpError } = useFlashKey('account');
    const [visible, setVisible] = useState(false);
    const { mutate } = useSSHKeys();

    const onClick = () => {
        clearAndAddHttpError();

        Promise.all([
            mutate((data) => data?.filter((value) => value.fingerprint !== fingerprint), false),
            deleteSSHKey(fingerprint),
        ]).catch((error) => {
            mutate(undefined, true).catch(console.error);
            clearAndAddHttpError(error);
        });
    };

    return (
        <>
            <Dialog.Confirm
                open={visible}
                title={t('ssh.delete.delete-ssh-key')}
                confirm={t('ssh.delete.delete-key')}
                onConfirmed={onClick}
                onClose={() => setVisible(false)}
            >
                {t('ssh.delete.info')} <Code>{name}</Code>
            </Dialog.Confirm>
            <button css={tw`ml-4 p-2 text-sm`} onClick={() => setVisible(true)}>
                <FaTrash className={'text-neutral-400 hover:text-red-400 transition-colors duration-150'} />
            </button>
        </>
    );
};

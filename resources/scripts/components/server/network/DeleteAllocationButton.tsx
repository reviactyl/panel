import { useState } from 'react';
import { FaTrash } from 'react-icons/fa6';
import tw from 'twin.macro';
import Icon from '@/reviactyl/elements/Icon';
import { ServerContext } from '@/state/server';
import deleteServerAllocation from '@/api/server/network/deleteServerAllocation';
import getServerAllocations from '@/api/swr/getServerAllocations';
import { useFlashKey } from '@/plugins/useFlash';
import { Dialog } from '@/reviactyl/elements/dialog';
import { Button } from '@/reviactyl/components/button/index';
import { useTranslation } from 'react-i18next';

interface Props {
    allocation: number;
}

const DeleteAllocationButton = ({ allocation }: Props) => {
    const { t } = useTranslation('server/network');
    const [confirm, setConfirm] = useState(false);

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const setServerFromState = ServerContext.useStoreActions((actions) => actions.server.setServerFromState);

    const { mutate } = getServerAllocations();
    const { clearFlashes, clearAndAddHttpError } = useFlashKey('server:network');

    const deleteAllocation = () => {
        clearFlashes();

        mutate((data) => data?.filter((a) => a.id !== allocation), false);
        setServerFromState((s) => ({ ...s, allocations: s.allocations.filter((a) => a.id !== allocation) }));

        deleteServerAllocation(uuid, allocation).catch((error) => {
            clearAndAddHttpError(error);
            mutate();
        });
    };

    return (
        <>
            <Dialog.Confirm
                open={confirm}
                onClose={() => setConfirm(false)}
                title={t('delete-allocation-title')}
                confirm={t('delete-allocation-confirm')}
                onConfirmed={deleteAllocation}
            >
                {t('delete-allocation-message')}
            </Dialog.Confirm>
            <Button.Danger
                variant={Button.Variants.Secondary}
                size={Button.Sizes.Small}
                shape={Button.Shapes.IconSquare}
                type={'button'}
                onClick={() => setConfirm(true)}
            >
                <Icon icon={FaTrash} css={tw`w-3 h-auto`} />
            </Button.Danger>
        </>
    );
};

export default DeleteAllocationButton;

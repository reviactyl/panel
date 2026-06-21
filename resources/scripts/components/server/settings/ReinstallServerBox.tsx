import { useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import reinstallServer from '@/api/server/reinstallServer';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import tw from 'twin.macro';
import { Button } from '@/reviactyl/components/button/index';
import { Dialog } from '@/reviactyl/elements/dialog';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('server/settings');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [modalVisible, setModalVisible] = useState(false);
    const { addFlash, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const reinstall = () => {
        clearFlashes('settings');
        reinstallServer(uuid)
            .then(() => {
                addFlash({
                    key: 'settings',
                    type: 'success',
                    message: 'Your server has begun the reinstallation process.',
                });
            })
            .catch((error) => {
                console.error(error);

                addFlash({ key: 'settings', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setModalVisible(false));
    };

    useEffect(() => {
        clearFlashes();
    }, []);

    return (
        <TitledGreyBox title={t('reinstall.title')} css={tw`relative`}>
            <Dialog.Confirm
                open={modalVisible}
                title={t('reinstall.confirm-title')}
                confirm={t('reinstall.confirm')}
                onClose={() => setModalVisible(false)}
                onConfirmed={reinstall}
            >
                {t('reinstall.info')}
            </Dialog.Confirm>
            <p css={tw`text-sm`}>
                {t('reinstall.info-1')}&nbsp;
                <strong css={tw`font-medium`}>{t('reinstall.info-2')}</strong>
            </p>
            <div css={tw`mt-6 text-right`}>
                <Button.Danger variant={Button.Variants.Secondary} onClick={() => setModalVisible(true)}>
                    {t('reinstall.button')}
                </Button.Danger>
            </div>
        </TitledGreyBox>
    );
};

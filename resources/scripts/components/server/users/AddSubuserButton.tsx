import { useState } from 'react';
import EditSubuserModal from '@/components/server/users/EditSubuserModal';
import { Button } from '@/reviactyl/elements/button/index';
import { useTranslation } from 'react-i18next';

export default () => {
    const [visible, setVisible] = useState(false);
    const { t } = useTranslation('server/users');

    return (
        <>
            <EditSubuserModal visible={visible} onModalDismissed={() => setVisible(false)} />
            <Button onClick={() => setVisible(true)}>{t('new-user')}</Button>
        </>
    );
};

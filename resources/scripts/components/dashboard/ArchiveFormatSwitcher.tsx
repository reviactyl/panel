import { useState } from 'react';
import { useStoreActions, useStoreState } from 'easy-peasy';
import { useTranslation } from 'react-i18next';
import Select from '@/reviactyl/elements/Select';
import useFlash from '@/plugins/useFlash';
import updateArchiveFormat, { ArchiveFormat } from '@/api/account/updateArchiveFormat';

export default () => {
    const { t } = useTranslation('dashboard/account');
    const format = useStoreState((state) => state.user.data!.archiveFormat);
    const updateUserData = useStoreActions((actions) => actions.user.updateUserData);
    const { clearAndAddHttpError } = useFlash();
    const [saving, setSaving] = useState(false);

    const save = (next: ArchiveFormat) => {
        setSaving(true);
        updateArchiveFormat(next)
            .then(() => updateUserData({ archiveFormat: next }))
            .catch((error) => clearAndAddHttpError({ error }))
            .finally(() => setSaving(false));
    };

    return (
        <div className='mb-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between'>
            <div className='min-w-0 flex-1'>
                <label htmlFor='account-archive-format'>{t('overview.archive-format')}</label>
                <p className='text-xs text-gray-400'>{t('overview.archive-format-description')}</p>
            </div>
            <Select
                id='account-archive-format'
                className='!pr-15 w-full min-w-0 sm:!w-auto'
                value={format}
                disabled={saving}
                onChange={(event) => save(event.target.value as ArchiveFormat)}
            >
                <option value='tar.gz'>tar.gz</option>
                <option value='zip'>zip</option>
            </Select>
        </div>
    );
};

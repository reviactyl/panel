import { useState } from 'react';
import { useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';
import Input from '@/reviactyl/elements/Input';
import Label from '@/reviactyl/elements/Label';

export const isValidFileMode = (mode: string) => /^[0-7]{3,4}$/.test(mode);

const subjects = ['owner', 'group', 'others'] as const;
const permissions = [
    { name: 'read', bit: 4 },
    { name: 'write', bit: 2 },
    { name: 'execute', bit: 1 },
] as const;

export default function FileModeInput() {
    const { t } = useTranslation('server/files');
    const { values, setFieldValue, handleBlur, touched, submitCount, isSubmitting } = useFormikContext<{
        mode: string;
    }>();
    const [lastValidMode, setLastValidMode] = useState(isValidFileMode(values.mode) ? values.mode : '000');
    const [focused, setFocused] = useState(false);
    const [editedAtSubmitCount, setEditedAtSubmitCount] = useState(submitCount);
    const valid = isValidFileMode(values.mode);
    const mode = valid ? values.mode : lastValidMode;
    const showError = !valid && ((!focused && touched.mode) || submitCount > editedAtSubmitCount);

    const updateMode = (next: string) => {
        setEditedAtSubmitCount(submitCount);
        if (isValidFileMode(next)) setLastValidMode(next);
        void setFieldValue('mode', next);
    };

    return (
        <fieldset disabled={isSubmitting}>
            <Label htmlFor='file_mode'>{t('file-mode-label')}</Label>
            <Input
                id='file_mode'
                name='mode'
                type='text'
                inputMode='numeric'
                autoComplete='off'
                autoFocus
                className='font-mono'
                value={values.mode}
                onChange={(event) => updateMode(event.target.value)}
                onFocus={() => setFocused(true)}
                onBlur={(event) => {
                    setFocused(false);
                    handleBlur(event);
                }}
                aria-invalid={showError || undefined}
                aria-describedby='file-mode-help'
                $hasError={showError}
            />
            <p id='file-mode-help' className={`mt-2 text-sm ${showError ? 'text-red-200' : 'text-gray-300'}`}>
                {t(showError ? 'permissions.invalid' : 'permissions.hint')}
            </p>
            <table className='mt-5 w-full table-fixed text-sm text-gray-200'>
                <caption className='sr-only'>{t('permissions.grid-label')}</caption>
                <thead>
                    <tr className='border-b border-gray-700'>
                        <th scope='col' className='text-left font-medium pb-2'>
                            {t('permissions.access')}
                        </th>
                        {permissions.map(({ name }) => (
                            <th key={name} scope='col' className='pb-2 text-center font-medium'>
                                {t(`permissions.${name}`)}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {subjects.map((subject, index) => (
                        <tr key={subject} className='border-b border-gray-700'>
                            <th scope='row' className='text-left font-medium'>
                                {t(`permissions.${subject}`)}
                            </th>
                            {permissions.map(({ name, bit }) => (
                                <td key={name} className='text-center'>
                                    <label className='flex min-h-11 items-center justify-center cursor-pointer hover:bg-gray-700/30 rounded-ui'>
                                        <Input
                                            type='checkbox'
                                            className='focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-400 disabled:cursor-wait'
                                            aria-label={t('permissions.toggle', {
                                                subject: t(`permissions.${subject}`),
                                                permission: t(`permissions.${name}`),
                                            })}
                                            checked={(Number(mode.slice(-3)[index]) & bit) !== 0}
                                            onChange={() => {
                                                const digits = mode.split('');
                                                const position = digits.length - 3 + index;
                                                digits[position] = String(Number(digits[position]) ^ bit);
                                                updateMode(digits.join(''));
                                            }}
                                        />
                                    </label>
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
            {mode.length === 4 && mode[0] !== '0' && (
                <p className='mt-2 text-sm text-gray-300'>{t('permissions.special', { digit: mode[0] })}</p>
            )}
        </fieldset>
    );
}

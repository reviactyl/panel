import { EyeIcon, EyeOffIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';

interface Props {
    visible: boolean;
    onToggle: () => void;
    disabled?: boolean;
}

export default function PasswordVisibilityButton({ visible, onToggle, disabled }: Props) {
    const { t } = useTranslation('auth');
    const label = t(visible ? 'hide-password' : 'show-password');
    const Icon = visible ? EyeOffIcon : EyeIcon;

    return (
        <button
            type={'button'}
            aria-label={label}
            title={label}
            disabled={disabled}
            onClick={onToggle}
            className='absolute inset-y-0 end-0 flex w-11 items-center justify-center rounded-e-ui text-muted hover:text-gray-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-50'
        >
            <Icon className='h-5 w-5' aria-hidden='true' />
        </button>
    );
}

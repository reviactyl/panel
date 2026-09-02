import { Actions, State, useStoreActions, useStoreState } from 'easy-peasy';
import classNames from 'classnames';
import { useRef, useState } from 'react';
import { ApplicationStore } from '@/state';
import updateAccountAvatar, { AvatarPreferences } from '@/api/account/updateAccountAvatar';
import Avatar, { ANIMATED_AVATAR_STYLES } from '@/reviactyl/ui/Avatar';
import Switch from '@/reviactyl/elements/Switch';
import { useFlashKey } from '@/plugins/useFlash';
import { useTranslation } from 'react-i18next';

const avatarStyles = ['gravatar', 'initials', 'identicon', 'loops', 'waves', 'critters', 'pixelbot', 'thumbs'];

export default () => {
    const { t } = useTranslation('dashboard/account');
    const user = useStoreState((state: State<ApplicationStore>) => state.user.data!);
    const updateUserData = useStoreActions((actions: Actions<ApplicationStore>) => actions.user.updateUserData);
    const { clearFlashes, clearAndAddHttpError } = useFlashKey('account:avatar');
    const saving = useRef(false);
    const [isSaving, setIsSaving] = useState(false);
    const supportsAnimation = ANIMATED_AVATAR_STYLES.includes(user.avatarStyle);

    const savePreferences = async (preferences: AvatarPreferences) => {
        if (
            saving.current ||
            (preferences.avatarStyle === user.avatarStyle && preferences.avatarAnimated === user.avatarAnimated)
        ) {
            return;
        }

        const previous = { avatarStyle: user.avatarStyle, avatarAnimated: user.avatarAnimated };
        saving.current = true;
        setIsSaving(true);
        clearFlashes();
        updateUserData(preferences);

        try {
            await updateAccountAvatar(preferences);
        } catch (error) {
            updateUserData(previous);
            clearAndAddHttpError(error as Error);
        } finally {
            saving.current = false;
            setIsSaving(false);
        }
    };

    return (
        <div className='w-full'>
            <div
                role='group'
                aria-label={t('avatar.style')}
                aria-busy={isSaving}
                className='flex w-full items-center justify-between gap-2 overflow-x-auto py-1'
            >
                {avatarStyles.map((style) => {
                    const isSelected = style === user.avatarStyle;
                    const label = t(`avatar.styles.${style}`);

                    return (
                        <button
                            key={style}
                            type='button'
                            title={label}
                            aria-label={label}
                            aria-pressed={isSelected}
                            disabled={isSaving}
                            onClick={() => savePreferences({ avatarStyle: style, avatarAnimated: user.avatarAnimated })}
                            className={classNames(
                                'flex h-11 w-11 flex-none items-center justify-center rounded-full border-2 transition-colors',
                                isSaving && 'cursor-wait opacity-70',
                                isSelected
                                    ? 'border-primary-500 bg-primary-500/10'
                                    : 'border-transparent hover:border-gray-600'
                            )}
                        >
                            <Avatar
                                email={user.email}
                                uuid={user.uuid}
                                avatarStyle={style}
                                avatarAnimated={user.avatarAnimated}
                                className='h-9 w-9'
                            />
                        </button>
                    );
                })}
            </div>

            {supportsAnimation && (
                <div className='mt-3 flex justify-center'>
                    <Switch
                        key={user.avatarAnimated ? 'animated' : 'static'}
                        name='avatar_animation'
                        label={t('avatar.animation')}
                        defaultChecked={user.avatarAnimated}
                        readOnly={isSaving}
                        onChange={(event) =>
                            savePreferences({
                                avatarStyle: user.avatarStyle,
                                avatarAnimated: event.currentTarget.checked,
                            })
                        }
                    />
                </div>
            )}
        </div>
    );
};

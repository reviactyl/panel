import { useStoreState } from 'easy-peasy';
import Md5 from 'md5';
import { useTranslation } from 'react-i18next';

interface Props {
    email?: string;
    uuid?: string;
    src?: string;
    avatarStyle?: string;
    avatarAnimated?: boolean;
    className?: string;
}

export const AVATAR_STYLES = ['gravatar', 'initials', 'identicon', 'loops', 'waves', 'critters', 'pixelbot', 'thumbs'];
export const ANIMATED_AVATAR_STYLES = ['loops', 'waves', 'critters', 'pixelbot', 'thumbs'];

export const avatarUrl = ({
    email,
    uuid,
    avatarStyle = 'gravatar',
    avatarAnimated = true,
}: Omit<Props, 'className' | 'src'>): string => {
    const style = AVATAR_STYLES.includes(avatarStyle) ? avatarStyle : 'gravatar';

    if (style === 'gravatar') {
        const hash =
            email === 'system'
                ? '00000000000000000000000000000000'
                : Md5(
                      String(email || 'system@localhost')
                          .trim()
                          .toLowerCase()
                  );

        return `https://www.gravatar.com/avatar/${hash}?s=200`;
    }

    const params = new URLSearchParams({ seed: uuid || 'system' });

    if (avatarAnimated && ANIMATED_AVATAR_STYLES.includes(style)) {
        params.set('animationVariant', 'medium');
    }

    return `https://api.dicebear.com/10.x/${style}/svg?${params.toString()}`;
};

export default ({ email, uuid, src, avatarStyle, avatarAnimated, className }: Props) => {
    const { t } = useTranslation('strings');
    const currentUser = useStoreState((state) => state.user.data);
    const image =
        src ||
        avatarUrl({
            email: email || currentUser?.email,
            uuid: uuid || currentUser?.uuid,
            avatarStyle: avatarStyle || currentUser?.avatarStyle,
            avatarAnimated: avatarAnimated ?? currentUser?.avatarAnimated,
        });

    return <img src={image} className={`${className || ''} rounded-full`} alt={t('avatar')} />;
};

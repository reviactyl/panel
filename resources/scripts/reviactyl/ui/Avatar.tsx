import { useMemo } from 'react';
import { useStoreState } from 'easy-peasy';
import Md5 from 'md5';
import { createAvatar } from '@dicebear/core';
import { initials, thumbs, identicon, rings } from '@dicebear/collection';

interface Props {
    email?: string;
    className?: string;
}

const avatarStyles: Record<string, any> = {
    initials,
    identicon,
    thumbs,
    rings,
    gravatar: null,
};

export default ({ email, className }: Props) => {
    const useremail = useStoreState((state) => state.user.data?.email);
    const nameFirst = useStoreState((state) => state.user.data?.name_first);
    const nameLast = useStoreState((state) => state.user.data?.name_last);
    const avatarType = useStoreState((state) => state.designify.data?.avatarType) || 'gravatar';

    // Use provided email, fallback to current user email, or system default
    const emailToUse = email || useremail || 'system@localhost';

    // For gravatar users, use a consistent hash
    const gravatarHash = email === 'system' ? '00000000000000000000000000000000' : Md5(String(emailToUse));

    // For dicebear avatars, use a seed based on the user nameFirst and nameLast
    const seed = `${nameFirst ?? ''} ${nameLast ?? ''}`;

    const avatarStyle = avatarStyles[avatarType] || initials;

    const avatar = useMemo(() => {
        return createAvatar(avatarStyle, {
            seed,
            size: 128,
        }).toDataUri();
    }, [seed]);

    return (
        <>
            {avatarType === 'gravatar' ? (
                <img
                    src={`https://www.gravatar.com/avatar/${gravatarHash}?s=200`}
                    className={`${className} rounded-full`}
                    alt='Avatar'
                />
            ) : (
                <img src={avatar} className={`${className} rounded-full`} alt='Avatar' />
            )}
        </>
    );
};

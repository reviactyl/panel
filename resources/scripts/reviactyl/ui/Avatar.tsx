import * as React from 'react';
import { useStoreState } from 'easy-peasy';
import Md5 from 'md5';

interface Props {
    email?: string;
    className?: string;
}

export default ({ email, className }: Props) => {
    const useremail = useStoreState((state) => state.user.data?.email);
    
    // Use provided email, fallback to current user email, or system default
    const emailToUse = email || useremail || 'system@localhost';
    
    // For system users, use a consistent hash
    const gravatarHash = email === 'system' ? '00000000000000000000000000000000' : Md5(String(emailToUse));
    
    return (
        <img
            src={`https://www.gravatar.com/avatar/${gravatarHash}?d=identicon&s=40`}
            className={`${className} rounded-full`}
            alt='Gravatar'
        />
    );
};

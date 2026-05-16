import React from 'react';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { LogoContainer } from '@/reviactyl/ui/LogoContainer';

interface Props {
    nostyles?: boolean;
}

const Logo: React.FC<Props> = ({ nostyles }) => {
    const logo = useStoreState((state: ApplicationStore) => state.settings.data!.logo);
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);

    const image = (
        <img
            src={logo}
            alt={name}
            onClick={() => (window.location.href = '/')}
            className={`h-[3rem] ${nostyles ? '' : 'mt-5'} cursor-pointer`}
        />
    );

    if (nostyles) {
        return image;
    }

    return <LogoContainer>{image}</LogoContainer>;
};

export default Logo;

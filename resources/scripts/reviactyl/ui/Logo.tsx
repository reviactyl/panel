import React from 'react';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { LogoContainer } from '@/reviactyl/ui/LogoContainer';
import { Link } from 'react-router-dom';
import { loadDashboardRouter } from '@/routers/loadRouter';

interface Props {
    nostyles?: boolean;
}

const Logo: React.FC<Props> = ({ nostyles }) => {
    const logo = useStoreState((state: ApplicationStore) => state.settings.data!.logo);
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);

    const prepare = () => void loadDashboardRouter().catch(() => undefined);
    const image = (
        <Link
            to='/'
            onPointerEnter={prepare}
            onFocus={prepare}
            onTouchStart={prepare}
            className={`inline-flex rounded-ui focus-visible:outline-solid focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-reviactyl ${nostyles ? '' : 'mt-5'}`}
        >
            <img
                src={logo}
                alt={name}
                className={`h-12 ${logo === '/reviactyl/logo.png' ? 'brightness-0 dark:brightness-100' : ''}`}
            />
        </Link>
    );

    if (nostyles) {
        return image;
    }

    return <LogoContainer>{image}</LogoContainer>;
};

export default Logo;

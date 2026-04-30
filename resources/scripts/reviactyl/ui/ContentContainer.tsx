import React from 'react';
import { css } from 'twin.macro';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';

export const ContentContainer: React.FC<React.PropsWithChildren> = ({ children }) => {
    const layoutType = useStoreState((state: ApplicationStore) => state.reviactyl.data!.layoutType);
    return (
        <div
            className={`flex ${layoutType !== 'modern' ? 'pt-16' : ''}`}
            css={[
                css`
                    padding-inline-end: 0.25rem;

                    @media (min-width: 1024px) {
                        padding-inline-start: 250px;
                    }
                `,
            ]}
        >
            {children}
        </div>
    );
};

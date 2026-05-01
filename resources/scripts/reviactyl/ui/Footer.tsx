import styled from 'styled-components';
import tw from 'twin.macro';
import { useStoreState } from 'easy-peasy';
import Md2React from '@/reviactyl/ui/Md2React';

const Container = styled.div`
    ${tw`mt-4 mb-4`}
`;

const Copyright = styled.div`
    ${tw`text-center text-gray-600 text-xs`}
`;

export default () => {
    const customCopyright = useStoreState((state) => state.reviactyl.data!.customCopyright);
    const copyright = useStoreState((state) => state.reviactyl.data!.copyright);
    return (
        <Container>
            <Copyright>
                <a
                    rel={'noopener nofollow noreferrer'}
                    href={'https://reviactyl.app'}
                    target={'_blank'}
                    css={tw`no-underline text-gray-600 hover:text-gray-300`}
                >
                    Reviactyl&trade;
                </a>
                &nbsp;&copy; {new Date().getFullYear()}
            </Copyright>
            {customCopyright ? (
                <Copyright>
                    <Md2React markdown={copyright} />
                </Copyright>
            ) : (
                ''
            )}
        </Container>
    );
};

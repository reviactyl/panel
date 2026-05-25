import tw, { TwStyle } from 'twin.macro';
import styled from 'styled-components';

export type FlashMessageType = 'success' | 'info' | 'warning' | 'error';

interface Props {
    title?: string;
    children: string;
    type?: FlashMessageType;
}

const styling = (type?: FlashMessageType): TwStyle | string => {
    switch (type) {
        case 'error':
            return tw`bg-red-600/50 border-red-800/80`;
        case 'info':
            return tw`bg-primary-600/50 border-primary-800/80`;
        case 'success':
            return tw`bg-green-600/50 border-green-800/80`;
        case 'warning':
            return tw`bg-yellow-600/50 border-yellow-800/80`;
        default:
            return tw`border-gray-800/80`;
    }
};

const getBackground = (type?: FlashMessageType): TwStyle | string => {
    switch (type) {
        case 'error':
            return tw`bg-red-500/60`;
        case 'info':
            return tw`bg-primary-500/60`;
        case 'success':
            return tw`bg-green-500/60`;
        case 'warning':
            return tw`bg-yellow-500/60`;
        default:
            return '';
    }
};

const Container = styled.div<{ $type?: FlashMessageType }>`
    ${tw`p-2 border items-center leading-normal rounded-ui flex w-full text-sm text-white`};
    ${(props) => styling(props.$type)};
`;
Container.displayName = 'MessageBox.Container';

const MessageBox = ({ title, children, type }: Props) => (
    <Container css={tw`lg:inline-flex`} $type={type} role={'alert'}>
        {title && (
            <span
                className={'title backdrop-blur-md'}
                css={[
                    tw`flex rounded-full uppercase px-2 py-1 text-xs font-bold mr-3 leading-none`,
                    getBackground(type),
                ]}
            >
                {title}
            </span>
        )}
        <span css={tw`mr-2 text-left flex-auto`}>{children}</span>
    </Container>
);
MessageBox.displayName = 'MessageBox';

export default MessageBox;

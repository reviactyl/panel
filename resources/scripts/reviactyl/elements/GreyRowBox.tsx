import styled from 'styled-components';
import tw from 'twin.macro';

export default styled.div<{ $hoverable?: boolean }>`
    ${tw`flex rounded-ui no-underline text-gray-200 items-center bg-gray-900 p-4 border border-gray-800 transition-colors duration-150 overflow-hidden`};

    ${(props) => props.$hoverable !== false && tw`hover:border-gray-600`};

    & .icon {
        ${tw`rounded-full w-16 flex items-center justify-center bg-gray-600 p-3`};
    }
`;

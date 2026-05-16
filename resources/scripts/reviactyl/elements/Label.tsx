import styled from 'styled-components';
import tw from 'twin.macro';

const Label = styled.label<{ isLight?: boolean }>`
    ${tw`block text-sm text-gray-200 mb-1 sm:mb-2`};
`;

export default Label;

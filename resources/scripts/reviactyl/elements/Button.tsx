import React from 'react';
import styled, { css } from 'styled-components';
import tw from 'twin.macro';
import Spinner from '@/reviactyl/elements/Spinner';

interface Props {
    isLoading?: boolean;
    size?: 'xsmall' | 'small' | 'large' | 'xlarge';
    color?: 'green' | 'red' | 'primary' | 'grey';
    isSecondary?: boolean;
}

const ButtonStyle = styled.button<Omit<Props, 'isLoading'>>`
    ${tw`relative inline-block rounded-ui p-2 tracking-wide text-base font-semibold transition-all duration-150 border`};

    ${(props) =>
        ((!props.isSecondary && !props.color) || props.color === 'primary') &&
        css<Props>`
            ${(props) => !props.isSecondary && tw`bg-primary-500/80 border-primary-600/80 border text-primary-50`};

            &:hover:not(:disabled) {
                ${tw`bg-primary-600/80 border-primary-700/80`};
            }
        `};

    ${(props) =>
        props.color === 'grey' &&
        css`
            ${tw`border-gray-600 bg-gray-500 text-gray-50`};

            &:hover:not(:disabled) {
                ${tw`bg-gray-600 border-gray-700`};
            }
        `};

    ${(props) =>
        props.color === 'green' &&
        css<Props>`
            ${tw`border-green-600 bg-green-500 text-green-50`};

            &:hover:not(:disabled) {
                ${tw`bg-green-600 border-green-700`};
            }

            ${(props) =>
                props.isSecondary &&
                css`
                    &:active:not(:disabled) {
                        ${tw`bg-green-600 border-green-700`};
                    }
                `};
        `};

    ${(props) =>
        props.color === 'red' &&
        css<Props>`
            ${tw`border-red-600 bg-red-500 text-red-50`};

            &:hover:not(:disabled) {
                ${tw`bg-red-600 border-red-700`};
            }

            ${(props) =>
                props.isSecondary &&
                css`
                    &:active:not(:disabled) {
                        ${tw`bg-red-600 border-red-700`};
                    }
                `};
        `};

    ${(props) => props.size === 'xsmall' && tw`px-2 py-1 text-xs`};
    ${(props) => (!props.size || props.size === 'small') && tw`px-4 py-2`};
    ${(props) => props.size === 'large' && tw`p-4 text-xl`};
    ${(props) => props.size === 'xlarge' && tw`p-4 w-full`};

    ${(props) =>
        props.isSecondary &&
        css<Props>`
            ${tw`border-gray-600 bg-transparent text-gray-200`};

            &:hover:not(:disabled) {
                ${tw`border-gray-500 text-gray-100`};
                ${(props) => props.color === 'red' && tw`bg-red-500 border-red-600 text-red-50`};
                ${(props) => props.color === 'primary' && tw`bg-primary-500 border-primary-600 text-primary-50`};
                ${(props) => props.color === 'green' && tw`bg-green-500 border-green-600 text-green-50`};
            }
        `};

    &:disabled {
        opacity: 0.55;
        cursor: default;
    }
`;

type ComponentProps = Omit<React.JSX.IntrinsicElements['button'], 'ref' | keyof Props> & Props;

const Button = ({ children, isLoading, ...props }: ComponentProps) => (
    <ButtonStyle {...props}>
        {isLoading && (
            <div css={tw`flex absolute justify-center items-center w-full h-full left-0 top-0`}>
                <Spinner size={'small'} />
            </div>
        )}
        <span css={isLoading ? tw`text-transparent` : undefined}>{children}</span>
    </ButtonStyle>
);

type LinkProps = Omit<React.JSX.IntrinsicElements['a'], 'ref' | keyof Props> & Props;

const LinkButton = (props: LinkProps) => <ButtonStyle as={'a'} {...props} />;

export { LinkButton, ButtonStyle };
export default Button;

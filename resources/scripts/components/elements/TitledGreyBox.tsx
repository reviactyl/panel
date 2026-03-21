import React, { memo } from 'react';
import { IconType } from 'react-icons';
import tw from 'twin.macro';
import isEqual from 'react-fast-compare';
import Card from '@/reviactyl/ui/Card';
import Title from '@/reviactyl/ui/Title';
import FlashMessageRender from '@/components/FlashMessageRender';

interface Props {
    icon?: IconType;
    title: string | React.ReactNode;
    className?: string;
    children: React.ReactNode;
    showFlashes?: string | boolean;
}

const TitledGreyBox = ({ icon, title, children, className, showFlashes }: Props) => {
    const HeaderIcon = icon;

    return (
        <Card css={tw`!p-0`} className={className}>
            <div css={tw`p-3`}>
                {typeof title === 'string' ? (
                    <Title css={tw`text-sm`}>
                        {HeaderIcon && <HeaderIcon className={'mr-2 text-neutral-300 inline-block'} />}
                        {title}
                    </Title>
                ) : (
                    title
                )}
            </div>
            {showFlashes && (
                <FlashMessageRender byKey={typeof showFlashes === 'string' ? showFlashes : undefined} css={tw`mb-4`} />
            )}
            <div css={tw`p-3`}>{children}</div>
        </Card>
    );
};

export default memo(TitledGreyBox, isEqual);

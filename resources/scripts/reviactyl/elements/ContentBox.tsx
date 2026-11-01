import React from 'react';
import FlashMessageRender from '@/components/FlashMessageRender';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import Card from '@/reviactyl/ui/Card';
import Title from '@/reviactyl/ui/Title';
import useModernHeaders from '@/reviactyl/ui/useModernHeaders';

type Props = Readonly<
    React.DetailedHTMLProps<React.HTMLAttributes<HTMLDivElement>, HTMLDivElement> & {
        title?: string;
        insetTitle?: boolean;
        borderColor?: string;
        showFlashes?: string | boolean;
        showLoadingOverlay?: boolean;
    }
>;

const ContentBox = ({
    title,
    insetTitle = false,
    borderColor,
    showFlashes,
    showLoadingOverlay,
    children,
    ...props
}: Props) => {
    const modernHeaders = useModernHeaders();
    const showInsetTitle = insetTitle && modernHeaders;

    return (
        <div {...props}>
            {title && !showInsetTitle && (
                <Title as='h2' className='mb-4 px-4 text-2xl'>
                    {title}
                </Title>
            )}
            {showFlashes && (
                <FlashMessageRender
                    byKey={typeof showFlashes === 'string' ? showFlashes : undefined}
                    className='mb-4'
                />
            )}
            <Card className={`relative p-4! ${borderColor ? 'border-t-4!' : ''}`}>
                <SpinnerOverlay visible={showLoadingOverlay || false} />
                {title && showInsetTitle && <h2 className='mb-4 text-base font-semibold text-gray-50'>{title}</h2>}
                {children}
            </Card>
        </div>
    );
};

export default ContentBox;

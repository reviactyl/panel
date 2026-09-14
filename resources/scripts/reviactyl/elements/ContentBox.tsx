import React from 'react';
import FlashMessageRender from '@/components/FlashMessageRender';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import Card from '@/reviactyl/ui/Card';
import Title from '@/reviactyl/ui/Title';

type Props = Readonly<
    React.DetailedHTMLProps<React.HTMLAttributes<HTMLDivElement>, HTMLDivElement> & {
        title?: string;
        borderColor?: string;
        showFlashes?: string | boolean;
        showLoadingOverlay?: boolean;
    }
>;

const ContentBox = ({ title, borderColor, showFlashes, showLoadingOverlay, children, ...props }: Props) => (
    <div {...props}>
        {title && <Title className='mb-4 px-4 text-2xl'>{title}</Title>}
        {showFlashes && (
            <FlashMessageRender byKey={typeof showFlashes === 'string' ? showFlashes : undefined} className='mb-4' />
        )}
        <Card className={`relative !p-4 ${borderColor ? '!border-t-4' : ''}`}>
            <SpinnerOverlay visible={showLoadingOverlay || false} />
            {children}
        </Card>
    </div>
);

export default ContentBox;

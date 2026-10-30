import React, { useEffect } from 'react';
import classNames from 'classnames';
import ContentContainer from '@/reviactyl/elements/ContentContainer';
import { motion } from 'framer-motion';
import FlashMessageRender from '@/components/FlashMessageRender';
import Footer from '@/reviactyl/ui/Footer';

export interface PageContentBlockProps {
    title?: string;
    inset?: boolean;
    className?: string;
    showFlashKey?: string;
    children?: React.ReactNode;
}

const PageContentBlock = ({ title, inset = false, showFlashKey, className, children }: PageContentBlockProps) => {
    useEffect(() => {
        if (title) {
            document.title = title;
        }
    }, [title]);

    const insetClass = inset ? 'mx-auto! my-2! w-[calc(100%-1rem)]' : undefined;

    return (
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ duration: 0.15, ease: 'easeIn' }}>
            <ContentContainer className={classNames(insetClass, className)}>
                {showFlashKey && <FlashMessageRender byKey={showFlashKey} className='mb-4' />}
                {children}
            </ContentContainer>
            <ContentContainer className={classNames(insetClass, 'mb-4')}>
                <Footer />
            </ContentContainer>
        </motion.div>
    );
};

export default PageContentBlock;

import React, { useEffect } from 'react';
import ContentContainer from '@/reviactyl/elements/ContentContainer';
import { motion } from 'framer-motion';
import tw from 'twin.macro';
import FlashMessageRender from '@/components/FlashMessageRender';
import Footer from '@/reviactyl/ui/Footer';

export interface PageContentBlockProps {
    title?: string;
    className?: string;
    showFlashKey?: string;
    children?: React.ReactNode;
}

const PageContentBlock = ({ title, showFlashKey, className, children }: PageContentBlockProps) => {
    useEffect(() => {
        if (title) {
            document.title = title;
        }
    }, [title]);

    return (
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ duration: 0.15, ease: 'easeIn' }}>
            <ContentContainer className={className}>
                {showFlashKey && <FlashMessageRender byKey={showFlashKey} css={tw`mb-4`} />}
                {children}
            </ContentContainer>
            <ContentContainer css={tw`mb-4`}>
                <Footer />
            </ContentContainer>
        </motion.div>
    );
};

export default PageContentBlock;

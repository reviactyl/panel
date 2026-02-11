import React, { useEffect, useRef } from 'react';
import Modal, { RequiredModalProps } from '@/components/elements/Modal';
import Viewer from 'viewerjs';
import 'viewerjs/dist/viewer.css';
import tw from 'twin.macro';
import styled from 'styled-components/macro';

// Quick styled component for the viewer container (might put this into its own file later)
const ViewerContainer = styled.div`
    ${tw`flex items-center justify-center bg-gray-900 rounded-lg overflow-hidden`};
    min-height: 500px;
    max-height: 80vh;
    
    img {
        ${tw`max-w-full max-h-full object-contain`};
        display: block;
        margin: auto;
    }
`;

interface Props extends RequiredModalProps {
    imageUrl: string;
    imageName: string;
}

const ImageViewerModal: React.FC<Props> = ({ imageUrl, imageName, ...modalProps }) => {
    const imageRef = useRef<HTMLImageElement>(null);
    const viewerRef = useRef<Viewer | null>(null);

    const handleImageClick = () => {
        if (!imageRef.current || viewerRef.current) return;

        // Create viewer on demand
        const viewer = new Viewer(imageRef.current, {
            inline: false,
            button: true,
            navbar: false,
            title: true,
            toolbar: {
                zoomIn: 1,
                zoomOut: 1,
                oneToOne: 1,
                reset: 1,
                rotateLeft: 1,
                rotateRight: 1,
                flipHorizontal: 1,
                flipVertical: 1,
            },
            tooltip: true,
            movable: true,
            zoomable: true,
            rotatable: true,
            scalable: true,
            transition: true,
            fullscreen: true,
            keyboard: true,
            backdrop: true,
            loading: true,
            loop: false,
            hidden() {
                // Destroy viewer when it's closed
                viewer.destroy();
                viewerRef.current = null;
            },
        });

        viewerRef.current = viewer;
        viewer.show();
    };

    return (
        <Modal {...modalProps} dismissable>
            <div css={tw`bg-gray-800 rounded-lg p-6 max-w-5xl w-full`}>
                <div css={tw`mb-4`}>
                    <h2 css={tw`text-xl font-semibold text-gray-100`}>{imageName}</h2>
                </div>
                <ViewerContainer>
                    <img
                        ref={imageRef}
                        src={imageUrl}
                        alt={imageName}
                        css={tw`cursor-pointer`}
                        onClick={handleImageClick}
                        onError={(e) => {
                            // Welp, time to use hacky fallback
                            (e.target as HTMLImageElement).src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200"%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" fill="%23999" font-size="16"%3EImage failed to load%3C/text%3E%3C/svg%3E';
                        }}
                    />
                </ViewerContainer>
                <div css={tw`mt-4 text-sm text-gray-400 text-center`}>
                    Click on the image to zoom, rotate, and use other viewer controls
                </div>
            </div>
        </Modal>
    );
    // TODO: Make the string in the div container under the ViewerContainer translatable
};

export default ImageViewerModal;

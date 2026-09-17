import { useRef } from 'react';
import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import Viewer from 'viewerjs';
import 'viewerjs/dist/viewer.css';

interface Props extends RequiredModalProps {
    imageUrl: string;
    imageName: string;
}

const ImageViewerModal = ({ imageUrl, imageName, ...modalProps }: Props) => {
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
        modalProps.onDismissed();
        viewer.show();
    };

    return (
        <Modal {...modalProps} dismissable>
            <div className='w-full max-w-5xl'>
                <div className='mb-4'>
                    <h2 className='text-xl font-semibold text-gray-100'>{imageName}</h2>
                </div>
                <div className='flex max-h-[80vh] min-h-[500px] items-center justify-center overflow-hidden rounded-ui bg-gray-900'>
                    <img
                        ref={imageRef}
                        src={imageUrl}
                        alt={imageName}
                        className='m-auto max-h-full max-w-full cursor-pointer object-contain'
                        onClick={handleImageClick}
                        onError={(e) => {
                            // Welp, time to use hacky fallback
                            (e.target as HTMLImageElement).src =
                                'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200"%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" fill="%23999" font-size="16"%3EImage failed to load%3C/text%3E%3C/svg%3E';
                        }}
                    />
                </div>
                <div className='mt-4 text-center text-sm text-gray-400'>
                    Click on the image to zoom, rotate, and use other viewer controls
                </div>
            </div>
        </Modal>
    );
    // TODO: Make the string in the div container under the ViewerContainer translatable
};

export default ImageViewerModal;

import { useEffect, useRef } from 'react';
import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import Viewer from 'viewerjs';
import 'viewerjs/dist/viewer.css';

interface Props extends RequiredModalProps {
    imageUrl: string;
    imageName: string;
}

const ImageViewerModal = ({ imageUrl, imageName, ...modalProps }: Props) => {
    const imageRef = useRef<HTMLImageElement>(null);
    const containerRef = useRef<HTMLDivElement>(null);
    const buttonRef = useRef<HTMLButtonElement>(null);
    const viewerRef = useRef<Viewer | null>(null);

    useEffect(
        () => () => {
            viewerRef.current?.destroy();
            viewerRef.current = null;
        },
        [imageUrl, modalProps.visible],
    );

    const handleImageClick = () => {
        if (!imageRef.current || !containerRef.current || viewerRef.current) return;

        const viewer = new Viewer(imageRef.current, {
            container: containerRef.current,
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
                viewer.destroy();
                viewerRef.current = null;
                buttonRef.current?.focus();
            },
        });

        viewerRef.current = viewer;
        viewer.show(true);
    };

    return (
        <Modal {...modalProps} size='lg' dismissable>
            <div
                ref={containerRef}
                className='w-full'
                onKeyDownCapture={(event) => {
                    if (event.key === 'Escape' && viewerRef.current) {
                        event.preventDefault();
                        event.stopPropagation();
                        viewerRef.current.hide();
                    }
                }}
            >
                <div className='mb-4'>
                    <h2 className='text-xl font-semibold break-all text-gray-100'>{imageName}</h2>
                </div>
                <button
                    ref={buttonRef}
                    type='button'
                    className='flex w-full cursor-zoom-in items-center justify-center overflow-hidden rounded-ui bg-gray-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand'
                    onClick={handleImageClick}
                >
                    <img
                        ref={imageRef}
                        src={imageUrl}
                        alt={imageName}
                        className='m-auto max-h-[calc(100vh-16rem)] max-w-full object-contain'
                        onError={(e) => {
                            // Welp, time to use hacky fallback
                            (e.target as HTMLImageElement).src =
                                'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200"%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" fill="%23999" font-size="16"%3EImage failed to load%3C/text%3E%3C/svg%3E';
                        }}
                    />
                </button>
                <div className='mt-4 text-center text-sm text-gray-400'>
                    Click on the image to zoom, rotate, and use other viewer controls
                </div>
            </div>
        </Modal>
    );
    // TODO: Make the string in the div container under the ViewerContainer translatable
};

export default ImageViewerModal;

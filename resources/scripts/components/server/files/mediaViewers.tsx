import { lazy, Suspense, type ComponentProps } from 'react';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import type ImageViewer from './ImageViewerModal';
import type VideoViewer from './VideoViewerModal';

type ImageViewerProps = ComponentProps<typeof ImageViewer>;
type VideoViewerProps = ComponentProps<typeof VideoViewer>;

const LazyImageViewerModal = lazy(() => import('./ImageViewerModal'));
const LazyVideoViewerModal = lazy(() => import('./VideoViewerModal'));

export const ImageViewerModal = (props: ImageViewerProps) => (
    <Suspense fallback={<SpinnerOverlay visible fixed />}>
        <LazyImageViewerModal {...props} />
    </Suspense>
);

export const VideoViewerModal = (props: VideoViewerProps) => (
    <Suspense fallback={<SpinnerOverlay visible fixed />}>
        <LazyVideoViewerModal {...props} />
    </Suspense>
);

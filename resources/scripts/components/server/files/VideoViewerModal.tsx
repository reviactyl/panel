import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import { Plyr } from 'plyr-react';
import 'plyr-react/plyr.css';

interface Props extends RequiredModalProps {
    videoUrl: string;
    videoName: string;
    videoMimeType: string;
}

const VideoViewerModal = ({ videoUrl, videoName, videoMimeType, ...modalProps }: Props) => {
    return (
        <Modal {...modalProps} size='lg'>
            <div className='w-full'>
                <div className='mb-4'>
                    <h2 className='text-xl font-semibold break-all text-gray-100'>{videoName}</h2>
                </div>

                <div className='flex w-full min-w-0 items-center justify-center rounded-ui bg-gray-900 [&_.plyr]:w-full [&_.plyr]:min-w-0 [&_video]:max-h-[calc(100vh-16rem)] [&_video]:object-contain'>
                    <Plyr
                        source={{
                            type: 'video',
                            sources: [
                                {
                                    src: videoUrl,
                                    type: videoMimeType,
                                },
                            ],
                        }}
                        options={{
                            controls: [
                                'play',
                                'progress',
                                'current-time',
                                'duration',
                                'mute',
                                'volume',
                                'settings',
                                'fullscreen',
                            ],
                        }}
                    />
                </div>
            </div>
        </Modal>
    );
};

export default VideoViewerModal;

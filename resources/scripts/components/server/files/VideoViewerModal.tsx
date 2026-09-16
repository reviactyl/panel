import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import { Plyr } from 'plyr-react';
import 'plyr-react/plyr.css';

interface Props extends RequiredModalProps {
    videoUrl: string;
    videoName: string;
}

const VideoViewerModal = ({ videoUrl, videoName, ...modalProps }: Props) => {
    return (
        <Modal {...modalProps}>
            <div className='w-full max-w-5xl'>
                <div className='mb-4'>
                    <h2 className='text-xl font-semibold text-gray-100'>{videoName}</h2>
                </div>

                <div className='flex max-h-[80vh] min-h-[360px] items-center justify-center overflow-hidden rounded-ui bg-gray-900 [&_.plyr]:w-1/2'>
                    <Plyr
                        source={{
                            type: 'video',
                            sources: [
                                {
                                    src: videoUrl,
                                    type: 'video/mp4',
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

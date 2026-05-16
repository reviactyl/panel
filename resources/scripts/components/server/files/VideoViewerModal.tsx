import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import { Plyr } from 'plyr-react';
import 'plyr-react/plyr.css';
import tw from 'twin.macro';
import styled from 'styled-components';

const PlayerContainer = styled.div`
    ${tw`flex items-center justify-center bg-gray-900 rounded-ui overflow-hidden`};
    min-height: 360px;
    max-height: 80vh;

    .plyr {
        ${tw`w-[50%]`};
    }
`;

interface Props extends RequiredModalProps {
    videoUrl: string;
    videoName: string;
}

const VideoViewerModal = ({ videoUrl, videoName, ...modalProps }: Props) => {
    return (
        <Modal {...modalProps}>
            <div css={tw`max-w-5xl w-full`}>
                <div css={tw`mb-4`}>
                    <h2 css={tw`text-xl font-semibold text-gray-100`}>{videoName}</h2>
                </div>

                <PlayerContainer>
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
                </PlayerContainer>
            </div>
        </Modal>
    );
};

export default VideoViewerModal;

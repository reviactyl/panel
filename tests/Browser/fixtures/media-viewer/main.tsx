import { useState } from 'react';
import { createRoot } from 'react-dom/client';
import { I18nextProvider } from 'react-i18next';
import { createInstance } from 'i18next';
import VideoViewerModal from '@/components/server/files/VideoViewerModal';
import ImageViewerModal from '@/components/server/files/ImageViewerModal';
import '@/assets/tailwind.css';

const i18n = createInstance();
void i18n.init({ lng: 'en', resources: { en: { strings: { close: 'Close' } } } });
const image =
    'data:image/svg+xml,' +
    encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1200"><rect width="1600" height="1200" fill="blue"/></svg>',
    );
function Fixture() {
    const [media, setMedia] = useState<string | null>(null);
    return (
        <I18nextProvider i18n={i18n}>
            {['mp4', 'webm', 'image'].map((type) => (
                <button key={type} onClick={() => setMedia(type)}>
                    {type}
                </button>
            ))}
            {media &&
                (media === 'image' ? (
                    <ImageViewerModal
                        visible
                        onDismissed={() => setMedia(null)}
                        imageUrl={image}
                        imageName='large image.png'
                    />
                ) : (
                    <VideoViewerModal
                        visible
                        onDismissed={() => setMedia(null)}
                        videoUrl='data:video/mp4;base64,'
                        videoName={`clip.${media}`}
                        videoMimeType={`video/${media}`}
                    />
                ))}
        </I18nextProvider>
    );
}
const root = createRoot(document.getElementById('root')!);
root.render(<Fixture />);
window.addEventListener('unmount-media-fixture', () => root.unmount());

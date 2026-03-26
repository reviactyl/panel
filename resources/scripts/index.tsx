import { createRoot } from 'react-dom/client';
import { App } from '@/components/App';

// Enable language support.
import './i18n';

console.info('🐦 Reviactyl, a modern, secure and fast application management panel -- https://reviactyl.app/');

if (import.meta.env.PROD && typeof window !== 'undefined') {
    console.log('%cStop!', 'color:#ef4444;font-size:64px;font-weight:900;text-shadow:0 2px 8px rgba(0,0,0,0.4);');

    console.log(
        '%cThis is a browser feature intended for developers.\n' +
            'If someone told you to paste something here, it may be a scam and could put your account at risk.\n\n' +
            'Unless you understand exactly what you are doing, close this window.',
        'font-size:16px;color:#e5e7eb;'
    );
}

createRoot(document.getElementById('app')!).render(<App />);

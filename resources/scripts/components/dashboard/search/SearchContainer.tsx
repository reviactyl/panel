import { useEffect, useState } from 'react';
import useEventListener from '@/plugins/useEventListener';
import SearchModal from '@/components/dashboard/search/SearchModal';
import { SearchIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('dashboard/index');
    const [visible, setVisible] = useState(false);
    const [isMac, setIsMac] = useState(false);

    useEffect(() => {
        setIsMac(navigator.platform.toLowerCase().includes('mac'));
    }, []);

    useEventListener('keydown', (e: KeyboardEvent) => {
        const tagName = (e.target as HTMLElement)?.tagName?.toLowerCase();
        const isTyping = tagName === 'input' || tagName === 'textarea' || (e.target as HTMLElement)?.isContentEditable;

        if (!isTyping) {
            if (
                (isMac && e.metaKey && e.key.toLowerCase() === 'k') ||
                (!isMac && e.ctrlKey && e.key.toLowerCase() === 'k')
            ) {
                e.preventDefault();
                setVisible(true);
            }
        }
    });

    return (
        <>
            {visible && <SearchModal appear visible={visible} onDismissed={() => setVisible(false)} />}
            <button
                type='button'
                aria-label='Search'
                onClick={() => setVisible(true)}
                className='flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-400 bg-gray-900 rounded-ui shadow-sm transition-all hover:scale-[1.02] active:scale-100 border border-gray-800'
            >
                <SearchIcon className='w-5 h-5 text-gray-400' />
                <div className='hidden md:block w-full text-left'>
                    <span>{t('search.label')}</span>
                </div>

                <div className='hidden md:flex items-center gap-1 text-xs text-gray-400 ml-auto'>
                    <kbd
                        className={`px-1 py-0.5 rounded bg-gray-900 text-gray-300 ${
                            isMac ? 'text-sm font-medium' : ''
                        }`}
                    >
                        {isMac ? '⌘' : 'Ctrl'}
                    </kbd>
                    <kbd className='px-1 py-0.5 rounded bg-gray-900 text-gray-300'>K</kbd>
                </div>
            </button>
        </>
    );
};

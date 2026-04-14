import { type ReactNode, useEffect, useState } from 'react';
import Switch from '@/reviactyl/elements/Switch';
import { useTranslation } from 'react-i18next';

interface InvertProps {
    children: ReactNode;
}

export const Invert = ({ children }: InvertProps) => {
    const [enabled, setEnabled] = useState(false);

    useEffect(() => {
        const stored = localStorage.getItem('invertMode');
        if (stored === 'true') setEnabled(true);
    }, []);

    useEffect(() => {
        const handleStorage = () => {
            const stored = localStorage.getItem('invertMode');
            setEnabled(stored === 'true');
        };
        window.addEventListener('storage', handleStorage);
        return () => window.removeEventListener('storage', handleStorage);
    }, []);

    return (
        <div className='relative'>
            {children}
            {enabled && (
                <div className='pointer-events-none fixed inset-0 z-[9999] mix-blend-difference bg-white'></div>
            )}
        </div>
    );
};

export const InvertToggle = () => {
    const { t } = useTranslation('dashboard/account');
    const [enabled, setEnabled] = useState(false);

    useEffect(() => {
        const stored = localStorage.getItem('invertMode');
        setEnabled(stored === 'true');
    }, []);

    useEffect(() => {
        localStorage.setItem('invertMode', enabled.toString());
        window.dispatchEvent(new Event('storage'));
    }, [enabled]);

    const toggle = () => {
        setEnabled((prev) => !prev);
    };

    return (
        <div className='flex justify-between items-center mb-2'>
            <p className='flex-1'>{t('overview.smart-invert')}</p>
            <div className='flex gap-x-2 items-center'>
                <span className='text-sm text-gray-300'>{t('overview.on')}</span>
                <Switch key={enabled ? 'on' : 'off'} name='smart_invert' defaultChecked={enabled} onChange={toggle} />
                <span className='text-sm text-gray-300'>{t('overview.off')}</span>
            </div>
        </div>
    );
};

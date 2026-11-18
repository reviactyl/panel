import React from 'react';
import { createRoot } from 'react-dom/client';
import { FaClock, FaGlobe } from 'react-icons/fa6';
import StatBlock from '@/components/server/console/StatBlock';
import UptimeDuration from '@/components/server/UptimeDuration';
import { updateThemeColors } from '@/lib/themeColors';
import '@/assets/tailwind.css';

updateThemeColors();
createRoot(document.getElementById('root')!).render(
    <div className='grid grid-cols-6 gap-2 md:gap-4'>
        <StatBlock title='Uptime' icon={FaClock}>
            <UptimeDuration uptime={123456 * 86400 + 23 * 3600 + 59 * 60} />
        </StatBlock>
        <StatBlock title='Offline uptime' icon={FaClock} color='bg-danger'>
            Offline
        </StatBlock>
        <StatBlock title='Address' icon={FaGlobe} copyOnClick={'very-long-hostname.'.repeat(8) + 'example:25565'}>
            {'very-long-hostname.'.repeat(8)}example:25565
        </StatBlock>
    </div>,
);

import React from 'react';
import { createRoot } from 'react-dom/client';
import i18next from 'i18next';
import { initReactI18next } from 'react-i18next';
import AllocationRow from '@/components/server/network/AllocationRow';
import { updateThemeColors } from '@/lib/themeColors';
import '@/assets/tailwind.css';

i18next.use(initReactI18next).init({
    lng: 'en',
    resources: {
        en: {
            'server/network': {
                'delete-allocation-title': 'Remove Allocation',
                'delete-allocation-confirm': 'Delete',
                'delete-allocation-message': 'This allocation will be immediately removed from your server.',
            },
            strings: { cancel: 'Cancel' },
        },
    },
});
updateThemeColors();
createRoot(document.getElementById('root')!).render(
    <AllocationRow
        allocation={{
            id: 1,
            ip: '127.0.0.1',
            alias: 'long-unbroken-alias-'.repeat(15) + '.example.test',
            port: 65535,
            notes: 'Saved notes',
            isDefault: false,
        }}
    />,
);

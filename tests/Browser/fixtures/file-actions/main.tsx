import React from 'react';
import { createRoot } from 'react-dom/client';
import { FaTrash, FaPen } from 'react-icons/fa6';
import { Row } from '@/components/server/files/FileDropdownMenu';
import { updateThemeColors } from '@/lib/themeColors';
import '@/assets/tailwind.css';

updateThemeColors();
createRoot(document.getElementById('root')!).render(
    <div className='bg-gray-800 text-gray-100 p-2' style={{ width: 192 }}>
        <Row icon={FaPen} title='Rename' />
        <Row icon={FaTrash} title='Delete' danger />
        <Row icon={FaTrash} title='Delete permanently' danger />
        <Row icon={FaTrash} title='Empty' danger />
        <Row icon={FaTrash} title='Unavailable' danger disabled />
    </div>,
);

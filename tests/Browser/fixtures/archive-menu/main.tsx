import React, { useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import useArchiveMenuPosition from '@/components/server/files/useArchiveMenuPosition';
import '@/assets/tailwind.css';

function Fixture() {
    const anchor = useRef<HTMLDivElement>(null);
    const menu = useRef<HTMLDivElement>(null);
    const [open, setOpen] = useState(false);
    const position = useArchiveMenuPosition(open, anchor, menu);
    return (
        <>
            <div style={{ height: 800 }} />
            <div className='flex gap-2' style={{ marginLeft: '60%' }}>
                <div ref={anchor}>
                    <button onFocus={() => setOpen(true)} onMouseEnter={() => setOpen(true)}>
                        Archive
                    </button>
                    <div
                        ref={menu}
                        role='group'
                        aria-label='Formats'
                        style={{ ...position, display: open ? 'block' : 'none' }}
                        className='fixed z-50 w-48 border p-2 bg-white'
                    >
                        <button className='block w-full p-2'>tar.gz</button>
                        <button className='block w-full p-2'>zip</button>
                    </div>
                </div>
                <button>Delete</button>
            </div>
            <div className='relative z-30'>
                File rows <input type='checkbox' />
            </div>
            <div style={{ height: 1000 }} />
            <nav
                data-mobile-bottom-bar
                className='fixed bottom-0 inset-x-0 z-50 bg-gray-800 lg:hidden'
                style={{ height: 73 }}
            >
                <button style={{ position: 'relative', top: -20 }}>Menu</button>
            </nav>
        </>
    );
}
createRoot(document.getElementById('root')!).render(<Fixture />);

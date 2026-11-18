import React from 'react';
import { createRoot } from 'react-dom/client';
import { MemoryRouter, useLocation } from 'react-router-dom';
import FileManagerBreadcrumbs from '@/components/server/files/FileManagerBreadcrumbs';
import { FileActionCheckbox } from '@/components/server/files/SelectFileCheckbox';
import '@/assets/tailwind.css';

const Fixture = () => {
    const { pathname } = useLocation();
    const editor = pathname.endsWith('/edit');
    return (
        <div className='m-4 min-w-0' data-testid='breadcrumbs'>
            <FileManagerBreadcrumbs
                withinFileEditor={editor}
                renderLeft={
                    editor ? undefined : (
                        <FileActionCheckbox type='checkbox' aria-label='Select All' className='mx-4 block' />
                    )
                }
            />
        </div>
    );
};

createRoot(document.getElementById('root')!).render(
    <MemoryRouter initialEntries={[document.body.dataset.route!]}>
        <Fixture />
    </MemoryRouter>,
);

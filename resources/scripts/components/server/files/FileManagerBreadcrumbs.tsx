import { useTranslation } from 'react-i18next';
import { isRecyclePath } from './recyclePaths';
import React, { useEffect, useRef, useState } from 'react';
import { ServerContext } from '@/state/server';
import { NavLink, useLocation } from 'react-router-dom';
import { encodePathSegments, hashToPath } from '@/helpers';

interface Props {
    renderLeft?: React.ReactElement;
    withinFileEditor?: boolean;
    isNewFile?: boolean;
}

export default ({ renderLeft, withinFileEditor, isNewFile }: Props) => {
    const { t } = useTranslation('server/recycle');
    const [file, setFile] = useState<string | null>(null);
    const pathRef = useRef<HTMLDivElement>(null);
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const { hash } = useLocation();

    useEffect(() => {
        const path = hashToPath(hash);

        if (withinFileEditor && !isNewFile) {
            const name = path.split('/').pop() || null;
            setFile(name);
        }
    }, [withinFileEditor, isNewFile, hash]);

    useEffect(() => {
        const path = pathRef.current;
        if (path) path.scrollLeft = path.scrollWidth;
    }, [directory, file]);

    const breadcrumbs = (): { name: string; path?: string }[] =>
        directory
            .split('/')
            .filter((directory) => !!directory)
            .map((directory, index, dirs) => {
                if (isRecyclePath('/' + dirs.join('/'))) {
                    if (index === 1) return { name: '' };
                    if (index === 0) return { name: t('title'), path: '/.recycle-bin' };
                }
                if (!withinFileEditor && index === dirs.length - 1) {
                    return { name: directory };
                }

                return { name: directory, path: `/${dirs.slice(0, index + 1).join('/')}` };
            });

    return (
        <div className='flex min-w-0 grow-0 items-center overflow-x-hidden text-sm text-muted'>
            {renderLeft && <div className='shrink-0'>{renderLeft}</div>}
            <div ref={pathRef} dir='ltr' className='min-w-0 flex-1 overflow-x-auto whitespace-nowrap'>
                /<span className='px-1 text-gray-300'>home</span>/
                <NavLink to={`/server/${id}/files`} className='px-1 text-gray-200 no-underline hover:text-gray-100'>
                    container
                </NavLink>
                /
                {breadcrumbs()
                    .filter((crumb) => crumb.name)
                    .map((crumb, index) =>
                        crumb.path ? (
                            <React.Fragment key={index}>
                                <NavLink
                                    to={`/server/${id}/files#${encodePathSegments(crumb.path)}`}
                                    className='px-1 text-gray-200 no-underline hover:text-gray-100'
                                >
                                    <bdi>{crumb.name}</bdi>
                                </NavLink>
                                /
                            </React.Fragment>
                        ) : (
                            <span key={index} className='px-1 text-gray-300'>
                                <bdi>{crumb.name}</bdi>
                            </span>
                        ),
                    )}
                {file && (
                    <React.Fragment>
                        <bdi className='px-1 text-gray-300'>{file}</bdi>
                    </React.Fragment>
                )}
            </div>
        </div>
    );
};

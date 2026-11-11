import { lazy, Suspense } from 'react';
import Spinner from './Spinner';
import styles from './style.module.css';
import type { Props } from '@/components/editors/CodemirrorEditor';
export type { Props } from '@/components/editors/CodemirrorEditor';

const Editor = lazy(() => import('@/components/editors/CodemirrorEditor'));

export default function CodemirrorEditor(props: Props) {
    return (
        <Suspense
            fallback={
                <div className={styles.editor} style={props.style}>
                    <Spinner centered size='large' />
                </div>
            }
        >
            <Editor {...props} />
        </Suspense>
    );
}

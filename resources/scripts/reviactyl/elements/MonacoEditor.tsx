import { lazy, Suspense } from 'react';
import Spinner from './Spinner';
import styles from './style.module.css';
import type { Props } from '@/components/editors/MonacoEditor';
export type { Props } from '@/components/editors/MonacoEditor';

const Editor = lazy(() => import('@/components/editors/MonacoEditor'));

export default function MonacoEditor(props: Props) {
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

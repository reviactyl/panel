import { useEffect, useRef } from 'react';
import { basicSetup } from 'codemirror';
import { indentWithTab } from '@codemirror/commands';
import { Compartment, EditorState, Prec } from '@codemirror/state';
import { EditorView, keymap } from '@codemirror/view';
import { indentUnit } from '@codemirror/language';
import { languages } from '@codemirror/language-data';
import modes from '@/modes';
import styles from '@/reviactyl/elements/style.module.css';
import useTheme from '@/plugins/useTheme';

export interface Props {
    style?: React.CSSProperties;
    initialContent?: string;
    mode: string;
    filename?: string;
    onModeChanged: (mode: string) => void;
    fetchContent: (callback: () => Promise<string>) => void;
    onContentSaved: () => void;
    onContentChanged?: (content: string) => void;
}

const findModeByFilename = (filename: string) => {
    for (let i = 0; i < modes.length; i++) {
        const info = modes[i];

        if (info?.file !== undefined && info.file.test(filename)) {
            return info;
        }
    }

    const dot = filename.lastIndexOf('.');
    const ext = dot > -1 && filename.substring(dot + 1, filename.length);

    if (ext) {
        for (let i = 0; i < modes.length; i++) {
            const info = modes[i];
            if (info?.ext !== undefined) {
                for (let j = 0; j < info.ext.length; j++) {
                    if (info.ext[j] === ext) {
                        return info;
                    }
                }
            }
        }
    }

    return undefined;
};

export default ({
    style,
    initialContent,
    filename,
    mode,
    fetchContent,
    onContentSaved,
    onModeChanged,
    onContentChanged,
}: Props) => {
    const container = useRef<HTMLDivElement>(null);
    const editor = useRef<EditorView | null>(null);
    const language = useRef(new Compartment());
    const theme = useRef(new Compartment());
    const { dark, revision } = useTheme();
    const save = useRef(onContentSaved);
    save.current = onContentSaved;
    const change = useRef(onContentChanged);
    change.current = onContentChanged;

    useEffect(() => {
        if (!container.current) return;

        const view = new EditorView({
            parent: container.current,
            state: EditorState.create({
                doc: initialContent || '',
                extensions: [
                    basicSetup,
                    EditorView.updateListener.of((update) => {
                        if (update.docChanged) change.current?.(update.state.doc.toString());
                    }),
                    EditorView.lineWrapping,
                    keymap.of([indentWithTab]),
                    EditorView.contentAttributes.of({ spellcheck: 'true', autocorrect: 'off', autocapitalize: 'off' }),
                    EditorState.tabSize.of(4),
                    indentUnit.of('    '),
                    language.current.of([]),
                    theme.current.of([]),
                    Prec.highest(
                        keymap.of([
                            {
                                key: 'Mod-s',
                                run: () => {
                                    save.current();
                                    return true;
                                },
                            },
                        ]),
                    ),
                ],
            }),
        });
        editor.current = view;

        return () => {
            editor.current = null;
            view.destroy();
        };
    }, [initialContent]);

    useEffect(() => {
        editor.current?.dispatch({
            effects: theme.current.reconfigure(
                EditorView.theme(
                    {
                        '&': { height: '100%', backgroundColor: 'transparent', color: 'rgb(var(--color-100))' },
                        '.cm-scroller': { overflow: 'auto', fontSize: '12px', lineHeight: '1.375rem' },
                        '.cm-content, .cm-cursor': {
                            caretColor: 'rgb(var(--color-100))',
                            borderLeftColor: 'rgb(var(--color-100))',
                        },
                        '.cm-gutters': {
                            backgroundColor: 'transparent',
                            border: 'none',
                            color: 'rgb(var(--text-muted))',
                        },
                        '.cm-activeLine, .cm-activeLineGutter': { backgroundColor: 'rgb(var(--color-primary) / 0.08)' },
                        '.cm-selectionBackground, &.cm-focused .cm-selectionBackground': {
                            backgroundColor: 'rgb(var(--color-primary) / 0.2)',
                        },
                        '.cm-panels': { backgroundColor: 'rgb(var(--color-900))', color: 'rgb(var(--color-100))' },
                    },
                    { dark },
                ),
            ),
        });
    }, [dark, revision, initialContent]);

    useEffect(() => {
        if (filename !== undefined) onModeChanged(findModeByFilename(filename)?.mime || 'text/plain');
    }, [filename, onModeChanged]);

    useEffect(() => {
        const view = editor.current;
        if (!view) return;

        let cancelled = false;
        const info = modes.find((item) => item.mime === mode || item.mimes?.includes(mode));
        const aliases: Record<string, string> = {
            'Git Markdown': 'Markdown',
            Golang: 'Go',
            MariaDB: 'MariaDB SQL',
            Properties: 'Properties files',
        };
        const name = info && (aliases[info.name] || info.name);
        const description = languages.find((item) => item.name.toLowerCase() === name?.toLowerCase());
        view.dispatch({ effects: language.current.reconfigure([]) });
        if (description) {
            void description.load().then(
                (support) => {
                    if (!cancelled) view.dispatch({ effects: language.current.reconfigure(support) });
                },
                () => {
                    // Keep editing as plain text if a language chunk cannot be loaded.
                },
            );
        }

        return () => {
            cancelled = true;
        };
    }, [mode, initialContent]);

    useEffect(() => {
        fetchContent(() => {
            const view = editor.current;
            return view
                ? Promise.resolve(view.state.doc.toString())
                : Promise.reject(new Error('no editor session has been configured'));
        });
    }, [fetchContent]);

    return <div className={styles.editor} style={style} ref={container} />;
};

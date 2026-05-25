import React, { useEffect, useRef, useState, lazy, Suspense } from 'react';
import type { Monaco } from '@monaco-editor/react';
import type { editor } from 'monaco-editor';
import styled from 'styled-components';
import tw from 'twin.macro';
import modes from '@/modes';
import Spinner from './Spinner';

const Editor = lazy(() => import('@monaco-editor/react'));

const EditorContainer = styled.div`
    min-height: 16rem;
    height: calc(100vh - 20rem);
    ${tw`relative rounded`};
`;

export interface Props {
    style?: React.CSSProperties;
    initialContent?: string;
    mode: string;
    filename?: string;
    onModeChanged: (mode: string) => void;
    fetchContent: (callback: () => Promise<string>) => void;
    onContentSaved: () => void;
}

const findModeByFilename = (filename: string) => {
    for (let i = 0; i < modes.length; i++) {
        const info = modes[i];

        if (info?.file && info?.file.test(filename)) {
            return info;
        }
    }

    const dot = filename.lastIndexOf('.');
    const ext = dot > -1 && filename.substring(dot + 1, filename.length);

    if (ext) {
        for (let i = 0; i < modes.length; i++) {
            const info = modes[i];
            if (info?.ext) {
                for (let j = 0; j < info?.ext.length; j++) {
                    if (info?.ext[j] === ext) {
                        return info;
                    }
                }
            }
        }
    }

    return undefined;
};

// Map MIME types to Monaco languages
const mimeToMonacoLanguage = (mime: string): string => {
    const mimeMap: Record<string, string> = {
        'text/x-csrc': 'c',
        'text/x-c++src': 'cpp',
        'text/x-csharp': 'csharp',
        'text/css': 'css',
        'text/x-dockerfile': 'dockerfile',
        'text/x-go': 'go',
        'text/html': 'html',
        'text/x-java': 'java',
        'text/javascript': 'javascript',
        'application/javascript': 'javascript',
        'application/json': 'json',
        'text/x-kotlin': 'kotlin',
        'text/x-lua': 'lua',
        'text/x-gfm': 'markdown',
        'text/x-markdown': 'markdown',
        'text/x-nginx-conf': 'nginx',
        'text/x-perl': 'perl',
        'text/x-php': 'php',
        'text/x-properties': 'ini',
        'text/x-python': 'python',
        'text/x-ruby': 'ruby',
        'text/x-rustsrc': 'rust',
        'text/x-scss': 'scss',
        'text/x-sass': 'sass',
        'text/x-sh': 'shell',
        'application/x-sh': 'shell',
        'application/x-sql': 'sql',
        'text/x-sql': 'sql',
        'text/x-swift': 'swift',
        'text/x-toml': 'toml',
        'application/xml': 'xml',
        'text/xml': 'xml',
        'text/x-yaml': 'yaml',
        'text/plain': 'plaintext',
    };

    return mimeMap[mime] || 'plaintext';
};

export default ({ style, initialContent, filename, mode, fetchContent, onContentSaved, onModeChanged }: Props) => {
    const editorRef = useRef<editor.IStandaloneCodeEditor | null>(null);
    const [monacoLanguage, setMonacoLanguage] = useState('plaintext');

    useEffect(() => {
        if (filename === undefined) {
            return;
        }

        onModeChanged(findModeByFilename(filename)?.mime || 'text/plain');
    }, [filename]);

    useEffect(() => {
        setMonacoLanguage(mimeToMonacoLanguage(mode));
    }, [mode]);

    const handleEditorDidMount = (editorInstance: editor.IStandaloneCodeEditor, monaco: Monaco) => {
        editorRef.current = editorInstance;

        editorInstance.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, () => {
            onContentSaved();
        });

        fetchContent(() => Promise.resolve(editorInstance.getValue()));

        editorInstance.focus();
    };

    useEffect(() => {
        if (editorRef.current) {
            fetchContent(() => Promise.resolve(editorRef.current!.getValue()));
        } else {
            fetchContent(() => Promise.reject(new Error('no editor session has been configured')));
        }
    }, [fetchContent]);

    return (
        <EditorContainer style={style}>
            <Suspense fallback={<Spinner centered size={Spinner.Size.LARGE} />}>
                <Editor
                    height='100%'
                    language={monacoLanguage}
                    value={initialContent || ''}
                    theme='vs-dark'
                    onMount={handleEditorDidMount}
                    loading={<Spinner centered size={Spinner.Size.LARGE} />}
                    options={{
                        fontSize: 14,
                        lineHeight: 22,
                        minimap: { enabled: true },
                        scrollBeyondLastLine: true,
                        automaticLayout: true,
                        tabSize: 4,
                        insertSpaces: true,
                        wordWrap: 'on',
                        lineNumbers: 'on',
                        folding: true,
                        fixedOverflowWidgets: true,
                        scrollbar: {
                            verticalScrollbarSize: 10,
                            horizontalScrollbarSize: 10,
                        },
                        bracketPairColorization: {
                            enabled: true,
                        },
                        matchBrackets: 'always',
                    }}
                />
            </Suspense>
        </EditorContainer>
    );
};

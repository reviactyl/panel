import React, { useEffect, useRef, useState } from 'react';
import Editor, { loader } from '@monaco-editor/react';
import type { Monaco } from '@monaco-editor/react';
import type { editor } from 'monaco-editor';
import * as monaco from 'monaco-editor';
import styled from 'styled-components';
import tw from 'twin.macro';
import modes from '@/modes';
import Spinner from './Spinner';

loader.config({ monaco });

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

export default function CodeEditor({
    style,
    initialContent,
    filename,
    mode,
    fetchContent,
    onContentSaved,
    onModeChanged,
}: Props) {
    const editorRef = useRef<editor.IStandaloneCodeEditor | null>(null);
    const [monacoLanguage, setMonacoLanguage] = useState('plaintext');

    useEffect(() => {
        if (filename === undefined) {
            return;
        }
        onModeChanged(findModeByFilename(filename)?.mode || 'plaintext');
    }, [filename]);

    useEffect(() => {
        setMonacoLanguage(mode);
    }, [mode]);

    const handleEditorDidMount = (editorInstance: editor.IStandaloneCodeEditor, monacoInstance: Monaco) => {
        editorRef.current = editorInstance;

        editorInstance.addCommand(monacoInstance.KeyMod.CtrlCmd | monacoInstance.KeyCode.KeyS, () => onContentSaved());

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
        </EditorContainer>
    );
}

import React, { useCallback, useEffect, useState } from 'react';
import CodeMirror from 'codemirror';
import styled from 'styled-components';
import tw from 'twin.macro';
import modes from '@/modes';

import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/ayu-mirage.css';
import 'codemirror/addon/edit/closebrackets';
import 'codemirror/addon/edit/closetag';
import 'codemirror/addon/edit/matchbrackets';
import 'codemirror/addon/edit/matchtags';
import 'codemirror/addon/edit/trailingspace';
import 'codemirror/addon/fold/foldcode';
import 'codemirror/addon/fold/foldgutter.css';
import 'codemirror/addon/fold/foldgutter';
import 'codemirror/addon/fold/brace-fold';
import 'codemirror/addon/fold/comment-fold';
import 'codemirror/addon/fold/indent-fold';
import 'codemirror/addon/fold/markdown-fold';
import 'codemirror/addon/fold/xml-fold';
import 'codemirror/addon/hint/css-hint';
import 'codemirror/addon/hint/html-hint';
import 'codemirror/addon/hint/javascript-hint';
import 'codemirror/addon/hint/show-hint.css';
import 'codemirror/addon/hint/show-hint';
import 'codemirror/addon/hint/sql-hint';
import 'codemirror/addon/hint/xml-hint';
import 'codemirror/addon/mode/simple';
import 'codemirror/addon/dialog/dialog.css';
import 'codemirror/addon/dialog/dialog';
import 'codemirror/addon/scroll/annotatescrollbar';
import 'codemirror/addon/scroll/scrollpastend';
import 'codemirror/addon/scroll/simplescrollbars.css';
import 'codemirror/addon/scroll/simplescrollbars';
import 'codemirror/addon/search/jump-to-line';
import 'codemirror/addon/search/match-highlighter';
import 'codemirror/addon/search/matchesonscrollbar.css';
import 'codemirror/addon/search/matchesonscrollbar';
import 'codemirror/addon/search/search';
import 'codemirror/addon/search/searchcursor';

import 'codemirror/mode/brainfuck/brainfuck';
import 'codemirror/mode/clike/clike';
import 'codemirror/mode/css/css';
import 'codemirror/mode/dart/dart';
import 'codemirror/mode/diff/diff';
import 'codemirror/mode/dockerfile/dockerfile';
import 'codemirror/mode/erlang/erlang';
import 'codemirror/mode/gfm/gfm';
import 'codemirror/mode/go/go';
import 'codemirror/mode/handlebars/handlebars';
import 'codemirror/mode/htmlembedded/htmlembedded';
import 'codemirror/mode/htmlmixed/htmlmixed';
import 'codemirror/mode/http/http';
import 'codemirror/mode/javascript/javascript';
import 'codemirror/mode/jsx/jsx';
import 'codemirror/mode/julia/julia';
import 'codemirror/mode/lua/lua';
import 'codemirror/mode/markdown/markdown';
import 'codemirror/mode/nginx/nginx';
import 'codemirror/mode/perl/perl';
import 'codemirror/mode/php/php';
import 'codemirror/mode/properties/properties';
import 'codemirror/mode/protobuf/protobuf';
import 'codemirror/mode/pug/pug';
import 'codemirror/mode/python/python';
import 'codemirror/mode/rpm/rpm';
import 'codemirror/mode/ruby/ruby';
import 'codemirror/mode/rust/rust';
import 'codemirror/mode/sass/sass';
import 'codemirror/mode/shell/shell';
import 'codemirror/mode/smarty/smarty';
import 'codemirror/mode/sql/sql';
import 'codemirror/mode/swift/swift';
import 'codemirror/mode/toml/toml';
import 'codemirror/mode/twig/twig';
import 'codemirror/mode/vue/vue';
import 'codemirror/mode/xml/xml';
import 'codemirror/mode/yaml/yaml';

const EditorContainer = styled.div`
    min-height: 16rem;
    height: calc(100vh - 20rem);
    ${tw`relative`};

    > div {
        ${tw`rounded h-full`};
    }

    .CodeMirror {
        font-size: 12px;
        line-height: 1.375rem;
        background: transparent !important;
        background-color: transparent !important;
    }

    .CodeMirror-scroll {
        background: transparent !important;
        background-color: transparent !important;
    }

    .CodeMirror-gutters {
        background: transparent !important;
        background-color: transparent !important;
    }

    .CodeMirror-linenumber {
        padding: 1px 12px 0 12px !important;
    }

    .CodeMirror-foldmarker {
        color: #cbccc6;
        text-shadow: none;
        margin-left: 0.25rem;
        margin-right: 0.25rem;
    }
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

export default ({ style, initialContent, filename, mode, fetchContent, onContentSaved, onModeChanged }: Props) => {
    const [editor, setEditor] = useState<CodeMirror.Editor>();

    const ref = useCallback<(_?: unknown) => void>((node) => {
        if (!node) {
            return;
        }

        const e = CodeMirror.fromTextArea(node as HTMLTextAreaElement, {
            mode: 'text/plain',
            theme: 'ayu-mirage',
            indentUnit: 4,
            smartIndent: true,
            tabSize: 4,
            indentWithTabs: false,
            lineWrapping: true,
            lineNumbers: true,
            fixedGutter: true,
            scrollbarStyle: 'overlay',
            coverGutterNextToScrollbar: false,
            readOnly: false,
            showCursorWhenSelecting: false,
            autofocus: false,
            spellcheck: true,
            autocorrect: false,
            autocapitalize: false,
            lint: false,
            autoCloseBrackets: true,
            matchBrackets: true,
            gutters: ['CodeMirror-linenumbers', 'CodeMirror-foldgutter'],
        });

        setEditor(e);
    }, []);

    useEffect(() => {
        if (filename === undefined) {
            return;
        }

        onModeChanged(findModeByFilename(filename)?.mime || 'text/plain');
    }, [filename]);

    useEffect(() => {
        void (editor && editor.setOption('mode', mode));
    }, [editor, mode]);

    useEffect(() => {
        if (editor) {
            editor.setValue(initialContent || '');
            // Reset the history so that "Ctrl+Z" doesn't delete the intial content
            // we just set above.
            editor.setHistory({ done: [], undone: [] });
        }
    }, [editor, initialContent]);

    useEffect(() => {
        if (!editor) {
            fetchContent(() => Promise.reject(new Error('no editor session has been configured')));
            return;
        }

        editor.addKeyMap({
            'Ctrl-S': () => onContentSaved(),
            'Cmd-S': () => onContentSaved(),
        });

        fetchContent(() => Promise.resolve(editor.getValue()));
    }, [editor, fetchContent, onContentSaved]);

    return (
        <EditorContainer style={style}>
            <textarea ref={ref} />
        </EditorContainer>
    );
};

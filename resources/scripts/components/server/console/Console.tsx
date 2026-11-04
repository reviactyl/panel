import React, { useEffect, useRef, useState } from 'react';
import { SearchBarAddon } from '@/plugins/XtermSearchBarAddon';
import { ITerminalAddon, ITerminalOptions, ITheme, Terminal } from '@xterm/xterm';
import { FitAddon } from '@xterm/addon-fit';
import { SearchAddon } from '@xterm/addon-search';
import { WebLinksAddon } from '@xterm/addon-web-links';
import { Unicode11Addon } from '@xterm/addon-unicode11';
import { ScrollDownHelperAddon } from '@/plugins/XtermScrollDownHelperAddon';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import { ServerContext } from '@/state/server';
import { usePermissions } from '@/plugins/usePermissions';
import { debounce } from 'debounce';
import { usePersistedState } from '@/plugins/usePersistedState';
import { SocketEvent, SocketRequest } from '@/components/server/events';
import classNames from 'classnames';
import { ChevronDoubleRightIcon } from '@heroicons/react/solid';

import '@xterm/xterm/css/xterm.css';
import styles from './style.module.css';
import { useTranslation } from 'react-i18next';

const theme: ITheme = {
    background: 'transparent',
};

const terminalProps: ITerminalOptions = {
    disableStdin: true,
    cursorStyle: 'underline',
    allowTransparency: true,
    fontSize: 12,
    fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
    allowProposedApi: true,
    theme: theme,
};

export default () => {
    const { t } = useTranslation('server/console');
    const containerText = ServerContext.useStoreState((state) => state.server.data?.containerText);
    const TERMINAL_PRELUDE = `\u001b[1m\u001b[33m${containerText} \u001b[0m`;
    const ref = useRef<HTMLDivElement>(null);
    const terminalRef = useRef<Terminal | null>(null);
    const { connected, instance } = ServerContext.useStoreState((state) => state.socket);
    const [canSendCommands] = usePermissions(['control.console']);
    const serverId = ServerContext.useStoreState((state) => state.server.data!.id);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data!.isTransferring);
    const [history, setHistory] = usePersistedState<string[]>(`${serverId}:command_history`, []);
    const [historyIndex, setHistoryIndex] = useState(-1);

    const handleConsoleOutput = (line: string, prelude = false) =>
        terminalRef.current?.writeln(
            (prelude ? TERMINAL_PRELUDE : '') + line.replace(/(?:\r\n|\r|\n)$/im, '') + '\u001b[0m',
        );

    const handleTransferStatus = (status: string) => {
        switch (status) {
            // Sent by either the source or target node if a failure occurs.
            case 'failure':
                terminalRef.current?.writeln(TERMINAL_PRELUDE + 'Transfer has failed.\u001b[0m');
                return;
        }
    };

    const handleDaemonErrorOutput = (line: string) =>
        terminalRef.current?.writeln(
            TERMINAL_PRELUDE + '\u001b[1m\u001b[41m' + line.replace(/(?:\r\n|\r|\n)$/im, '') + '\u001b[0m',
        );

    const handlePowerChangeEvent = (state: string) =>
        terminalRef.current?.writeln(TERMINAL_PRELUDE + 'Server marked as ' + state + '...\u001b[0m');

    const handleCommandKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.nativeEvent.isComposing || e.nativeEvent.keyCode === 229) {
            return;
        }

        if (e.key === 'ArrowUp') {
            const newIndex = Math.min(historyIndex + 1, history!.length - 1);

            setHistoryIndex(newIndex);
            e.currentTarget.value = history![newIndex] || '';

            // By default up arrow will also bring the cursor to the start of the line,
            // so we'll preventDefault to keep it at the end.
            e.preventDefault();
        }

        if (e.key === 'ArrowDown') {
            const newIndex = Math.max(historyIndex - 1, -1);

            setHistoryIndex(newIndex);
            e.currentTarget.value = history![newIndex] || '';
        }

        const command = e.currentTarget.value;
        if (e.key === 'Enter' && command.length > 0) {
            setHistory((prev) => [command, ...prev!].slice(0, 32));
            setHistoryIndex(-1);

            void (instance && instance.send('send command', command));
            e.currentTarget.value = '';
        }
    };

    useEffect(() => {
        const container = ref.current;
        if (!container) return;

        const terminal = new Terminal({ ...terminalProps });
        const fitAddon = new FitAddon();
        const searchAddon = new SearchAddon();
        const searchBar = new SearchBarAddon(searchAddon);
        terminalRef.current = terminal;
        terminal.loadAddon(fitAddon);
        terminal.loadAddon(searchAddon);
        terminal.loadAddon(searchBar as unknown as ITerminalAddon);
        terminal.loadAddon(new WebLinksAddon());
        terminal.loadAddon(new Unicode11Addon());
        terminal.loadAddon(new ScrollDownHelperAddon());

        terminal.open(container);
        terminal.unicode.activeVersion = '11';
        fitAddon.fit();

        terminal.attachCustomKeyEventHandler((e: KeyboardEvent) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
                document.execCommand('copy');
                return false;
            } else if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                searchBar.show();
                return false;
            } else if (e.key === 'Escape') {
                searchBar.hide();
            }
            return true;
        });

        let lastY = 0;
        const handleTouchStart = (e: TouchEvent) => {
            lastY = e.touches[0]?.clientY ?? 0;
        };
        const handleTouchMove = (e: TouchEvent) => {
            const currentY = e.touches[0]?.clientY ?? lastY;
            const dy = lastY - currentY;
            lastY = currentY;
            const lines = Math.round(dy / (terminal.options.fontSize ?? 12));
            if (lines !== 0) terminal.scrollLines(lines);
            e.preventDefault();
        };
        const handleResize = debounce(() => fitAddon.fit(), 100);

        container.addEventListener('touchstart', handleTouchStart, { passive: true });
        container.addEventListener('touchmove', handleTouchMove, { passive: false });
        window.addEventListener('resize', handleResize);

        return () => {
            window.removeEventListener('resize', handleResize);
            handleResize.clear();
            container.removeEventListener('touchstart', handleTouchStart);
            container.removeEventListener('touchmove', handleTouchMove);
            terminalRef.current = null;
            terminal.dispose();
        };
    }, []);

    useEffect(() => {
        const listeners: Record<string, (s: string) => void> = {
            [SocketEvent.STATUS]: handlePowerChangeEvent,
            [SocketEvent.CONSOLE_OUTPUT]: handleConsoleOutput,
            [SocketEvent.INSTALL_OUTPUT]: handleConsoleOutput,
            [SocketEvent.TRANSFER_LOGS]: handleConsoleOutput,
            [SocketEvent.TRANSFER_STATUS]: handleTransferStatus,
            [SocketEvent.DAEMON_MESSAGE]: (line) => handleConsoleOutput(line, true),
            [SocketEvent.DAEMON_ERROR]: handleDaemonErrorOutput,
        };

        if (connected && instance) {
            // Do not clear the console if the server is being transferred.
            if (!isTransferring) {
                terminalRef.current?.clear();
            }

            Object.entries(listeners).forEach(([key, listener]) => {
                if (listener) instance.addListener(key, listener);
            });
            instance.send(SocketRequest.SEND_LOGS);
        }

        return () => {
            if (instance) {
                Object.entries(listeners).forEach(([key, listener]) => {
                    if (listener) instance.removeListener(key, listener);
                });
            }
        };
    }, [connected, instance]);

    return (
        <div className={classNames(styles.terminal, 'relative')}>
            <SpinnerOverlay visible={!connected} size={'large'} />
            <div
                className={classNames(styles.container, styles.overflows_container, {
                    'rounded-b': !canSendCommands,
                })}
            >
                <div className={'h-full'}>
                    <div id={styles.terminal} ref={ref} />
                </div>
            </div>
            {canSendCommands && (
                <div className={classNames('relative', styles.overflows_container)}>
                    <input
                        className={classNames('peer', styles.command_input)}
                        type={'text'}
                        placeholder={t('run-command')}
                        aria-label={'Console command input.'}
                        disabled={!instance || !connected}
                        onKeyDown={handleCommandKeyDown}
                        autoCorrect={'off'}
                        autoCapitalize={'none'}
                    />
                    <div
                        className={classNames(
                            'text-gray-100 peer-focus:text-gray-50 peer-focus:animate-pulse',
                            styles.command_icon,
                        )}
                    >
                        <ChevronDoubleRightIcon className={'w-4 h-4'} />
                    </div>
                </div>
            )}
        </div>
    );
};

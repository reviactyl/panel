import React, { createRef } from 'react';
import classNames from 'classnames';
import Fade from '@/reviactyl/elements/Fade';

interface Props {
    children: React.ReactNode;
    renderToggle: (onClick: (e: React.MouseEvent<any, MouseEvent>) => void, visible: boolean) => React.ReactNode;
    manageFocus?: boolean;
    id?: string;
}

export const DropdownButtonRow = ({
    danger,
    className,
    ...props
}: React.ButtonHTMLAttributes<HTMLButtonElement> & { danger?: boolean }) => (
    <button
        className={classNames(
            'flex w-full items-center rounded-ui p-2 text-gray-400 transition-all duration-150 hover:bg-gray-700',
            danger ? 'hover:text-red-300' : 'hover:text-gray-300',
            className,
        )}
        {...props}
    />
);

interface State {
    posX: number;
    visible: boolean;
}

class DropdownMenu extends React.PureComponent<Props, State> {
    menu = createRef<HTMLDivElement>();
    container = createRef<HTMLDivElement>();
    listenerTimer?: ReturnType<typeof setTimeout>;

    override state: State = {
        posX: 0,
        visible: false,
    };

    override componentWillUnmount() {
        this.removeListeners();
    }

    override componentDidUpdate(_prevProps: Readonly<Props>, prevState: Readonly<State>) {
        const menu = this.menu.current;
        if (this.props.manageFocus && menu) menu.inert = !this.state.visible;

        if (this.state.visible && menu && (!prevState.visible || prevState.posX !== this.state.posX)) {
            if (!prevState.visible) {
                // Delay by one tick so the event that opened this menu finishes
                // propagating before we register listeners that can close it.
                this.listenerTimer = setTimeout(() => {
                    document.addEventListener('click', this.windowListener);
                    document.addEventListener('contextmenu', this.contextMenuListener);
                    if (this.props.manageFocus) document.addEventListener('keydown', this.keyListener);
                }, 0);
            }
            const left = this.state.posX - menu.clientWidth;
            menu.style.left = `${Math.round(Math.max(8, Math.min(left, window.innerWidth - menu.clientWidth - 8)))}px`;
            menu.style.marginTop = '0px';
            menu.style.marginTop = `${Math.min(0, window.innerHeight - menu.getBoundingClientRect().bottom - 8)}px`;
        }

        if (!this.state.visible && prevState.visible) {
            this.removeListeners();
        }
    }

    removeListeners = () => {
        clearTimeout(this.listenerTimer);
        document.removeEventListener('click', this.windowListener);
        document.removeEventListener('contextmenu', this.contextMenuListener);
        document.removeEventListener('keydown', this.keyListener);
    };

    onClickHandler = (e: React.MouseEvent<any, MouseEvent>) => {
        e.preventDefault();
        this.toggleMenu(e.detail === 0 ? e.currentTarget.getBoundingClientRect().right : e.clientX);
    };

    restoreFocus = () => {
        if (this.props.manageFocus) this.container.current?.querySelector('button')?.focus();
    };

    closeMenu = (restoreFocus = true) => {
        if (restoreFocus) this.restoreFocus();
        this.setState({ visible: false });
    };

    keyListener = (e: KeyboardEvent) => {
        if (e.key === 'Escape' && this.state.visible) {
            e.preventDefault();
            e.stopPropagation();
            this.closeMenu();
        }
    };

    contextMenuListener = (e: MouseEvent) => {
        const menu = this.menu.current;

        if (!this.state.visible || !menu) {
            return;
        }

        if (e.defaultPrevented || e.target === menu || menu.contains(e.target as Node)) {
            return;
        }

        this.closeMenu(false);
    };

    windowListener = (e: MouseEvent) => {
        const menu = this.menu.current;

        if (e.button === 2 || !this.state.visible || !menu) {
            return;
        }

        if (e.target === menu || menu.contains(e.target as Node)) {
            return;
        }

        if (e.target !== menu && !menu.contains(e.target as Node)) {
            this.closeMenu();
        }
    };

    toggleMenu = (posX: number) =>
        this.setState((s) => ({
            posX: !s.visible ? posX : s.posX,
            visible: !s.visible,
        }));

    triggerMenu = (posX: number) =>
        this.setState({
            posX,
            visible: true,
        });

    override render() {
        return (
            <div
                ref={this.container}
                onBlur={(e) => {
                    if (this.props.manageFocus && this.state.visible && !e.currentTarget.contains(e.relatedTarget)) {
                        this.closeMenu(false);
                    }
                }}
            >
                {this.props.renderToggle(this.onClickHandler, this.state.visible)}
                <Fade timeout={150} in={this.state.visible} unmountOnExit>
                    <div
                        ref={this.menu}
                        id={this.props.id}
                        inert={this.props.manageFocus && !this.state.visible}
                        onClick={(e) => {
                            e.stopPropagation();
                            this.closeMenu();
                        }}
                        style={{ width: '12rem' }}
                        className='absolute z-50 rounded-ui border border-gray-800 bg-gray-800 p-2 text-gray-100 shadow-lg'
                    >
                        {this.props.children}
                    </div>
                </Fade>
            </div>
        );
    }
}

export default DropdownMenu;

import React, { createRef } from 'react';
import classNames from 'classnames';
import Fade from '@/reviactyl/elements/Fade';

interface Props {
    children: React.ReactNode;
    renderToggle: (onClick: (e: React.MouseEvent<any, MouseEvent>) => void) => React.ReactNode;
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
            className
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

    override state: State = {
        posX: 0,
        visible: false,
    };

    override componentWillUnmount() {
        this.removeListeners();
    }

    override componentDidUpdate(_prevProps: Readonly<Props>, prevState: Readonly<State>) {
        const menu = this.menu.current;

        if (this.state.visible && menu && (!prevState.visible || prevState.posX !== this.state.posX)) {
            if (!prevState.visible) {
                // Delay by one tick so the event that opened this menu finishes
                // propagating before we register listeners that can close it.
                setTimeout(() => {
                    document.addEventListener('click', this.windowListener);
                    document.addEventListener('contextmenu', this.contextMenuListener);
                }, 0);
            }
            menu.style.left = `${Math.round(this.state.posX - menu.clientWidth)}px`;
        }

        if (!this.state.visible && prevState.visible) {
            this.removeListeners();
        }
    }

    removeListeners = () => {
        document.removeEventListener('click', this.windowListener);
        document.removeEventListener('contextmenu', this.contextMenuListener);
    };

    onClickHandler = (e: React.MouseEvent<any, MouseEvent>) => {
        e.preventDefault();
        this.toggleMenu(e.clientX);
    };

    contextMenuListener = (e: MouseEvent) => {
        const menu = this.menu.current;

        if (!this.state.visible || !menu) {
            return;
        }

        if (e.defaultPrevented || e.target === menu || menu.contains(e.target as Node)) {
            return;
        }

        this.setState({ visible: false });
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
            this.setState({ visible: false });
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
            <div>
                {this.props.renderToggle(this.onClickHandler)}
                <Fade timeout={150} in={this.state.visible} unmountOnExit>
                    <div
                        ref={this.menu}
                        onClick={(e) => {
                            e.stopPropagation();
                            this.setState({ visible: false });
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

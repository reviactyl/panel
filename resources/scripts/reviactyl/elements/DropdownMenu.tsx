import React, { createRef } from 'react';
import styled from 'styled-components';
import tw from 'twin.macro';
import Fade from '@/reviactyl/elements/Fade';

interface Props {
    children: React.ReactNode;
    renderToggle: (onClick: (e: React.MouseEvent<any, MouseEvent>) => void) => React.ReactNode;
}

export const DropdownButtonRow = styled.button<{ danger?: boolean }>`
    ${tw`p-2 flex items-center rounded-ui w-full text-gray-500`};
    transition: 150ms all ease;

    &:hover {
        ${(props) => (props.danger ? tw`text-red-700 bg-red-100` : tw`text-gray-700 bg-gray-100`)};
    }
`;

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
                        css={tw`absolute bg-gray-700 p-2 rounded-ui border border-gray-600 shadow-lg text-gray-100 z-50`}
                    >
                        {this.props.children}
                    </div>
                </Fade>
            </div>
        );
    }
}

export default DropdownMenu;

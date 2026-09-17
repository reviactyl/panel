import React, { ElementType, forwardRef, useMemo } from 'react';
import { Menu, Transition } from '@headlessui/react';
import styles from './style.module.css';
import classNames from 'classnames';
import DropdownItem from '@/reviactyl/elements/dropdown/DropdownItem';
import DropdownButton from '@/reviactyl/elements/dropdown/DropdownButton';

interface Props {
    as?: ElementType;
    children: React.ReactNode;
}

const DropdownGap = ({ invisible }: { invisible?: boolean }) => (
    <div className={classNames('border m-2', { 'border-gray-800': !invisible, 'border-transparent': invisible })} />
);

type TypedChild = React.ReactElement & {
    type?: React.ReactElement;
};

const Dropdown = forwardRef<HTMLDivElement, Props>(({ as, children }, ref) => {
    const [Button, items] = useMemo(() => {
        const list = React.Children.toArray(children) as unknown as TypedChild[];

        return [
            list.filter((child) => child.type === DropdownButton),
            list.filter((child) => child.type !== DropdownButton),
        ];
    }, [children]);

    if (!Button) {
        throw new Error('Cannot mount <Dropdown /> component without a child <Dropdown.Button />.');
    }

    return (
        <Menu as={as || 'div'} className={styles.menu} ref={ref}>
            {Button}
            <Transition
                enter={'transition duration-100 ease-out'}
                enterFrom={'transition scale-95 opacity-0'}
                enterTo={'transform scale-100 opacity-100'}
                leave={'transition duration-75 ease-out'}
                leaveFrom={'transform scale-100 opacity-100'}
                leaveTo={'transform scale-95 opacity-0'}
            >
                <Menu.Items className={classNames(styles.items_container, 'w-56')}>
                    <div className={'px-1 py-1'}>{items}</div>
                </Menu.Items>
            </Transition>
        </Menu>
    );
});

type DropdownComponent = typeof Dropdown & {
    Button: typeof DropdownButton;
    Item: typeof DropdownItem;
    Gap: typeof DropdownGap;
};

const _Dropdown = Object.assign(Dropdown, {
    Button: DropdownButton,
    Item: DropdownItem,
    Gap: DropdownGap,
}) as DropdownComponent;

export { _Dropdown as default };

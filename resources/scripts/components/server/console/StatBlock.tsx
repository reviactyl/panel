import React from 'react';
import Icon from '@/reviactyl/elements/Icon';
import { IconType } from 'react-icons';
import classNames from 'classnames';
import styles from './style.module.css';
import CopyOnClick from '@/reviactyl/elements/CopyOnClick';

interface StatBlockProps {
    title: string;
    copyOnClick?: string;
    color?: string | undefined;
    icon: IconType;
    children: React.ReactNode;
    className?: string;
}

export default ({ title, copyOnClick, icon, color, className, children }: StatBlockProps) => {
    return (
        <CopyOnClick text={copyOnClick}>
            <div className={classNames(styles.stat_block, 'bg-gray-900', className)}>
                <div className={classNames(styles.status_bar, color || 'bg-secondary')} />
                <div className={classNames(styles.icon, color || 'bg-gray-700')}>
                    <Icon
                        icon={icon}
                        className={classNames({
                            'text-gray-100': !color || color === 'bg-gray-700',
                            'text-gray-50': color && color !== 'bg-gray-700',
                        })}
                    />
                </div>
                <div className={'flex flex-col justify-center overflow-hidden w-full'}>
                    <p className={'font-header leading-tight text-xs md:text-sm text-gray-200'}>{title}</p>
                    <div className={'h-[1.75rem] w-full font-semibold text-gray-50 text-xl'}>{children}</div>
                </div>
            </div>
        </CopyOnClick>
    );
};

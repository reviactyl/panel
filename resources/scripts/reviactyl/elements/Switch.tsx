import React, { useMemo } from 'react';
import { v4 } from 'uuid';
import Label from '@/reviactyl/elements/Label';
import Input from '@/reviactyl/elements/Input';

export interface SwitchProps {
    name: string;
    label?: string;
    description?: string;
    defaultChecked?: boolean;
    readOnly?: boolean;
    onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
    children?: React.ReactNode;
}

const Switch = ({ name, label, description, defaultChecked, readOnly, onChange, children }: SwitchProps) => {
    const uuid = useMemo(() => v4(), []);

    return (
        <div className='flex items-center'>
            <div className='relative w-12 shrink-0 select-none leading-normal'>
                {children || (
                    <Input
                        id={uuid}
                        name={name}
                        type='checkbox'
                        className='peer sr-only'
                        onChange={(e) => onChange && onChange(e)}
                        defaultChecked={defaultChecked}
                        disabled={readOnly}
                    />
                )}
                <label
                    htmlFor={uuid}
                    className="relative mb-0 block h-6 cursor-pointer overflow-hidden rounded-full border border-gray-600 bg-gray-700 shadow-inner transition-colors duration-150 before:absolute before:left-0.5 before:top-px before:block before:h-5 before:w-5 before:rounded-full before:border before:border-gray-300 before:bg-white before:shadow-sm before:content-[''] before:transition-transform before:duration-150 peer-checked:border-reviactyl/80 peer-checked:bg-reviactyl peer-checked:shadow-none peer-checked:before:translate-x-6 peer-focus-visible:ring-2 peer-focus-visible:ring-reviactyl/60 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-gray-900 peer-disabled:cursor-not-allowed peer-disabled:opacity-60"
                />
            </div>
            {(label || description) && (
                <div className='ml-4 w-full'>
                    {label && (
                        <Label className={`cursor-pointer ${description ? 'mb-0' : ''}`} htmlFor={uuid}>
                            {label}
                        </Label>
                    )}
                    {description && <p className='mt-2 text-sm text-gray-400'>{description}</p>}
                </div>
            )}
        </div>
    );
};

export default Switch;

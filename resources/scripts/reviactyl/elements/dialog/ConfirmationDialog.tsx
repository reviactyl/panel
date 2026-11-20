import React from 'react';
import { Dialog, RenderDialogProps } from './';
import { Button } from '@/reviactyl/components/button/index';
import Spinner from '@/reviactyl/elements/Spinner';

type ConfirmationProps = Omit<RenderDialogProps, 'description' | 'children'> & {
    children: React.ReactNode;
    confirm?: string | undefined;
    onConfirmed: (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => void;
    confirmDisabled?: boolean;
    confirmLoading?: boolean;
};

export default ({
    confirm = 'Okay',
    children,
    onConfirmed,
    confirmDisabled = false,
    confirmLoading = false,
    ...props
}: ConfirmationProps) => {
    return (
        <Dialog {...props} description={typeof children === 'string' ? children : undefined}>
            {typeof children !== 'string' && children}
            <Dialog.Footer>
                <Button.Text onClick={props.onClose}>Cancel</Button.Text>
                <Button.Danger disabled={confirmDisabled} aria-busy={confirmLoading} onClick={onConfirmed}>
                    {confirmLoading ? <Spinner size='small' /> : confirm}
                </Button.Danger>
            </Dialog.Footer>
        </Dialog>
    );
};

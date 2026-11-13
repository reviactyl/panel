import classNames from 'classnames';

export type FlashMessageType = 'success' | 'info' | 'warning' | 'error';

interface Props {
    title?: string;
    children: string;
    type?: FlashMessageType;
}

const styling = (type?: FlashMessageType): string => {
    switch (type) {
        case 'error':
            return 'bg-danger/10 border-danger/40';
        case 'info':
            return 'bg-primary-600/20 border-primary-800/40';
        case 'success':
            return 'bg-success/10 border-success/40';
        case 'warning':
            return 'bg-yellow-600/20 border-yellow-800/40';
        default:
            return 'bg-gray-900/20 border-gray-800/40';
    }
};

const getBackground = (type?: FlashMessageType): string => {
    switch (type) {
        case 'error':
            return 'bg-danger/15';
        case 'info':
            return 'bg-primary-500/15';
        case 'success':
            return 'bg-success/15';
        case 'warning':
            return 'bg-yellow-500/15';
        default:
            return 'bg-gray-700/60';
    }
};

const MessageBox = ({ title, children, type }: Props) => (
    <div
        className={classNames(
            'flex w-full items-center rounded-ui border p-2 text-sm leading-normal text-gray-100 lg:inline-flex',
            styling(type),
        )}
        role='alert'
    >
        {title && (
            <span
                className={classNames(
                    'title mr-3 flex rounded-full px-2 py-1 text-xs font-bold leading-none uppercase backdrop-blur-md',
                    getBackground(type),
                )}
            >
                {title}
            </span>
        )}
        <span className='mr-2 flex-auto text-left'>{children}</span>
    </div>
);
MessageBox.displayName = 'MessageBox';

export default MessageBox;

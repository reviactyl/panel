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
            return 'bg-red-600/50 border-red-800/80';
        case 'info':
            return 'bg-primary-600/50 border-primary-800/80';
        case 'success':
            return 'bg-green-600/50 border-green-800/80';
        case 'warning':
            return 'bg-yellow-600/50 border-yellow-800/80';
        default:
            return 'border-gray-800/80';
    }
};

const getBackground = (type?: FlashMessageType): string => {
    switch (type) {
        case 'error':
            return 'bg-red-500/60';
        case 'info':
            return 'bg-primary-500/60';
        case 'success':
            return 'bg-green-500/60';
        case 'warning':
            return 'bg-yellow-500/60';
        default:
            return '';
    }
};

const MessageBox = ({ title, children, type }: Props) => (
    <div
        className={classNames(
            'flex w-full items-center rounded-ui border p-2 text-sm leading-normal text-white lg:inline-flex',
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

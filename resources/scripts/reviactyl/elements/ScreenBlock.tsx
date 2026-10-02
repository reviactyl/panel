import PageContentBlock from '@/reviactyl/elements/PageContentBlock';
import Button from '@/reviactyl/elements/Button';
import Card from '@/reviactyl/ui/Card';
import styles from '@/reviactyl/elements/style.module.css';
import { FaArrowLeft, FaArrowsRotate, FaCircleExclamation, FaMagnifyingGlass } from 'react-icons/fa6';

interface ScreenBlockProps {
    title: string;
    message: string;
    onBack?: () => void;
    onRetry?: () => void;
}

const ScreenBlock = ({ title, message, onBack, onRetry }: ScreenBlockProps) => {
    const Icon = title === '404' ? FaMagnifyingGlass : FaCircleExclamation;

    return (
        <PageContentBlock>
            <div className='flex items-center justify-center'>
                <Card className='relative w-full max-w-2xl p-6 sm:p-10'>
                    {(onBack || onRetry) && (
                        <Button
                            onClick={onRetry || onBack}
                            className={`absolute left-4 top-4 flex h-9 w-9 items-center justify-center rounded-full p-0 ${
                                onRetry ? styles.retryButton : ''
                            }`}
                        >
                            {onRetry ? <FaArrowsRotate /> : <FaArrowLeft />}
                        </Button>
                    )}

                    <div className='flex flex-col items-center gap-5 pt-8 text-center sm:flex-row sm:gap-6 sm:pt-4 sm:text-left'>
                        <div className='flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gray-800/60 text-gray-300'>
                            <Icon className='h-9 w-9' aria-hidden='true' />
                        </div>

                        <div>
                            <h2 className='text-2xl font-bold text-gray-100 sm:text-3xl'>{title}</h2>
                            <p className='mt-2 text-sm leading-6 text-gray-400 sm:text-base'>{message}</p>
                        </div>
                    </div>
                </Card>
            </div>
        </PageContentBlock>
    );
};

interface ServerErrorProps {
    title?: string;
    message: string;
    onRetry?: () => void;
    onBack?: () => void;
}

const ServerError = ({ title = 'Something went wrong', message, onRetry, onBack }: ServerErrorProps) => (
    <ScreenBlock title={title} message={message} onRetry={onRetry} onBack={onBack} />
);

interface NotFoundProps {
    title?: string;
    message?: string;
    onBack?: () => void;
}

const NotFound = ({ title = '404', message = 'The requested resource was not found.', onBack }: NotFoundProps) => (
    <ScreenBlock title={title} message={message} onBack={onBack} />
);

export { ServerError, NotFound };
export default ScreenBlock;

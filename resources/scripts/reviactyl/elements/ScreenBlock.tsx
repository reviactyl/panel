import PageContentBlock from '@/reviactyl/elements/PageContentBlock';
import { FaArrowLeft, FaArrowsRotate } from 'react-icons/fa6';
import Button from '@/reviactyl/elements/Button';
import NotFoundSvg from '@/assets/images/not_found.svg';
import ServerErrorSvg from '@/assets/images/server_error.svg';
import Card from '@/reviactyl/ui/Card';
import styles from '@/reviactyl/elements/style.module.css';

interface BaseProps {
    title: string;
    image: string;
    message: string;
    onRetry?: () => void;
    onBack?: () => void;
}

interface PropsWithRetry extends BaseProps {
    onRetry?: () => void;
    onBack?: never;
}

interface PropsWithBack extends BaseProps {
    onBack?: () => void;
    onRetry?: never;
}

export type ScreenBlockProps = PropsWithBack | PropsWithRetry;

const ScreenBlock = ({ title, image, message, onBack, onRetry }: ScreenBlockProps) => (
    <PageContentBlock>
        <div className='flex justify-center'>
            <Card className='relative w-full p-12 sm:w-3/4 md:w-1/2 md:p-20'>
                {(typeof onBack === 'function' || typeof onRetry === 'function') && (
                    <div className='absolute top-0 left-0 mt-4 ml-4'>
                        <Button
                            onClick={() => (onRetry ? onRetry() : onBack ? onBack() : null)}
                            className={`flex h-8 w-8 items-center justify-center rounded-full p-0 ${
                                onRetry ? styles.retryButton : ''
                            }`}
                        >
                            {onRetry ? <FaArrowsRotate /> : <FaArrowLeft />}
                        </Button>
                    </div>
                )}
                <div className='grid grid-rows-2 gap-x-4 items-center grid-cols-[auto,1fr]'>
                    <img src={image} className='w-20 h-20 row-span-2 select-none' />
                    <h2 className='text-gray-200 font-bold text-3xl'>{title}</h2>
                    <p className='text-sm text-gray-100'>{message}</p>
                </div>
            </Card>
        </div>
    </PageContentBlock>
);

type ServerErrorProps = (Omit<PropsWithBack, 'image' | 'title'> | Omit<PropsWithRetry, 'image' | 'title'>) & {
    title?: string;
};

const ServerError = ({ title, ...props }: ServerErrorProps) => (
    <ScreenBlock title={title || 'Something went wrong'} image={ServerErrorSvg} {...props} />
);

const NotFound = ({ title, message, onBack }: Partial<Pick<ScreenBlockProps, 'title' | 'message' | 'onBack'>>) => (
    <ScreenBlock
        title={title || '404'}
        image={NotFoundSvg}
        message={message || 'The requested resource was not found.'}
        onBack={onBack}
    />
);

export { ServerError, NotFound };
export default ScreenBlock;

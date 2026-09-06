import { useStoreState } from 'easy-peasy';
import Md2React from '@/reviactyl/ui/Md2React';

export default () => {
    const customCopyright = useStoreState((state) => state.designify.data!.customCopyright);
    const copyright = useStoreState((state) => state.designify.data!.copyright);
    return (
        <div className='mt-4 mb-4'>
            <div className='text-center text-xs text-muted'>
                <a
                    rel={'noopener nofollow noreferrer'}
                    href={'https://reviactyl.app'}
                    target={'_blank'}
                    className='text-muted no-underline hover:text-gray-300'
                >
                    Reviactyl&trade;
                </a>
                &nbsp;&copy; {new Date().getFullYear()}
            </div>
            {customCopyright ? (
                <div className='text-center text-xs text-muted'>
                    <Md2React markdown={copyright} />
                </div>
            ) : (
                ''
            )}
        </div>
    );
};

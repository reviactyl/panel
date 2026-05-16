import tw from 'twin.macro';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('server/schedules');

    return (
        <>
            <div css={tw`md:w-1/2 h-full bg-gray-700`}>
                <div css={tw`flex flex-col`}>
                    <h2 css={tw`py-4 px-6 font-bold`}>{t('cheatsheet.examples')}</h2>
                    <div css={tw`flex py-4 px-6 bg-gray-600`}>
                        <div css={tw`w-1/2`}>*/5 * * * *</div>
                        <div css={tw`w-1/2`}>{t('cheatsheet.every-five-minutes')}</div>
                    </div>
                    <div css={tw`flex py-4 px-6`}>
                        <div css={tw`w-1/2`}>0 */1 * * *</div>
                        <div css={tw`w-1/2`}>{t('cheatsheet.every-hour')}</div>
                    </div>
                    <div css={tw`flex py-4 px-6 bg-gray-600`}>
                        <div css={tw`w-1/2`}>0 8-12 * * *</div>
                        <div css={tw`w-1/2`}>{t('cheatsheet.hour-range')}</div>
                    </div>
                    <div css={tw`flex py-4 px-6`}>
                        <div css={tw`w-1/2`}>0 0 * * *</div>
                        <div css={tw`w-1/2`}>{t('cheatsheet.once-a-day')}</div>
                    </div>
                    <div css={tw`flex py-4 px-6 bg-gray-600`}>
                        <div css={tw`w-1/2`}>0 0 * * MON</div>
                        <div css={tw`w-1/2`}>{t('cheatsheet.every-monday')}</div>
                    </div>
                </div>
            </div>
            <div css={tw`md:w-1/2 h-full bg-gray-700`}>
                <h2 css={tw`py-4 px-6 font-bold`}>{t('cheatsheet.special-characters')}</h2>
                <div css={tw`flex flex-col`}>
                    <div css={tw`flex py-4 px-6 bg-gray-600`}>
                        <div css={tw`w-1/2`}>*</div>
                        <div css={tw`w-1/2`}>{t('cheatsheet.any-value')}</div>
                    </div>
                    <div css={tw`flex py-4 px-6`}>
                        <div css={tw`w-1/2`}>,</div>
                        <div css={tw`w-1/2`}>{t('cheatsheet.value-list-separator')}</div>
                    </div>
                    <div css={tw`flex py-4 px-6 bg-gray-600`}>
                        <div css={tw`w-1/2`}>-</div>
                        <div css={tw`w-1/2`}>{t('cheatsheet.range-values')}</div>
                    </div>
                    <div css={tw`flex py-4 px-6`}>
                        <div css={tw`w-1/2`}>/</div>
                        <div css={tw`w-1/2`}>{t('cheatsheet.step-values')}</div>
                    </div>
                </div>
            </div>
        </>
    );
};

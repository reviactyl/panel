import React from 'react';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { InformationCircleIcon, SupportIcon, CurrencyDollarIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';

interface CardData {
    link: string;
    titleKey: string;
    descriptionKey: string;
    icon: React.ComponentType<{ className?: string }>;
    iconColor: string;
}

const QuickLinks = () => {
    const { t } = useTranslation('dashboard/index');
    const statusCardLink = useStoreState((state: ApplicationStore) => state.designify.data?.statusCardLink);
    const supportCardLink = useStoreState((state: ApplicationStore) => state.designify.data?.supportCardLink);
    const billingCardLink = useStoreState((state: ApplicationStore) => state.designify.data?.billingCardLink);

    const cards: CardData[] = [];

    if (supportCardLink && supportCardLink.trim() !== '') {
        cards.push({
            link: supportCardLink,
            titleKey: 'support-card.title',
            descriptionKey: 'support-card.description',
            icon: SupportIcon,
            iconColor: '#10b981',
        });
    }

    if (billingCardLink && billingCardLink.trim() !== '') {
        cards.push({
            link: billingCardLink,
            titleKey: 'billing-card.title',
            descriptionKey: 'billing-card.description',
            icon: CurrencyDollarIcon,
            iconColor: '#f59e0b',
        });
    }

    if (statusCardLink && statusCardLink.trim() !== '') {
        cards.push({
            link: statusCardLink,
            titleKey: 'status-card.title',
            descriptionKey: 'status-card.description',
            icon: InformationCircleIcon,
            iconColor: 'var(--color-primary)',
        });
    }

    if (cards.length === 0) {
        return null;
    }

    return (
        <div className='px-2'>
            <div className='mx-auto mt-2 grid w-full max-w-[1200px] gap-3 [grid-template-columns:repeat(auto-fit,minmax(200px,1fr))]'>
                {cards.map((card, index) => (
                    <a
                        key={index}
                        href={card.link}
                        target='_blank'
                        rel='noopener noreferrer'
                        className='flex cursor-pointer items-center justify-between rounded-ui border border-gray-800 bg-gray-900 p-4 text-gray-100 transition-all duration-200 hover:bg-gray-700'
                    >
                        <div>
                            <h3 className='font-semibold text-gray-100'>{t(card.titleKey)}</h3>
                            <p className='text-sm text-gray-400'>{t(card.descriptionKey)}</p>
                        </div>
                        <div
                            className='flex items-center justify-center rounded-ui p-2 [&_svg]:fill-current'
                            style={{ color: card.iconColor }}
                        >
                            <card.icon className='h-6 w-6' />
                        </div>
                    </a>
                ))}
            </div>
        </div>
    );
};

export default QuickLinks;

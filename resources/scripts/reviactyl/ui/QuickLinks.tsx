import React from 'react';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { InformationCircleIcon, SupportIcon, CurrencyDollarIcon } from '@heroicons/react/solid';
import styled from 'styled-components';
import tw from 'twin.macro';
import { useTranslation } from 'react-i18next';

const Container = styled.div`
    ${tw`px-2`}
`;

const CardsGrid = styled.div`
    ${tw`mx-auto w-full max-w-[1200px] grid gap-3 mt-2`}
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
`;

const CardLink = styled.a`
    ${tw`flex items-center justify-between p-4 rounded-ui text-gray-100 bg-gray-900 border border-gray-800 hover:bg-gray-700 transition-all duration-200 cursor-pointer`}
`;

const IconWrapper = styled.div<{ $color?: string }>`
    ${tw`p-2 rounded-ui flex items-center justify-center`}

    svg {
        color: ${({ $color }) => $color || 'var(--color-primary)'};
        fill: currentColor;
    }
`;

interface CardData {
    link: string;
    titleKey: string;
    descriptionKey: string;
    icon: React.ComponentType<{ className?: string }>;
    iconColor: string;
}

const QuickLinks = () => {
    const { t } = useTranslation('dashboard/index');
    const statusCardLink = useStoreState((state: ApplicationStore) => state.reviactyl.data?.statusCardLink);
    const supportCardLink = useStoreState((state: ApplicationStore) => state.reviactyl.data?.supportCardLink);
    const billingCardLink = useStoreState((state: ApplicationStore) => state.reviactyl.data?.billingCardLink);

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
        <Container>
            <CardsGrid>
                {cards.map((card, index) => (
                    <CardLink key={index} href={card.link} target='_blank' rel='noopener noreferrer'>
                        <div>
                            <h3 className='font-semibold text-gray-100'>{t(card.titleKey)}</h3>
                            <p className='text-sm text-gray-400'>{t(card.descriptionKey)}</p>
                        </div>
                        <IconWrapper $color={card.iconColor}>
                            <card.icon className='h-6 w-6' />
                        </IconWrapper>
                    </CardLink>
                ))}
            </CardsGrid>
        </Container>
    );
};

export default QuickLinks;

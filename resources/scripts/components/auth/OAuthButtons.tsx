import tw from 'twin.macro';
import { useTranslation } from 'react-i18next';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';

// https://developers.google.com/identity/branding-guidelines
const GoogleLogo = () => (
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48' width='20' height='20' style={{ flexShrink: 0 }}>
        <path
            fill='#EA4335'
            d='M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z'
        />
        <path
            fill='#4285F4'
            d='M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z'
        />
        <path
            fill='#FBBC05'
            d='M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z'
        />
        <path
            fill='#34A853'
            d='M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z'
        />
    </svg>
);

//https://discord.com/branding
const DiscordLogo = () => (
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='22' height='22' fill='#5865F2'>
        <path d='M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057.1 18.08.118 18.1.137 18.11a19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z' />
    </svg>
);

//https://brand.github.com/foundations/logo
const GitHubLogo = () => (
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='22' height='22' fill='#fff'>
        <path d='M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12' />
    </svg>
);

type Provider = 'google' | 'discord' | 'github';

const providers: Provider[] = ['google', 'discord', 'github'];

const meta: Record<Provider, { label: string; href: string }> = {
    google: { label: 'Google', href: '/auth/login/google' },
    discord: { label: 'Discord', href: '/auth/login/discord' },
    github: { label: 'GitHub', href: '/auth/login/github' },
};

const ProviderLogo = ({ provider }: { provider: Provider }) => {
    switch (provider) {
        case 'google':
            return <GoogleLogo />;
        case 'discord':
            return <DiscordLogo />;
        case 'github':
            return <GitHubLogo />;
    }
};

function OAuthButton({ provider }: { provider: Provider }) {
    const { label, href } = meta[provider];

    return (
        <Tooltip content={label}>
            <a
                href={href}
                aria-label={label}
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    flex: 1,
                    height: '44px',
                    borderRadius: '10px',
                    // Panel's secondary color from Designify (--color-secondary)
                    backgroundColor: 'rgb(var(--color-secondary))',
                    border: '1px solid rgba(255, 255, 255, 0.1)',
                    textDecoration: 'none',
                    transition: 'filter 0.15s ease',
                    cursor: 'pointer',
                }}
                onMouseEnter={(e) => {
                    (e.currentTarget as HTMLAnchorElement).style.filter = 'brightness(1.25)';
                }}
                onMouseLeave={(e) => {
                    (e.currentTarget as HTMLAnchorElement).style.filter = 'brightness(1)';
                }}
            >
                <ProviderLogo provider={provider} />
            </a>
        </Tooltip>
    );
}

interface OAuthButtonsProps {
    google?: boolean;
    discord?: boolean;
    github?: boolean;
}

export default function OAuthButtons({ google, discord, github }: OAuthButtonsProps) {
    const { t } = useTranslation('auth');

    const toggles: Record<Provider, boolean | undefined> = { google, discord, github };
    const enabled = providers.filter((p) => toggles[p]);

    if (enabled.length === 0) return null;

    return (
        <div>
            <div css={tw`relative flex py-2 items-center`}>
                <div css={tw`flex-grow border-t border-gray-800`} />
                <span css={tw`flex-shrink mx-4 text-gray-400 text-xs`}>{t('social.or')}</span>
                <div css={tw`flex-grow border-t border-gray-800`} />
            </div>
            <div style={{ display: 'flex', gap: '10px' }}>
                {enabled.map((provider) => (
                    <OAuthButton key={provider} provider={provider} />
                ))}
            </div>
        </div>
    );
}

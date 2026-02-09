declare module '*.jpg';
declare module '*.png';
declare module '*.svg';
declare module '*.css';

interface Window {
    PterodactylUser?: {
        uuid: string;
        username: string;
        email: string;
        language: string;
        root_admin: boolean;
        use_totp: boolean;
    };
    SiteConfiguration?: Record<string, any>;
    SocialLoginConfiguration?: {
        google: boolean;
        discord: boolean;
        github: boolean;
    };
    ReviactylConfiguration?: Record<string, any>;
}

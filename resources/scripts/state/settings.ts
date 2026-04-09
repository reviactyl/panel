import { action, Action } from 'easy-peasy';

export interface SiteSettings {
    name: string;
    logo: string;
    locale: string;
    captcha: {
        provider: 'none' | 'recaptcha' | 'turnstile';
        recaptcha: {
            siteKey: string;
        };
        turnstile: {
            siteKey: string;
        };
    };
      passkeys?: {
          loginRequiresUsername: boolean;
      };
}

export interface SettingsStore {
    data?: SiteSettings;
    setSettings: Action<SettingsStore, SiteSettings>;
}

const settings: SettingsStore = {
    data: undefined,

    setSettings: action((state, payload) => {
        state.data = payload;
    }),
};

export default settings;

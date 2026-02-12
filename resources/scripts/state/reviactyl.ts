import { action, Action } from 'easy-peasy';

export interface ReviactylSettings {
    customCopyright: boolean;
    copyright: string;
    isUnderMaintenance: boolean;
    maintenance: string;
    themeSelector: boolean;
    sidebarLogout: boolean;
    allocationBlur: boolean;
    alertType: string;
    alertMessage: string;
    statusCardLink: string;
    supportCardLink: string;
    billingCardLink: string;
    alwaysShowKillButton: boolean;
}

export interface ReviactylSettingsStore {
    data?: ReviactylSettings;
    setReviactyl: Action<ReviactylSettingsStore, ReviactylSettings>;
}

const reviactyl: ReviactylSettingsStore = {
    data: undefined,

    setReviactyl: action((state, payload) => {
        state.data = payload;
    }),
};

export default reviactyl;

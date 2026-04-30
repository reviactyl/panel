import { action, Action } from 'easy-peasy';

export interface ReviactylAlert {
    type: string;
    message: string;
}

export interface ReviactylSidebarButton {
    label: string;
    url: string;
    newTab: boolean;
}

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
    alerts?: ReviactylAlert[];
    sidebarButtons?: ReviactylSidebarButton[];
    statusCardLink: string;
    supportCardLink: string;
    billingCardLink: string;
    alwaysShowKillButton: boolean;
    cardType: 'grid' | 'row';
    layoutType: 'modern' | 'classic' | 'compact' | 'accent';
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

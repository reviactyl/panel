import { action, Action } from 'easy-peasy';

export interface DesignifyAlert {
    type: string;
    message: string;
}

export interface DesignifySidebarButton {
    label: string;
    url: string;
    newTab: boolean;
}

export interface DesignifySettings {
    customCopyright: boolean;
    copyright: string;
    isUnderMaintenance: boolean;
    maintenance: string;
    themeSelector: boolean;
    sidebarLogout: boolean;
    allocationBlur: boolean;
    alertType: string;
    alertMessage: string;
    alerts?: DesignifyAlert[];
    sidebarButtons?: DesignifySidebarButton[];
    statusCardLink: string;
    supportCardLink: string;
    billingCardLink: string;
    alwaysShowKillButton: boolean;
    cardType: 'grid' | 'row';
    layoutType: 'modern' | 'classic' | 'compact' | 'accent';
}

export interface DesignifySettingsStore {
    data?: DesignifySettings;
    setDesignify: Action<DesignifySettingsStore, DesignifySettings>;
}

const designify: DesignifySettingsStore = {
    data: undefined,

    setDesignify: action((state, payload) => {
        state.data = payload;
    }),
};

export default designify;

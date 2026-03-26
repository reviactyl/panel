export interface ExtensionRouteGuard {
    eggId?: number;
    nestId?: number;
    eggIds?: number[];
    nestIds?: number[];

    egg_id?: number;
    nest_id?: number;
    egg_ids?: number[];
    nest_ids?: number[];
}

export interface ExtensionSlotDefinition extends ExtensionRouteGuard {
    name: string;
    module: string;
    export?: string;
    order?: number;
    permission?: string | string[];
}

export interface ExtensionRouteDefinition extends ExtensionRouteGuard {
    path: string;
    label?: string;
    module: string;
    export?: string;
    permission?: string | string[];
    icon?: string;
}

export interface ExtensionFrontendDefinition {
    build_strategy?: 'precompiled' | 'source';
    entry_points?: string[];
    slots?: ExtensionSlotDefinition[];
    routes?: {
        dashboardRouter?: ExtensionRouteDefinition[];
        serverRouter?: ExtensionRouteDefinition[];
    };
}

export interface ExtensionRegistryRecord {
    id: string;
    name: string;
    version: string;
    permissions?: string[];
    feature_flags?: string[];
    frontend?: ExtensionFrontendDefinition;
}

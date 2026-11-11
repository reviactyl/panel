import { Action, action, Thunk, thunk } from 'easy-peasy';
import updateAccountEmail from '@/api/account/updateAccountEmail';

export interface PanelUser {
    uuid: string;
    username: string;
    name_first: string;
    name_last: string;
    email: string;
    root_admin: boolean;
    admin?: boolean;
    permissions?: string[];
    use_totp: boolean;
    language: string;
    editor: string;
    avatar_style: string;
    avatar_animated: boolean;
    archive_format: 'tar.gz' | 'zip';
    updated_at: string;
    created_at: string;
}

export interface UserData {
    uuid: string;
    username: string;
    name_first: string;
    name_last: string;
    email: string;
    language: string;
    fileEditor: string;
    avatarStyle: string;
    avatarAnimated: boolean;
    archiveFormat: 'tar.gz' | 'zip';
    rootAdmin: boolean;
    adminAccess: boolean;
    rolePermissions: string[];
    useTotp: boolean;
    createdAt: Date;
    updatedAt: Date;
}

export interface UserStore {
    data?: UserData;
    setUserData: Action<UserStore, UserData | undefined>;
    updateUserData: Action<UserStore, Partial<UserData>>;
    updateUserEmail: Thunk<UserStore, { email: string; password: string }, any, UserStore, Promise<void>>;
}

const user: UserStore = {
    data: undefined,
    setUserData: action((state, payload) => {
        state.data = payload;
    }),

    updateUserData: action((state, payload) => {
        // @ts-expect-error limitation of Typescript, can't do much about that currently unfortunately.
        state.data = { ...state.data, ...payload };
    }),

    updateUserEmail: thunk(async (actions, payload) => {
        await updateAccountEmail(payload.email, payload.password);

        actions.updateUserData({ email: payload.email });
    }),
};

export default user;

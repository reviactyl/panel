import type { PanelUser, UserData } from '@/state/user';

export const rawUserToUserData = (user: PanelUser): UserData => ({
    uuid: user.uuid,
    username: user.username,
    name_first: user.name_first,
    name_last: user.name_last,
    email: user.email,
    language: user.language,
    rootAdmin: user.root_admin,
    adminAccess: user.admin ?? user.root_admin,
    rolePermissions: user.permissions ?? [],
    useTotp: user.use_totp,
    createdAt: new Date(user.created_at),
    fileEditor: user.editor,
    avatarStyle: user.avatar_style || 'gravatar',
    avatarAnimated: user.avatar_animated ?? true,
    archiveFormat: user.archive_format || 'tar.gz',
    updatedAt: new Date(user.updated_at),
});

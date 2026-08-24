import http from '@/api/http';

export interface AvatarPreferences {
    avatarStyle: string;
    avatarAnimated: boolean;
}

export default ({ avatarStyle, avatarAnimated }: AvatarPreferences): Promise<void> =>
    new Promise((resolve, reject) => {
        http.put('/api/client/account/avatar', {
            avatar_style: avatarStyle,
            avatar_animated: avatarAnimated,
        })
            .then(() => resolve())
            .catch(reject);
    });

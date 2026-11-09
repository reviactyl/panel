import { useStore } from 'easy-peasy';
import type { ApplicationStore } from '@/state';
import i18n from '@/i18n';
import updateAccountLanguage from '@/api/account/updateAccountLanguage';
import updateAccountEditor from '@/api/account/updateAccountEditor';

type Preference = 'language' | 'fileEditor';
const pending = new WeakMap<object, Map<string, Promise<boolean>>>();

export default function useAccountPreference() {
    const store = useStore<ApplicationStore>();

    return async (preference: Preference, value: string) => {
        const user = store.getState().user.data;
        if (!user) return false;

        let saves = pending.get(store);
        if (!saves) {
            saves = new Map();
            pending.set(store, saves);
        }
        const key = `${user.uuid}:${preference}`;
        const previous = saves.get(key) || Promise.resolve(false);
        const request = previous
            .catch(() => undefined)
            .then(async () => {
                if (store.getState().user.data?.uuid !== user.uuid) return false;

                if (preference === 'language') {
                    await updateAccountLanguage({ language: value });
                } else {
                    await updateAccountEditor({ fileEditor: value });
                }

                if (store.getState().user.data?.uuid !== user.uuid) return false;
                store.getActions().user.updateUserData({ [preference]: value });
                if (preference === 'language') await i18n.changeLanguage(value);
                return true;
            });
        saves.set(key, request);

        try {
            return await request;
        } finally {
            if (saves.get(key) === request) saves.delete(key);
        }
    };
}

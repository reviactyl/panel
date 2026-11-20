export interface LanguageInfo {
    name: string;
    flag: string;
}

const fallback: Record<string, LanguageInfo> = { en: { name: 'English', flag: 'us' } };
let request: Promise<Record<string, LanguageInfo>> | undefined;

export default function getLanguageList(): Promise<Record<string, LanguageInfo>> {
    request ??= fetch('/locales/list.json')
        .then((response) => response.json() as Promise<Record<string, LanguageInfo>>)
        .catch(() => {
            request = undefined;
            return fallback;
        });

    return request;
}

export const resetLanguageList = () => {
    request = undefined;
};

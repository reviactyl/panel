const labels: Record<string, string> = {
    'overview.language': 'Panel Language',
    'overview.editor': 'Preferred File Editor',
};
export const useTranslation = () => ({ t: (key: string) => labels[key] || key });
export default { on() {}, off() {}, changeLanguage() {} };

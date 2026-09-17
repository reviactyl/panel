export const PREVIEW_TOKEN_KEY = 'reviactyl:subuser-preview-token';

export const getPreviewToken = (): string | null => sessionStorage.getItem(PREVIEW_TOKEN_KEY);

export const storePreviewToken = (token: string): void => sessionStorage.setItem(PREVIEW_TOKEN_KEY, token);

export const clearPreviewToken = (): void => sessionStorage.removeItem(PREVIEW_TOKEN_KEY);

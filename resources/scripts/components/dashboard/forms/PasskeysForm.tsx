import { FormEvent, useEffect, useState } from 'react';
import useSWR from 'swr';
import tw from 'twin.macro';
import Label from '@/components/elements/Label';
import Input from '@/components/elements/Input';
import Spinner from '@/components/elements/Spinner';
import Code from '@/components/elements/Code';
import { Dialog } from '@/components/elements/dialog';
import { Button } from '@/components/elements/button';
import { useFlashKey } from '@/plugins/useFlash';
import { useTranslation } from 'react-i18next';
import { deleteAccountPasskey, getAccountPasskeys, registerAccountPasskey } from '@/api/account/passkeys';

export default () => {
    const { t } = useTranslation('dashboard/account');
    const [name, setName] = useState('');
    const [password, setPassword] = useState('');
    const [isRegistering, setIsRegistering] = useState(false);
    const [deletingId, setDeletingId] = useState<string | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<{ id: string; name: string | null } | null>(null);
    const { clearAndAddHttpError, clearFlashes } = useFlashKey('account:passkeys');

    const { data: passkeys, error, mutate } = useSWR('/api/client/account/passkeys', () => getAccountPasskeys());

    const isLoading = !passkeys && !error;

    useEffect(() => {
        if (error) {
            clearAndAddHttpError(error);
        }
    }, [error]);

    const onRegister = async (event: FormEvent) => {
        event.preventDefault();
        clearFlashes();
        setIsRegistering(true);

        try {
            await registerAccountPasskey(password, name);
            setName('');
            setPassword('');
            await mutate();
        } catch (registerError) {
            clearAndAddHttpError(registerError as Error);
        } finally {
            setIsRegistering(false);
        }
    };

    const onDelete = async () => {
        if (!deleteTarget) {
            return;
        }

        clearFlashes();
        setDeleteTarget(null);

        setDeletingId(deleteTarget.id);

        try {
            await deleteAccountPasskey(deleteTarget.id);
            await mutate();
        } catch (deleteError) {
            clearAndAddHttpError(deleteError as Error);
        } finally {
            setDeletingId(null);
        }
    };

    return (
        <div>
            <Dialog.Confirm
                open={deleteTarget !== null}
                title={t('passkeys.remove')}
                confirm={t('passkeys.remove')}
                onClose={() => setDeleteTarget(null)}
                onConfirmed={onDelete}
            >
                {t('passkeys.delete-confirm')}
                {deleteTarget && <Code>{deleteTarget.name || deleteTarget.id}</Code>}
            </Dialog.Confirm>

            <p css={tw`text-sm text-gray-200`}>{t('passkeys.description')}</p>

            <div css={tw`mt-4 space-y-3`}>
                {isLoading && <Spinner size={'small'} />}

                {!isLoading && (!passkeys || passkeys.length === 0) && (
                    <p css={tw`text-sm text-gray-300`}>{t('passkeys.empty')}</p>
                )}

                {(passkeys || []).map((passkey) => (
                    <div
                        key={passkey.id}
                        css={tw`flex flex-col gap-2 rounded-ui border border-gray-500 bg-gray-600 p-3 sm:flex-row sm:items-center sm:justify-between`}
                    >
                        <div css={tw`min-w-0`}>
                            <p css={tw`truncate text-sm font-medium text-gray-100`}>{passkey.name || passkey.id}</p>
                            <p css={tw`truncate text-xs text-gray-300`}>{passkey.origin}</p>
                            <p css={tw`mt-1 text-xs text-gray-300`}>
                                {t('passkeys.created')}: {passkey.createdAt.toLocaleString()}
                            </p>
                            <p css={tw`text-xs text-gray-300`}>
                                {t('passkeys.updated')}: {passkey.updatedAt.toLocaleString()}
                            </p>
                        </div>
                        <div>
                            <Button.Danger
                                type={'button'}
                                disabled={deletingId !== null || isRegistering}
                                onClick={() => setDeleteTarget({ id: passkey.id, name: passkey.name })}
                            >
                                {deletingId === passkey.id ? (
                                    <span css={tw`flex justify-center items-center`}>
                                        <Spinner size={'small'} />
                                    </span>
                                ) : (
                                    t('passkeys.remove')
                                )}
                            </Button.Danger>
                        </div>
                    </div>
                ))}
            </div>

            <form css={tw`mt-6`} onSubmit={onRegister}>
                <div css={tw`space-y-3`}>
                    <div>
                        <Label>{t('passkeys.name')}</Label>
                        <Input
                            value={name}
                            maxLength={191}
                            disabled={isRegistering || deletingId !== null}
                            onChange={(event) => setName(event.currentTarget.value)}
                            placeholder={t('passkeys.name-placeholder')}
                        />
                    </div>
                    <div>
                        <Label>{t('passkeys.password')}</Label>
                        <Input
                            type={'password'}
                            value={password}
                            required
                            disabled={isRegistering || deletingId !== null}
                            onChange={(event) => setPassword(event.currentTarget.value)}
                        />
                    </div>
                </div>

                <div css={tw`mt-4`}>
                    <Button type={'submit'} disabled={isRegistering || deletingId !== null || password.length < 1}>
                        {isRegistering ? (
                            <span css={tw`flex justify-center items-center`}>
                                <Spinner size={'small'} />
                            </span>
                        ) : (
                            t('passkeys.register')
                        )}
                    </Button>
                </div>
            </form>
        </div>
    );
};

import { ChangeEvent, FormEvent, useEffect, useState } from 'react';
import useSWR from 'swr';
import Label from '@/reviactyl/elements/Label';
import Input from '@/reviactyl/elements/Input';
import Spinner from '@/reviactyl/elements/Spinner';
import Code from '@/reviactyl/elements/Code';
import { Dialog } from '@/reviactyl/elements/dialog';
import { Button } from '@/reviactyl/components/button';
import { useFlashKey } from '@/plugins/useFlash';
import { useTranslation } from 'react-i18next';
import { deleteAccountPasskey, getAccountPasskeys, registerAccountPasskey } from '@/api/account/passkeys';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import { FaFingerprint, FaGoogle, FaTrash } from 'react-icons/fa6';

export const ListPasskeysForm = () => {
    const { t } = useTranslation('dashboard/account');
    const [deleteTarget, setDeleteTarget] = useState<{ id: string; name: string | null } | null>(null);
    const [deletePassword, setDeletePassword] = useState('');
    const { clearAndAddHttpError, clearFlashes } = useFlashKey('account:passkeys');

    const { data: passkeys, error, mutate } = useSWR('/api/client/account/passkeys', () => getAccountPasskeys());

    const isLoading = !passkeys && !error;

    useEffect(() => {
        if (error) {
            clearAndAddHttpError(error);
        }
    }, [error]);

    const onDelete = async () => {
        if (!deleteTarget) {
            return;
        }

        clearFlashes();

        if (!deletePassword) {
            clearAndAddHttpError(new Error(t('passkeys.password-required')));
            return;
        }

        const passwordConfirmation = deletePassword;

        setDeleteTarget(null);
        setDeletePassword('');

        try {
            await deleteAccountPasskey(deleteTarget.id, passwordConfirmation);
            await mutate();
        } catch (deleteError) {
            clearAndAddHttpError(deleteError as Error);
        }
    };

    return (
        <div>
            <Dialog.Confirm
                open={deleteTarget !== null}
                title={t('passkeys.remove')}
                confirm={t('passkeys.remove')}
                onClose={() => {
                    setDeleteTarget(null);
                    setDeletePassword('');
                }}
                onConfirmed={onDelete}
            >
                <div className='space-y-3'>
                    <p>{t('passkeys.delete-confirm')}</p>
                    {deleteTarget && <Code>{deleteTarget.name || deleteTarget.id}</Code>}
                    <div>
                        <Label>{t('passkeys.password')}</Label>
                        <Input
                            type={'password'}
                            value={deletePassword}
                            autoComplete={'current-password'}
                            onChange={(event: ChangeEvent<HTMLInputElement>) =>
                                setDeletePassword(event.currentTarget.value)
                            }
                        />
                        <p className='mt-1 text-xs text-gray-300'>{t('passkeys.delete-password-prompt')}</p>
                    </div>
                </div>
            </Dialog.Confirm>

            <div className='space-y-3'>
                {isLoading && <Spinner size={'small'} />}

                {!isLoading && (!passkeys || passkeys.length === 0) && (
                    <p className='text-center text-sm'>{t('passkeys.empty')}</p>
                )}

                {(passkeys || []).map((passkey) => (
                    <GreyRowBox key={passkey.id} className='flex space-x-4 items-center'>
                        {passkey.authenticator === 'Google Password Manager' ? (
                            <FaGoogle className='text-gray-300' />
                        ) : (
                            <FaFingerprint className='text-gray-300' />
                        )}
                        <div className='flex-1 min-w-0'>
                            <p className='truncate text-sm font-medium text-gray-100'>{passkey.name || passkey.id}</p>
                            {passkey.authenticator && (
                                <p className='truncate text-xs text-gray-300'>{passkey.authenticator}</p>
                            )}
                            <p className='mt-1 text-xs text-gray-300'>
                                {t('passkeys.created')}: {passkey.createdAt.toLocaleString()}
                            </p>
                            <p className='text-xs text-gray-300'>
                                {t('passkeys.updated')}: {passkey.updatedAt.toLocaleString()}
                            </p>
                        </div>
                        <div>
                            <button
                                className='ml-4 p-2 text-sm'
                                onClick={() => setDeleteTarget({ id: passkey.id, name: passkey.name })}
                            >
                                <FaTrash
                                    className={'text-gray-400 hover:text-red-400 transition-colors duration-150'}
                                />
                            </button>
                        </div>
                    </GreyRowBox>
                ))}
            </div>
        </div>
    );
};

export const CreatePasskeysForm = () => {
    const { t } = useTranslation('dashboard/account');
    const [name, setName] = useState('');
    const [password, setPassword] = useState('');
    const [isRegistering, setIsRegistering] = useState(false);
    const { clearAndAddHttpError, clearFlashes } = useFlashKey('account:passkeys');

    const { error, mutate } = useSWR('/api/client/account/passkeys', () => getAccountPasskeys());

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

    return (
        <div>
            <p className='text-sm text-gray-200'>{t('passkeys.description')}</p>

            <form className='mt-6' onSubmit={onRegister}>
                <div className='space-y-3'>
                    <div>
                        <Label>{t('passkeys.name')}</Label>
                        <Input
                            value={name}
                            maxLength={191}
                            disabled={isRegistering}
                            onChange={(event: ChangeEvent<HTMLInputElement>) => setName(event.currentTarget.value)}
                            placeholder={t('passkeys.name-placeholder')}
                        />
                    </div>
                    <div>
                        <Label>{t('passkeys.password')}</Label>
                        <Input
                            type={'password'}
                            value={password}
                            required
                            disabled={isRegistering}
                            onChange={(event: ChangeEvent<HTMLInputElement>) => setPassword(event.currentTarget.value)}
                        />
                    </div>
                </div>

                <div className='mt-4'>
                    <Button type={'submit'} disabled={isRegistering || password.length < 1}>
                        {isRegistering ? (
                            <span className='flex justify-center items-center'>
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

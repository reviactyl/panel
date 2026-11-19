import { ChangeEvent, FormEvent, useEffect, useId, useRef, useState } from 'react';
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
import FlashMessageRender from '@/components/FlashMessageRender';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import { FaFingerprint, FaGoogle, FaTrash } from 'react-icons/fa6';

export const ListPasskeysForm = () => {
    const { t } = useTranslation('dashboard/account');
    const deletePasswordId = useId();
    const [deleteTarget, setDeleteTarget] = useState<{ id: string; name: string | null } | null>(null);
    const [deletePassword, setDeletePassword] = useState('');
    const [isDeleting, setIsDeleting] = useState(false);
    const deleting = useRef(false);
    const deleteFlash = useFlashKey('account:passkeys:delete');
    const { clearAndAddHttpError, clearFlashes } = useFlashKey('account:passkeys');

    const { data: passkeys, error, mutate } = useSWR('/api/client/account/passkeys', () => getAccountPasskeys());

    const isLoading = !passkeys && !error;

    useEffect(() => {
        if (error) {
            clearAndAddHttpError(error);
        } else {
            clearFlashes();
        }
    }, [error]);

    const onDelete = async () => {
        if (!deleteTarget || deleting.current) {
            return;
        }

        deleteFlash.clearFlashes();

        if (!deletePassword) {
            deleteFlash.clearAndAddHttpError(new Error(t('passkeys.password-required')));
            return;
        }

        deleting.current = true;
        setIsDeleting(true);

        try {
            await deleteAccountPasskey(deleteTarget.id, deletePassword);
            setDeleteTarget(null);
            setDeletePassword('');
            await mutate().catch(clearAndAddHttpError);
        } catch (deleteError) {
            deleteFlash.clearAndAddHttpError(deleteError as Error);
        } finally {
            deleting.current = false;
            setIsDeleting(false);
        }
    };

    return (
        <div>
            <Dialog
                open={deleteTarget !== null}
                title={t('passkeys.remove')}
                onClose={() => {
                    if (deleting.current) return;
                    deleteFlash.clearFlashes();
                    setDeleteTarget(null);
                    setDeletePassword('');
                }}
                preventExternalClose={isDeleting}
                hideCloseIcon={isDeleting}
            >
                <FlashMessageRender byKey='account:passkeys:delete' className='mb-3' />
                <div className='space-y-3'>
                    <p>{t('passkeys.delete-confirm')}</p>
                    {deleteTarget && <Code>{deleteTarget.name || deleteTarget.id}</Code>}
                    <div>
                        <Label htmlFor={deletePasswordId}>{t('passkeys.password')}</Label>
                        <Input
                            id={deletePasswordId}
                            type={'password'}
                            value={deletePassword}
                            autoComplete={'current-password'}
                            disabled={isDeleting}
                            onChange={(event: ChangeEvent<HTMLInputElement>) =>
                                setDeletePassword(event.currentTarget.value)
                            }
                        />
                        <p className='mt-1 text-xs text-gray-300'>{t('passkeys.delete-password-prompt')}</p>
                    </div>
                </div>
                <Dialog.Footer>
                    <Button.Text
                        disabled={isDeleting}
                        onClick={() => {
                            deleteFlash.clearFlashes();
                            setDeleteTarget(null);
                            setDeletePassword('');
                        }}
                    >
                        {t('strings:cancel')}
                    </Button.Text>
                    <Button.Danger disabled={isDeleting} onClick={onDelete} aria-busy={isDeleting}>
                        {t('passkeys.remove')}
                    </Button.Danger>
                </Dialog.Footer>
            </Dialog>

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
                                type='button'
                                aria-label={`${t('passkeys.remove')}: ${passkey.name || passkey.id}`}
                                className='ml-4 p-2 text-sm text-muted transition-colors duration-150 hover:text-danger-text focus-visible:text-danger-text'
                                disabled={isDeleting}
                                onClick={() => {
                                    deleteFlash.clearFlashes();
                                    setDeleteTarget({ id: passkey.id, name: passkey.name });
                                }}
                            >
                                <FaTrash aria-hidden='true' />
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
    const nameId = useId();
    const passwordId = useId();
    const [name, setName] = useState('');
    const [password, setPassword] = useState('');
    const [isRegistering, setIsRegistering] = useState(false);
    const { clearAndAddHttpError, clearFlashes } = useFlashKey('account:passkeys');

    const { error, mutate } = useSWR('/api/client/account/passkeys', () => getAccountPasskeys());

    useEffect(() => {
        if (error) {
            clearAndAddHttpError(error);
        } else {
            clearFlashes();
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
                        <Label htmlFor={nameId}>{t('passkeys.name')}</Label>
                        <Input
                            id={nameId}
                            value={name}
                            maxLength={191}
                            disabled={isRegistering}
                            onChange={(event: ChangeEvent<HTMLInputElement>) => setName(event.currentTarget.value)}
                            placeholder={t('passkeys.name-placeholder')}
                        />
                    </div>
                    <div>
                        <Label htmlFor={passwordId}>{t('passkeys.password')}</Label>
                        <Input
                            id={passwordId}
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

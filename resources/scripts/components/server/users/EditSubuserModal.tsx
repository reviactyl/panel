import React, { useContext, useEffect, useRef } from 'react';
import { Subuser } from '@/state/server/subusers';
import { Form, Formik, useFormikContext } from 'formik';
import { array, object, string } from 'yup';
import Field from '@/reviactyl/elements/Field';
import { Actions, useStoreActions, useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import createOrUpdateSubuser from '@/api/server/users/createOrUpdateSubuser';
import { ServerContext } from '@/state/server';
import FlashMessageRender from '@/components/FlashMessageRender';
import Can from '@/reviactyl/elements/Can';
import { usePermissions } from '@/plugins/usePermissions';
import { useDeepCompareMemo } from '@/plugins/useDeepCompareMemo';
import Button from '@/reviactyl/elements/Button';
import Select from '@/reviactyl/elements/Select';
import PermissionTitleBox from '@/components/server/users/PermissionTitleBox';
import asModal from '@/hoc/asModal';
import PermissionRow from '@/components/server/users/PermissionRow';
import ModalContext from '@/context/ModalContext';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';

type Props = {
    subuser?: Subuser;
};

interface Values {
    email: string;
    permissions: string[];
}

const PRESET_PERMISSIONS = {
    power: [
        'control.console',
        'control.start',
        'control.stop',
        'control.restart',
        'file.create',
        'file.read',
        'file.read-content',
        'file.update',
        'file.delete',
        'file.archive',
        'file.sftp',
        'backup.create',
        'backup.read',
        'backup.delete',
        'backup.download',
        'allocation.read',
        'startup.read',
        'settings.rename',
    ],
    moderator: [
        'control.console',
        'control.start',
        'control.stop',
        'control.restart',
        'user.create',
        'user.read',
        'user.update',
        'user.delete',
        'file.read',
    ],
    viewer: ['control.console', 'file.read', 'user.read'],
};

const PresetSelector = ({ editablePermissions }: { editablePermissions: string[] }) => {
    const { setFieldValue } = useFormikContext<Values>();

    const onPresetChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        const val = e.target.value;
        if (val === 'all') {
            setFieldValue('permissions', editablePermissions);
        } else if (val === 'none') {
            setFieldValue('permissions', []);
        } else {
            // @ts-expect-error keys are valid
            const preset: string[] = PRESET_PERMISSIONS[val] || [];
            const perms = preset.filter((p) => editablePermissions.includes(p));
            setFieldValue('permissions', perms);
        }
    };

    return (
        <Select onChange={onPresetChange} defaultValue={'custom'}>
            <option value='custom' disabled>
                Select a preset...
            </option>
            <option value='all'>Full Access (Select All)</option>
            <option value='power'>Power User (Control + Files + Backups)</option>
            <option value='moderator'>Moderator (Control + Users)</option>
            <option value='viewer'>Viewer (Read Only)</option>
            <option value='none'>No Access (Select None)</option>
        </Select>
    );
};

const EditSubuserModal = ({ subuser }: Props) => {
    const ref = useRef<HTMLHeadingElement>(null);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const appendSubuser = ServerContext.useStoreActions((actions) => actions.subusers.appendSubuser);
    const { clearFlashes, clearAndAddHttpError } = useStoreActions(
        (actions: Actions<ApplicationStore>) => actions.flashes
    );
    const { dismiss, setPropOverrides } = useContext(ModalContext);

    const accountRootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const { session } = useSubuserPreview();
    const isRootAdmin = accountRootAdmin && !session;
    const permissions = useStoreState((state) => state.permissions.data);
    // The currently logged in user's permissions. We're going to filter out any permissions
    // that they should not need.
    const loggedInPermissions = ServerContext.useStoreState((state) => state.server.permissions);
    const [canEditUser] = usePermissions(subuser ? ['user.update'] : ['user.create']);

    // The permissions that can be modified by this user.
    const editablePermissions = useDeepCompareMemo(() => {
        const cleaned = Object.keys(permissions).map((key) =>
            Object.keys(permissions[key]?.keys ?? {}).map((pkey) => `${key}.${pkey}`)
        );

        const list: string[] = ([] as string[]).concat.apply([], Object.values(cleaned));

        if (isRootAdmin || (loggedInPermissions.length === 1 && loggedInPermissions[0] === '*')) {
            return list;
        }

        return list.filter((key) => loggedInPermissions.indexOf(key) >= 0);
    }, [isRootAdmin, permissions, loggedInPermissions]);

    const submit = (values: Values) => {
        setPropOverrides({ showSpinnerOverlay: true });
        clearFlashes('user:edit');

        createOrUpdateSubuser(uuid, values, subuser)
            .then((subuser) => {
                appendSubuser(subuser);
                dismiss();
            })
            .catch((error) => {
                console.error(error);
                setPropOverrides(null);
                clearAndAddHttpError({ key: 'user:edit', error });

                if (ref.current) {
                    ref.current.scrollIntoView();
                }
            });
    };

    useEffect(
        () => () => {
            clearFlashes('user:edit');
        },
        []
    );

    return (
        <Formik
            onSubmit={submit}
            initialValues={
                {
                    email: subuser?.email || '',
                    permissions: subuser?.permissions || [],
                } as Values
            }
            validationSchema={object().shape({
                email: string()
                    .max(191, 'Email addresses must not exceed 191 characters.')
                    .email('A valid email address must be provided.')
                    .required('A valid email address must be provided.'),
                permissions: array().of(string()),
            })}
        >
            <Form>
                <div className='flex justify-between'>
                    <h2 className='text-2xl' ref={ref}>
                        {subuser
                            ? `${canEditUser ? 'Modify' : 'View'} permissions for ${subuser.email}`
                            : 'Create new subuser'}
                    </h2>
                    <div>
                        <Button type='submit' className='w-full sm:w-auto'>
                            {subuser ? 'Save' : 'Invite User'}
                        </Button>
                    </div>
                </div>
                <FlashMessageRender byKey='user:edit' className='mt-4' />
                <div className='mt-6'>
                    <label className='mb-2 block text-sm font-bold text-gray-300'>Select Info</label>
                    <div className='rounded-lg border border-gray-600 bg-gray-700 p-4'>
                        <h3 className='mb-2 font-semibold text-white'>Role Presets</h3>
                        <p className='mb-4 text-sm text-gray-300'>
                            Select a preset to automatically configure permissions for this user. You can still
                            fine-tune individual permissions below.
                        </p>
                        <PresetSelector editablePermissions={editablePermissions} />
                    </div>
                </div>
                {!isRootAdmin && loggedInPermissions[0] !== '*' && (
                    <div className='mt-4 border-l-4 border-cyan-400 py-2 pl-4'>
                        <p className='text-sm text-gray-300'>
                            Only permissions which your account is currently assigned may be selected when creating or
                            modifying other users.
                        </p>
                    </div>
                )}
                {!subuser && (
                    <div className='mt-6'>
                        <Field
                            name={'email'}
                            label={'User Email'}
                            description={
                                'Enter the email address of the user you wish to invite as a subuser for this server.'
                            }
                        />
                    </div>
                )}
                <div className='my-6'>
                    {Object.keys(permissions)
                        .filter((key) => key !== 'websocket')
                        .map((key, index) => (
                            <PermissionTitleBox
                                key={`permission_${key}`}
                                title={key}
                                isEditable={canEditUser}
                                permissions={Object.keys(permissions[key]?.keys ?? {}).map((pkey) => `${key}.${pkey}`)}
                                className={index > 0 ? 'mt-4' : undefined}
                            >
                                <p className='mb-4 text-sm text-gray-400'>{permissions[key]?.description}</p>
                                {Object.keys(permissions[key]?.keys ?? {}).map((pkey) => (
                                    <PermissionRow
                                        key={`permission_${key}.${pkey}`}
                                        permission={`${key}.${pkey}`}
                                        disabled={!canEditUser || editablePermissions.indexOf(`${key}.${pkey}`) < 0}
                                    />
                                ))}
                            </PermissionTitleBox>
                        ))}
                </div>
                <Can action={subuser ? 'user.update' : 'user.create'}>
                    <div className='flex justify-end pb-6'>
                        <Button type='submit' className='w-full sm:w-auto'>
                            {subuser ? 'Save' : 'Invite User'}
                        </Button>
                    </div>
                </Can>
            </Form>
        </Formik>
    );
};

export default asModal<Props>({
    top: false,
})(EditSubuserModal);

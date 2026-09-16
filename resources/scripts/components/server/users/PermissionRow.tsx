import clsx from 'clsx';
import Checkbox from '@/reviactyl/elements/Checkbox';
import { useStoreState } from 'easy-peasy';

interface Props {
    permission: string;
    disabled: boolean;
}

const PermissionRow = ({ permission, disabled }: Props) => {
    const [key = '', pkey = ''] = permission.split('.', 2);
    const permissions = useStoreState((state) => state.permissions.data);

    return (
        <label
            htmlFor={`permission_${permission}`}
            className={clsx(
                'flex items-center border border-transparent rounded md:p-2 transition-colors duration-75',
                'not-first:mt-4 sm:not-first:mt-2',
                {
                    'cursor-pointer hover:border-gray-600 hover:bg-gray-900': !disabled,
                    'opacity-50': disabled,
                },
            )}
        >
            <div className='p-2'>
                <Checkbox
                    id={`permission_${permission}`}
                    name='permissions'
                    value={permission}
                    className='w-5 h-5 mr-2'
                    disabled={disabled}
                />
            </div>

            <div className='flex-1'>
                <p className='font-medium'>{pkey}</p>

                {(permissions[key]?.keys?.[pkey]?.length ?? 0) > 0 && (
                    <p className='text-xs text-gray-400 mt-1'>{permissions[key]?.keys?.[pkey] ?? ''}</p>
                )}
            </div>
        </label>
    );
};

export default PermissionRow;

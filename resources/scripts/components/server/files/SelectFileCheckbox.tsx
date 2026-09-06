import * as React from 'react';
import { ServerContext } from '@/state/server';
import Input from '@/reviactyl/elements/Input';

export const FileActionCheckbox = (props: React.ComponentProps<typeof Input>) => <Input {...props} />;

export default ({ name }: { name: string }) => {
    const isChecked = ServerContext.useStoreState((state) => state.files.selectedFiles.indexOf(name) >= 0);
    const appendSelectedFile = ServerContext.useStoreActions((actions) => actions.files.appendSelectedFile);
    const removeSelectedFile = ServerContext.useStoreActions((actions) => actions.files.removeSelectedFile);

    return (
        <label className='absolute z-30 flex-none cursor-pointer self-center px-4 py-2'>
            <FileActionCheckbox
                name='selectedFiles'
                value={name}
                checked={isChecked}
                type='checkbox'
                onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                    if (e.currentTarget.checked) {
                        appendSelectedFile(name);
                    } else {
                        removeSelectedFile(name);
                    }
                }}
            />
        </label>
    );
};

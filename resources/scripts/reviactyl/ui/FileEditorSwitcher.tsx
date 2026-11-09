import React, { useState, useEffect, useId } from 'react';
import { useTranslation } from 'react-i18next';
import { ApplicationStore } from '@/state';
import { useStoreActions, useStoreState } from 'easy-peasy';
import updateEditor from '@/api/account/updateAccountEditor';
import Select from '@/reviactyl/elements/Select';

interface FileEditorSwitcherProps {
    display: string;
}

const FileEditorSwitcher = () => {
    const id = useId();
    const { t } = useTranslation('dashboard/account');
    const user = useStoreState((state: ApplicationStore) => state.user.data);
    const updateUserData = useStoreActions((actions: any) => actions.user.updateUserData);
    const [editors, setEditors] = useState<Record<string, FileEditorSwitcherProps>>({});
    const [currentEditor, setCurrentEditor] = useState(user?.fileEditor || 'cm');

    useEffect(() => {
        fetch('/file-editors/list.json')
            .then((res) => res.json())
            .then((data) => setEditors(data))
            .catch(() =>
                setEditors({
                    cm: { display: 'CodeMirror (Default Editor)' },
                    mo: { display: 'Monaco (Like VS Code)' },
                }),
            );
    }, []);

    const handleChange = async (e: React.ChangeEvent<HTMLSelectElement>) => {
        const newEditor = e.target.value;
        setCurrentEditor(newEditor);

        if (user) {
            try {
                await updateEditor({ fileEditor: newEditor });
                updateUserData({ fileEditor: newEditor });
            } catch (error) {
                console.error('Failed to update editor:', error);
            }
        }
    };

    return (
        <div className='flex flex-col gap-2 mb-2 sm:flex-row sm:justify-between sm:items-center'>
            <label htmlFor={id} className='min-w-0 flex-1'>
                {t('overview.editor')}
            </label>
            <Select id={id} className='pr-15! w-full min-w-0 sm:w-auto!' value={currentEditor} onChange={handleChange}>
                {Object.entries(editors).map(([key, name]) => (
                    <option key={key} value={key}>
                        {name.display}
                    </option>
                ))}
            </Select>
        </div>
    );
};

export default FileEditorSwitcher;

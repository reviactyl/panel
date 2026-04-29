import { useEffect, useState } from 'react';
import getFileContents from '@/api/server/files/getFileContents';
import { httpErrorToHuman } from '@/api/http';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import saveFileContents from '@/api/server/files/saveFileContents';
import FileManagerBreadcrumbs from '@/components/server/files/FileManagerBreadcrumbs';
import { useLocation, useNavigate } from 'react-router-dom';
import FileNameModal from '@/components/server/files/FileNameModal';
import Can from '@/reviactyl/elements/Can';
import FlashMessageRender from '@/components/FlashMessageRender';
import ContentBlock from '@/reviactyl/ui/ContentBlock';
import { ServerError } from '@/reviactyl/elements/ScreenBlock';
import tw from 'twin.macro';
import Button from '@/reviactyl/elements/Button';
import Select from '@/reviactyl/elements/Select';
import modes from '@/modes';
import useFlash from '@/plugins/useFlash';
import { ServerContext } from '@/state/server';
import ErrorBoundary from '@/reviactyl/elements/ErrorBoundary';
import { encodePathSegments, hashToPath } from '@/helpers';
import { dirname } from 'pathe';
import MonacoEditor from '@/reviactyl/elements/MonacoEditor';
import Card from '@/reviactyl/ui/Card';

export default () => {
    const [error, setError] = useState('');
    const location = useLocation();
    const { hash } = location;
    // Use regex that includes # for isNewFile
    const isNewFile = /\/files\/new(\/|$|#)/.test(location.pathname);
    const [loading, setLoading] = useState(!isNewFile);
    const [content, setContent] = useState('');
    const [modalVisible, setModalVisible] = useState(false);
    const [mode, setMode] = useState('text/plain');

    const navigate = useNavigate();

    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const setDirectory = ServerContext.useStoreActions((actions) => actions.files.setDirectory);

    const { addError, clearFlashes } = useFlash();

    let fetchFileContent: null | (() => Promise<string>) = null;

    useEffect(() => {
        if (isNewFile) return;

        setError('');
        const path = hashToPath(hash);
        // Guard: if path resolves to root (no file hash), redirect rather than asking Agent to read "/" and getting a 200/500 error.
        if (path === '/' || path === '') {
            navigate(`/server/${id}/files`);
            return;
        }
        setLoading(true);
        setDirectory(dirname(path));
        getFileContents(uuid, path)
            .then(setContent)
            .catch((error) => {
                console.error(error);
                setError(httpErrorToHuman(error));
            })
            .then(() => setLoading(false));
    }, [isNewFile, uuid, hash]);

    const save = (name?: string) => {
        if (!fetchFileContent) {
            return;
        }

        setLoading(true);
        clearFlashes('files:view');
        fetchFileContent()
            .then((content) => saveFileContents(uuid, name || hashToPath(hash), content))
            .then(() => {
                if (name) {
                    navigate(`/server/${id}/files/edit#/${encodePathSegments(name)}`);
                    return;
                }

                return Promise.resolve();
            })
            .catch((error) => {
                console.error(error);
                addError({ message: httpErrorToHuman(error), key: 'files:view' });
            })
            .then(() => setLoading(false));
    };

    if (error) {
        return <ServerError message={error} onBack={() => navigate(-1)} />;
    }

    return (
        <ContentBlock title={'File Editor'}>
            <FlashMessageRender byKey={'files:view'} css={tw`mb-4`} />
            <ErrorBoundary>
                <Card css={tw`!rounded-b-none !px-2 !py-6 mb-1 mt-2`}>
                    <FileManagerBreadcrumbs withinFileEditor isNewFile={isNewFile} />
                </Card>
            </ErrorBoundary>
            {hash.replace(/^#/, '').endsWith('.pteroignore') && (
                <Card className='!rounded-none mb-1'>
                    <div css={tw`mb-4 p-4 rounded-ui border border-gray-600`}>
                        <p css={tw`text-gray-300 text-sm`}>
                            You&apos;re editing a{' '}
                            <code css={tw`font-mono bg-gray-900 rounded-ui border border-gray-600 py-px px-1`}>
                                .pteroignore
                            </code>{' '}
                            file. Any files or directories listed in here will be excluded from backups. Wildcards are
                            supported by using an asterisk (
                            <code css={tw`font-mono bg-gray-900 rounded-ui border border-gray-600 py-px px-1`}>*</code>
                            ). You can negate a prior rule by prepending an exclamation point (
                            <code css={tw`font-mono bg-gray-900 rounded-ui border border-gray-600 py-px px-1`}>!</code>
                            ).
                        </p>
                    </div>
                </Card>
            )}
            <FileNameModal
                visible={modalVisible}
                onDismissed={() => setModalVisible(false)}
                onFileNamed={(name) => {
                    setModalVisible(false);
                    save(name);
                }}
            />
            <Card css={tw`relative !p-1 !rounded-none mb-1`}>
                <SpinnerOverlay visible={loading} />
                <MonacoEditor
                    mode={mode}
                    filename={hash.replace(/^#/, '')}
                    onModeChanged={setMode}
                    initialContent={content}
                    fetchContent={(value) => {
                        fetchFileContent = value;
                    }}
                    onContentSaved={() => {
                        if (isNewFile) {
                            setModalVisible(true);
                        } else {
                            save();
                        }
                    }}
                />
            </Card>
            <Card css={tw`flex justify-end !rounded-t-none !px-2 !py-3`}>
                <div css={tw`flex-1 sm:flex-none rounded-ui bg-gray-700 border border-gray-600 mr-4`}>
                    <Select value={mode} onChange={(e) => setMode(e.currentTarget.value)}>
                        {modes.map((mode) => (
                            <option key={`${mode.name}_${mode.mode}`} value={mode.mode}>
                                {mode.name}
                            </option>
                        ))}
                    </Select>
                </div>
                {!isNewFile ? (
                    <Can action={'file.update'}>
                        <Button css={tw`flex-1 sm:flex-none`} onClick={() => save()}>
                            Save Content
                        </Button>
                    </Can>
                ) : (
                    <Can action={'file.create'}>
                        <Button css={tw`flex-1 sm:flex-none`} onClick={() => setModalVisible(true)}>
                            Create File
                        </Button>
                    </Can>
                )}
            </Card>
        </ContentBlock>
    );
};

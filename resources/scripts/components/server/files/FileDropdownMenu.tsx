import React, { memo, useRef, useState } from 'react';
import {
    FaBoxOpen,
    FaCopy,
    FaEllipsis,
    FaFileZipper,
    FaFileCode,
    FaFileArrowDown,
    FaTurnUp,
    FaPen,
    FaTrash,
} from 'react-icons/fa6';
import { IconType } from 'react-icons';
import RenameFileModal from '@/components/server/files/RenameFileModal';
import { ServerContext } from '@/state/server';
import { join } from 'pathe';
import deleteFiles from '@/api/server/files/deleteFiles';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import copyFile from '@/api/server/files/copyFile';
import Can from '@/components/elements/Can';
import getFileDownloadUrl from '@/api/server/files/getFileDownloadUrl';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import { FileObject } from '@/api/server/files/loadDirectory';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import DropdownMenu from '@/components/elements/DropdownMenu';
import styled from 'styled-components';
import useEventListener from '@/plugins/useEventListener';
import compressFiles from '@/api/server/files/compressFiles';
import decompressFiles from '@/api/server/files/decompressFiles';
import isEqual from 'react-fast-compare';
import ChmodFileModal from '@/components/server/files/ChmodFileModal';
import { Dialog } from '@/components/elements/dialog';

type ModalType = 'rename' | 'move' | 'chmod';

const StyledRow = styled.div<{ $danger?: boolean }>`
    ${tw`p-2 flex items-center rounded-ui w-full cursor-pointer`};
    transition: 150ms all ease;

    &:hover {
        ${(props) => (props.$danger ? tw`text-red-700 bg-red-100` : tw`text-gray-700 bg-gray-100`)};
    }
`;

interface RowProps extends React.HTMLAttributes<HTMLDivElement> {
    icon: IconType;
    title: string;
    $danger?: boolean;
}

const Row = ({ icon, title, ...props }: RowProps) => {
    const ItemIcon = icon;

    return (
        <StyledRow {...props}>
            <ItemIcon className={'text-xs inline-block w-[1.25em]'} />
            <span css={tw`ml-2`}>{title}</span>
        </StyledRow>
    );
};

const FileDropdownMenu = ({ file }: { file: FileObject }) => {
    const onClickRef = useRef<DropdownMenu>(null);
    const [showSpinner, setShowSpinner] = useState(false);
    const [modal, setModal] = useState<ModalType | null>(null);
    const [showConfirmation, setShowConfirmation] = useState(false);

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { mutate } = useFileManagerSwr();
    const { clearAndAddHttpError, clearFlashes } = useFlash();
    const directory = ServerContext.useStoreState((state) => state.files.directory);

    useEventListener(`panel:files:ctx:${file.key}`, (e: CustomEvent) => {
        if (onClickRef.current) {
            onClickRef.current.triggerMenu(e.detail);
        }
    });

    const doDeletion = () => {
        clearFlashes('files');

        // For UI speed, immediately remove the file from the listing before calling the deletion function.
        // If the delete actually fails, we'll fetch the current directory contents again automatically.
        mutate((files) => files.filter((f) => f.key !== file.key), false);

        deleteFiles(uuid, directory, [file.name]).catch((error) => {
            mutate();
            clearAndAddHttpError({ key: 'files', error });
        });
    };

    const doCopy = () => {
        setShowSpinner(true);
        clearFlashes('files');

        copyFile(uuid, join(directory, file.name))
            .then(() => mutate())
            .catch((error) => clearAndAddHttpError({ key: 'files', error }))
            .then(() => setShowSpinner(false));
    };

    const doDownload = () => {
        setShowSpinner(true);
        clearFlashes('files');

        getFileDownloadUrl(uuid, join(directory, file.name))
            .then((url) => {
                window.location.href = url;
            })
            .catch((error) => clearAndAddHttpError({ key: 'files', error }))
            .then(() => setShowSpinner(false));
    };

    const doArchive = () => {
        setShowSpinner(true);
        clearFlashes('files');

        compressFiles(uuid, directory, [file.name])
            .then(() => mutate())
            .catch((error) => clearAndAddHttpError({ key: 'files', error }))
            .then(() => setShowSpinner(false));
    };

    const doUnarchive = () => {
        setShowSpinner(true);
        clearFlashes('files');

        decompressFiles(uuid, directory, file.name)
            .then(() => mutate())
            .catch((error) => clearAndAddHttpError({ key: 'files', error }))
            .then(() => setShowSpinner(false));
    };

    return (
        <>
            <Dialog.Confirm
                open={showConfirmation}
                onClose={() => setShowConfirmation(false)}
                title={`Delete ${file.isFile ? 'File' : 'Directory'}`}
                confirm={'Delete'}
                onConfirmed={doDeletion}
            >
                You will not be able to recover the contents of&nbsp;
                <span className={'font-semibold text-gray-50'}>{file.name}</span> once deleted.
            </Dialog.Confirm>
            {modal === 'chmod' && (
                <ChmodFileModal
                    visible
                    appear
                    files={[{ file: file.name, mode: file.modeBits }]}
                    onDismissed={() => setModal(null)}
                />
            )}
            {(modal === 'rename' || modal === 'move') && (
                <RenameFileModal
                    visible
                    appear
                    files={[file.name]}
                    useMoveTerminology={modal === 'move'}
                    onDismissed={() => setModal(null)}
                />
            )}
            <SpinnerOverlay visible={showSpinner} fixed size={'large'} />
            <DropdownMenu
                ref={onClickRef}
                renderToggle={(onClick) => (
                    <div css={tw`px-4 py-2 hover:text-white`} onClick={onClick}>
                        <FaEllipsis />
                    </div>
                )}
            >
                <Can action={'file.update'}>
                    <Row onClick={() => setModal('rename')} icon={FaPen} title={'Rename'} />
                    <Row onClick={() => setModal('move')} icon={FaTurnUp} title={'Move'} />
                    <Row onClick={() => setModal('chmod')} icon={FaFileCode} title={'Permissions'} />
                </Can>
                {file.isFile && (
                    <Can action={'file.create'}>
                        <Row onClick={doCopy} icon={FaCopy} title={'Copy'} />
                    </Can>
                )}
                {file.isArchiveType() ? (
                    <Can action={'file.create'}>
                        <Row onClick={doUnarchive} icon={FaBoxOpen} title={'Unarchive'} />
                    </Can>
                ) : (
                    <Can action={'file.archive'}>
                        <Row onClick={doArchive} icon={FaFileZipper} title={'Archive'} />
                    </Can>
                )}
                {file.isFile && <Row onClick={doDownload} icon={FaFileArrowDown} title={'Download'} />}
                <Can action={'file.delete'}>
                    <Row onClick={() => setShowConfirmation(true)} icon={FaTrash} title={'Delete'} $danger />
                </Can>
            </DropdownMenu>
        </>
    );
};

export default memo(FileDropdownMenu, isEqual);

import { memo, useEffect, useMemo, useState } from 'react';
import isEqual from 'react-fast-compare';
import { FaNetworkWired } from 'react-icons/fa6';
import InputSpinner from '@/reviactyl/elements/InputSpinner';
import { Textarea } from '@/reviactyl/elements/Input';
import Can from '@/reviactyl/elements/Can';
import { Button } from '@/reviactyl/components/button/index';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import { Allocation } from '@/api/server/getServer';
import { debounce } from 'debounce';
import setServerAllocationNotes from '@/api/server/network/setServerAllocationNotes';
import useFlash from '@/plugins/useFlash';
import { ServerContext } from '@/state/server';
import CopyOnClick from '@/reviactyl/elements/CopyOnClick';
import DeleteAllocationButton from '@/components/server/network/DeleteAllocationButton';
import setPrimaryServerAllocation from '@/api/server/network/setPrimaryServerAllocation';
import getServerAllocations from '@/api/swr/getServerAllocations';
import { ip } from '@/lib/formatters';
import Code from '@/reviactyl/elements/Code';
import { usePermissions } from '@/plugins/usePermissions';

interface Props {
    allocation: Allocation;
}

interface AllocationSaveState {
    revision: number;
    pendingSave: Promise<void>;
}

const notesSaves = new Map<string, AllocationSaveState>();
const primarySaves = new Map<string, AllocationSaveState>();

const AllocationRow = ({ allocation }: Props) => {
    const [loading, setLoading] = useState(false);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { mutate } = getServerAllocations();
    const [canUpdate] = usePermissions(['allocation.update']);

    const saveKey = `${uuid}:${allocation.id}`;
    const saveAllocationNotes = useMemo(
        () =>
            debounce((notes: string, state: AllocationSaveState, requestRevision: number) => {
                setLoading(true);
                clearFlashes('server:network');

                state.pendingSave = state.pendingSave
                    .then(async () => {
                        if (requestRevision !== state.revision) return;

                        await setServerAllocationNotes(uuid, allocation.id, notes);
                        if (requestRevision !== state.revision) return;

                        await mutate(
                            (data) =>
                                requestRevision === state.revision
                                    ? data?.map((a) => (a.id === allocation.id ? { ...a, notes } : a))
                                    : data,
                            false,
                        );
                    })
                    .catch(async (error) => {
                        if (requestRevision === state.revision) {
                            clearAndAddHttpError({ error, key: 'server:network' });
                            await mutate().catch(() => undefined);
                        }
                    })
                    .then(() => {
                        if (requestRevision === state.revision) {
                            setLoading(false);
                            if (notesSaves.get(saveKey) === state) notesSaves.delete(saveKey);
                        }
                    });
            }, 750),
        [uuid, allocation.id, saveKey, mutate, clearFlashes, clearAndAddHttpError],
    );

    useEffect(
        () => () => {
            saveAllocationNotes.flush();
        },
        [saveAllocationNotes],
    );

    const setAllocationNotes = (notes: string) => {
        let state = notesSaves.get(saveKey);
        if (!state) {
            state = { revision: 0, pendingSave: Promise.resolve() };
            notesSaves.set(saveKey, state);
        }
        state.revision += 1;
        saveAllocationNotes(notes, state, state.revision);
    };

    const setPrimaryAllocation = () => {
        let state = primarySaves.get(uuid);
        if (!state) {
            state = { revision: 0, pendingSave: Promise.resolve() };
            primarySaves.set(uuid, state);
        }
        const requestRevision = ++state.revision;
        const saveState = state;
        clearFlashes('server:network');
        mutate((data) => data?.map((a) => ({ ...a, isDefault: a.id === allocation.id })), false);

        saveState.pendingSave = saveState.pendingSave
            .then(async () => {
                if (requestRevision !== saveState.revision) return;

                const saved = await setPrimaryServerAllocation(uuid, allocation.id);
                if (requestRevision !== saveState.revision) return;

                await mutate(
                    (data) =>
                        requestRevision === saveState.revision
                            ? data?.map((a) => ({ ...a, isDefault: a.id === saved.id }))
                            : data,
                    false,
                );
            })
            .catch(async (error) => {
                if (requestRevision === saveState.revision) {
                    clearAndAddHttpError({ error, key: 'server:network' });
                    await mutate().catch(() => undefined);
                }
            })
            .then(() => {
                if (requestRevision === saveState.revision && primarySaves.get(uuid) === saveState) {
                    primarySaves.delete(uuid);
                }
            });
    };

    return (
        <GreyRowBox $hoverable={false} className={'flex-wrap md:flex-nowrap mt-2'}>
            <div className={'flex items-center w-full md:w-auto'}>
                <div className={'pl-4 pr-6 text-gray-400'}>
                    <FaNetworkWired />
                </div>
                <div className={'mr-4 flex-1'}>
                    {allocation.alias ? (
                        <CopyOnClick text={allocation.alias}>
                            <Code>{allocation.alias}</Code>
                        </CopyOnClick>
                    ) : (
                        <CopyOnClick text={ip(allocation.ip)}>
                            <Code>{ip(allocation.ip)}</Code>
                        </CopyOnClick>
                    )}
                    <label
                        className={
                            'uppercase text-xs mt-1 text-gray-400 block px-1 select-none transition-colors duration-150'
                        }
                    >
                        {allocation.alias ? 'Hostname' : 'IP Address'}
                    </label>
                </div>
                <div className={'w-16 md:w-24 overflow-hidden'}>
                    <Code>{allocation.port}</Code>
                    <label
                        className={
                            'uppercase text-xs mt-1 text-gray-400 block px-1 select-none transition-colors duration-150'
                        }
                    >
                        Port
                    </label>
                </div>
            </div>
            <div className={'mt-4 w-full md:mt-0 md:flex-1 md:w-auto'}>
                <InputSpinner visible={loading}>
                    <Textarea
                        placeholder={'Notes'}
                        defaultValue={allocation.notes || undefined}
                        disabled={!canUpdate}
                        onChange={(e) => setAllocationNotes(e.currentTarget.value)}
                    />
                </InputSpinner>
            </div>
            <div className={'flex justify-end space-x-4 mt-4 w-full md:mt-0 md:w-48'}>
                {allocation.isDefault ? (
                    <Button size={Button.Sizes.Small} className={'text-gray-50! bg-blue-600!'} disabled>
                        Primary
                    </Button>
                ) : (
                    <>
                        <Can action={'allocation.delete'}>
                            <DeleteAllocationButton allocation={allocation.id} />
                        </Can>
                        <Can action={'allocation.update'}>
                            <Button.Text size={Button.Sizes.Small} onClick={setPrimaryAllocation}>
                                Make Primary
                            </Button.Text>
                        </Can>
                    </>
                )}
            </div>
        </GreyRowBox>
    );
};

export default memo(AllocationRow, isEqual);

import { memo, useEffect, useMemo, useRef, useState } from 'react';
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
    const [notes, setNotes] = useState(allocation.notes || '');
    const savedNotes = useRef(allocation.notes || '');
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { mutate } = getServerAllocations();
    const [canUpdate] = usePermissions(['allocation.update']);

    const saveKey = `${uuid}:${allocation.id}`;
    useEffect(() => {
        savedNotes.current = allocation.notes || '';
        if (!notesSaves.has(saveKey)) setNotes(savedNotes.current);
    }, [saveKey, allocation.notes]);

    const saveAllocationNotes = useMemo(
        () =>
            debounce((notes: string, state: AllocationSaveState, requestRevision: number) => {
                setLoading(true);
                clearFlashes('server:network');

                state.pendingSave = state.pendingSave
                    .then(async () => {
                        if (requestRevision !== state.revision) return;

                        await setServerAllocationNotes(uuid, allocation.id, notes);
                        savedNotes.current = notes;
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
                            const data = await mutate().catch(() => undefined);
                            if (requestRevision === state.revision) {
                                const saved = data?.find((a) => a.id === allocation.id);
                                setNotes(saved ? saved.notes || '' : savedNotes.current);
                            }
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
        setNotes(notes);
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
        <GreyRowBox
            $hoverable={false}
            className={
                'grid grid-cols-1 gap-4 mt-2 md:grid-cols-[minmax(0,1fr)_auto] xl:grid-cols-[minmax(0,1fr)_minmax(12rem,1fr)_auto]'
            }
        >
            <div className={'flex items-center min-w-0 md:col-span-2 xl:col-span-1'}>
                <div className={'shrink-0 ps-4 pe-6 text-gray-400'}>
                    <FaNetworkWired />
                </div>
                <div className={'me-4 min-w-0 flex-1 [&_code]:max-w-full [&_code]:truncate'}>
                    {allocation.alias ? (
                        <CopyOnClick text={allocation.alias}>
                            <span className='block' title={allocation.alias}>
                                <Code>{allocation.alias}</Code>
                            </span>
                        </CopyOnClick>
                    ) : (
                        <CopyOnClick text={ip(allocation.ip)}>
                            <span className='block' title={ip(allocation.ip)}>
                                <Code>{ip(allocation.ip)}</Code>
                            </span>
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
                <div className={'w-20 shrink-0 md:w-24'}>
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
            <div className={'min-w-0'}>
                <InputSpinner visible={loading}>
                    <Textarea
                        placeholder={'Notes'}
                        value={notes}
                        disabled={!canUpdate}
                        onChange={(e) => setAllocationNotes(e.currentTarget.value)}
                    />
                </InputSpinner>
            </div>
            <div className={'flex justify-end space-x-4'}>
                {allocation.isDefault ? (
                    <Button size={Button.Sizes.Small} disabled>
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

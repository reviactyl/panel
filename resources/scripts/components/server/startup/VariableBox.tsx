import { memo, useEffect, useMemo, useRef, useState } from 'react';
import { ServerEggVariable } from '@/api/server/types';
import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import { usePermissions } from '@/plugins/usePermissions';
import { useTranslation } from 'react-i18next';
import InputSpinner from '@/reviactyl/elements/InputSpinner';
import Button from '@/reviactyl/elements/Button';
import Input from '@/reviactyl/elements/Input';
import Switch from '@/reviactyl/elements/Switch';
import { debounce } from 'debounce';
import updateStartupVariable from '@/api/server/updateStartupVariable';
import useFlash from '@/plugins/useFlash';
import FlashMessageRender from '@/components/FlashMessageRender';
import getServerStartup from '@/api/swr/getServerStartup';
import Select from '@/reviactyl/elements/Select';
import isEqual from 'react-fast-compare';
import { ServerContext } from '@/state/server';

interface Props {
    variable: ServerEggVariable;
}

const VariableBox = ({ variable }: Props) => {
    const FLASH_KEY = `server:startup:${variable.envVariable}`;
    const { t } = useTranslation('server/startup');

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [loading, setLoading] = useState(false);
    const [canEdit] = usePermissions(['startup.update']);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const { mutate } = getServerStartup(uuid);

    const secretInput = useRef<HTMLInputElement>(null);
    const revision = useRef(0);
    const saveVariableValue = useMemo(
        () =>
            debounce((value: string, requestRevision: number) => {
                setLoading(true);
                clearFlashes(FLASH_KEY);

                updateStartupVariable(uuid, variable.envVariable, value)
                    .then(async ([response]) => {
                        if (requestRevision !== revision.current) return;

                        await mutate(
                            (data) =>
                                data && requestRevision === revision.current
                                    ? {
                                          ...data,
                                          variables: data.variables.map((v) =>
                                              v.envVariable === response.envVariable ? response : v,
                                          ),
                                      }
                                    : data,
                            false,
                        );

                        if (requestRevision === revision.current) await mutate();
                    })
                    .catch((error) => {
                        if (requestRevision !== revision.current) return;

                        if (!variable.isSecret) console.error(error);
                        clearAndAddHttpError({ error, key: FLASH_KEY });
                    })
                    .then(() => {
                        if (requestRevision === revision.current) setLoading(false);
                    });
            }, 500),
        [uuid, variable.envVariable, variable.isSecret, mutate, clearFlashes, clearAndAddHttpError, FLASH_KEY],
    );

    useEffect(
        () => () => {
            saveVariableValue.clear();
            revision.current += 1;
        },
        [saveVariableValue],
    );

    const setVariableValue = (value: string) => {
        revision.current += 1;
        saveVariableValue(value, revision.current);
    };

    const useSwitch = variable.rules.some(
        (v) => v === 'boolean' || v === 'in:0,1' || v === 'in:1,0' || v === 'in:true,false' || v === 'in:false,true',
    );
    const isStringSwitch = variable.rules.some((v) => v === 'in:true,false' || v === 'in:false,true');
    const effectiveValue = variable.serverValue ?? variable.defaultValue;
    const selectValues = variable.rules.find((v) => v.startsWith('in:'))?.split(',') || [];

    return (
        <TitledGreyBox
            title={
                <p className='text-sm uppercase'>
                    {!variable.isEditable && (
                        <span className='bg-gray-900 text-xs py-1 px-2 rounded-full mr-2 mb-1'>{t('read-only')}</span>
                    )}
                    {variable.isSecret && (
                        <span className='bg-gray-900 text-xs py-1 px-2 rounded-full mr-2 mb-1'>{t('sensitive')}</span>
                    )}
                    {variable.name}
                </p>
            }
        >
            <FlashMessageRender byKey={FLASH_KEY} className='mb-2 md:mb-4' />
            <InputSpinner visible={loading}>
                {variable.isSecret ? (
                    <div className='flex gap-3'>
                        <Input
                            ref={secretInput}
                            type='password'
                            className='min-w-0 flex-1'
                            aria-label={variable.name}
                            autoComplete='new-password'
                            name={variable.envVariable}
                            placeholder={variable.hasValue ? '••••••••••••' : ''}
                            readOnly={!canEdit || !variable.isEditable}
                            onChange={(event) => {
                                if (canEdit && variable.isEditable) setVariableValue(event.currentTarget.value);
                            }}
                        />
                        <Button
                            type='button'
                            isSecondary
                            disabled={!canEdit || !variable.isEditable}
                            onClick={() => {
                                if (secretInput.current) secretInput.current.value = '';
                                setVariableValue('');
                            }}
                        >
                            {t('clear')}
                        </Button>
                    </div>
                ) : useSwitch ? (
                    <>
                        <Switch
                            readOnly={!canEdit || !variable.isEditable}
                            name={variable.envVariable}
                            defaultChecked={isStringSwitch ? effectiveValue === 'true' : effectiveValue === '1'}
                            onChange={(event) => {
                                if (canEdit && variable.isEditable) {
                                    if (isStringSwitch) {
                                        setVariableValue(event.currentTarget.checked ? 'true' : 'false');
                                    } else {
                                        setVariableValue(event.currentTarget.checked ? '1' : '0');
                                    }
                                }
                            }}
                        />
                    </>
                ) : (
                    <>
                        {selectValues.length > 0 ? (
                            <>
                                <Select
                                    onChange={(e) => setVariableValue(e.target.value)}
                                    name={variable.envVariable}
                                    defaultValue={effectiveValue}
                                    disabled={!canEdit || !variable.isEditable}
                                >
                                    {selectValues.map((selectValue) => (
                                        <option
                                            key={selectValue.replace('in:', '')}
                                            value={selectValue.replace('in:', '')}
                                        >
                                            {selectValue.replace('in:', '')}
                                        </option>
                                    ))}
                                </Select>
                            </>
                        ) : (
                            <>
                                <Input
                                    onChange={(e) => {
                                        if (canEdit && variable.isEditable) {
                                            setVariableValue(e.currentTarget.value);
                                        }
                                    }}
                                    readOnly={!canEdit || !variable.isEditable}
                                    name={variable.envVariable}
                                    defaultValue={variable.serverValue ?? ''}
                                    placeholder={variable.defaultValue}
                                />
                            </>
                        )}
                    </>
                )}
            </InputSpinner>

            <p className='mt-1 text-xs text-gray-300'>{variable.description}</p>
        </TitledGreyBox>
    );
};

export default memo(VariableBox, isEqual);

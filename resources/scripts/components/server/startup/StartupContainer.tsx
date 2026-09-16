import React, { useCallback, useEffect, useState } from 'react';
import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import VariableBox from '@/components/server/startup/VariableBox';
import ServerContentBlock from '@/reviactyl/elements/ServerContentBlock';
import getServerStartup from '@/api/swr/getServerStartup';
import Spinner from '@/reviactyl/elements/Spinner';
import { ServerError } from '@/reviactyl/elements/ScreenBlock';
import { httpErrorToHuman } from '@/api/http';
import { ServerContext } from '@/state/server';
import { useDeepCompareEffect } from '@/plugins/useDeepCompareEffect';
import Select from '@/reviactyl/elements/Select';
import isEqual from 'react-fast-compare';
import Input from '@/reviactyl/elements/Input';
import setSelectedDockerImage from '@/api/server/setSelectedDockerImage';
import InputSpinner from '@/reviactyl/elements/InputSpinner';
import useFlash from '@/plugins/useFlash';
import { useTranslation } from 'react-i18next';
import { usePermissions } from '@/plugins/usePermissions';

const StartupContainer = () => {
    const { t } = useTranslation('server/startup');
    const [loading, setLoading] = useState(false);
    const { clearFlashes, clearAndAddHttpError } = useFlash();

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [canUpdateDockerImage] = usePermissions(['startup.docker-image']);
    const variables = ServerContext.useStoreState(
        ({ server }) => ({
            variables: server.data!.variables,
            invocation: server.data!.invocation,
            dockerImage: server.data!.dockerImage,
        }),
        isEqual,
    );

    const { data, error, isValidating, mutate } = getServerStartup(uuid, {
        ...variables,
        dockerImages: { [variables.dockerImage]: variables.dockerImage },
    });

    const setServerFromState = ServerContext.useStoreActions((actions) => actions.server.setServerFromState);
    const isCustomImage =
        data &&
        !Object.values(data.dockerImages)
            .map((v) => v.toLowerCase())
            .includes(variables.dockerImage.toLowerCase());

    useEffect(() => {
        // Since we're passing in initial data this will not trigger on mount automatically. We
        // want to always fetch fresh information from the API however when we're loading the startup
        // information.
        mutate();
    }, []);

    useDeepCompareEffect(() => {
        if (!data) return;

        setServerFromState((s) => ({
            ...s,
            invocation: data.invocation,
            variables: data.variables,
        }));
    }, [data]);

    const updateSelectedDockerImage = useCallback(
        (v: React.ChangeEvent<HTMLSelectElement>) => {
            setLoading(true);
            clearFlashes('startup:image');

            const image = v.currentTarget.value;
            setSelectedDockerImage(uuid, image)
                .then(() => setServerFromState((s) => ({ ...s, dockerImage: image })))
                .catch((error) => {
                    console.error(error);
                    clearAndAddHttpError({ key: 'startup:image', error });
                })
                .then(() => setLoading(false));
        },
        [uuid],
    );

    return !data ? (
        !error || (error && isValidating) ? (
            <Spinner centered size={Spinner.Size.LARGE} />
        ) : (
            <ServerError title={'Oops!'} message={httpErrorToHuman(error)} onRetry={() => mutate()} />
        )
    ) : (
        <ServerContentBlock title={t('title')} showFlashKey={'startup:image'}>
            <div className='md:flex'>
                <TitledGreyBox title={t('startup-command')} className='flex-1'>
                    <div className='px-1 py-2'>
                        <p className='rounded-ui border border-gray-800 bg-gray-900 px-4 py-2 font-mono'>
                            {data.invocation}
                        </p>
                    </div>
                </TitledGreyBox>
                <TitledGreyBox title={t('docker-image')} className='mt-8 flex-1 md:mt-0 md:ml-10 lg:w-1/3 lg:flex-none'>
                    {Object.keys(data.dockerImages).length > 1 && !isCustomImage ? (
                        <>
                            <InputSpinner visible={loading}>
                                <Select
                                    disabled={!canUpdateDockerImage || Object.keys(data.dockerImages).length < 2}
                                    onChange={updateSelectedDockerImage}
                                    value={variables.dockerImage}
                                >
                                    {Object.keys(data.dockerImages).map((key) => (
                                        <option key={data.dockerImages[key]} value={data.dockerImages[key]}>
                                            {key}
                                        </option>
                                    ))}
                                </Select>
                            </InputSpinner>
                            <p className='mt-2 text-xs text-gray-300'>{t('docker-info')}</p>
                        </>
                    ) : (
                        <>
                            <Input disabled readOnly value={variables.dockerImage} />
                            {isCustomImage && <p className='mt-2 text-xs text-gray-300'>{t('manually-set-docker')}</p>}
                        </>
                    )}
                </TitledGreyBox>
            </div>
            <h3 className='mt-8 mb-2 text-2xl'>{t('variables')}</h3>
            <div className='grid gap-8 md:grid-cols-2'>
                {data.variables.map((variable) => (
                    <VariableBox key={variable.envVariable} variable={variable} />
                ))}
            </div>
        </ServerContentBlock>
    );
};

export default StartupContainer;

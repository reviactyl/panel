import { useEffect, useState } from 'react';
import getServerSchedules from '@/api/server/schedules/getServerSchedules';
import { ServerContext } from '@/state/server';
import Spinner from '@/reviactyl/elements/Spinner';
import { Link } from 'react-router-dom';
import FlashMessageRender from '@/components/FlashMessageRender';
import ScheduleRow from '@/components/server/schedules/ScheduleRow';
import { httpErrorToHuman } from '@/api/http';
import EditScheduleModal from '@/components/server/schedules/EditScheduleModal';
import Can from '@/reviactyl/elements/Can';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import { Button } from '@/reviactyl/components/button/index';
import ServerContentBlock from '@/reviactyl/elements/ServerContentBlock';
import Card from '@/reviactyl/ui/Card';
import { ClockIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';

function ScheduleContainer() {
    const { t } = useTranslation('server/schedules');

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearFlashes, addError } = useFlash();
    const [loading, setLoading] = useState(true);
    const [visible, setVisible] = useState(false);

    const schedules = ServerContext.useStoreState((state) => state.schedules.data);
    const setSchedules = ServerContext.useStoreActions((actions) => actions.schedules.setSchedules);

    useEffect(() => {
        clearFlashes('schedules');
        getServerSchedules(uuid)
            .then((schedules) => setSchedules(schedules))
            .catch((error) => {
                addError({ message: httpErrorToHuman(error), key: 'schedules' });
                console.error(error);
            })
            .then(() => setLoading(false));
    }, []);

    return (
        <ServerContentBlock title={t('title')}>
            <FlashMessageRender byKey={'schedules'} css={tw`mb-4`} />
            {!schedules.length && loading ? (
                <Spinner size={'large'} centered />
            ) : (
                <>
                    {schedules.length === 0 ? (
                        <Card>
                            <p css={tw`flex justify-center text-center text-sm text-gray-400`}>
                                <ClockIcon className='w-5 h-5 mr-1' />
                                {t('no-schedules')}
                            </p>
                        </Card>
                    ) : (
                        schedules.map((schedule) => (
                            <GreyRowBox
                                as={Link}
                                key={schedule.id}
                                to={String(schedule.id)}
                                css={tw`cursor-pointer mb-2 flex-wrap`}
                            >
                                <ScheduleRow schedule={schedule} />
                            </GreyRowBox>
                        ))
                    )}
                    <Can action={'schedule.create'}>
                        <div css={tw`mt-2 flex justify-end`}>
                            <EditScheduleModal visible={visible} onModalDismissed={() => setVisible(false)} />
                            <Button type={'button'} onClick={() => setVisible(true)}>
                                {t('create-schedule')}
                            </Button>
                        </div>
                    </Can>
                </>
            )}
        </ServerContentBlock>
    );
}

export default ScheduleContainer;

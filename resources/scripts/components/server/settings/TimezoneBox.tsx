import { useEffect, useMemo, useRef, useState } from 'react';
import { Combobox, ComboboxButton, ComboboxInput, ComboboxOption, ComboboxOptions } from '@headlessui/react';
import { Actions, useStoreActions } from 'easy-peasy';
import { useTranslation } from 'react-i18next';
import { FaCheck, FaChevronDown } from 'react-icons/fa6';
import classNames from 'classnames';
import { ServerContext } from '@/state/server';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import setServerTimezone from '@/api/server/setServerTimezone';
import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import Label from '@/reviactyl/elements/Label';
import { Button } from '@/reviactyl/components/button/index';
import inputStyles from '@/reviactyl/elements/inputs/styles.module.css';
import { formatInTimezone, getBrowserTimezone, getTimezoneOffset, getTimezones } from '@/lib/timezones';

interface Option {
    value: string | null;
    label: string;
    offset: string;
    search: string;
}

const CurrentTime = ({ timezone }: { timezone: string }) => {
    const [now, setNow] = useState(() => new Date());

    useEffect(() => {
        const interval = setInterval(() => setNow(new Date()), 1000);

        return () => clearInterval(interval);
    }, []);

    return <span className='font-medium text-gray-100'>{formatInTimezone(now, timezone)}</span>;
};

export default () => {
    const { t } = useTranslation('server/settings');
    const server = ServerContext.useStoreState((state) => state.server.data!);
    const setServer = ServerContext.useStoreActions((actions) => actions.server.setServer);
    const setSchedules = ServerContext.useStoreActions((actions) => actions.schedules.setSchedules);
    const { addFlash, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const [selected, setSelected] = useState<string | null>(server.timezone);
    const [query, setQuery] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const button = useRef<HTMLButtonElement>(null);

    const options = useMemo<Option[]>(() => {
        const zones = getTimezones();
        if (server.timezone && !zones.includes(server.timezone)) {
            zones.unshift(server.timezone);
        }

        return [
            { value: null, label: t('timezone.default'), offset: '', search: t('timezone.default').toLowerCase() },
            ...zones.map((zone) => {
                const label = zone.replace(/_/g, ' ');
                const offset = getTimezoneOffset(zone);

                return { value: zone, label, offset, search: `${zone} ${label} ${offset}`.toLowerCase() };
            }),
        ];
    }, [server.timezone, t]);

    const filtered = useMemo(() => {
        const terms = query.toLowerCase().split(/\s+/).filter(Boolean);

        return terms.length ? options.filter((option) => terms.every((term) => option.search.includes(term))) : options;
    }, [options, query]);

    const browserTimezone = useMemo(getBrowserTimezone, []);

    const submit = () => {
        setIsSubmitting(true);
        clearFlashes('settings');
        setServerTimezone(server.uuid, selected)
            .then(() => {
                setServer({ ...server, timezone: selected });
                setSchedules([]);
                addFlash({ key: 'settings', type: 'success', message: t('timezone.saved') });
            })
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'settings', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setIsSubmitting(false));
    };

    const canUseBrowserTimezone = browserTimezone !== null && browserTimezone !== selected;

    return (
        <TitledGreyBox title={t('timezone.title')} className='relative'>
            <SpinnerOverlay visible={isSubmitting} />
            <Label htmlFor={'server-timezone'}>{t('timezone.label')}</Label>
            <Combobox
                value={options.find((option) => option.value === selected) ?? options[0]}
                virtual={{ options: filtered }}
                onChange={(option: Option | null) => option && setSelected(option.value)}
                onClose={() => setQuery('')}
            >
                <div className='relative'>
                    <ComboboxInput
                        id={'server-timezone'}
                        className={classNames(inputStyles.input, 'pr-10')}
                        placeholder={t('timezone.placeholder')}
                        autoComplete={'off'}
                        displayValue={(option: Option | null) => option?.label ?? ''}
                        onChange={(event) => setQuery(event.target.value)}
                        onFocus={(event) => event.target.select()}
                        onClick={(event) =>
                            event.currentTarget.getAttribute('aria-expanded') !== 'true' && button.current?.click()
                        }
                    />
                    <ComboboxButton
                        ref={button}
                        className='absolute inset-y-0 right-0 flex items-center px-3 text-gray-300'
                    >
                        <FaChevronDown className='h-3 w-3' />
                    </ComboboxButton>
                </div>
                <ComboboxOptions
                    anchor={{ to: 'bottom start', gap: 4 }}
                    className='z-50 !max-h-64 w-(--input-width) rounded-ui border border-gray-700 bg-gray-900 p-1 shadow-lg empty:invisible'
                >
                    {({ option }: { option: Option }) => (
                        <ComboboxOption
                            value={option}
                            className='group flex w-full cursor-pointer items-center rounded px-2 py-2 text-sm text-gray-200 data-focus:bg-gray-700 data-focus:text-gray-50'
                        >
                            <FaCheck className='invisible mr-2 h-3 w-3 shrink-0 text-reviactyl group-data-selected:visible' />
                            <span className='flex-1 truncate'>{option.label}</span>
                            <span className='ml-3 shrink-0 font-mono text-xs text-gray-400'>{option.offset}</span>
                        </ComboboxOption>
                    )}
                </ComboboxOptions>
            </Combobox>
            {filtered.length === 0 && <p className='mt-2 text-xs text-gray-400'>{t('timezone.no-results')}</p>}
            <div className='mt-4 flex items-center justify-between gap-4'>
                <div className={canUseBrowserTimezone ? 'text-sm' : 'text-base'}>
                    <p className='text-gray-300'>
                        {t('timezone.current-time')}:&nbsp;
                        <CurrentTime timezone={selected ?? server.defaultTimezone} />
                    </p>
                    {canUseBrowserTimezone && (
                        <button
                            type={'button'}
                            className='text-left text-reviactyl hover:underline'
                            onClick={() => setSelected(browserTimezone)}
                        >
                            {t('timezone.use-browser', { timezone: browserTimezone.replace(/_/g, ' ') })}
                        </button>
                    )}
                </div>
                <Button disabled={isSubmitting || selected === server.timezone} onClick={submit}>
                    {t('timezone.button')}
                </Button>
            </div>
        </TitledGreyBox>
    );
};

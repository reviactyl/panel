import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import Modal from '@/reviactyl/elements/Modal';
import type { RequiredModalProps } from '@/reviactyl/elements/Modal';
import { Field, Form, Formik, useFormikContext } from 'formik';
import { useStoreActions, useStoreState } from 'easy-peasy';
import type { Actions } from 'easy-peasy';
import debounce from 'debounce';
import { object, string } from 'yup';
import FormikFieldWrapper from '@/reviactyl/elements/FormikFieldWrapper';
import InputSpinner from '@/reviactyl/elements/InputSpinner';
import getServers from '@/api/getServers';
import type { Server } from '@/api/server/getServer';
import type { ApplicationStore } from '@/state';
import { Link } from 'react-router-dom';
import styled from 'styled-components';
import tw from 'twin.macro';
import Input from '@/reviactyl/elements/Input';
import { ip } from '@/lib/formatters';
import { useTranslation } from 'react-i18next';

type Props = RequiredModalProps;

interface Values {
    term: string;
}

const ServerResult = styled(Link)`
    ${tw`flex items-center bg-gray-950 p-4 rounded border-l-4 border-gray-950 no-underline transition-all duration-150`};

    &:hover {
        ${tw`shadow border-cyan-500`};
    }

    &:not(:last-of-type) {
        ${tw`mb-2`};
    }
`;

interface SearchWatcherProps {
    onTermChanged: (term: string, setSubmitting: (submitting: boolean) => void) => void;
}

const SearchWatcher = ({ onTermChanged }: SearchWatcherProps) => {
    const { values, setFieldTouched, setSubmitting } = useFormikContext<Values>();

    useEffect(() => {
        if (values.term.length > 0) {
            void setFieldTouched('term', true, true);
        }
        onTermChanged(values.term, setSubmitting);
    }, [values.term, onTermChanged, setFieldTouched, setSubmitting]);

    return null;
};

export default ({ ...props }: Props) => {
    const { t } = useTranslation('dashboard/index');
    const ref = useRef<HTMLInputElement>(null);
    const searchGeneration = useRef(0);
    const isAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const [servers, setServers] = useState<Server[]>([]);
    const { clearAndAddHttpError, clearFlashes } = useStoreActions(
        (actions: Actions<ApplicationStore>) => actions.flashes
    );

    const search = useMemo(
        () =>
            debounce((term: string, generation: number, setSubmitting: (submitting: boolean) => void) => {
                clearFlashes('search');

                // if (ref.current) ref.current.focus();
                getServers({ query: term, type: isAdmin ? 'admin-all' : undefined })
                    .then((response) => {
                        if (generation === searchGeneration.current) {
                            setServers(response.items.filter((_, index) => index < 5));
                        }
                    })
                    .catch((error) => {
                        if (generation === searchGeneration.current) {
                            console.error(error);
                            clearAndAddHttpError({ key: 'search', error });
                        }
                    })
                    .then(() => {
                        if (generation === searchGeneration.current) {
                            setSubmitting(false);
                            ref.current?.focus();
                        }
                    });
            }, 500),
        [clearAndAddHttpError, clearFlashes, isAdmin]
    );

    const onTermChanged = useCallback(
        (term: string, setSubmitting: (submitting: boolean) => void) => {
            const generation = ++searchGeneration.current;

            search.clear();
            setServers([]);

            if (term.length < 3) {
                setSubmitting(false);
                return;
            }

            setSubmitting(true);
            search(term, generation, setSubmitting);
        },
        [search]
    );

    useEffect(
        () => () => {
            searchGeneration.current++;
            search.clear();
        },
        [search]
    );

    useEffect(() => {
        if (props.visible) {
            if (ref.current) ref.current.focus();
        }
    }, [props.visible]);

    // Formik does not support an innerRef on custom components.
    const InputWithRef = (props: any) => <Input autoFocus {...props} ref={ref} />;

    return (
        <Formik
            onSubmit={() => Promise.resolve()}
            validationSchema={object().shape({
                term: string().min(3, t('search.string-min')),
            })}
            initialValues={{ term: '' } as Values}
        >
            {({ isSubmitting }) => (
                <Modal {...props}>
                    <Form>
                        <FormikFieldWrapper
                            name={'term'}
                            label={t('search.form-label')}
                            description={t('search.form-description')}
                        >
                            <SearchWatcher onTermChanged={onTermChanged} />
                            <InputSpinner visible={isSubmitting}>
                                <Field as={InputWithRef} name={'term'} />
                            </InputSpinner>
                        </FormikFieldWrapper>
                    </Form>
                    {servers.length > 0 && (
                        <div css={tw`mt-6`}>
                            {servers.map((server) => (
                                <ServerResult
                                    key={server.uuid}
                                    to={`/server/${server.id}`}
                                    onClick={() => props.onDismissed()}
                                >
                                    <div css={tw`flex-1 mr-4`}>
                                        <p css={tw`text-sm`}>{server.name}</p>
                                        <p css={tw`mt-1 text-xs text-gray-400`}>
                                            {server.allocations
                                                .filter((alloc) => alloc.isDefault)
                                                .map((allocation) => (
                                                    <span key={allocation.ip + allocation.port.toString()}>
                                                        {allocation.alias || ip(allocation.ip)}:{allocation.port}
                                                    </span>
                                                ))}
                                        </p>
                                    </div>
                                    <div css={tw`flex-none text-right`}>
                                        <span css={tw`text-xs py-1 px-2 bg-cyan-800 text-cyan-100 rounded`}>
                                            {server.node}
                                        </span>
                                    </div>
                                </ServerResult>
                            ))}
                        </div>
                    )}
                </Modal>
            )}
        </Formik>
    );
};

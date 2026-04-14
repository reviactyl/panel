import { Trans, TransSelectorProps, useTranslation } from 'react-i18next';

type Props = Omit<TransSelectorProps<any, any>, 't'>;

function Translate({ ns, children, ...props }: Props) {
    const { t } = useTranslation(ns);

    return (
        <Trans t={t} {...props}>
            {children}
        </Trans>
    );
}

export default Translate;

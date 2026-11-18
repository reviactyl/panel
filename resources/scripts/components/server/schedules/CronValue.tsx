import { Fragment } from 'react';

export default function CronValue({ value }: { value: string }) {
    return value.split(',').map((part, index, parts) => (
        <Fragment key={index}>
            {part}
            {index < parts.length - 1 && (
                <>
                    ,<wbr />
                </>
            )}
        </Fragment>
    ));
}

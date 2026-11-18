import {
    FaArrowDown19,
    FaArrowDown91,
    FaArrowDownAZ,
    FaArrowDownZA,
    FaArrowDownShortWide,
    FaArrowDownWideShort,
} from 'react-icons/fa6';
import type { SortDirection, SortType } from './sortFiles';

const icons = {
    name: { asc: FaArrowDownAZ, desc: FaArrowDownZA },
    size: { asc: FaArrowDown19, desc: FaArrowDown91 },
    date: { asc: FaArrowDownShortWide, desc: FaArrowDownWideShort },
};

export default function FileSortIcon({
    column,
    direction,
    className,
}: {
    column: SortType;
    direction: SortDirection;
    className?: string;
}) {
    const Icon = icons[column][direction];
    return <Icon aria-hidden='true' className={className} />;
}

import { CSSProperties } from 'react';
import { IconType } from 'react-icons';

interface Props {
    icon: IconType;
    className?: string;
    style?: CSSProperties;
}

const Icon = ({ icon, className, style }: Props) => {
    const IconComponent = icon;

    return <IconComponent className={className} style={style} />;
};

export default Icon;

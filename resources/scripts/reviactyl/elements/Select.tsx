import { forwardRef } from 'react';
import classNames from 'classnames';
import styles from '@/reviactyl/elements/inputs/styles.module.css';

interface Props {
    hideDropdownArrow?: boolean;
}
type SelectProps = Props & React.SelectHTMLAttributes<HTMLSelectElement>;

const Select = forwardRef<HTMLSelectElement, SelectProps>(({ hideDropdownArrow, className, ...props }, ref) => (
    <select ref={ref} className={classNames(styles.select, hideDropdownArrow && 'bg-none', className)} {...props} />
));

Select.displayName = 'Select';
export default Select;

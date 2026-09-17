import React from 'react';
import classNames from 'classnames';

const Label = ({
    className,
    isLight: _isLight,
    ...props
}: React.LabelHTMLAttributes<HTMLLabelElement> & { isLight?: boolean }) => (
    <label className={classNames('mb-1 block text-sm text-gray-200 sm:mb-2', className)} {...props} />
);

export default Label;

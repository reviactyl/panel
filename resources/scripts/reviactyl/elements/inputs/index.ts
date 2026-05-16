import Checkbox from '@/reviactyl/elements/inputs/Checkbox';
import InputField from '@/reviactyl/elements/inputs/InputField';

type InputComponent = {
    Text: typeof InputField;
    Checkbox: typeof Checkbox;
};

const Input: InputComponent = {
    Text: InputField,
    Checkbox: Checkbox,
};

export { Input };
export { default as styles } from './styles.module.css';

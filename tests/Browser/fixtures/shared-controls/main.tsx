import React from 'react';
import { createRoot } from 'react-dom/client';
import Select from '@/reviactyl/elements/Select';
import Input from '@/reviactyl/elements/Input';
import '@/assets/tailwind.css';

createRoot(document.getElementById('root')!).render(
    <form onSubmit={(event) => event.preventDefault()}>
        <label htmlFor='action'>Action</label>
        <Select id='action' name='action' defaultValue='command' required>
            <option value='command'>Send command</option>
            <option value='power'>Send power action</option>
        </Select>
        <label htmlFor='hidden'>Custom indicator</label>
        <Select id='hidden' hideDropdownArrow>
            <option>Hidden arrow</option>
        </Select>
        <Select aria-label='Disabled select' disabled>
            <option>Unavailable</option>
        </Select>
        <fieldset>
            <legend>Power action</legend>
            <label>
                <Input type='radio' name='power' value='start' defaultChecked required />
                Start
            </label>
            <label>
                <Input type='radio' name='power' value='stop' />
                Stop
            </label>
            <label>
                <Input type='radio' name='power' value='restart' disabled />
                Restart
            </label>
        </fieldset>
        <label>
            <Input type='checkbox' defaultChecked />
            Enabled
        </label>
        <label htmlFor='invalid'>Required value</label>
        <Input id='invalid' name='required' required $hasError aria-describedby='error' />
        <p id='error'>A value is required.</p>
        <button type='submit'>Save</button>
    </form>,
);

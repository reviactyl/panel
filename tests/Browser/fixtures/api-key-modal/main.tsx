import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import flashes from '@/state/flashes';
import CreateApiKeyForm from '@/components/dashboard/forms/CreateApiKeyForm';
import '@/assets/tailwind.css';

const store = createStore({ flashes });
const App = () => {
    const [created, setCreated] = useState(false);
    return (
        <StoreProvider store={store}>
            <CreateApiKeyForm onKeyCreated={() => setCreated(true)} />
            {created && <p>Key added to list</p>}
        </StoreProvider>
    );
};
createRoot(document.getElementById('root')!).render(<App />);

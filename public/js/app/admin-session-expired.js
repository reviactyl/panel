const registerSessionExpiryHandler = () => {
    window.Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status !== 419) {
                return;
            }

            preventDefault();
            window.location.reload();
        });
    });
};

if (window.Livewire) {
    registerSessionExpiryHandler();
} else {
    document.addEventListener('livewire:init', registerSessionExpiryHandler, { once: true });
}

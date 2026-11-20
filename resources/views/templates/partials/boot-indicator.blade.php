<div class="boot-indicator" role="progressbar" aria-busy="true" aria-label="Loading">
    <span></span>
</div>
<style>
    .boot-indicator {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        animation: boot-indicator-in 0.2s ease-out 0.3s forwards;
    }

    .boot-indicator span {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        border: 3px solid rgb(var(--color-primary) / 0.2);
        border-top-color: rgb(var(--color-primary));
        animation: boot-indicator-spin 0.8s linear infinite;
    }

    @keyframes boot-indicator-in {
        to {
            opacity: 1;
        }
    }

    @keyframes boot-indicator-spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .boot-indicator span {
            animation-duration: 2.4s;
        }
    }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('designify.errors.500.title') ?? '500 - Server Error' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        :root { --designify-error-color: {{ config('designify.errors.500.color') ?? '#ef4444' }}; }
        .accent-color { color: var(--designify-error-color); }
        .accent-bg { background-color: var(--designify-error-color); }
        .accent-ring { --tw-ring-color: var(--designify-error-color); }
    </style>
</head>
<body class="bg-[#0b0f17] text-white min-h-screen flex items-center justify-center p-6">
    <div class="max-w-2xl w-full text-center space-y-8">
        <img data-designify-error-image @if(config('designify.errors.500.image')) src="{{ config('designify.errors.500.image') }}" @endif alt="500" class="mx-auto max-h-64 object-contain mb-8 {{ config('designify.errors.500.image') ? '' : 'hidden' }}">
        <div data-designify-error-code class="text-9xl font-bold opacity-10 absolute inset-0 items-center justify-center pointer-events-none select-none {{ config('designify.errors.500.image') ? 'hidden' : 'flex' }}">500</div>

        <div class="relative z-10 space-y-4">
            <h1 data-designify-error-title class="text-4xl md:text-5xl font-bold tracking-tight">
                {{ config('designify.errors.500.title') ?? 'Internal Server Error' }}
            </h1>
            <p data-designify-error-message class="text-zinc-400 text-lg md:text-xl max-w-md mx-auto">
                {{ config('designify.errors.500.message') ?? 'We encountered an error while processing your request. Please try again later or contact support if the problem persists.' }}
            </p>
        </div>

        <div class="pt-4">
            <a data-designify-error-button href="/" class="inline-flex items-center px-8 py-3 rounded-xl font-semibold text-white accent-bg hover:opacity-90 transition-all focus:ring-4 accent-ring outline-none">
                {{ config('designify.errors.500.button') ?? 'Try Again' }}
            </a>
        </div>
    </div>
    @include('errors.designify-preview', ['status' => 500])
</body>
</html>

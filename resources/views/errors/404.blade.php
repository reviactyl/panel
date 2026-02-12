<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('designify.errors.404.title') ?? '404 - Page Not Found' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .accent-color { color: {{ config('designify.errors.404.color') ?? '#3b82f6' }}; }
        .accent-bg { background-color: {{ config('designify.errors.404.color') ?? '#3b82f6' }}; }
        .accent-ring { --tw-ring-color: {{ config('designify.errors.404.color') ?? '#3b82f6' }}; }
    </style>
</head>
<body class="bg-[#0b0f17] text-white min-h-screen flex items-center justify-center p-6">
    <div class="max-w-2xl w-full text-center space-y-8">
        @if(config('designify.errors.404.image'))
            <img src="{{ config('designify.errors.404.image') }}" alt="404" class="mx-auto max-h-64 object-contain mb-8">
        @else
            <div class="text-9xl font-bold opacity-10 absolute inset-0 flex items-center justify-center pointer-events-none select-none">404</div>
        @endif

        <div class="relative z-10 space-y-4">
            <h1 class="text-4xl md:text-5xl font-bold tracking-tight">
                {{ config('designify.errors.404.title') ?? 'Page Not Found' }}
            </h1>
            <p class="text-zinc-400 text-lg md:text-xl max-w-md mx-auto">
                {{ config('designify.errors.404.message') ?? 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.' }}
            </p>
        </div>

        <div class="pt-4">
            <a href="/" class="inline-flex items-center px-8 py-3 rounded-xl font-semibold text-white accent-bg hover:opacity-90 transition-all focus:ring-4 accent-ring outline-none">
                {{ config('designify.errors.404.button') ?? 'Back to Dashboard' }}
            </a>
        </div>
    </div>
</body>
</html>

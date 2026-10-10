@extends('templates/wrapper', [
    'css' => [
        'body' => 'bg-gray-950 min-h-screen flex flex-col items-center justify-center',
    ],
])

@section('container')
    <div class="w-full max-w-md px-4">
        <h1 class="text-4xl font-bold text-gray-100 text-center mb-6">
            API Docs
        </h1>

        <div class="bg-gray-900 border border-gray-800 shadow-lg rounded-ui py-8 px-8 w-full text-center">
            <div>
                <a
                    href="/docs/api/client"
                    class="inline-flex items-center text-gray-400 hover:text-reviactyl transition-colors
                    @if (is_null(Auth::user()) || config('app.disable_api_docs')) pointer-events-none opacity-50 @endif"
                    @if (is_null(Auth::user()) || config('app.disable_api_docs')) aria-disabled="true" tabindex="-1" @endif
                >
                    <x-tabler-user class="w-5 h-5 mr-1" />
                    <span>Client API</span>
                </a>
            </div>

            <div class="mt-2">
                <a
                    href="/docs/api/application"
                    class="inline-flex items-center text-gray-400 hover:text-reviactyl transition-colors
                    @if (is_null(Auth::user()) || config('app.disable_api_docs')) pointer-events-none opacity-50 @endif"
                    @if (is_null(Auth::user()) || config('app.disable_api_docs')) aria-disabled="true" tabindex="-1" @endif
                >
                    <x-tabler-user-key class="w-5 h-5 mr-1" />
                    <span>Application API</span>
                </a>
            </div>

            @if (is_null(Auth::user()))
                <div
                    class="p-4 mt-4 text-sm text-danger rounded-ui bg-danger/10 border border-danger/50"
                    role="alert"
                >
                    <span class="font-medium">Note:</span>
                    You need to be logged in to view the API docs.
                </div>
            @elseif (config('app.disable_api_docs'))
                <div
                    class="p-4 mt-4 text-sm text-danger rounded-ui bg-danger/10 border border-danger/50"
                    role="alert"
                >
                    <span class="font-medium">Note:</span>
                    API documentation is currently disabled.
                </div>
            @endif
        </div>

        <p class="text-center text-xs text-muted mt-4">
            <a
                href="https://reviactyl.app"
                target="_blank"
                rel="noopener noreferrer"
                class="hover:text-reviactyl transition-colors"
            >
                Reviactyl&trade; &copy; {{ date('Y') }}
            </a>
        </p>
    </div>
@endsection

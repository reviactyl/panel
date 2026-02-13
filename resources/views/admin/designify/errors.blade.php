@extends('layouts.designify', ['sideEditor' => true])

@section('title')
    Error Page Settings
@endsection

@section('content')
    <form id="designifyEditor" action="" method="POST" class="h-full flex flex-col">
        @csrf
        @method('PATCH')
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">Error Page Customization</h1>
            <p class="text-zinc-400 text-sm">Customize titles, messages, images, and colors for HTTP error pages.</p>
        </div>

        <div class="flex-1 space-y-12 pb-20">
            <!-- 403 Forbidden -->
            <div class="space-y-6">
                <div class="flex items-center space-x-3 border-b border-zinc-800 pb-3">
                    <span class="px-2 py-1 bg-amber-500/10 text-amber-500 rounded text-xs font-bold">403</span>
                    <h2 class="text-lg font-semibold text-white">Forbidden</h2>
                    <button type="button" onclick="changePreview('{{ route('admin.designify.errors.preview', 403) }}')" class="ml-auto text-xs font-medium text-zinc-500 hover:text-white transition-colors bg-zinc-800/50 px-2 py-1 rounded-md border border-zinc-700">Preview 403</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Title</label>
                        <input type="text" name="designify:errors:403:title" value="{{ old('designify:errors:403:title', config('designify.errors.403.title')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Forbidden">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Button Text</label>
                        <input type="text" name="designify:errors:403:button" value="{{ old('designify:errors:403:button', config('designify.errors.403.button')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Go to Dashboard">
                    </div>
                    <div class="col-span-full space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Message</label>
                        <textarea name="designify:errors:403:message" rows="3" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="You do not have permission to access this resource.">{{ old('designify:errors:403:message', config('designify.errors.403.message')) }}</textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Image URL</label>
                        <input type="text" name="designify:errors:403:image" value="{{ old('designify:errors:403:image', config('designify.errors.403.image')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="https://example.com/forbidden.svg">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Accent Color</label>
                        <input type="text" name="designify:errors:403:color" value="{{ old('designify:errors:403:color', config('designify.errors.403.color')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="#f59e0b">
                    </div>
                </div>
            </div>

            <!-- 404 Not Found -->
            <div class="space-y-6">
                <div class="flex items-center space-x-3 border-b border-zinc-800 pb-3">
                    <span class="px-2 py-1 bg-blue-500/10 text-blue-500 rounded text-xs font-bold">404</span>
                    <h2 class="text-lg font-semibold text-white">Not Found</h2>
                    <button type="button" onclick="changePreview('{{ route('admin.designify.errors.preview', 404) }}')" class="ml-auto text-xs font-medium text-zinc-500 hover:text-white transition-colors bg-zinc-800/50 px-2 py-1 rounded-md border border-zinc-700">Preview 404</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Title</label>
                        <input type="text" name="designify:errors:404:title" value="{{ old('designify:errors:404:title', config('designify.errors.404.title')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Page Not Found">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Button Text</label>
                        <input type="text" name="designify:errors:404:button" value="{{ old('designify:errors:404:button', config('designify.errors.404.button')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Go Home">
                    </div>
                    <div class="col-span-full space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Message</label>
                        <textarea name="designify:errors:404:message" rows="3" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="The page you are looking for does not exist.">{{ old('designify:errors:404:message', config('designify.errors.404.message')) }}</textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Image URL</label>
                        <input type="text" name="designify:errors:404:image" value="{{ old('designify:errors:404:image', config('designify.errors.404.image')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="https://example.com/notfound.svg">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Accent Color</label>
                        <input type="text" name="designify:errors:404:color" value="{{ old('designify:errors:404:color', config('designify.errors.404.color')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="#3b82f6">
                    </div>
                </div>
            </div>

            <!-- 500 Server Error -->
            <div class="space-y-6">
                <div class="flex items-center space-x-3 border-b border-zinc-800 pb-3">
                    <span class="px-2 py-1 bg-red-500/10 text-red-500 rounded text-xs font-bold">500</span>
                    <h2 class="text-lg font-semibold text-white">Server Error</h2>
                    <button type="button" onclick="changePreview('{{ route('admin.designify.errors.preview', 500) }}')" class="ml-auto text-xs font-medium text-zinc-500 hover:text-white transition-colors bg-zinc-800/50 px-2 py-1 rounded-md border border-zinc-700">Preview 500</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Title</label>
                        <input type="text" name="designify:errors:500:title" value="{{ old('designify:errors:500:title', config('designify.errors.500.title')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Internal Server Error">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Button Text</label>
                        <input type="text" name="designify:errors:500:button" value="{{ old('designify:errors:500:button', config('designify.errors.500.button')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Try Again">
                    </div>
                    <div class="col-span-full space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Message</label>
                        <textarea name="designify:errors:500:message" rows="3" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Something went wrong on our end. Please try again later.">{{ old('designify:errors:500:message', config('designify.errors.500.message')) }}</textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Image URL</label>
                        <input type="text" name="designify:errors:500:image" value="{{ old('designify:errors:500:image', config('designify.errors.500.image')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="https://example.com/server_error.svg">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-400">Accent Color</label>
                        <input type="text" name="designify:errors:500:color" value="{{ old('designify:errors:500:color', config('designify.errors.500.color')) }}" class="w-full px-4 py-2 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="#ef4444">
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        // Debounce function to limit preview requests
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Auto-refresh preview when inputs change
        const autoRefresh = debounce(() => {
            if (typeof refreshPreview === 'function') {
                refreshPreview();
            }
        }, 500);

        document.getElementById('designifyEditor').querySelectorAll('input, textarea').forEach(input => {
            input.addEventListener('input', autoRefresh);
        });
    </script>
@endsection

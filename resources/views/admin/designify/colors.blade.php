@extends('layouts.designify', ['sideEditor' => true])

@section('title')
    Color Settings
@endsection

@section('content')
    <form id="designifyEditor" action="" method="POST" class="h-full flex flex-col">
        @csrf
        @method('PATCH')
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">Color settings</h1>
            <p class="text-zinc-400 text-sm">Change the color scheme of Reviactyl Theme.</p>
        </div>
        <div class="flex-1 space-y-6 pb-[80px]">
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-zinc-200 mb-1">Basic Colors</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-300">Primary</label>
                        <div class="flex items-center space-x-2">
                            <input type="color"
                                class="h-10 w-16 rounded border border-zinc-600 bg-zinc-700 cursor-pointer"
                                name="designify:colorPrimary"
                                value="{{ old('designify:colorPrimary', config('designify.colorPrimary')) }}" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="designify:colorSuccess" class="block text-sm font-medium text-zinc-300">Success</label>
                        <div class="flex items-center space-x-2">
                            <input type="color"
                                class="h-10 w-16 rounded border border-zinc-600 bg-zinc-700 cursor-pointer"
                                name="designify:colorSuccess" id="designify:colorSuccess"
                                value="{{ old('designify:colorSuccess', config('designify.colorSuccess')) }}" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="designify:colorDanger" class="block text-sm font-medium text-zinc-300">Danger</label>
                        <div class="flex items-center space-x-2">
                            <input type="color"
                                class="h-10 w-16 rounded border border-zinc-600 bg-zinc-700 cursor-pointer"
                                name="designify:colorDanger" id="designify:colorDanger"
                                value="{{ old('designify:colorDanger', config('designify.colorDanger')) }}" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="designify:colorSecondary"
                            class="block text-sm font-medium text-zinc-300">Secondary</label>
                        <div class="flex items-center space-x-2">
                            <input type="color"
                                class="h-10 w-16 rounded border border-zinc-600 bg-zinc-700 cursor-pointer"
                                name="designify:colorSecondary" id="designify:colorSecondary"
                                value="{{ old('designify:colorSecondary', config('designify.colorSecondary')) }}" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="designify:colorDiscord"
                            class="block text-sm font-medium text-zinc-300">Discord</label>
                        <div class="flex items-center space-x-2">
                            <input type="color"
                                class="h-10 w-16 rounded border border-zinc-600 bg-zinc-700 cursor-pointer"
                                name="designify:colorDiscord" id="designify:colorDiscord"
                                value="{{ old('designify:colorDiscord', config('designify.colorDiscord') ?? '#5865F2') }}" />
                        </div>
                    </div>
                </div>
                <div class="border-t border-zinc-700"></div>
                <div>
                    <h3 class="text-lg font-bold text-zinc-200 mb-1">Default colors</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach ([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                            <div class="space-y-2">
                                <label for="designify:color{{ $shade }}"
                                    class="block text-sm font-medium text-zinc-300">Color {{ $shade }}</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color"
                                        class="h-10 w-16 rounded border border-zinc-600 bg-zinc-700 cursor-pointer"
                                        name="designify:color{{ $shade }}" id="designify:color{{ $shade }}"
                                        value="{{ old('designify:color' . $shade, config('designify.color'. $shade)) }}" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @foreach (range(1, 7) as $theme)
                    @php
                        $themeVar = 'theme' . $theme;
                    @endphp
                    <div class="border-t border-zinc-700"></div>
                    <div>
                        <h3 class="text-lg font-bold text-zinc-200 mb-1">Theme{{ $theme }} Settings</h3>
                        <div class="space-y-3 mb-3">
                            <label class="block text-sm font-medium text-zinc-300"
                                for="designify:theme{{ $theme }}:name">
                                Name
                            </label>
                            <input type="text" id="designify:theme{{ $theme }}:name"
                                name="designify:theme{{ $theme }}:name"
                                value="{{ old('designify:theme' . $theme . ':name', config('designify.theme' . $theme . '.name')) }}"
                                class="w-full px-4 py-3 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="Theme{{ $theme }} Display name" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-zinc-300"
                                    for="designify:theme{{ $theme }}:colorPrimary">
                                    Primary
                                </label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" id="designify:theme{{ $theme }}:colorPrimary"
                                        name="designify:theme{{ $theme }}:colorPrimary"
                                        value="{{ old('designify:theme' . $theme . ':colorPrimary', config('designify.theme' . $theme . '.colorPrimary')) }}"
                                        class="h-10 w-16 rounded border border-zinc-600 bg-zinc-700 cursor-pointer" />
                                </div>
                            </div>

                            @foreach ([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                                <div class="space-y-2">
                                    <label for="designify:theme{{ $theme }}:color{{ $shade }}"
                                        class="block text-sm font-medium text-zinc-300">
                                        Color {{ $shade }}
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <input type="color"
                                            class="h-10 w-16 rounded border border-zinc-600 bg-zinc-700 cursor-pointer"
                                            name="designify:theme{{ $theme }}:color{{ $shade }}"
                                            id="designify:theme{{ $theme }}:color{{ $shade }}"
                                            value="{{ old('designify:theme' . $theme . ':color' . $shade, config('designify.theme' . $theme . '.color' . $shade)) }}" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </form>
@endsection

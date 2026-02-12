@extends('layouts.designify', ['sideEditor' => true])

@section('title')
    General Settings
@endsection

@section('content')
    <form id="designifyEditor" action="" method="POST" class="h-full flex flex-col">
        @csrf
        @method('PATCH')
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">General settings</h1>
            <p class="text-zinc-400 text-sm">Change the general settings of Reviactyl Theme.</p>
        </div>
        <div class="flex-1 space-y-6">
            <div class="space-y-3">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300" for="designify:customCopyright">
                    Custom Copyright
                </label>
                <select name="designify:customCopyright" id="designify:customCopyright"
                    class="w-full px-4 py-3 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    <option value="true"
                        {{ old('designify:customCopyright', config('designify.customCopyright')) === true ? 'selected' : '' }}>
                        Enabled
                    </option>
                    <option value="false"
                        {{ old('designify:customCopyright', config('designify.customCopyright')) === false ? 'selected' : '' }}>
                        Disabled
                    </option>
                </select>
                <input type="text" id="designify:copyright" name="designify:copyright"
                    value="{{ old('designify:copyright', config('designify.copyright')) }}"
                    class="w-full px-4 py-3 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    placeholder="Powered by [Reviactyl](https://revix.cc)" />
            </div>
            <div class="space-y-3">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                    for="designify:isUnderMaintenance">
                    Maintenance
                </label>
                <select name="designify:isUnderMaintenance" id="designify:isUnderMaintenance"
                    class="w-full px-4 py-3 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    <option value="true"
                        {{ old('designify:isUnderMaintenance', config('designify.isUnderMaintenance')) === true ? 'selected' : '' }}>
                        Enabled
                    </option>
                    <option value="false"
                        {{ old('designify:isUnderMaintenance', config('designify.isUnderMaintenance')) === false ? 'selected' : '' }}>
                        Disabled
                    </option>
                </select>
                <input type="text" id="designify:maintenance" name="designify:maintenance"
                    value="{{ old('designify:maintenance', config('designify.maintenance')) }}"
                    class="w-full px-4 py-3 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    placeholder="Maintenance description." />
            </div>
            <div class="space-y-3">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                    for="designify:alwaysShowKillButton">
                    Always Show Kill Button
                </label>
                <select name="designify:alwaysShowKillButton" id="designify:alwaysShowKillButton"
                    class="w-full px-4 py-3 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    <option value="true"
                        {{ old('designify:alwaysShowKillButton', config('designify.alwaysShowKillButton')) === true ? 'selected' : '' }}>
                        Enabled
                    </option>
                    <option value="false"
                        {{ old('designify:alwaysShowKillButton', config('designify.alwaysShowKillButton')) === false ? 'selected' : '' }}>
                        Disabled
                    </option>
                </select>
                <p class="text-xs text-zinc-500">If enabled, the kill button will always be shown on the server control page, even if the server is offline.</p>
            </div>
            <div class="space-y-3">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                    for="designify:statusCardLink">
                    Status Card Link
                </label>
                <input type="text" id="designify:statusCardLink" name="designify:statusCardLink"
                    value="{{ old('designify:statusCardLink', config('designify.statusCardLink')) }}"
                    class="w-full px-4 py-3 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    placeholder="https://example.com/status" />
                <p class="text-xs text-zinc-500">Leave empty to remove the status card from user dashboard.</p>
            </div>
            <div class="space-y-3">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                    for="designify:supportCardLink">
                    Support Card Link
                </label>
                <input type="text" id="designify:supportCardLink" name="designify:supportCardLink"
                    value="{{ old('designify:supportCardLink', config('designify.supportCardLink')) }}"
                    class="w-full px-4 py-3 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    placeholder="https://example.com/support" />
                <p class="text-xs text-zinc-500">Leave empty to remove the support card from user dashboard.</p>
            </div>
            <div class="space-y-3 !mb-20">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                    for="designify:billingCardLink">
                    Billing Card Link
                </label>
                <input type="text" id="designify:billingCardLink" name="designify:billingCardLink"
                    value="{{ old('designify:billingCardLink', config('designify.billingCardLink')) }}"
                    class="w-full px-4 py-3 bg-zinc-800/50 border border-zinc-700 rounded-xl text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    placeholder="https://example.com/billing" />
                <p class="text-xs text-zinc-500">Leave empty to remove the billing card from user dashboard.</p>
            </div>
        </div>
    </form>
@endsection

<div class="relative space-y-6" wire:loading.class="pointer-events-none select-none" wire:target="saveDatabaseSettingsAndMigrate">
    <div class="flex justify-end">
        <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-300">
            <span>{{ trans('installer.language.label') }}</span>
            <select wire:model.live="locale" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs normal-case tracking-normal text-zinc-100">
                @foreach ($availableLanguages as $code => $name)
                    <option class="bg-zinc-800" value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div wire:loading.flex wire:target="saveDatabaseSettingsAndMigrate" class="absolute inset-0 z-50 hidden flex flex-col items-center justify-center">
        <div class="w-full max-w-md space-y-3 rounded-xl bg-zinc-800/20 p-5 backdrop-blur-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-red-200">{{ trans('installer.migration.title') }}</p>
            <p class="text-sm text-red-200">{{ trans('installer.migration.message') }}</p>
            <div class="h-2 overflow-hidden rounded-full bg-white/10">
                <div class="installer-progress-bar h-full w-1/2 rounded-full bg-blue-400"></div>
            </div>
        </div>
    </div>

    @if ($step != 0 && $step != 5)
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-300">
            @foreach ([1, 2, 3, 4] as $indicatorStep)
                <span class="rounded-full px-3 py-1 {{ $step === $indicatorStep ? match ($indicatorStep) {
                    1 => 'bg-sky-500/20 text-sky-200',
                    2 => 'bg-emerald-500/20 text-emerald-200',
                    3 => 'bg-amber-500/20 text-amber-200',
                    default => 'bg-fuchsia-500/20 text-fuchsia-200',
                } : 'bg-white/5' }}">
                    {{ trans('installer.progress.step', ['step' => $indicatorStep]) }}
                </span>
            @endforeach
        </div>
    @endif

    @if ($status !== '')
        <div class="rounded-2xl border border-blue-400/30 bg-blue-400/10 px-4 py-3 text-sm text-blue-100">
            {{ $status }}
        </div>
    @endif

    @if ($error !== '')
        <div class="rounded-2xl border border-red-400/30 bg-red-400/10 px-4 py-3 text-sm text-red-100">
            {{ $error }}
        </div>
    @endif

    @if ($step === 0)
        <section class="flex flex-col items-center justify-center space-y-4 text-center">
            <img src="https://cdn.reviactyl.app/@reviactyl/brand/fission_falcon_art.webp" class="mx-auto w-[200px] select-none pointer-events-none" alt="{{ trans('installer.brand.art_alt') }}">
            <h2 class="select-none text-2xl font-semibold text-white">{{ trans('installer.brand.title') }}</h2>
            <p class="select-none text-sm leading-7 text-zinc-300">{{ trans('installer.welcome.message') }}</p>
            <button type="button" wire:click="start" class="inline-flex select-none items-center justify-center gap-2 rounded-xl bg-blue-500 px-3 py-2 text-xs font-semibold text-white transition hover:brightness-110">
                {{ trans('installer.actions.continue') }} <x-tabler-arrow-narrow-right-dashed class="h-4 w-4" />
            </button>
        </section>
    @endif

    @if ($step === 1)
        <form wire:submit.prevent="saveAppSettings" class="space-y-4">
            <h2 class="select-none text-2xl font-semibold text-white">{{ trans('installer.sections.application') }}</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.service_author_email') }}</span>
                    <input type="email" wire:model.blur="app.author" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('app.author') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.application_url') }}</span>
                    <input type="url" wire:model.blur="app.url" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('app.url') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.logo_url') }}</span>
                    <input wire:model.blur="app.logo" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('app.logo') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.icon_url') }}</span>
                    <input wire:model.blur="app.icon" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('app.icon') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.timezone') }}</span>
                    <input type="text" wire:model.blur="app.timezone" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('app.timezone') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.cache_driver') }}</span>
                    <select wire:model.live="app.cache" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100">
                        @foreach (\App\Console\Commands\Environment\AppSettingsCommand::CACHE_DRIVERS as $key => $label)
                            <option class="bg-zinc-800" value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('app.cache') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.session_driver') }}</span>
                    <select wire:model.live="app.session" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100">
                        @foreach (\App\Console\Commands\Environment\AppSettingsCommand::SESSION_DRIVERS as $key => $label)
                            <option class="bg-zinc-800" value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('app.session') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.queue_driver') }}</span>
                    <select wire:model.live="app.queue" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100">
                        @foreach (\App\Console\Commands\Environment\AppSettingsCommand::QUEUE_DRIVERS as $key => $label)
                            <option class="bg-zinc-800" value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('app.queue') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.redis_host') }}</span>
                    <input type="text" wire:model.blur="app.redis_host" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('app.redis_host') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.redis_password') }}</span>
                    <input type="password" wire:model.blur="app.redis_pass" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('app.redis_pass') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.redis_port') }}</span>
                    <input type="number" wire:model.blur="app.redis_port" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('app.redis_port') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="grid gap-4 rounded-2xl border border-white/10 bg-white/5 p-4 sm:grid-cols-2">
                <label class="flex items-center justify-between text-sm text-zinc-300">
                    {{ trans('installer.fields.enable_settings_editor') }}
                    <input type="checkbox" wire:model.live="app.settings_ui" class="h-5 w-5 rounded" />
                </label>
                <label class="flex items-center justify-between text-sm text-zinc-300">
                    {{ trans('installer.fields.send_telemetry') }}
                    <input type="checkbox" wire:model.live="app.telemetry" class="h-5 w-5 rounded" />
                </label>
            </div>

            <button type="submit" class="inline-flex select-none items-center justify-center rounded-xl bg-sky-500 px-3 py-2 text-xs font-semibold text-white transition hover:brightness-110">
                {{ trans('installer.actions.proceed') }}
            </button>
        </form>
    @endif

    @if ($step === 2)
        <form
            wire:submit.prevent="saveDatabaseSettingsAndMigrate"
            class="space-y-5"
            x-data="{
                sqlitePath: @js(database_path('panel.sqlite')),
                driver: @entangle('database.driver').live,
                databaseValue: @entangle('database.database').live,
            }"
            x-init="
                if (driver === 'sqlite') {
                    databaseValue = sqlitePath;
                } else if (!databaseValue || databaseValue === sqlitePath) {
                    databaseValue = 'panel';
                }
            "
            x-effect="
                if (driver === 'sqlite') {
                    databaseValue = sqlitePath;
                } else if (!databaseValue || databaseValue === sqlitePath) {
                    databaseValue = 'panel';
                }
            "
        >
            <h2 class="text-2xl font-semibold text-white">{{ trans('installer.sections.database') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.driver') }}</span>
                    <select wire:model.live="database.driver" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100">
                        <option class="bg-zinc-800" value="sqlite">SQLite</option>
                        <option class="bg-zinc-800" value="mysql">MySQL</option>
                        <option class="bg-zinc-800" value="mariadb">MariaDB</option>
                        <option class="bg-zinc-800" value="pgsql">PostgreSQL</option>
                    </select>
                    @error('database.driver') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>

                @if ($database['driver'] === 'sqlite')
                    <label class="hidden space-y-2 sm:col-span-2">
                        <span class="text-sm text-zinc-300">{{ trans('installer.fields.sqlite_database_path') }}</span>
                        <input type="text" x-model="databaseValue" readonly class="w-full cursor-not-allowed rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100 opacity-80" />
                        @error('database.database') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                    </label>
                @else
                    <label class="space-y-2">
                        <span class="text-sm text-zinc-300">{{ trans('installer.fields.host') }}</span>
                        <input type="text" wire:model.blur="database.host" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                        @error('database.host') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm text-zinc-300">{{ trans('installer.fields.port') }}</span>
                        <input type="number" wire:model.blur="database.port" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                        @error('database.port') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                    </label>
                    <label class="space-y-2 sm:col-span-2">
                        <span class="text-sm text-zinc-300">{{ trans('installer.fields.database_name') }}</span>
                        <input type="text" x-model="databaseValue" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                        @error('database.database') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm text-zinc-300">{{ trans('installer.fields.username') }}</span>
                        <input type="text" wire:model.blur="database.username" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                        @error('database.username') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm text-zinc-300">{{ trans('installer.fields.password') }}</span>
                        <input type="password" wire:model.blur="database.password" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                        @error('database.password') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                    </label>
                @endif
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="saveDatabaseSettingsAndMigrate" class="inline-flex select-none items-center justify-center rounded-xl bg-emerald-500 px-3 py-2 text-xs font-semibold text-white transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60">
                {{ trans('installer.actions.proceed') }}
            </button>
        </form>
    @endif

    @if ($step === 3)
        <form wire:submit.prevent="createAdminUser" class="space-y-5">
            <h2 class="text-2xl font-semibold text-white">{{ trans('installer.sections.user') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.email') }}</span>
                    <input type="email" wire:model.blur="user.email" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('user.email') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.username') }}</span>
                    <input type="text" wire:model.blur="user.username" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('user.username') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.first_name') }}</span>
                    <input type="text" wire:model.blur="user.name_first" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('user.name_first') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.last_name') }}</span>
                    <input type="text" wire:model.blur="user.name_last" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('user.name_last') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2 sm:col-span-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.password') }}</span>
                    <input type="password" wire:model.blur="user.password" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('user.password') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
            </div>

            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-400">
                {{ trans('installer.actions.proceed') }}
            </button>
        </form>
    @endif

    @if ($step === 4)
        <form wire:submit.prevent="saveMailSettings" class="space-y-5">
            <h2 class="text-2xl font-semibold text-white">{{ trans('installer.sections.mail') }}</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.driver') }}</span>
                    <select wire:model.live="mail.driver" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100">
                        <option class="bg-zinc-800" value="smtp">SMTP</option>
                        <option class="bg-zinc-800" value="sendmail">sendmail</option>
                        <option class="bg-zinc-800" value="mailgun">Mailgun</option>
                        <option class="bg-zinc-800" value="mandrill">Mandrill</option>
                        <option class="bg-zinc-800" value="postmark">Postmark</option>
                    </select>
                    @error('mail.driver') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.from_email') }}</span>
                    <input type="email" wire:model.blur="mail.email" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('mail.email') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2 sm:col-span-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.from_name') }}</span>
                    <input type="text" wire:model.blur="mail.from" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('mail.from') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.host_domain') }}</span>
                    <input type="text" wire:model.blur="mail.host" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('mail.host') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.port') }}</span>
                    <input type="number" wire:model.blur="mail.port" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('mail.port') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.username') }}</span>
                    <input type="text" wire:model.blur="mail.username" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('mail.username') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.password_secret') }}</span>
                    <input type="password" wire:model.blur="mail.password" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('mail.password') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.encryption') }}</span>
                    <select wire:model.live="mail.encryption" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100">
                        <option class="bg-zinc-800" value="tls">TLS</option>
                        <option class="bg-zinc-800" value="ssl">SSL</option>
                        <option class="bg-zinc-800" value="">{{ trans('installer.options.none') }}</option>
                    </select>
                    @error('mail.encryption') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
                <label class="space-y-2">
                    <span class="text-sm text-zinc-300">{{ trans('installer.fields.endpoint') }}</span>
                    <input type="text" wire:model.blur="mail.endpoint" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-zinc-100" />
                    @error('mail.endpoint') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-fuchsia-500 px-3 py-2 text-sm font-semibold text-white hover:bg-fuchsia-400">
                    {{ trans('installer.actions.proceed') }}
                </button>
                <button type="button" wire:click="skipMailSettings" class="inline-flex items-center justify-center rounded-xl border border-white/20 px-3 py-2 text-sm font-semibold text-zinc-100 hover:bg-white/10">
                    {{ trans('installer.actions.skip') }}
                </button>
            </div>
        </form>
    @endif

    @if ($step === 5)
        <section class="flex flex-col items-center justify-center space-y-4 text-center">
            <img src="https://cdn.reviactyl.app/@reviactyl/brand/fission_falcon_art.webp" class="mx-auto w-[200px] select-none pointer-events-none" alt="{{ trans('installer.brand.art_alt') }}">
            <h2 class="select-none text-2xl font-semibold text-white">{{ trans('installer.brand.title') }}</h2>
            <p class="select-none text-sm leading-7 text-zinc-300">{{ trans('installer.finish.message') }}</p>
            <button type="button" wire:click="finish" class="inline-flex select-none items-center justify-center gap-2 rounded-xl bg-teal-500 px-3 py-2 text-xs font-semibold text-white transition hover:brightness-110">
                {{ trans('installer.actions.finish') }} <x-tabler-arrow-narrow-right-dashed class="h-4 w-4" />
            </button>
        </section>
    @endif

    <style>
        @keyframes installerProgressSlide {
            0% {
                transform: translateX(-120%);
            }

            100% {
                transform: translateX(260%);
            }
        }

        .installer-progress-bar {
            animation: installerProgressSlide 1.2s linear infinite;
        }
    </style>
</div>

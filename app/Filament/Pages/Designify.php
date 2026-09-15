<?php

namespace App\Filament\Pages;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Filament\Components\Alert;
use App\Filament\Components\ImageInput;
use App\Filament\Widgets\PreviewWidget;
use App\Traits\Helpers\AvailableLanguages;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithHeaderActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Designify extends Page implements HasSchemas
{
    use AvailableLanguages;
    use InteractsWithForms;
    use InteractsWithHeaderActions;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-palette';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'tabler-palette-filled';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.designify';

    public ?array $data = [];

    protected array $settingKeys = [
        'designify:customCopyright',
        'designify:copyright',
        'designify:isUnderMaintenance',
        'designify:maintenance',
        'designify:colorPrimary',
        'designify:colorSuccess',
        'designify:colorDanger',
        'designify:colorSecondary',
        'designify:color50',
        'designify:color100',
        'designify:color200',
        'designify:color300',
        'designify:color400',
        'designify:color500',
        'designify:color600',
        'designify:color700',
        'designify:color800',
        'designify:color900',
        'designify:color950',
        'designify:color50L',
        'designify:color100L',
        'designify:color200L',
        'designify:color300L',
        'designify:color400L',
        'designify:color500L',
        'designify:color600L',
        'designify:color700L',
        'designify:color800L',
        'designify:color900L',
        'designify:color950L',
        'designify:sidebarLogout',
        'designify:sidebarButtons',
        'designify:background',
        'designify:radius',
        'designify:allocationBlur',
        'designify:fontFamily',
        'designify:alertType',
        'designify:alertMessage',
        'designify:alerts',
        'designify:site_color',
        'designify:site_title',
        'designify:site_description',
        'designify:site_image',
        'designify:site_favicon',
        'designify:statusCardLink',
        'designify:supportCardLink',
        'designify:billingCardLink',
        'designify:alwaysShowKillButton',
        'designify:cardType',
        'designify:layoutType',
        'designify:avatarType',
        'designify:errors:403:title',
        'designify:errors:403:message',
        'designify:errors:403:button',
        'designify:errors:403:image',
        'designify:errors:403:color',
        'designify:errors:404:title',
        'designify:errors:404:message',
        'designify:errors:404:button',
        'designify:errors:404:image',
        'designify:errors:404:color',
        'designify:errors:500:title',
        'designify:errors:500:message',
        'designify:errors:500:button',
        'designify:errors:500:image',
        'designify:errors:500:color',
    ];

    public function getHeading(): string
    {
        return trans('admin/settings.designify.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/settings.designify.title');
    }

    public function getTitle(): string
    {
        return trans('admin/settings.designify.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.administration.title');
    }

    public function mount(): void
    {
        $settings = app(SettingsRepositoryInterface::class);
        $config = app(ConfigRepository::class);

        $formData = [];

        foreach ($this->settingKeys as $key) {

            $value = $settings->get('settings::'.$key);

            if ($value === null) {
                $value = $config->get(Str::replace(':', '.', $key));
            }

            if ($value === 'true') {
                $value = true;
            }
            if ($value === 'false') {
                $value = false;
            }

            // Decode JSON for fields that should be arrays
            if (in_array($key, ['designify:alerts', 'designify:sidebarButtons'])) {
                $value = is_string($value) ? json_decode($value, true) ?? [] : $value;
            }

            $formData[$key] = $value;
        }

        $form = $this->getForm('form');

        if ($form !== null) {
            $form->fill($formData);
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('settings-tabs')
                ->disabled(fn (): bool => config('panel.load_environment_only'))
                ->persistTabInQueryString()
                ->vertical()
                ->tabs([
                    Tab::make('general')
                        ->label(trans('admin/settings.designify.general'))
                        ->icon('tabler-settings-2')
                        ->schema($this->generalSettings()),

                    Tab::make('colors')
                        ->label(trans('admin/settings.designify.colors'))
                        ->icon('tabler-palette')
                        ->schema($this->colorSettings()),

                    Tab::make('looks')
                        ->label(trans('admin/settings.designify.look-and-feel'))
                        ->icon('tabler-layers-intersect')
                        ->schema($this->lookAndFeelSettings()),

                    Tab::make('layout')
                        ->label(trans('admin/settings.designify.layout'))
                        ->icon('tabler-layout-grid')
                        ->schema($this->layoutSettings()),

                    Tab::make('alerts')
                        ->label(trans('admin/settings.designify.alerts'))
                        ->icon('tabler-bell')
                        ->schema($this->alertSettings()),

                    Tab::make('meta')
                        ->label(trans('admin/settings.designify.meta-options'))
                        ->icon('tabler-link')
                        ->schema($this->siteSettings()),

                    Tab::make('errors')
                        ->label(trans('admin/settings.designify.error-pages'))
                        ->icon('tabler-device-imac-exclamation')
                        ->schema($this->errorSettings()),

                    Tab::make('sidebar')
                        ->label(trans('admin/settings.designify.sidebar'))
                        ->icon('tabler-layout-sidebar')
                        ->schema($this->sidebarSettings()),
                ]),
            ...$this->environmentNotice(),
        ];
    }

    private function environmentNotice(): array
    {
        return [
            Alert::make()
                ->title('App Environment Only Mode Enabled')
                ->description(new HtmlString('Your Panel is currently configured to read settings from the environment only. You will need to set <code>APP_ENVIRONMENT_ONLY=false</code> in your environment file in order to load settings dynamically.'))
                ->warning()
                ->columnSpanFull()
                ->visible(fn (): bool => config('panel.load_environment_only')),

        ];
    }

    private function generalSettings(): array
    {
        return [
            Group::make()
                ->columns(2)
                ->schema([
                    Toggle::make('designify:customCopyright')
                        ->label(trans('admin/settings.designify.custom-copyright'))
                        ->inline(false)
                        ->columnSpan(1),

                    Textarea::make('designify:copyright')
                        ->label(trans('admin/settings.designify.copyright-text'))
                        ->rows(3)
                        ->required()
                        ->columnSpan(1),
                ]),

            Group::make()
                ->columns(2)
                ->schema([
                    Toggle::make('designify:isUnderMaintenance')
                        ->label(trans('admin/settings.designify.maintenance-mode'))
                        ->inline(false)
                        ->columnSpan(1),

                    Textarea::make('designify:maintenance')
                        ->label(trans('admin/settings.designify.maintenance-message'))
                        ->rows(3)
                        ->required()
                        ->columnSpan(1),
                ]),

            Group::make()
                ->columns(2)
                ->schema([
                    Toggle::make('designify:alwaysShowKillButton')
                        ->label(trans('admin/settings.designify.always-show-kill-button'))
                        ->inline(false)
                        ->columnSpan(1),

                    Select::make('designify:avatarType')
                        ->label(trans('admin/settings.designify.avatar-type'))
                        ->helperText(trans('admin/settings.designify.avatar-type-description'))
                        ->options([
                            'gravatar' => 'Gravatar',
                            'initials' => 'Initials',
                            'identicon' => 'Identicon',
                            'loops' => 'Loops',
                            'waves' => 'Waves',
                            'critters' => 'Critters',
                            'pixelbot' => 'Pixelbot',
                            'thumbs' => 'Thumbs',
                        ])
                        ->required()
                        ->columnSpan(1),
                ]),

            Group::make()
                ->columns(3)
                ->schema([
                    TextInput::make('designify:statusCardLink')
                        ->label(trans('admin/settings.designify.status-card-link'))
                        ->placeholder('https://status.reviactyl.app')
                        ->columnSpan(1),

                    TextInput::make('designify:supportCardLink')
                        ->label(trans('admin/settings.designify.support-card-link'))
                        ->placeholder('https://support.reviactyl.app')
                        ->columnSpan(1),

                    TextInput::make('designify:billingCardLink')
                        ->label(trans('admin/settings.designify.billing-card-link'))
                        ->placeholder('https://billing.reviactyl.app')
                        ->columnSpan(1),
                ]),
        ];
    }

    private function colorSettings(): array
    {
        return [
            Section::make(trans('admin/settings.designify.basic-colors'))
                ->columns(4)
                ->icon('tabler-palette')
                ->schema([
                    ColorPicker::make('designify:colorPrimary')->label(trans('admin/settings.designify.primary')),
                    ColorPicker::make('designify:colorSuccess')->label(trans('admin/settings.designify.success')),
                    ColorPicker::make('designify:colorDanger')->label(trans('admin/settings.designify.danger')),
                    ColorPicker::make('designify:colorSecondary')->label(trans('admin/settings.designify.secondary')),
                ]),

            Section::make(trans('admin/settings.designify.dark-colors'))
                ->columns(5)
                ->icon('tabler-color-swatch')
                ->schema([
                    ColorPicker::make('designify:color50')->label('50'),
                    ColorPicker::make('designify:color100')->label('100'),
                    ColorPicker::make('designify:color200')->label('200'),
                    ColorPicker::make('designify:color300')->label('300'),
                    ColorPicker::make('designify:color400')->label('400'),
                    ColorPicker::make('designify:color500')->label('500'),
                    ColorPicker::make('designify:color600')->label('600'),
                    ColorPicker::make('designify:color700')->label('700'),
                    ColorPicker::make('designify:color800')->label('800'),
                    ColorPicker::make('designify:color900')->label('900'),
                    ColorPicker::make('designify:color950')->label('950'),
                ]),

            Section::make(trans('admin/settings.designify.light-colors'))
                ->columns(5)
                ->icon('tabler-color-swatch')
                ->schema([
                    ColorPicker::make('designify:color50L')->label('50'),
                    ColorPicker::make('designify:color100L')->label('100'),
                    ColorPicker::make('designify:color200L')->label('200'),
                    ColorPicker::make('designify:color300L')->label('300'),
                    ColorPicker::make('designify:color400L')->label('400'),
                    ColorPicker::make('designify:color500L')->label('500'),
                    ColorPicker::make('designify:color600L')->label('600'),
                    ColorPicker::make('designify:color700L')->label('700'),
                    ColorPicker::make('designify:color800L')->label('800'),
                    ColorPicker::make('designify:color900L')->label('900'),
                    ColorPicker::make('designify:color950L')->label('950'),
                ]),
        ];
    }

    private function lookAndFeelSettings(): array
    {
        return [
            Group::make()
                ->columns(4)
                ->schema([

                    Toggle::make('designify:sidebarLogout')
                        ->label(trans('admin/settings.designify.sidebar-logout-button'))
                        ->inline(false)
                        ->columnSpan(1),

                    TextInput::make('designify:background')
                        ->label(trans('admin/settings.designify.panel-background'))
                        ->helperText(trans('admin/settings.designify.panel-background-helper'))
                        ->maxLength(255)
                        ->columnSpan(2),
                ]),
            Group::make()
                ->columns(4)
                ->schema([
                    Toggle::make('designify:allocationBlur')
                        ->label(trans('admin/settings.designify.allocation-blur'))
                        ->inline(false)
                        ->columnSpan(1),

                    TextInput::make('designify:radius')
                        ->label(trans('admin/settings.designify.border-radius'))
                        ->helperText(trans('admin/settings.designify.border-radius-helper'))
                        ->maxLength(32)
                        ->columnSpan(1),

                    Select::make('designify:fontFamily')
                        ->label(trans('admin/settings.designify.font-family'))
                        ->options([
                            'Poppins' => 'Poppins',
                            'Inter' => 'Inter',
                            'Roboto' => 'Roboto',
                            'Cairo' => 'Cairo',
                            'Google+Sans' => 'Google Sans',
                            'Playpen+Sans+Arabic' => 'Playpen Sans Arabic',
                            'Noto+Sans' => 'Noto Sans',
                        ])
                        ->searchable()
                        ->native(false)
                        ->columnSpan(2),
                ]),
        ];
    }

    private function layoutSettings(): array
    {
        return [
            Group::make()
                ->columns(1)
                ->schema([
                    ToggleButtons::make('designify:cardType')
                        ->label(trans('admin/settings.designify.server-card-layout'))
                        ->inline()
                        ->icons([
                            'grid' => 'tabler-layout-grid',
                            'row' => 'tabler-layout-row',
                        ])
                        ->options([
                            'grid' => trans('admin/settings.designify.grid'),
                            'row' => trans('admin/settings.designify.row'),
                        ])
                        ->required()
                        ->columnSpan(2),
                    ToggleButtons::make('designify:layoutType')
                        ->label(trans('admin/settings.designify.navbar-sidebar-layout'))
                        ->inline()
                        ->icons([
                            'modern' => 'tabler-layout-dashboard',
                            'classic' => 'tabler-layout-kanban',
                            'compact' => 'tabler-layout-list',
                            'accent' => 'tabler-layout-bottombar',
                        ])
                        ->options([
                            'modern' => trans('admin/settings.designify.modern'),
                            'classic' => trans('admin/settings.designify.classic'),
                            'compact' => trans('admin/settings.designify.compact'),
                            'accent' => trans('admin/settings.designify.accent'),
                        ])
                        ->required()
                        ->columnSpan(2),
                ]),
        ];
    }

    private function alertSettings(): array
    {
        return [
            Group::make()
                ->schema([
                    Repeater::make('designify:alerts')
                        ->label(trans('admin/settings.designify.alert-messages'))
                        ->defaultItems(1)
                        ->minItems(1)
                        ->schema([
                            Select::make('type')
                                ->label(trans('admin/settings.designify.type'))
                                ->options([
                                    'info' => trans('admin/settings.designify.info'),
                                    'announcement' => trans('admin/settings.designify.announcement'),
                                    'success' => trans('admin/settings.designify.success'),
                                    'warning' => trans('admin/settings.designify.warning'),
                                    'danger' => trans('admin/settings.designify.danger'),
                                    'disabled' => trans('admin/settings.designify.disabled'),
                                ])
                                ->required()
                                ->native(false),

                            Textarea::make('message')
                                ->label(trans('admin/settings.designify.message'))
                                ->rows(4)
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel(trans('admin/settings.designify.add-alert'))
                        ->columnSpanFull(),
                ]),
        ];
    }

    private function siteSettings(): array
    {
        return [
            Group::make()
                ->columns(2)
                ->schema([
                    TextInput::make('designify:site_title')
                        ->label(trans('admin/settings.designify.site-title'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Textarea::make('designify:site_description')
                        ->label(trans('admin/settings.designify.site-description'))
                        ->rows(3)
                        ->required()
                        ->columnSpan(1),

                    ImageInput::make('designify:site_image')
                        ->label(trans('admin/settings.designify.site-image'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    ImageInput::make('designify:site_favicon')
                        ->label(trans('admin/settings.designify.site-favicon'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    ColorPicker::make('designify:site_color')
                        ->label(trans('admin/settings.designify.site-color'))
                        ->required()
                        ->columnSpan(1),
                ]),
        ];
    }

    private function errorSettings(): array
    {
        return [
            Section::make(trans('admin/settings.designify.error-404'))
                ->columns(2)
                ->iconColor('warning')
                ->icon('tabler-device-imac-exclamation')
                ->schema([
                    TextInput::make('designify:errors:404:title')
                        ->label(trans('admin/settings.designify.title'))
                        ->required()
                        ->columnSpan(1),
                    TextInput::make('designify:errors:404:button')
                        ->label(trans('admin/settings.designify.button-text'))
                        ->required()
                        ->columnSpan(1),
                    Textarea::make('designify:errors:404:message')
                        ->label(trans('admin/settings.designify.message'))
                        ->rows(3)
                        ->required()
                        ->columnSpan(2),
                ]),

            Section::make(trans('admin/settings.designify.error-403'))
                ->columns(2)
                ->icon('tabler-device-imac-exclamation')
                ->iconColor('primary')
                ->schema([
                    TextInput::make('designify:errors:403:title')
                        ->label(trans('admin/settings.designify.title'))
                        ->required()
                        ->columnSpan(1),
                    TextInput::make('designify:errors:403:button')
                        ->label(trans('admin/settings.designify.button-text'))
                        ->required()
                        ->columnSpan(1),
                    Textarea::make('designify:errors:403:message')
                        ->label(trans('admin/settings.designify.message'))
                        ->rows(3)
                        ->required()
                        ->columnSpan(2),
                ]),

            Section::make(trans('admin/settings.designify.error-500'))
                ->iconColor('danger')
                ->icon('tabler-device-imac-exclamation')
                ->columns(2)
                ->schema([
                    TextInput::make('designify:errors:500:title')
                        ->label(trans('admin/settings.designify.title'))
                        ->required()
                        ->columnSpan(1),
                    TextInput::make('designify:errors:500:button')
                        ->label(trans('admin/settings.designify.button-text'))
                        ->required()
                        ->columnSpan(1),
                    Textarea::make('designify:errors:500:message')
                        ->label(trans('admin/settings.designify.message'))
                        ->rows(3)
                        ->required()
                        ->columnSpan(2),
                ]),
        ];
    }

    private function sidebarSettings(): array
    {
        return [
            Group::make()
                ->schema([
                    Repeater::make('designify:sidebarButtons')
                        ->label(trans('admin/settings.designify.sidebar-links'))
                        ->defaultItems(1)
                        ->schema([
                            TextInput::make('label')
                                ->label(trans('admin/settings.designify.label'))
                                ->placeholder('phpMyAdmin')
                                ->maxLength(60),

                            TextInput::make('url')
                                ->label(trans('admin/settings.designify.url'))
                                ->placeholder('https://pma.reviactyl.app')
                                ->maxLength(255),

                            Toggle::make('newTab')
                                ->label(trans('admin/settings.designify.open-in-new-tab'))
                                ->inline(false),
                        ])
                        ->columns(3)
                        ->addActionLabel(trans('admin/settings.designify.add-link'))
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function save(): void
    {
        if (config('panel.load_environment_only')) {
            return;
        }

        $settings = app(SettingsRepositoryInterface::class);
        $kernel = app(Kernel::class);
        $form = $this->getForm('form');
        $data = $form?->getState() ?? [];

        foreach ($data as $key => $value) {
            // JSON encode arrays for storage
            if (in_array($key, ['designify:alerts', 'designify:sidebarButtons'])) {
                $value = is_array($value) ? json_encode($value) : $value;
            }

            $settings->set(
                'settings::'.$key,
                is_bool($value) ? ($value ? 'true' : 'false') : $value
            );
        }

        try {
            $kernel->call('queue:restart');
        } catch (\Throwable) {
        }

        Notification::make()
            ->title(trans('admin/settings.overview.saved'))
            ->success()
            ->send();

        $this->dispatch('$refresh');
        $this->js("window.dispatchEvent(new CustomEvent('reload-iframe'))");
    }

    public function resetToDefaults(): void
    {
        if (config('panel.load_environment_only')) {
            return;
        }

        DB::table('settings')
            ->where('key', 'like', 'settings::designify:%')
            ->delete();

        try {
            app(Kernel::class)->call('queue:restart');
        } catch (\Throwable) {
        }

        Notification::make()
            ->title(trans('admin/settings.designify.reset-to-defaults'))
            ->success()
            ->send();

        $this->dispatch('$refresh');

        // We have to reload the window after resetting designify settings.
        // Please open PR if there's another way without running js code.
        $this->js('window.location.reload()');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('admin/settings.overview.save-btn'))
                ->icon('tabler-device-floppy')
                ->action('save')
                ->disabled(fn (): bool => config('panel.load_environment_only'))
                ->keyBindings(['mod+s']),

            Action::make('reset')
                ->label(trans('admin/settings.designify.reset-to-defaults'))
                ->icon('tabler-restore')
                ->color('danger')
                ->requiresConfirmation()
                ->disabled(fn (): bool => config('panel.load_environment_only'))
                ->modalHeading(trans('admin/settings.designify.reset-designify'))
                ->modalDescription(trans('admin/settings.designify.reset-description'))
                ->action(fn () => $this->resetToDefaults()),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            PreviewWidget::class,
        ];
    }
}

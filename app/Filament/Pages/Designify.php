<?php

namespace App\Filament\Pages;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Traits\Helpers\AvailableLanguages;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
        'designify:themeSelector',
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
        'designify:theme1:name',
        'designify:theme1:colorPrimary',
        'designify:theme1:color50',
        'designify:theme1:color100',
        'designify:theme1:color200',
        'designify:theme1:color300',
        'designify:theme1:color400',
        'designify:theme1:color500',
        'designify:theme1:color600',
        'designify:theme1:color700',
        'designify:theme1:color800',
        'designify:theme1:color900',
        'designify:theme2:name',
        'designify:theme2:colorPrimary',
        'designify:theme2:color50',
        'designify:theme2:color100',
        'designify:theme2:color200',
        'designify:theme2:color300',
        'designify:theme2:color400',
        'designify:theme2:color500',
        'designify:theme2:color600',
        'designify:theme2:color700',
        'designify:theme2:color800',
        'designify:theme2:color900',
        'designify:theme3:name',
        'designify:theme3:colorPrimary',
        'designify:theme3:color50',
        'designify:theme3:color100',
        'designify:theme3:color200',
        'designify:theme3:color300',
        'designify:theme3:color400',
        'designify:theme3:color500',
        'designify:theme3:color600',
        'designify:theme3:color700',
        'designify:theme3:color800',
        'designify:theme3:color900',
        'designify:theme4:name',
        'designify:theme4:colorPrimary',
        'designify:theme4:color50',
        'designify:theme4:color100',
        'designify:theme4:color200',
        'designify:theme4:color300',
        'designify:theme4:color400',
        'designify:theme4:color500',
        'designify:theme4:color600',
        'designify:theme4:color700',
        'designify:theme4:color800',
        'designify:theme4:color900',
        'designify:theme5:name',
        'designify:theme5:colorPrimary',
        'designify:theme5:color50',
        'designify:theme5:color100',
        'designify:theme5:color200',
        'designify:theme5:color300',
        'designify:theme5:color400',
        'designify:theme5:color500',
        'designify:theme5:color600',
        'designify:theme5:color700',
        'designify:theme5:color800',
        'designify:theme5:color900',
        'designify:theme6:name',
        'designify:theme6:colorPrimary',
        'designify:theme6:color50',
        'designify:theme6:color100',
        'designify:theme6:color200',
        'designify:theme6:color300',
        'designify:theme6:color400',
        'designify:theme6:color500',
        'designify:theme6:color600',
        'designify:theme6:color700',
        'designify:theme6:color800',
        'designify:theme6:color900',
        'designify:theme7:name',
        'designify:theme7:colorPrimary',
        'designify:theme7:color50',
        'designify:theme7:color100',
        'designify:theme7:color200',
        'designify:theme7:color300',
        'designify:theme7:color400',
        'designify:theme7:color500',
        'designify:theme7:color600',
        'designify:theme7:color700',
        'designify:theme7:color800',
        'designify:theme7:color900',
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
        return 'Designify';
    }

    public static function getNavigationLabel(): string
    {
        return 'Designify';
    }

    public function getTitle(): string
    {
        return 'Designify';
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
                ->persistTabInQueryString()
                ->vertical()
                ->tabs([
                    Tab::make('general')
                        ->label('General')
                        ->icon('tabler-settings-2')
                        ->schema($this->generalSettings()),

                    Tab::make('colors')
                        ->label('Colors')
                        ->icon('tabler-palette')
                        ->schema($this->colorSettings()),

                    Tab::make('looks')
                        ->label('Look and Feel')
                        ->icon('tabler-layers-intersect')
                        ->schema($this->lookAndFeelSettings()),

                    Tab::make('alerts')
                        ->label('Alerts')
                        ->icon('tabler-bell')
                        ->schema($this->alertSettings()),

                    Tab::make('meta')
                        ->label('Meta Options')
                        ->icon('tabler-link')
                        ->schema($this->siteSettings()),

                    Tab::make('errors')
                        ->label('Error Pages')
                        ->icon('tabler-device-imac-exclamation')
                        ->schema($this->errorSettings()),

                    Tab::make('sidebar')
                        ->label('Sidebar')
                        ->icon('tabler-layout-sidebar')
                        ->schema($this->sidebarSettings()),
                ]),
        ];
    }

    private function generalSettings(): array
    {
        return [
            Group::make()
                ->columns(2)
                ->schema([
                    Toggle::make('designify:customCopyright')
                        ->label('Custom Copyright')
                        ->inline(false)
                        ->columnSpan(1),

                    Textarea::make('designify:copyright')
                        ->label('Copyright Text')
                        ->rows(3)
                        ->required()
                        ->columnSpan(1),
                ]),

            Group::make()
                ->columns(2)
                ->schema([
                    Toggle::make('designify:isUnderMaintenance')
                        ->label('Maintenance Mode')
                        ->inline(false)
                        ->columnSpan(1),

                    Textarea::make('designify:maintenance')
                        ->label('Maintenance Message')
                        ->rows(3)
                        ->required()
                        ->columnSpan(1),
                ]),

            Group::make()
                ->columns(2)
                ->schema([
                    Toggle::make('designify:alwaysShowKillButton')
                        ->label('Always Show Kill Button')
                        ->inline(false)
                        ->columnSpan(1),
                ]),

            Group::make()
                ->columns(3)
                ->schema([
                    TextInput::make('designify:statusCardLink')
                        ->label('Status Card Link')
                        ->placeholder('https://status.reviactyl.app')
                        ->columnSpan(1),

                    TextInput::make('designify:supportCardLink')
                        ->label('Support Card Link')
                        ->placeholder('https://support.reviactyl.app')
                        ->columnSpan(1),

                    TextInput::make('designify:billingCardLink')
                        ->label('Billing Card Link')
                        ->placeholder('https://billing.reviactyl.app')
                        ->columnSpan(1),
                ]),
        ];
    }

    private function colorSettings(): array
    {
        return [
            Section::make('Basic Colors')
                ->columns(4)
                ->icon('tabler-palette')
                ->schema([
                    ColorPicker::make('designify:colorPrimary')->label('Primary'),
                    ColorPicker::make('designify:colorSuccess')->label('Success'),
                    ColorPicker::make('designify:colorDanger')->label('Danger'),
                    ColorPicker::make('designify:colorSecondary')->label('Secondary'),
                ]),

            Section::make('System Colors')
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
                ]),

            $this->themeSection(1),
            $this->themeSection(2),
            $this->themeSection(3),
            $this->themeSection(4),
            $this->themeSection(5),
            $this->themeSection(6),
            $this->themeSection(7),
        ];
    }

    private function themeSection(int $index): Section
    {
        return Section::make("Theme {$index}")
            ->columns(5)
            ->schema([
                TextInput::make("designify:theme{$index}:name")
                    ->label('Theme Name')
                    ->columnSpanFull(),

                ColorPicker::make("designify:theme{$index}:colorPrimary")->label('Primary'),

                ColorPicker::make("designify:theme{$index}:color50")->label('50'),
                ColorPicker::make("designify:theme{$index}:color100")->label('100'),
                ColorPicker::make("designify:theme{$index}:color200")->label('200'),
                ColorPicker::make("designify:theme{$index}:color300")->label('300'),
                ColorPicker::make("designify:theme{$index}:color400")->label('400'),
                ColorPicker::make("designify:theme{$index}:color500")->label('500'),
                ColorPicker::make("designify:theme{$index}:color600")->label('600'),
                ColorPicker::make("designify:theme{$index}:color700")->label('700'),
                ColorPicker::make("designify:theme{$index}:color800")->label('800'),
                ColorPicker::make("designify:theme{$index}:color900")->label('900'),
            ])
            ->collapsed()
            ->icon('tabler-color-swatch')
            ->collapsible();
    }

    private function lookAndFeelSettings(): array
    {
        return [
            Group::make()
                ->columns(4)
                ->schema([
                    Toggle::make('designify:themeSelector')
                        ->label('Theme Selector')
                        ->inline(false)
                        ->columnSpan(1),

                    Toggle::make('designify:sidebarLogout')
                        ->label('Sidebar Logout Button')
                        ->inline(false)
                        ->columnSpan(1),

                    TextInput::make('designify:background')
                        ->label('Panel Background')
                        ->helperText('Use a URL, path, or "none" to disable the background.')
                        ->maxLength(255)
                        ->columnSpan(2),
                ]),
            Group::make()
                ->columns(4)
                ->schema([
                    Toggle::make('designify:allocationBlur')
                        ->label('Allocation Blur')
                        ->inline(false)
                        ->columnSpan(1),

                    TextInput::make('designify:radius')
                        ->label('Border Radius')
                        ->helperText('Example: 15px or 1rem.')
                        ->maxLength(32)
                        ->columnSpan(1),

                    Select::make('designify:fontFamily')
                        ->label('Font Family')
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

    private function alertSettings(): array
    {
        return [
            Group::make()
                ->schema([
                    Repeater::make('designify:alerts')
                        ->label('Alert Messages')
                        ->defaultItems(1)
                        ->minItems(1)
                        ->schema([
                            Select::make('type')
                                ->label('Type')
                                ->options([
                                    'info' => 'Info',
                                    'announcement' => 'Announcement',
                                    'success' => 'Success',
                                    'warning' => 'Warning',
                                    'danger' => 'Danger',
                                    'disabled' => 'Disabled',
                                ])
                                ->required()
                                ->native(false),

                            Textarea::make('message')
                                ->label('Message')
                                ->rows(4)
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('Add Alert')
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
                        ->label('Site Title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Textarea::make('designify:site_description')
                        ->label('Site Description')
                        ->rows(3)
                        ->required()
                        ->columnSpan(1),

                    TextInput::make('designify:site_image')
                        ->label('Site Image')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    TextInput::make('designify:site_favicon')
                        ->label('Site Favicon')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    ColorPicker::make('designify:site_color')
                        ->label('Site Color')
                        ->required()
                        ->columnSpan(1),
                ]),
        ];
    }

    private function errorSettings(): array
    {
        return [
            Section::make('404')
                ->columns(2)
                ->iconColor('warning')
                ->icon('tabler-device-imac-exclamation')
                ->schema([
                    TextInput::make('designify:errors:404:title')
                        ->label('Title')
                        ->required()
                        ->columnSpan(1),
                    TextInput::make('designify:errors:404:button')
                        ->label('Button Text')
                        ->required()
                        ->columnSpan(1),
                    Textarea::make('designify:errors:404:message')
                        ->label('Message')
                        ->rows(3)
                        ->required()
                        ->columnSpan(2),
                ]),

            Section::make('403')
                ->columns(2)
                ->icon('tabler-device-imac-exclamation')
                ->iconColor('primary')
                ->schema([
                    TextInput::make('designify:errors:403:title')
                        ->label('Title')
                        ->required()
                        ->columnSpan(1),
                    TextInput::make('designify:errors:403:button')
                        ->label('Button Text')
                        ->required()
                        ->columnSpan(1),
                    Textarea::make('designify:errors:403:message')
                        ->label('Message')
                        ->rows(3)
                        ->required()
                        ->columnSpan(2),
                ]),

            Section::make('500')
                ->iconColor('danger')
                ->icon('tabler-device-imac-exclamation')
                ->columns(2)
                ->schema([
                    TextInput::make('designify:errors:500:title')
                        ->label('Title')
                        ->required()
                        ->columnSpan(1),
                    TextInput::make('designify:errors:500:button')
                        ->label('Button Text')
                        ->required()
                        ->columnSpan(1),
                    Textarea::make('designify:errors:500:message')
                        ->label('Message')
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
                        ->label('Sidebar Links')
                        ->defaultItems(1)
                        ->schema([
                            TextInput::make('label')
                                ->label('Label')
                                ->placeholder('phpMyAdmin')
                                ->maxLength(60),

                            TextInput::make('url')
                                ->label('URL')
                                ->placeholder('https://pma.reviactyl.app')
                                ->maxLength(255),

                            Toggle::make('newTab')
                                ->label('Open in New Tab')
                                ->inline(false),
                        ])
                        ->columns(3)
                        ->addActionLabel('Add Link')
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
    }

    public function resetToDefaults(): void
    {
        DB::table('settings')
            ->where('key', 'like', 'settings::designify:%')
            ->delete();

        try {
            app(Kernel::class)->call('queue:restart');
        } catch (\Throwable) {
        }

        Notification::make()
            ->title('Designify settings reset to defaults.')
            ->success()
            ->send();

        $this->dispatch('$refresh');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('admin/settings.overview.save-btn'))
                ->icon('tabler-device-floppy')
                ->action('save')
                ->keyBindings(['mod+s']),

            Action::make('reset')
                ->label('Reset to Defaults')
                ->icon('tabler-restore')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset Designify?')
                ->modalDescription('This will remove all saved Designify overrides and restore the default configuration.')
                ->action(fn () => $this->resetToDefaults()),
        ];
    }
}

<?php

namespace App\Filament\Resources\Alerts\Schemas;

use App\Models\Alert;
use App\Models\Egg;
use App\Models\Location;
use App\Models\Nest;
use App\Models\Node;
use App\Models\User;
use App\Traits\Helpers\AvailableLanguages;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class AlertForm
{
    use AvailableLanguages;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(trans('admin/alerts.sections.content'))
                ->description(trans('admin/alerts.sections.content_description'))
                ->icon('tabler-message-2')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(trans('admin/alerts.fields.name'))
                        ->helperText(trans('admin/alerts.helpers.name'))
                        ->required()
                        ->maxLength(191),

                    Select::make('type')
                        ->label(trans('admin/alerts.fields.type'))
                        ->options(self::translatedOptions('types', Alert::TYPES))
                        ->default('info')
                        ->required()
                        ->selectablePlaceholder(false)
                        ->native(false),

                    TextInput::make('title')
                        ->label(trans('admin/alerts.fields.title'))
                        ->helperText(trans('admin/alerts.helpers.title'))
                        ->maxLength(191),

                    ColorPicker::make('color')
                        ->label(trans('admin/alerts.fields.color'))
                        ->helperText(trans('admin/alerts.helpers.color'))
                        ->regex('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i'),

                    Textarea::make('message')
                        ->label(trans('admin/alerts.fields.message'))
                        ->helperText(trans('admin/alerts.helpers.message'))
                        ->rows(4)
                        ->required()
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ]),

            Section::make(trans('admin/alerts.sections.buttons'))
                ->description(trans('admin/alerts.sections.buttons_description'))
                ->icon('tabler-click')
                ->columnSpanFull()
                ->schema([
                    Repeater::make('buttons')
                        ->label(trans('admin/alerts.fields.buttons'))
                        ->hiddenLabel()
                        ->defaultItems(0)
                        ->maxItems(Alert::MAX_BUTTONS)
                        ->columns(2)
                        ->addActionLabel(trans('admin/alerts.actions.add_button'))
                        ->schema([
                            TextInput::make('label')
                                ->label(trans('admin/alerts.fields.button_label'))
                                ->required()
                                ->maxLength(48),

                            TextInput::make('url')
                                ->label(trans('admin/alerts.fields.button_url'))
                                ->helperText(trans('admin/alerts.helpers.button_url'))
                                ->required()
                                ->maxLength(2048)
                                ->regex(Alert::BUTTON_URL_PATTERN)
                                ->validationMessages(['regex' => trans('admin/alerts.validation.button_url')]),

                            Select::make('style')
                                ->label(trans('admin/alerts.fields.button_style'))
                                ->options(self::translatedOptions('button_styles', Alert::BUTTON_STYLES))
                                ->default('primary')
                                ->required()
                                ->native(false),

                            Toggle::make('new_tab')
                                ->label(trans('admin/alerts.fields.button_new_tab'))
                                ->default(false)
                                ->inline(false),
                        ]),

                    Toggle::make('dismiss_on_action')
                        ->label(trans('admin/alerts.fields.dismiss_on_action'))
                        ->helperText(trans('admin/alerts.helpers.dismiss_on_action'))
                        ->default(false)
                        ->live(),
                ]),

            Section::make(trans('admin/alerts.sections.display'))
                ->description(trans('admin/alerts.sections.display_description'))
                ->icon('tabler-layout-navbar')
                ->columns(2)
                ->schema([
                    Toggle::make('enabled')
                        ->label(trans('admin/alerts.fields.enabled'))
                        ->helperText(trans('admin/alerts.helpers.enabled'))
                        ->default(true),

                    Toggle::make('dismissible')
                        ->label(trans('admin/alerts.fields.dismissible'))
                        ->helperText(trans('admin/alerts.helpers.dismissible'))
                        ->default(true)
                        ->live(),

                    CheckboxList::make('placements')
                        ->label(trans('admin/alerts.fields.placements'))
                        ->helperText(trans('admin/alerts.helpers.placements'))
                        ->options(self::translatedOptions('placements', Alert::PLACEMENTS))
                        ->default([Alert::PLACEMENT_DASHBOARD, Alert::PLACEMENT_SERVER])
                        ->required()
                        ->columns(2)
                        ->columnSpanFull(),

                    TextInput::make('priority')
                        ->label(trans('admin/alerts.fields.priority'))
                        ->helperText(trans('admin/alerts.helpers.priority'))
                        ->integer()
                        ->default(0)
                        ->required()
                        ->minValue(-1000)
                        ->maxValue(1000),

                    TextInput::make('redisplay_after_days')
                        ->label(trans('admin/alerts.fields.redisplay_after_days'))
                        ->helperText(trans('admin/alerts.helpers.redisplay_after_days'))
                        ->integer()
                        ->minValue(1)
                        ->maxValue(3650)
                        ->suffix(trans('admin/alerts.suffixes.days'))
                        ->visible(fn (Get $get): bool => $get('dismissible') || $get('dismiss_on_action')),
                ]),

            Section::make(trans('admin/alerts.sections.schedule'))
                ->description(trans('admin/alerts.sections.schedule_description'))
                ->icon('tabler-calendar-time')
                ->schema([
                    DateTimePicker::make('starts_at')
                        ->label(trans('admin/alerts.fields.starts_at'))
                        ->helperText(trans('admin/alerts.helpers.starts_at', ['timezone' => config('app.timezone')]))
                        ->seconds(false),

                    DateTimePicker::make('ends_at')
                        ->label(trans('admin/alerts.fields.ends_at'))
                        ->helperText(trans('admin/alerts.helpers.ends_at'))
                        ->seconds(false)
                        ->rule(fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                            $start = $get('starts_at');

                            if ($start && $value && Carbon::parse($value)->lessThanOrEqualTo(Carbon::parse($start))) {
                                $fail(trans('admin/alerts.validation.ends_after_start'));
                            }
                        }),
                ]),

            Section::make(trans('admin/alerts.sections.audience'))
                ->description(trans('admin/alerts.sections.audience_description'))
                ->icon('tabler-users-group')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('audience')
                        ->label(trans('admin/alerts.fields.audience'))
                        ->options(self::translatedOptions('audiences', Alert::AUDIENCES))
                        ->default('everyone')
                        ->required()
                        ->selectablePlaceholder(false)
                        ->native(false),

                    Select::make('targeting.two_factor')
                        ->label(trans('admin/alerts.fields.two_factor'))
                        ->options(self::translatedOptions('two_factor', Alert::TWO_FACTOR_STATES))
                        ->default('any')
                        ->selectablePlaceholder(false)
                        ->native(false),

                    Select::make('targeting.users')
                        ->label(trans('admin/alerts.fields.users'))
                        ->helperText(trans('admin/alerts.helpers.users'))
                        ->multiple()
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => self::userOptions(
                            User::query()
                                ->where(fn ($query) => $query
                                    ->where('username', 'like', "%$search%")
                                    ->orWhere('email', 'like', "%$search%"))
                                ->limit(25)
                        ))
                        ->getOptionLabelsUsing(fn (array $values): array => self::userOptions(
                            User::query()->whereIn('id', $values)
                        )),

                    Select::make('targeting.languages')
                        ->label(trans('admin/alerts.fields.languages'))
                        ->helperText(trans('admin/alerts.helpers.languages'))
                        ->multiple()
                        ->options(fn (): array => (new self())->getAvailableLanguages(true)),

                    TextInput::make('targeting.account_age_min')
                        ->label(trans('admin/alerts.fields.account_age_min'))
                        ->helperText(trans('admin/alerts.helpers.account_age'))
                        ->integer()
                        ->minValue(0)
                        ->suffix(trans('admin/alerts.suffixes.days')),

                    TextInput::make('targeting.account_age_max')
                        ->label(trans('admin/alerts.fields.account_age_max'))
                        ->helperText(trans('admin/alerts.helpers.account_age'))
                        ->integer()
                        ->minValue(0)
                        ->suffix(trans('admin/alerts.suffixes.days'))
                        ->rule(fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                            $minimum = $get('targeting.account_age_min');

                            if (is_numeric($minimum) && is_numeric($value) && (int) $value < (int) $minimum) {
                                $fail(trans('admin/alerts.validation.age_range'));
                            }
                        }),
                ]),

            Section::make(trans('admin/alerts.sections.servers'))
                ->description(trans('admin/alerts.sections.servers_description'))
                ->icon('tabler-server')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('targeting.servers')
                        ->label(trans('admin/alerts.fields.servers'))
                        ->options(self::translatedOptions('servers', Alert::SERVER_RELATIONS))
                        ->default('any')
                        ->selectablePlaceholder(false)
                        ->native(false)
                        ->live()
                        ->columnSpanFull(),

                    self::serverFilter('nodes', fn (): array => Node::query()->orderBy('name')->pluck('name', 'id')->all()),
                    self::serverFilter('locations', fn (): array => Location::query()->orderBy('short')->pluck('short', 'id')->all()),
                    self::serverFilter('nests', fn (): array => Nest::query()->orderBy('name')->pluck('name', 'id')->all()),
                    self::serverFilter('eggs', fn (): array => Egg::query()
                        ->with('nest')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Egg $egg) => [$egg->id => "{$egg->nest->name} / {$egg->name}"])
                        ->all()),
                ]),
        ]);
    }

    private static function serverFilter(string $key, \Closure $options): Select
    {
        return Select::make("targeting.$key")
            ->label(trans("admin/alerts.fields.$key"))
            ->helperText(trans('admin/alerts.helpers.server_filters'))
            ->multiple()
            ->options($options)
            ->visible(fn (Get $get): bool => $get('targeting.servers') !== 'none');
    }

    /**
     * @param  string[]  $values
     * @return array<string, string>
     */
    private static function translatedOptions(string $group, array $values): array
    {
        return collect($values)
            ->mapWithKeys(fn (string $value) => [$value => trans("admin/alerts.$group.$value")])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function userOptions($query): array
    {
        return $query->get()
            ->mapWithKeys(fn (User $user) => [$user->id => "{$user->username} ({$user->email})"])
            ->all();
    }
}

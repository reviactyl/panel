<?php

namespace App\Filament\Resources\Servers\Schemas;

use App\Contracts\Repository\AllocationRepositoryInterface;
use App\Enum\JwtScope;
use App\Filament\Resources\Servers\ServerResource;
use App\Models\Allocation;
use App\Models\Egg;
use App\Models\EggVariable;
use App\Models\Node;
use App\Models\Server;
use App\Models\ServerTransfer;
use App\Models\User;
use App\Repositories\Agent\DaemonTransferRepository;
use App\Repositories\Eloquent\NodeRepository;
use App\Repositories\Eloquent\ServerRepository;
use App\Services\Activity\ActivityLogService;
use App\Services\Helpers\RandomWordService;
use App\Services\Nodes\NodeJWTService;
use App\Services\Servers\ReinstallServerService;
use App\Services\Servers\ServerDeletionService;
use App\Services\Servers\SuspensionService;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\CodeEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\RedirectResponse;
use Lcobucci\JWT\Token\Plain;
use Phiki\Grammar\Grammar;

class EditServerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Tabs::make(trans('admin/server.label'))
                    ->tabs([
                        Tab::make('information')
                            ->icon('tabler-info-circle')
                            ->label(trans('admin/server.edit.tabs.information'))
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(trans('admin/server.edit.fields.server_name.label'))
                                            ->required()
                                            ->maxLength(191)
                                            ->helperText(trans('admin/server.edit.fields.server_name.helper'))
                                            ->suffixAction(Action::make('random')
                                                ->label(trans('admin/server.actions.random'))
                                                ->icon('tabler-dice-'.rand(1, 6))
                                                ->action(fn (Set $set) => RandomWordService::setRandomName($set))
                                            ),

                                        Select::make('owner_id')
                                            ->label(trans('admin/server.edit.fields.server_owner.label'))
                                            ->relationship('user', 'email')
                                            ->getOptionLabelFromRecordUsing(fn (User $record): string => sprintf(
                                                '%s %s (%s)',
                                                $record->name_first,
                                                $record->name_last,
                                                $record->email,
                                            ))
                                            ->searchable(['email', 'username', 'name_first', 'name_last'])
                                            ->required()
                                            ->helperText(trans('admin/server.edit.fields.server_owner.helper')),

                                        Textarea::make('description')
                                            ->label(trans('admin/server.edit.fields.server_description.label'))
                                            ->rows(2)
                                            ->helperText(trans('admin/server.edit.fields.server_description.helper'))
                                            ->columnSpanFull(),

                                        TextInput::make('uuid')
                                            ->label(trans('admin/server.edit.fields.server_uuid.label'))
                                            ->copyable()
                                            ->readOnly(),

                                        TextInput::make('uuidShort')
                                            ->label(trans('admin/server.edit.fields.server_uuid_short.label'))
                                            ->copyable()
                                            ->readOnly(),

                                        TextInput::make('external_id')
                                            ->label(trans('admin/server.edit.fields.external_identifier.label'))
                                            ->maxLength(191)
                                            ->helperText(trans('admin/server.edit.fields.external_identifier.helper')),

                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make(trans('admin/server.edit.tabs.build_configuration'))
                            ->icon('heroicon-o-cpu-chip')
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Section::make(trans('admin/server.edit.sections.resource_management'))
                                            ->schema(self::resourceFields())
                                            ->columns(2)
                                            ->columnSpan(['lg' => 5]),

                                        Group::make()
                                            ->schema([
                                                Section::make(trans('admin/server.edit.sections.application_feature_limits'))
                                                    ->schema(self::featureLimitFields())
                                                    ->columns(2),

                                                Section::make(trans('admin/server.edit.sections.allocation_management'))
                                                    ->schema([
                                                        Select::make('allocation_id')
                                                            ->label(trans('admin/server.edit.fields.game_port.label'))
                                                            ->required()
                                                            ->searchable()
                                                            ->options(fn (?Server $record): array => self::assignedAllocations($record))
                                                            ->helperText(trans('admin/server.edit.fields.game_port.helper')),

                                                        Select::make('allocation_additional')
                                                            ->label(trans('admin/server.edit.fields.additional_ports.label'))
                                                            ->multiple()
                                                            ->searchable()
                                                            ->options(fn (Get $get, ?Server $record): array => self::editableAllocations($get, $record))
                                                            ->helperText(trans('admin/server.edit.fields.additional_ports.helper')),
                                                    ]),
                                            ])
                                            ->columnSpan(['lg' => 7]),
                                    ]),
                            ]),

                        Tab::make(trans('admin/server.edit.tabs.startup'))
                            ->icon('heroicon-o-command-line')
                            ->schema([
                                Section::make(trans('admin/server.edit.sections.startup_command_modification'))
                                    ->schema([
                                        TextInput::make('startup')
                                            ->label(trans('admin/server.edit.fields.startup_command.label'))
                                            ->required()
                                            ->helperText(trans('admin/server.edit.fields.startup_command.helper'))
                                            ->columnSpanFull(),

                                        CodeEntry::make('default_startup')
                                            ->label(trans('admin/server.edit.fields.default_startup_command.label'))
                                            ->grammar(Grammar::Shellscript)
                                            ->state(function (Get $get, ?Server $record): string {
                                                $egg = Egg::query()->find($get('egg_id') ?? $record?->egg_id);

                                                return $egg !== null ? $egg->startup : trans('admin/server.edit.fields.default_startup_command.error');
                                            })
                                            ->copyable()
                                            ->disabled()
                                            ->columnSpanFull(),
                                    ]),

                                Grid::make(12)
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                Section::make(trans('admin/server.edit.sections.service_configuration'))
                                                    ->description(trans('admin/server.edit.section_descriptions.service_configuration'))
                                                    ->schema([
                                                        Select::make('nest_id')
                                                            ->label(trans('admin/server.create.fields.nest.label'))
                                                            ->relationship('nest', 'name')
                                                            ->searchable()
                                                            ->preload()
                                                            ->required()
                                                            ->live()
                                                            ->afterStateUpdated(fn (Set $set, mixed $state): null => self::selectFirstEggForNest($set, $state))
                                                            ->helperText(trans('admin/server.create.fields.nest.helper')),

                                                        Select::make('egg_id')
                                                            ->label(trans('admin/server.create.fields.egg.label'))
                                                            ->required()
                                                            ->searchable()
                                                            ->options(fn (Get $get, ?Server $record): array => self::eggOptions($get, $record))
                                                            ->live()
                                                            ->afterStateUpdated(fn (Set $set, ?string $state): null => self::hydrateEggDefaults($set, $state))
                                                            ->helperText(trans('admin/server.create.fields.egg.helper')),

                                                        Toggle::make('skip_scripts')
                                                            ->label(trans('admin/server.create.fields.skip_scripts.label'))
                                                            ->default(false)
                                                            ->helperText(trans('admin/server.create.fields.skip_scripts.helper')),
                                                    ]),

                                                Section::make(trans('admin/server.edit.sections.docker_image_configuration'))
                                                    ->schema([
                                                        Select::make('image')
                                                            ->label(trans('admin/server.create.fields.image.label'))
                                                            ->required(fn (Get $get): bool => blank($get('custom_image')))
                                                            ->searchable()
                                                            ->live()
                                                            ->afterStateUpdated(function (Set $set): null {
                                                                $set('custom_image', null);

                                                                return null;
                                                            })
                                                            ->options(fn (Get $get, ?Server $record): array => self::dockerImageOptions($get, $record))
                                                            ->helperText(trans('admin/server.create.fields.image.helper')),

                                                        TextInput::make('custom_image')
                                                            ->label(trans('admin/server.create.fields.custom_image.label'))
                                                            ->maxLength(191)
                                                            ->placeholder('ghcr.io/reviactyl/images:java_25')
                                                            ->helperText(trans('admin/server.create.fields.custom_image.helper')),
                                                    ]),
                                            ])
                                            ->columnSpan(['lg' => 5]),

                                        Section::make(trans('admin/server.edit.sections.service_variables'))
                                            ->schema([
                                                Grid::make(1)
                                                    ->schema(fn (Get $get, ?Server $record): array => self::environmentFields($get, $record)),
                                            ])
                                            ->columnSpan(['lg' => 7]),
                                    ]),
                            ]),

                        Tab::make(trans('admin/server.edit.tabs.manage'))
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Section::make(trans('admin/server.edit.sections.reinstall_server'))
                                            ->description(trans('admin/server.edit.section_descriptions.reinstall_server'))
                                            ->schema([
                                                Actions::make([
                                                    Action::make('reinstall_server')
                                                        ->label(trans('admin/server.edit.actions.reinstall_server'))
                                                        ->color('danger')
                                                        ->disabled(fn (?Server $record): bool => ! $record?->isInstalled())
                                                        ->requiresConfirmation()
                                                        ->action(fn (Server $record) => self::reinstall($record)),
                                                ]),
                                            ])
                                            ->columnSpan(['lg' => 4]),

                                        Section::make(trans('admin/server.edit.sections.install_status'))
                                            ->description(trans('admin/server.edit.section_descriptions.install_status'))
                                            ->schema([
                                                Actions::make([
                                                    Action::make('toggle_install_status')
                                                        ->label(trans('admin/server.edit.actions.toggle_install_status'))
                                                        ->color('primary')
                                                        ->requiresConfirmation()
                                                        ->action(fn (Server $record) => self::toggleInstall($record)),
                                                ]),
                                            ])
                                            ->columnSpan(['lg' => 4]),

                                        Section::make(fn (?Server $record): string => $record?->isSuspended() ? trans('admin/server.edit.sections.unsuspend_server') : trans('admin/server.edit.sections.suspend_server'))
                                            ->description(fn (?Server $record): string => $record?->isSuspended()
                                                ? trans('admin/server.edit.section_descriptions.unsuspend_server')
                                                : trans('admin/server.edit.section_descriptions.suspend_server'))
                                            ->schema([
                                                Actions::make([
                                                    Action::make('toggle_suspension')
                                                        ->label(fn (?Server $record): string => $record?->isSuspended() ? trans('admin/server.edit.actions.unsuspend_server') : trans('admin/server.edit.actions.suspend_server'))
                                                        ->color(fn (?Server $record): string => $record?->isSuspended() ? 'success' : 'warning')
                                                        ->disabled(fn (?Server $record): bool => ! is_null($record?->transfer))
                                                        ->requiresConfirmation()
                                                        ->action(fn (Server $record) => self::toggleSuspension($record)),
                                                ]),
                                            ])
                                            ->columnSpan(['lg' => 4]),

                                        Section::make(trans('admin/server.edit.sections.transfer_server'))
                                            ->columnSpan(['lg' => 4])
                                            ->description(fn (?Server $record): string => $record?->transfer
                                                ? trans('admin/server.edit.section_descriptions.transfer_server_transferring')
                                                : trans('admin/server.edit.section_descriptions.transfer_server'))
                                            ->schema([
                                                Actions::make([
                                                    Action::make('transfer_server')
                                                        ->label(trans('admin/server.edit.actions.transfer_server'))
                                                        ->color('success')
                                                        ->disabled(fn (?Server $record): bool => ! self::canTransfer($record))
                                                        ->modalHeading(trans('admin/server.edit.actions.transfer_server'))
                                                        ->modalSubmitActionLabel(trans('admin/server.edit.actions.confirm'))
                                                        ->form([
                                                            Select::make('node_id')
                                                                ->label(trans('admin/server.edit.fields.transfer_node.label'))
                                                                ->required()
                                                                ->live()
                                                                ->options(fn (?Server $record): array => self::transferNodeOptions($record))
                                                                ->afterStateUpdated(function (Set $set): void {
                                                                    $set('allocation_id', null);
                                                                    $set('allocation_additional', []);
                                                                })
                                                                ->helperText(trans('admin/server.edit.fields.transfer_node.helper')),

                                                            Select::make('allocation_id')
                                                                ->label(trans('admin/server.edit.fields.transfer_allocation.label'))
                                                                ->required()
                                                                ->searchable()
                                                                ->options(fn (Get $get): array => self::transferAllocationOptions($get))
                                                                ->helperText(trans('admin/server.edit.fields.transfer_allocation.helper')),

                                                            Select::make('allocation_additional')
                                                                ->label(trans('admin/server.edit.fields.transfer_additional_allocations.label'))
                                                                ->multiple()
                                                                ->searchable()
                                                                ->options(fn (Get $get): array => self::transferAllocationOptions($get, excludePrimary: true))
                                                                ->helperText(trans('admin/server.edit.fields.transfer_additional_allocations.helper')),
                                                        ])
                                                        ->action(fn (array $data, Server $record) => self::transfer($record, $data)),
                                                ]),
                                            ]),
                                        Section::make(trans('admin/server.edit.sections.delete_server'))
                                            ->description(trans('admin/server.edit.section_descriptions.delete_server'))
                                            ->schema([
                                                Actions::make([
                                                    Action::make('delete_server')
                                                        ->label(trans('admin/server.edit.actions.delete_server'))
                                                        ->color('danger')
                                                        ->requiresConfirmation()
                                                        ->action(fn (Server $record) => self::delete($record, false)),

                                                    Action::make('force_delete_server')
                                                        ->label(trans('admin/server.edit.actions.forcibly_delete_server'))
                                                        ->color('danger')
                                                        ->requiresConfirmation()
                                                        ->action(fn (Server $record) => self::delete($record, true)),
                                                ]),
                                            ])
                                            ->columnSpan(['lg' => 4]),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    private static function resourceFields(): array
    {
        return [
            TextInput::make('cpu')
                ->label(trans('admin/server.edit.fields.cpu_limit.label'))
                ->required()
                ->numeric()->minValue(0)->suffix('%')->helperText(trans('admin/server.edit.fields.cpu_limit.helper')),
            TextInput::make('threads')
                ->label(trans('admin/server.edit.fields.cpu_pinning.label'))
                ->regex('/^[0-9-,]+$/')
                ->helperText(trans('admin/server.edit.fields.cpu_pinning.helper')),
            TextInput::make('memory')
                ->label(trans('admin/server.edit.fields.allocated_memory.label'))
                ->required()
                ->numeric()->minValue(0)->suffix('MiB')
                ->helperText(trans('admin/server.edit.fields.allocated_memory.helper')),
            TextInput::make('swap')
                ->label(trans('admin/server.edit.fields.allocated_swap.label'))
                ->required()
                ->numeric()->minValue(-1)->suffix('MiB')
                ->helperText(trans('admin/server.edit.fields.allocated_swap.helper')),
            TextInput::make('disk')
                ->label(trans('admin/server.edit.fields.disk_space_limit.label'))
                ->required()
                ->numeric()->minValue(0)->suffix('MiB')
                ->helperText(trans('admin/server.edit.fields.disk_space_limit.helper')),
            TextInput::make('io')
                ->label(trans('admin/server.edit.fields.block_io_proportion.label'))
                ->required()
                ->numeric()->minValue(10)->maxValue(1000)
                ->helperText(trans('admin/server.edit.fields.block_io_proportion.helper')),
            Toggle::make('oom_disabled')
                ->label(trans('admin/server.edit.fields.disable_oom_killer.label'))
                ->default(true)
                ->helperText(trans('admin/server.edit.fields.disable_oom_killer.helper')),
        ];
    }

    private static function featureLimitFields(): array
    {
        return [
            TextInput::make('database_limit')
                ->label(trans('admin/server.edit.fields.database_limit.label'))
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->helperText(trans('admin/server.edit.fields.database_limit.helper')),
            TextInput::make('allocation_limit')
                ->label(trans('admin/server.edit.fields.allocation_limit.label'))
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->helperText(trans('admin/server.edit.fields.allocation_limit.helper')),
            TextInput::make('backup_limit')
                ->label(trans('admin/server.edit.fields.backup_limit.label'))
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->helperText(trans('admin/server.edit.fields.backup_limit.helper')),
        ];
    }

    private static function assignedAllocations(?Server $record): array
    {
        return $record?->allocations()->orderBy('ip')->orderBy('port')->get()
            ->mapWithKeys(fn (Allocation $allocation): array => [$allocation->id => $allocation->toString()])
            ->all() ?? [];
    }

    private static function editableAllocations(Get $get, ?Server $record): array
    {
        if (! $record) {
            return [];
        }

        return Allocation::query()
            ->where('node_id', $record->node_id)
            ->where(fn ($query) => $query->whereNull('server_id')->orWhere('server_id', $record->id))
            ->whereKeyNot($get('allocation_id') ?? $record->allocation_id)
            ->orderBy('ip')
            ->orderBy('port')
            ->get()
            ->mapWithKeys(fn (Allocation $allocation): array => [$allocation->id => $allocation->toString()])
            ->all();
    }

    private static function canTransfer(?Server $server): bool
    {
        return $server !== null
            && $server->transfer === null
            && Node::query()->whereKeyNot($server->node_id)->exists();
    }

    private static function transferNodeOptions(?Server $server): array
    {
        if (! $server) {
            return [];
        }

        return Node::query()
            ->whereKeyNot($server->node_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    private static function transferAllocationOptions(Get $get, bool $excludePrimary = false): array
    {
        if (blank($get('node_id'))) {
            return [];
        }

        return Allocation::query()
            ->where('node_id', $get('node_id'))
            ->whereNull('server_id')
            ->when($excludePrimary && filled($get('allocation_id')), fn ($query) => $query->whereKeyNot($get('allocation_id')))
            ->orderBy('ip')
            ->orderBy('port')
            ->get()
            ->mapWithKeys(fn (Allocation $allocation): array => [$allocation->id => $allocation->toString()])
            ->all();
    }

    private static function eggOptions(Get $get, ?Server $record): array
    {
        $nestId = $get('nest_id') ?? $record?->nest_id;

        return filled($nestId) ? Egg::query()->where('nest_id', $nestId)->orderBy('name')->pluck('name', 'id')->all() : [];
    }

    private static function dockerImageOptions(Get $get, ?Server $record): array
    {
        $egg = filled($get('egg_id') ?? $record?->egg_id) ? Egg::query()->find($get('egg_id') ?? $record?->egg_id) : null;
        $currentImage = $get('image') ?? $record?->image;
        $options = collect($egg !== null ? $egg->docker_images : [])
            ->mapWithKeys(fn (string $image, string $label): array => [$image => sprintf('%s (%s)', $label, $image)])
            ->all();

        if (filled($currentImage) && ! array_key_exists((string) $currentImage, $options)) {
            $options = [(string) $currentImage => 'Custom ('.$currentImage.')'] + $options;
        }

        return $options;
    }

    private static function environmentFields(Get $get, ?Server $record): array
    {
        $eggId = $get('egg_id') ?? $record?->egg_id;

        if (blank($eggId)) {
            return [TextInput::make('environment_placeholder')->label(trans('admin/server.create.fields.environment_placeholder.label'))->disabled()->dehydrated(false)];
        }

        return EggVariable::query()
            ->where('egg_id', $eggId)
            ->orderBy('name')
            ->get()
            ->map(fn (EggVariable $variable) => TextInput::make('environment.'.$variable->env_variable)
                ->label($variable->name.($variable->required ? ' *' : ''))
                ->default($variable->server_value ?? $variable->default_value)
                ->required((bool) $variable->required)
                ->helperText(trim($variable->description.' Startup Command Variable: '.$variable->env_variable.'. Input Rules: '.$variable->rules)))
            ->all();
    }

    private static function hydrateEggDefaults(Set $set, ?string $eggId): null
    {
        $egg = filled($eggId) ? Egg::query()->with('variables')->find($eggId) : null;

        if (! $egg) {
            return null;
        }

        $set('startup', $egg->startup);
        $set('image', collect($egg->docker_images ?? [])->first());
        $set('custom_image', null);
        $set('environment', $egg->variables->mapWithKeys(fn (EggVariable $variable): array => [$variable->env_variable => $variable->default_value])->all());

        return null;
    }

    private static function firstEggId(mixed $nestId): ?int
    {
        if (blank($nestId)) {
            return null;
        }

        return Egg::query()->where('nest_id', $nestId)->orderBy('id')->value('id');
    }

    private static function selectFirstEggForNest(Set $set, mixed $nestId): null
    {
        $eggId = self::firstEggId($nestId);

        $set('egg_id', $eggId);
        self::hydrateEggDefaults($set, $eggId ? (string) $eggId : null);

        return null;
    }

    private static function toggleInstall(Server $server): void
    {
        if ($server->status === Server::STATUS_INSTALL_FAILED) {
            Notification::make()->title(trans('admin/server.exceptions.marked_as_failed'))->danger()->send();

            return;
        }

        try {
            app(ServerRepository::class)->update($server->id, [
                'status' => $server->isInstalled() ? Server::STATUS_INSTALLING : null,
            ], true, true);

            app(ActivityLogService::class)->subject($server)->event('server:toggle-install')->log();
            Notification::make()->title(trans('admin/server.alerts.install_toggled'))->success()->send();
        } catch (\Throwable $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private static function toggleSuspension(Server $server): void
    {
        $action = $server->isSuspended() ? SuspensionService::ACTION_UNSUSPEND : SuspensionService::ACTION_SUSPEND;

        try {
            app(SuspensionService::class)->toggle($server, $action);
            app(ActivityLogService::class)->subject($server)->event('server:'.$action)->log();
            Notification::make()->title(trans('admin/server.alerts.suspension_toggled', ['status' => $action]))->success()->send();
        } catch (\Throwable $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private static function reinstall(Server $server): void
    {
        try {
            app(ReinstallServerService::class)->handle($server);
            app(ActivityLogService::class)->subject($server)->event('server:reinstall')->log();
            Notification::make()->title(trans('admin/server.alerts.server_reinstalled'))->success()->send();
        } catch (\Throwable $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private static function transfer(Server $server, array $data): void
    {
        try {
            $nodeId = (int) $data['node_id'];
            $allocationId = (int) $data['allocation_id'];
            $additionalAllocations = array_map('intval', $data['allocation_additional'] ?? []);

            $node = app(NodeRepository::class)->getNodeWithResourceUsage($nodeId);

            if (! $node->isViable($server->memory, $server->disk)) {
                Notification::make()->title(trans('admin/server.alerts.transfer_not_viable'))->danger()->send();

                return;
            }

            $server->validateTransferState();

            app(ConnectionInterface::class)->transaction(function () use ($server, $nodeId, $allocationId, $additionalAllocations): void {
                $transfer = new ServerTransfer();
                $transfer->server_id = $server->id;
                $transfer->old_node = $server->node_id;
                $transfer->new_node = $nodeId;
                $transfer->old_allocation = $server->allocation_id;
                $transfer->new_allocation = $allocationId;
                $transfer->old_additional_allocations = $server->allocations
                    ->where('id', '!=', $server->allocation_id)
                    ->pluck('id')
                    ->values()
                    ->toArray();
                $transfer->new_additional_allocations = $additionalAllocations;
                $transfer->save();

                self::assignTransferAllocations($server, $nodeId, $allocationId, $additionalAllocations);

                $token = app(NodeJWTService::class)
                    ->setExpiresAt(CarbonImmutable::now()->addMinutes(15))
                    ->setSubject($server->uuid)
                    ->setScopes(JwtScope::ServerTransfer)
                    ->handle($transfer->newNode, $server->uuid);

                assert($token instanceof Plain);

                app(DaemonTransferRepository::class)
                    ->setServer($server)
                    ->notify($transfer->newNode, $token);
            });

            app(ActivityLogService::class)->subject($server)->event('server:transfer')->log();
            Notification::make()->title(trans('admin/server.alerts.transfer_started'))->success()->send();
        } catch (\Throwable $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private static function assignTransferAllocations(Server $server, int $nodeId, int $allocationId, array $additionalAllocations): void
    {
        $allocations = [...$additionalAllocations, $allocationId];
        $unassigned = app(AllocationRepositoryInterface::class)->getUnassignedAllocationIds($nodeId);

        $updateIds = [];
        foreach ($allocations as $allocation) {
            if (in_array($allocation, $unassigned, true)) {
                $updateIds[] = $allocation;
            }
        }

        if ($updateIds !== []) {
            app(AllocationRepositoryInterface::class)->updateWhereIn('id', $updateIds, ['server_id' => $server->id]);
        }
    }

    private static function delete(Server $server, bool $force): ?RedirectResponse
    {
        try {
            $service = app(ServerDeletionService::class);

            if ($force) {
                $service->withForce();
            }

            $service->handle($server);
            app(ActivityLogService::class)->subject($server)->event('server:delete')->log();
            Notification::make()->title(trans('admin/server.alerts.server_deleted'))->success()->send();

            return redirect(ServerResource::getUrl('index'));
        } catch (\Throwable $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            return null;
        }
    }
}

<?php

namespace App\Filament\Resources\Nodes\Schemas;

use App\Models\Node;
use App\Models\ApiKey;
use Phiki\Grammar\Grammar;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Textarea;
use App\Services\Api\KeyCreationService;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Infolists\Components\CodeEntry;
use Illuminate\Contracts\Encryption\Encrypter;

class NodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Node Configuration')
                    ->tabs([
                        Tab::make(trans('admin/node.sections.identity.title'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make()
                                    ->description(trans('admin/node.sections.identity.description'))
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(trans('admin/node.fields.name.label'))
                                            ->required()
                                            ->maxLength(100)
                                            ->placeholder(trans('admin/node.fields.name.placeholder'))
                                            ->helperText(trans('admin/node.fields.name.helper'))
                                            ->columnSpanFull(),

                                        Textarea::make('description')
                                            ->label(trans('admin/node.fields.description.label'))
                                            ->placeholder(trans('admin/node.fields.description.placeholder'))
                                            ->helperText(trans('admin/node.fields.description.helper'))
                                            ->columnSpanFull(),

                                        Select::make('location_id')
                                            ->label(trans('admin/node.fields.location.label'))
                                            ->relationship('location', 'short')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->helperText(trans('admin/node.fields.location.helper'))
                                            ->columnSpanFull(),

                                        Toggle::make('public')
                                            ->label(trans('admin/node.fields.public.label'))
                                            ->default(true)
                                            ->helperText(trans('admin/node.fields.public.helper')),

                                        Toggle::make('maintenance_mode')
                                            ->label(trans('admin/node.fields.maintenance_mode.label'))
                                            ->default(false)
                                            ->helperText(trans('admin/node.fields.maintenance_mode.helper')),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make(trans('admin/node.sections.resources.title'))
                            ->icon('heroicon-o-cpu-chip')
                            ->schema([
                                Section::make()
                                    ->description(trans('admin/node.sections.resources.description'))
                                    ->schema([
                                        TextInput::make('memory')
                                            ->label(trans('admin/node.fields.memory.label'))
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->suffix('MiB')
                                            ->helperText(trans('admin/node.fields.memory.helper')),

                                        TextInput::make('memory_overallocate')
                                            ->label(trans('admin/node.fields.memory_overallocate.label'))
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('%')
                                            ->helperText(trans('admin/node.fields.memory_overallocate.helper')),

                                        TextInput::make('disk')
                                            ->label(trans('admin/node.fields.disk.label'))
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->suffix('MiB')
                                            ->helperText(trans('admin/node.fields.disk.helper')),

                                        TextInput::make('disk_overallocate')
                                            ->label(trans('admin/node.fields.disk_overallocate.label'))
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('%')
                                            ->helperText(trans('admin/node.fields.disk_overallocate.helper')),

                                        TextInput::make('upload_size')
                                            ->label(trans('admin/node.fields.upload_size.label'))
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(100)
                                            ->suffix('MiB')
                                            ->helperText(trans('admin/node.fields.upload_size.helper')),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make(trans('admin/node.sections.daemon.title'))
                            ->icon('heroicon-o-command-line')
                            ->schema([
                                Section::make()
                                    ->description(trans('admin/node.sections.daemon.description'))
                                    ->schema([
                                        TextInput::make('daemonBase')
                                            ->label(trans('admin/node.fields.daemon_base.label'))
                                            ->required()
                                            ->maxLength(255)
                                            ->default('/var/lib/reviactyl/volumes')
                                            ->placeholder(trans('admin/node.fields.daemon_base.placeholder'))
                                            ->helperText(trans('admin/node.fields.daemon_base.helper')),

                                        TextInput::make('daemonListen')
                                            ->label(trans('admin/node.fields.daemon_listen.label'))
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(65535)
                                            ->default(8080)
                                            ->helperText(trans('admin/node.fields.daemon_listen.helper')),

                                        TextInput::make('daemonSFTP')
                                            ->label(trans('admin/node.fields.daemon_sftp.label'))
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(65535)
                                            ->default(2022)
                                            ->helperText(trans('admin/node.fields.daemon_sftp.helper')),

                                        TextInput::make('containerText')
                                            ->label(trans('admin/node.fields.container_text.label'))
                                            ->maxLength(50)
                                            ->default('container@reviactyl~')
                                            ->helperText(trans('admin/node.fields.container_text.helper')),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make(trans('admin/node.sections.connection.title'))
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Section::make()
                                    ->description(trans('admin/node.sections.connection.description'))
                                    ->schema([
                                        TextInput::make('fqdn')
                                            ->label(trans('admin/node.fields.fqdn.label'))
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder(trans('admin/node.fields.fqdn.placeholder'))
                                            ->helperText(trans('admin/node.fields.fqdn.helper'))
                                            ->columnSpanFull(),

                                        Toggle::make('scheme')
                                            ->label(trans('admin/node.fields.ssl.label'))
                                            ->default(true)
                                            ->disabled(fn () => request()->secure())
                                            ->dehydrated(true)
                                            ->afterStateHydrated(function ($component, $state, $record) {
                                                $isSecure = request()->secure();

                                                // Force HTTPS if panel is running on HTTPS
                                                if ($isSecure) {
                                                    $component->state(true);

                                                    return;
                                                }

                                                if ($record && isset($record->scheme)) {
                                                    $component->state($record->scheme === 'https');
                                                } elseif (is_string($state)) {
                                                    // Fallback: convert string to boolean
                                                    $component->state($state === 'https');
                                                }
                                            })
                                            ->dehydrateStateUsing(fn ($state) => $state ? 'https' : 'http')
                                            ->helperText(fn () => request()->secure()
                                                ? trans('admin/node.fields.ssl.helper_forced')
                                                : trans('admin/node.fields.ssl.helper')),

                                        Toggle::make('behind_proxy')
                                            ->label(trans('admin/node.fields.behind_proxy.label'))
                                            ->default(false)
                                            ->helperText(trans('admin/node.fields.behind_proxy.helper')),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make(trans('admin/node.sections.configuration.title'))
                            ->icon('heroicon-o-rocket-launch')
                            ->schema([
                                Section::make()
                                    ->description(trans('admin/node.sections.configuration.config_description'))
                                    ->schema([
                                        CodeEntry::make('config')
                                            ->label('/etc/reviactyl/config.yml')
                                            ->grammar(Grammar::Yaml)
                                            ->state(fn (Node $node) => $node->getYamlConfiguration())
                                            ->copyable()
                                            ->disabled()
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(fn ($record) => $record && $record->id),
                                Section::make()
                                    ->description(trans('admin/node.sections.configuration.deploy_description'))
                                    ->schema([
                                        Actions::make([
                                            Action::make('generateToken')
                                                ->label(trans('admin/node.deployment.generate_label'))
                                                ->icon('heroicon-o-key')
                                                ->color('success')
                                                ->modalHeading(trans('admin/node.deployment.modal_heading'))
                                                ->modalDescription(trans('admin/node.deployment.modal_description'))
                                                ->modalSubmitActionLabel(trans('admin/node.deployment.modal_close'))
                                                ->modalCancelAction(false)
                                                ->form([
                                                    Textarea::make('command')
                                                        ->label(trans('admin/node.deployment.command_label'))
                                                        ->rows(3)
                                                        ->disabled()
                                                        ->extraAttributes(['class' => 'font-mono text-xs'])
                                                        ->helperText(trans('admin/node.deployment.command_helper')),
                                                ])
                                                ->fillForm(function ($record) {
                                                    if (!$record || !$record->id) {
                                                        return ['command' => 'Please save the node first.'];
                                                    }

                                                    try {
                                                        $user = auth()->user();

                                                        $key = ApiKey::query()
                                                            ->where('user_id', $user->id)
                                                            ->where('key_type', ApiKey::TYPE_APPLICATION)
                                                            ->get()
                                                            ->filter(function (ApiKey $key) {
                                                                foreach ($key->getAttributes() as $permission => $value) {
                                                                    if ($permission === 'r_nodes' && $value === 1) {
                                                                        return true;
                                                                    }
                                                                }

                                                                return false;
                                                            })
                                                            ->first();

                                                        // Create a Deployment Key if one doesn't exist
                                                        if (!$key) {
                                                            $keyCreationService = app(KeyCreationService::class);
                                                            $key = $keyCreationService->setKeyType(ApiKey::TYPE_APPLICATION)->handle([
                                                                'user_id' => $user->id,
                                                                'memo' => 'Automatically generated node deployment key.',
                                                                'allowed_ips' => [],
                                                            ], ['r_nodes' => 1]);
                                                        }

                                                        $encrypter = app(Encrypter::class);
                                                        $token = $key->identifier . $encrypter->decrypt($key->token);

                                                        $appUrl = config('app.url');
                                                        $debug = config('app.debug');
                                                        $allowInsecure = $debug ? ' --allow-insecure' : '';

                                                        $command = "cd /etc/reviactyl && sudo wings configure --panel-url {$appUrl} --token {$token} --node {$record->id}{$allowInsecure}";

                                                        Notification::make()
                                                            ->title(trans('admin/node.deployment.token_success'))
                                                            ->body(trans('admin/node.deployment.token_success_body'))
                                                            ->success()
                                                            ->send();

                                                        return ['command' => $command];
                                                    } catch (\Exception $e) {
                                                        return ['command' => 'Error generating token. Please try again.'];
                                                    }
                                                })
                                                ->action(function () {
                                                    // No action needed, just close the modal
                                                }),
                                        ])
                                        ->fullWidth(),
                                    ])
                                    ->visible(fn ($record) => $record && $record->id),
                            ])
                            ->hidden(fn ($record) => !$record || !$record->id),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Nodes\Schemas;

use App\Services\Helpers\RandomWordService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class CreateNodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make(trans('admin/node.sections.identity.title'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Grid::make()
                                ->schema([
                                    TextInput::make('name')
                                        ->label(trans('admin/node.fields.name.label'))
                                        ->required()
                                        ->maxLength(100)
                                        ->placeholder(trans('admin/node.fields.name.placeholder'))
                                        ->helperText(trans('admin/node.fields.name.helper'))
                                        ->columnSpan(1)
                                        ->suffixAction(Action::make('random')
                                            ->label('Random')
                                            ->icon('tabler-dice-'.rand(1, 6))
                                            ->action(fn (Set $set) => RandomWordService::setRandomName($set))
                                        ),

                                    Select::make('location_id')
                                        ->label(trans('admin/node.fields.location.label'))
                                        ->relationship('location', 'short')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->helperText(trans('admin/node.fields.location.helper'))
                                        ->columnSpan(1),

                                    Textarea::make('description')
                                        ->label(trans('admin/node.fields.description.label'))
                                        ->placeholder(trans('admin/node.fields.description.placeholder'))
                                        ->helperText(trans('admin/node.fields.description.helper'))
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

                    Step::make(trans('admin/node.sections.resources.title'))
                        ->icon('heroicon-o-cpu-chip')
                        ->schema([
                            Grid::make()
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

                    Step::make(trans('admin/node.sections.daemon.title'))
                        ->icon('heroicon-o-command-line')
                        ->schema([
                            Grid::make()
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

                    Step::make(trans('admin/node.sections.connection.title'))
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            Grid::make()
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
                ])
                    ->columnSpanFull(),
            ]);
    }
}

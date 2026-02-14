<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make(trans('admin/user.details.account_details'))
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('email')
                            ->label(trans('admin/user.details.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        TextInput::make('username')
                            ->label(trans('admin/user.details.username'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        TextInput::make('name_first')
                            ->label(trans('admin/user.details.first_name'))
                            ->required()
                            ->maxLength(191),
                        TextInput::make('name_last')
                            ->label(trans('admin/user.details.last_name'))
                            ->required()
                            ->maxLength(191),
                        TextInput::make('external_id')
                            ->label(trans('admin/user.details.external_id'))
                            ->nullable()
                            ->maxLength(191),
                        Select::make('language')
                            ->label(trans('admin/user.details.language'))
                            ->options((new User())->getAvailableLanguages(true))
                            ->default('en')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpan(6),

                Group::make()
                    ->schema([
                        Section::make(trans('admin/user.details.password'))
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                TextInput::make('password')
                                    ->label(trans('admin/user.details.password'))
                                    ->password()
                                    ->confirmed()
                                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                    ->dehydrated(fn ($state) => filled($state)),
                                TextInput::make('password_confirmation')
                                    ->label(trans('admin/user.details.password_confirmation'))
                                    ->password()
                                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                    ->dehydrated(false),
                            ]),

                        Section::make(trans('admin/user.details.privileges'))
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Toggle::make('root_admin')
                                    ->label(trans('admin/user.details.root_admin'))
                                    ->helperText(trans('admin/user.details.root_admin_desc'))
                                    ->onColor('success'),
                            ]),
                    ])
                    ->columnSpan(6),
            ]);
    }
}

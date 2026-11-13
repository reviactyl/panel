<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedbackWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 2;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(trans('admin/index.feedback-header'))
                    ->icon('heroicon-o-code-bracket')
                    ->iconColor('primary')
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->schema([
                        TextEntry::make('info')
                            ->hiddenLabel()
                            ->state(trans('admin/index.feedback-body')),
                    ])
                    ->headerActions([
                        Action::make('donate')
                            ->label(trans('admin/index.feedback-btn'))
                            ->icon('heroicon-s-squares-plus')
                            ->url('https://github.com/reviactyl/panel/issues', true),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Services\Helpers\SoftwareVersionService;

class SponsorWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 3;

    private SoftwareVersionService $softwareVersionService;

    public function mount(SoftwareVersionService $softwareVersionService): void
    {
        $this->softwareVersionService = $softwareVersionService;
    }

    public function form(Schema $schema): Schema
    {
        $getDonations = $this->softwareVersionService->getDonations();

        return $schema
            ->components([
                Section::make(trans('admin/index.sponsor-header'))
                    ->icon('heroicon-o-heart')
                    ->iconColor('danger')
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->schema([
                        TextEntry::make('info')
                            ->hiddenLabel()
                            ->state(trans('admin/index.sponsor-body')),
                    ])
                    ->headerActions([
                        Action::make('donate')
                            ->label(trans('admin/index.sponsor-btn'))
                            ->icon('heroicon-s-banknotes')
                            ->url($getDonations, true)
                            ->color('success'),
                    ]),
            ]);
    }
}

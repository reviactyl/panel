<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\SoftwareUpdates;
use App\Services\Helpers\SoftwareVersionService;
use App\Services\Updates\InstallationTypeService;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UpdateWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 2;

    protected static ?int $sort = 1;

    private SoftwareVersionService $softwareVersionService;

    public function mount(SoftwareVersionService $softwareVersionService): void
    {
        $this->softwareVersionService = $softwareVersionService;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(
                trans('admin/index.notuptodate-header')
            )
                ->icon('heroicon-o-information-circle')
                ->iconColor('warning')
                ->visible(! $this->softwareVersionService->isLatestPanel())
                ->headerActions([
                    Action::make('update')
                        ->label(trans('admin/index.update-btn'))
                        ->icon('heroicon-c-cursor-arrow-rays')
                        ->url(
                            fn (): string => app(InstallationTypeService::class)->panelSupportsAutomaticUpdates()
                                ? SoftwareUpdates::getUrl()
                                : 'https://reviactyl.app/docs/panel/updating-the-panel',
                            fn (): bool => ! app(InstallationTypeService::class)->panelSupportsAutomaticUpdates(),
                        )
                        ->color('warning'),
                ])
                ->schema([
                    TextEntry::make('info')
                        ->hiddenLabel()
                        ->state(
                            trans(
                                'admin/index.notuptodate-body',
                                [
                                    'latest' => $this->softwareVersionService->getPanel(),
                                ]
                            )
                        ),
                ]),
        ]);
    }
}

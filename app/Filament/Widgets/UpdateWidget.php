<?php

namespace App\Filament\Widgets;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Services\Helpers\SoftwareVersionService;

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
        $isLatest = $this->softwareVersionService->isLatestPanel();

        return $schema->components([
            $isLatest
                ? Section::make(
                    trans('admin/index.uptodate-header')
                )
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->schema([
                        TextEntry::make('info')
                            ->hiddenLabel()
                            ->state(
                                trans(
                                    'admin/index.uptodate-body',
                                    [
                                        'version' => config('app.version'),
                                    ]
                                )
                            ),
                    ])

                : Section::make(
                    trans('admin/index.notuptodate-header')
                )
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('warning')
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

<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\ActivityLogResource;

class ActivityLogWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 5;

    public function form(Schema $schema): Schema
    {
        $logs = ActivityLog::query()
            ->with('actor')
            ->latest('timestamp')
            ->limit(5)
            ->get();

        $entries = [];

        foreach ($logs as $log) {
            $entries[] = TextEntry::make("log_{$log->id}")
                ->hiddenLabel()
                ->state(
                    ($log->actor?->name ?? trans('admin/activity_log.fallback_actor'))
                    . ' — '
                    . $log->event
                    . ' — '
                    . $log->timestamp?->diffForHumans()
                );
        }

        $entries[] = TextEntry::make('view_more')
            ->hiddenLabel()
            ->state(trans('admin/index.more-btn') . '  →')
            ->color('primary')
            ->url(ActivityLogResource::getUrl());

        return $schema->components([
            Section::make(trans('admin/index.activity-header'))
                ->icon('heroicon-o-clipboard-document-list')
                ->iconColor('primary')
                ->collapsible()
                ->schema($entries),
        ]);
    }
}

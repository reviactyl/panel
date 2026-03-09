<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ActivityLogWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 2;

    protected static ?int $sort = 5;

    public function form(Schema $schema): Schema
    {
        $logs = ActivityLog::query()
            ->with('actor')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        if ($logs->isEmpty()) {
            return $schema->components([
                Section::make(trans('admin/navigation.administration.activity_log'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->iconColor('primary')
                    ->schema([
                        TextEntry::make('no_data')
                            ->hiddenLabel()
                            ->state(trans('admin/navigation.administration.no_data')),
                    ]),
            ]);
        }

        $entries = $logs->map(function (ActivityLog $log, int $index) {
            $actor = $log->actor?->username ?? 'system';
            $time = $log->timestamp?->diffForHumans() ?? '—';
            $event = e($log->event);
            $ip = e($log->ip ?? '—');

            $html = '<div class="flex items-center justify-between gap-2 py-1 text-sm">'
                . '<span class="font-mono text-xs text-primary-400">' . $event . '</span>'
                . '<span class="text-gray-400">' . e($actor) . '</span>'
                . '<span class="text-gray-500 text-xs">' . $ip . '</span>'
                . '<span class="text-gray-500 text-xs shrink-0">' . $time . '</span>'
                . '</div>';

            return TextEntry::make('log_' . $index)
                ->hiddenLabel()
                ->state(new HtmlString($html));
        })->all();

        return $schema->components([
            Section::make(trans('admin/navigation.administration.activity_log'))
                ->icon('heroicon-o-clipboard-document-list')
                ->iconColor('primary')
                ->schema($entries),
        ]);
    }
}

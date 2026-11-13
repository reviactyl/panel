<?php

namespace App\Filament\Widgets;

use App\Filament\AdminAccess;
use App\Filament\Resources\ActivityLog\ActivityLogResource;
use App\Models\ActivityLog;
use App\Services\Acl\RolePermission;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return AdminAccess::allows(RolePermission::RESOURCE_ACTIVITY_LOG);
    }

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 5;

    public function form(Schema $schema): Schema
    {
        $logs = ActivityLog::query()
            ->without('subjects')
            ->with('actor')
            ->latest('timestamp')
            ->latest('id')
            ->limit(5)
            ->get(['id', 'event', 'timestamp', 'actor_type', 'actor_id']);

        $entries = [];

        foreach ($logs as $log) {
            $actorName = data_get($log->actor, 'name');
            $resolvedActorName = is_string($actorName) && $actorName !== ''
                ? $actorName
                : trans('admin/activity_log.fallback_actor');

            $entries[] = TextEntry::make("log_{$log->id}")
                ->hiddenLabel()
                ->state(
                    $resolvedActorName
                    .' — '
                    .$log->event
                    .' — '
                    .$log->timestamp->diffForHumans()
                );
        }

        $entries[] = TextEntry::make('view_more')
            ->hiddenLabel()
            ->state(trans('admin/index.more-btn').'  →')
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

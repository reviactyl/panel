<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Widgets\Widget;

class ActivityLogSummary extends Widget
{
    protected string $view = 'filament.widgets.activity-log-summary';

    protected static ?int $sort = -5;

    protected function getHeader(): ?string
    {
        return trans('admin/navigation.administration.activity_log');
    }

    /**
     * @var int
     */
    public $limit = 5;

    public function getActivities()
    {
        return ActivityLog::query()
            ->with(['actor', 'subjects'])
            ->orderByDesc('timestamp')
            ->limit($this->limit)
            ->get();
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Widgets\Widget;

class ActivityLogSummary extends Widget
{
    protected string $view = 'filament.widgets.activity-log-summary';

    protected static ?int $sort = -5;

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

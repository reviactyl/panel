<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ActivityLogResource;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;
}

<?php

namespace App\Filament\Resources\Nests\Eggs\Pages;

use App\Models\Nest;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Nests\EggResource;
use App\Filament\Resources\Nests\NestResource;

class ListEggs extends ListRecords
{
    protected static string $resource = EggResource::class;

    public function mount(): void
    {
        $nestId = request()->integer('nest');

        if ($nestId > 0 && Nest::query()->whereKey($nestId)->exists()) {
            $this->redirect(NestResource::getUrl('edit', ['record' => $nestId]), navigate: true);

            return;
        }

        $nest = Nest::query()->orderBy('id')->first();

        if ($nest !== null) {
            $this->redirect(NestResource::getUrl('edit', ['record' => $nest]), navigate: true);

            return;
        }

        $this->redirect(NestResource::getUrl('create'), navigate: true);
    }
}

<?php

namespace App\Filament\Components;

use App\Filament\Resources\Nests\Eggs\Pages\CreateEgg;
use App\Filament\Resources\Nests\Eggs\Pages\EditEgg;
use App\Models\Egg;

class EggImageUpload extends SiteImageInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->maxLength(fn (CreateEgg|EditEgg $livewire): int => $livewire->eggImageFile ? 2048 : 191)
            ->formatStateUsing(fn ($state, ?Egg $record) => $record && $state === $record->image ? $record->image_url : $state)
            ->afterStateUpdated(fn (CreateEgg|EditEgg $livewire) => $livewire->clearEggImageUpload())
            ->dehydrateStateUsing(function (?string $state, ?Egg $record, CreateEgg|EditEgg $livewire): ?string {
                if ($livewire->eggImageFile && $state === $livewire->eggImageReference) {
                    return $record?->image;
                }

                return $record && $state === $record->image_url ? $record->image : (trim($state ?? '') ?: null);
            });
    }

    public function getUploadPath(): string
    {
        return 'eggImageUpload';
    }

    public function isSquarePreview(): bool
    {
        return true;
    }
}

<?php

namespace App\Filament\Resources\Nests\Eggs\Pages;

use App\Models\Egg;
use App\Services\Eggs\EggImageService;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait HandlesEggImageUpload
{
    public ?TemporaryUploadedFile $eggImageUpload = null;

    public ?TemporaryUploadedFile $eggImageFile = null;

    public ?string $eggImageReference = null;

    public function updatedEggImageUpload(mixed $value): void
    {
        $this->authorizeAccess();
        if (! $value instanceof TemporaryUploadedFile) {
            return;
        }

        $this->resetValidation('eggImageUpload');
        try {
            app(EggImageService::class)->validate($value, 'eggImageUpload');
        } finally {
            $this->eggImageUpload = null;
        }

        $this->eggImageFile = $value;
        $this->eggImageReference = $value->isPreviewable() ? $value->temporaryUrl() : $value->getClientOriginalName();
        $this->data['image'] = $this->eggImageReference;
    }

    public function clearEggImageUpload(): void
    {
        $this->eggImageUpload = null;
        $this->eggImageFile = null;
        $this->eggImageReference = null;
        $this->resetValidation('eggImageUpload');
    }

    protected function afterSave(): void
    {
        /** @var Egg $egg */
        $egg = $this->getRecord();
        if ($this->eggImageFile && $this->data['image'] === $this->eggImageReference) {
            app(EggImageService::class)->store($egg, $this->eggImageFile);
        } elseif ($egg->wasChanged('image')) {
            DB::table('egg_images')->where('egg_id', $egg->id)->delete();
        }

        $this->clearEggImageUpload();
        $this->data['image'] = $this->getRecord()->getAttribute('image_url');
    }

    protected function afterCreate(): void
    {
        $this->afterSave();
    }
}

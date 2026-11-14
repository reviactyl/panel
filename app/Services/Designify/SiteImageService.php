<?php

namespace App\Services\Designify;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SiteImageService
{
    public static function normalizeUrl(?string $value): string
    {
        return Str::replaceStart('/public/', '/', trim($value ?? ''));
    }

    public function store(UploadedFile $file, string $attribute = 'image', ?string $label = null): string
    {
        $data = [];
        Arr::set($data, $attribute, $file);
        Validator::make($data, [
            $attribute => ['required', 'file', 'mimes:png,jpg,jpeg,gif,webp,ico,avif,bmp', 'max:20480'],
        ], attributes: [$attribute => $label ?? $attribute])->validate();

        $path = $file->storePublicly('designify', ['disk' => 'public']);
        throw_unless(is_string($path), \RuntimeException::class, 'Unable to store the uploaded image.');

        return '/storage/'.$path;
    }

    public function pruneUnusedUploads(): int
    {
        $references = Setting::query()->pluck('value')->merge(Arr::flatten([
            config('designify', []), config('app.logo'), config('app.icon'),
        ]))->filter(fn ($value) => is_string($value))->implode("\n");
        $disk = Storage::disk('public');
        $cutoff = now()->subDay()->timestamp;
        $deleted = 0;

        foreach ($disk->files('designify') as $path) {
            $filename = basename($path);
            if (! preg_match('/^[a-zA-Z0-9]{40}\.(png|jpe?g|gif|webp|ico|avif|bmp)$/D', $filename)
                || str_contains($references, $filename)
                || $disk->lastModified($path) >= $cutoff) {
                continue;
            }

            throw_unless($disk->delete($path), \RuntimeException::class, 'Unable to delete an unused Designify image.');
            $deleted++;
        }

        return $deleted;
    }
}

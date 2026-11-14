<?php

namespace App\Services\Designify;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
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
}

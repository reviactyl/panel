<?php

namespace App\Services\Eggs;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class EggImageService
{
    public function encode(UploadedFile $file): string
    {
        $this->validate($file);

        return 'data:'.$file->getMimeType().';base64,'.base64_encode($file->getContent());
    }

    public function validate(UploadedFile $file, string $attribute = 'image'): void
    {
        Validator::make([$attribute => $file], [
            $attribute => ['required', 'file', 'mimetypes:image/*', 'max:10240'],
        ], attributes: [$attribute => trans('admin/eggs.fields.image')])->validate();
    }
}

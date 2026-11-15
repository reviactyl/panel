<?php

namespace App\Services\Eggs;

use App\Models\Egg;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EggImageService
{
    public function store(Egg $egg, UploadedFile $file): void
    {
        $data = $this->encode($file);
        $path = route('egg-image', ['egg' => $egg->uuid, 'version' => hash('xxh128', $data)], false);

        DB::transaction(function () use ($egg, $data, $path): void {
            DB::table('egg_images')->updateOrInsert(['egg_id' => $egg->id], ['data' => $data]);
            $egg->update(['image' => $path]);
        });
    }

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

<?php

namespace App\Services\Eggs;

use App\Exceptions\Service\InvalidFileUploadException;
use App\Models\Egg;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EggImageService
{
    public function store(Egg $egg, UploadedFile $file): void
    {
        $this->storeData($egg, $this->encode($file));
    }

    public function export(Egg $egg): ?string
    {
        return str_starts_with($egg->image ?? '', '/images/eggs/'.$egg->uuid.'/')
            ? DB::table('egg_images')->where('egg_id', $egg->id)->value('data')
            : $egg->image;
    }

    public function restore(Egg $egg, string $data): void
    {
        $tooLarge = trans('validation.max.file', ['attribute' => trans('admin/eggs.fields.image'), 'max' => 10240]);
        if (strlen($data) > 4 * ceil(10 * 1024 * 1024 / 3) + 128) {
            throw new InvalidFileUploadException($tooLarge);
        }
        if (! preg_match('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', $data, $matches)) {
            throw new InvalidFileUploadException(trans('admin/catalog.errors.definition'));
        }

        $contents = base64_decode(substr($data, strlen($matches[0])), true);
        if ($contents === false || ! str_starts_with($mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: '', 'image/')) {
            throw new InvalidFileUploadException(trans('admin/catalog.errors.definition'));
        }
        if (strlen($contents) > 10 * 1024 * 1024) {
            throw new InvalidFileUploadException($tooLarge);
        }

        $this->storeData($egg, 'data:'.$mime.';base64,'.base64_encode($contents));
    }

    private function storeData(Egg $egg, string $data): void
    {
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

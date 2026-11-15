<?php

namespace App\Services\Eggs\Sharing;

use App\Exceptions\Service\InvalidFileUploadException;
use App\Models\Egg;
use App\Models\EggVariable;
use App\Models\Nest;
use App\Services\Eggs\EggImageService;
use App\Services\Eggs\EggParserService;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Ramsey\Uuid\Uuid;

class EggImporterService
{
    public function __construct(protected ConnectionInterface $connection, protected EggParserService $parser, protected EggImageService $images) {}

    /**
     * Take an uploaded JSON file and parse it into a new egg.
     *
     * @throws InvalidFileUploadException|\Throwable
     */
    public function handle(UploadedFile $file, int $nest): Egg
    {
        $parsed = $this->parser->handle($file);

        /** @var Nest $nest */
        $nest = Nest::query()->with('eggs', 'eggs.variables')->findOrFail($nest);

        return $this->connection->transaction(function () use ($nest, $parsed) {
            $image = Arr::get($parsed, 'image');
            $embeddedImage = is_string($image) && str_starts_with($image, 'data:');
            $egg = (new Egg())->forceFill([
                'creation_source' => 'Imported',
                'uuid' => Uuid::uuid4()->toString(),
                'nest_id' => $nest->id,
                'author' => Arr::get($parsed, 'author'),
                'image' => $embeddedImage ? null : $image,
                'copy_script_from' => null,
            ]);

            $egg = $this->parser->fillFromParsed($egg, $parsed);
            $egg->save();
            if ($embeddedImage) {
                $this->images->restore($egg, $image);
            }

            foreach ($parsed['variables'] ?? [] as $variable) {
                EggVariable::query()->forceCreate(array_merge(Arr::only($variable, ['name', 'description', 'env_variable', 'default_value', 'user_viewable', 'user_editable', 'is_secret', 'rules']), ['egg_id' => $egg->id]));
            }

            return $egg;
        });
    }
}

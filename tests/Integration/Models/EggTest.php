<?php

namespace Tests\Integration\Models;

use App\Exceptions\Service\InvalidFileUploadException;
use App\Models\Egg;
use App\Models\EggVariable;
use App\Models\Nest;
use App\Models\User;
use App\Services\Eggs\EggConfigurationService;
use App\Services\Eggs\EggCreationService;
use App\Services\Eggs\EggImageService;
use App\Services\Eggs\Sharing\EggExporterService;
use App\Services\Eggs\Sharing\EggImporterService;
use App\Services\Eggs\Sharing\EggUpdateImporterService;
use App\Services\Servers\ServerConfigurationStructureService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class EggTest extends IntegrationTestCase
{
    #[DataProvider('uploadedImageFormats')]
    public function test_uploaded_images_survive_export_and_import_without_the_source_egg(string $format, string $mime): void
    {
        $this->actingAs(User::factory()->create());
        $source = $this->cloneEggAndVariables($this->createServerModel()->egg);
        $contents = match ($format) {
            'png' => str_pad(file_get_contents(public_path('reviactyl/icon.png')), 10 * 1024 * 1024, "\0"),
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h10v10H0z"/></svg>',
            'gif' => base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'),
        };
        app(EggImageService::class)->store($source, UploadedFile::fake()->createWithContent('custom.'.$format, $contents));
        $export = app(EggExporterService::class)->handle($source->id);
        $parsed = json_decode($export, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(hash('sha256', 'data:'.$mime.';base64,'.base64_encode($contents)), hash('sha256', $parsed['image']));
        $source->delete();

        $imported = app(EggImporterService::class)->handle(
            UploadedFile::fake()->createWithContent('egg.json', $export), Nest::factory()->create()->id
        );
        $this->assertNotSame($source->uuid, $imported->uuid);
        $this->assertLessThan(10000, strlen($imported->toJson()));
        $this->assertSame($contents, $this->get($imported->image_url)->assertOk()->assertHeader('Content-Type', $mime)->getContent());
        $this->assertSame($parsed['image'], json_decode(app(EggExporterService::class)->handle($imported->id), true)['image']);

        $updated = $this->cloneEggAndVariables($this->createServerModel()->egg);
        app(EggUpdateImporterService::class)->handle($updated, UploadedFile::fake()->createWithContent('egg.json', $export));
        $this->assertSame($contents, $this->get($updated->image_url)->assertOk()->getContent());
    }

    public static function uploadedImageFormats(): array
    {
        return [['png', 'image/png'], ['svg', 'image/svg+xml'], ['gif', 'image/gif']];
    }

    #[DataProvider('invalidEmbeddedImageModes')]
    public function test_invalid_embedded_images_do_not_change_stored_eggs(string $kind, bool $update): void
    {
        $egg = $this->cloneEggAndVariables($this->createServerModel()->egg);
        $contents = file_get_contents(public_path('reviactyl/icon.png'));
        app(EggImageService::class)->store($egg, UploadedFile::fake()->createWithContent('original.png', $contents));
        $originalPath = $egg->image;
        $originalName = $egg->name;
        $count = Egg::query()->count();
        $parsed = json_decode(app(EggExporterService::class)->handle($egg->id), true);
        $parsed['name'] = 'Invalid image import';
        $parsed['image'] = match ($kind) {
            'invalid-base64' => 'data:image/png;base64,%%%invalid',
            'non-image' => 'data:image/png;base64,'.base64_encode('not an image'),
            'non-image-mime' => 'data:text/html;base64,'.base64_encode('<script>alert(1)</script>'),
            'too-large' => 'data:image/png;base64,'.base64_encode(str_pad($contents, 10 * 1024 * 1024 + 1, "\0")),
        };
        $file = UploadedFile::fake()->createWithContent('egg.json', json_encode($parsed));

        try {
            if ($update) {
                app(EggUpdateImporterService::class)->handle($egg, $file);
            } else {
                app(EggImporterService::class)->handle($file, $egg->nest_id);
            }
            $this->fail('Invalid embedded images must be rejected.');
        } catch (InvalidFileUploadException) {
            $this->assertDatabaseCount('eggs', $count);
            $this->assertSame($originalName, $egg->fresh()->name);
            $this->assertSame($originalPath, $egg->fresh()->image);
            $this->assertSame('data:image/png;base64,'.base64_encode($contents), DB::table('egg_images')->where('egg_id', $egg->id)->value('data'));
        }
    }

    public static function invalidEmbeddedImageModes(): array
    {
        $cases = [];
        foreach (['invalid-base64', 'non-image', 'non-image-mime', 'too-large'] as $kind) {
            foreach ([false, true] as $update) {
                $cases[$kind.($update ? '-update' : '-create')] = [$kind, $update];
            }
        }

        return $cases;
    }

    public function test_configuration_resolves_through_inheritance_chains(): void
    {
        $server = $this->createServerModel();
        $parent = $server->egg;
        $parent->forceFill([
            'config_files' => '{"server.properties":{"parser":"properties","find":{"port":"{{server.build.default.port}}"}}}',
            'config_startup' => '{"done":"ready"}',
            'config_logs' => '{"custom":true}',
            'config_stop' => '^SIGTERM',
            'features' => ['eula'],
            'file_denylist' => ['secret.txt'],
        ])->save();

        $child = $this->createInheritedEgg($parent);
        $grandchild = $this->createInheritedEgg($child);
        $server->forceFill(['egg_id' => $grandchild->id])->save();
        $server->refresh();

        foreach (['config_files', 'config_startup', 'config_logs', 'config_stop', 'features', 'file_denylist'] as $field) {
            $this->assertNull($grandchild->{$field});
            $this->assertSame($parent->{$field}, $grandchild->{'inherit_'.$field});
        }

        $configuration = $this->app->make(EggConfigurationService::class)->handle($server);
        $this->assertSame(['ready'], $configuration['startup']['done']);
        $this->assertSame(['type' => 'signal', 'value' => 'SIGTERM'], $configuration['stop']);
        $this->assertSame('server.properties', $configuration['configs'][0]['file']);
        $this->assertSame((string) $server->allocation->port, $configuration['configs'][0]['replace'][0]['replace_with']);
        $this->assertSame(['secret.txt'], $this->app->make(ServerConfigurationStructureService::class)->handle($server)['egg']['file_denylist']);
    }

    public function test_nearest_configuration_overrides_are_preserved(): void
    {
        $parent = $this->createServerModel()->egg;
        $child = $this->createInheritedEgg($parent);
        $child->forceFill([
            'config_files' => '{}',
            'config_startup' => '{"done":"child ready"}',
            'config_logs' => '{}',
            'config_stop' => 'quit',
            'features' => [],
            'file_denylist' => ['ignored-child.txt'],
        ])->save();
        $grandchild = $this->createInheritedEgg($child);

        foreach (['config_files', 'config_startup', 'config_logs', 'config_stop', 'features'] as $field) {
            $this->assertSame($child->{$field}, $grandchild->{'inherit_'.$field});
        }

        $grandchild->config_stop = 'exit';
        $this->assertSame('exit', $grandchild->inherit_config_stop);
        $this->assertSame($parent->file_denylist, $grandchild->inherit_file_denylist);
    }

    public function test_circular_inheritance_fails_without_unbounded_traversal(): void
    {
        $parent = $this->createServerModel()->egg;
        $child = $this->createInheritedEgg($parent);
        $parent->forceFill(['config_from' => $child->id, 'config_files' => null])->save();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Egg configuration inheritance contains a cycle.');

        $child->inherit_config_files;
    }

    #[DataProvider('importModes')]
    public function test_import_rejects_duplicate_names_without_changing_stored_eggs(bool $update): void
    {
        $egg = $this->createServerModel()->egg;
        $parsed = json_decode(app(EggExporterService::class)->handle($egg->id), true, flags: JSON_THROW_ON_ERROR);
        $parsed['name'] = 'Imported egg';
        $parsed['variables'][] = array_merge($parsed['variables'][0], ['default_value' => 'duplicate']);
        $eggCount = Egg::query()->count();
        $variables = EggVariable::query()->orderBy('id')->get()->toArray();
        $originalName = $egg->name;
        $file = UploadedFile::fake()->createWithContent('egg.json', json_encode($parsed, JSON_THROW_ON_ERROR));

        try {
            if ($update) {
                app(EggUpdateImporterService::class)->handle($egg, $file);
            } else {
                app(EggImporterService::class)->handle($file, $egg->nest_id);
            }
            $this->fail('An import with duplicate environment names must be rejected.');
        } catch (InvalidFileUploadException $exception) {
            $this->assertSame(trans('exceptions.service.variables.env_not_unique', ['name' => $parsed['variables'][0]['env_variable']]), $exception->getMessage());
        }

        $this->assertDatabaseCount('eggs', $eggCount);
        $this->assertSame($originalName, $egg->fresh()->name);
        $this->assertSame($variables, EggVariable::query()->orderBy('id')->get()->toArray());
    }

    #[DataProvider('importModes')]
    public function test_import_accepts_unique_environment_names(bool $update): void
    {
        $egg = $this->createServerModel()->egg;
        $parsed = json_decode(app(EggExporterService::class)->handle($egg->id), true, flags: JSON_THROW_ON_ERROR);
        $parsed['name'] = 'Imported egg';
        $file = UploadedFile::fake()->createWithContent('egg.json', json_encode($parsed, JSON_THROW_ON_ERROR));
        $imported = $update
            ? app(EggUpdateImporterService::class)->handle($egg, $file)
            : app(EggImporterService::class)->handle($file, $egg->nest_id);

        $this->assertSame('Imported egg', $imported->name);
        $this->assertSame(array_column($parsed['variables'], 'env_variable'), $imported->variables()->orderBy('id')->pluck('env_variable')->all());
    }

    public static function importModes(): array
    {
        return [[false], [true]];
    }

    private function createInheritedEgg(Egg $parent): Egg
    {
        return $this->app->make(EggCreationService::class)->handle([
            'nest_id' => $parent->nest_id,
            'name' => 'Inherited '.$parent->id,
            'docker_images' => $parent->docker_images,
            'startup' => $parent->startup,
            'config_from' => $parent->id,
        ]);
    }
}

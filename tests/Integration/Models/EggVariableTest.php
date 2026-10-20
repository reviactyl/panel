<?php

namespace Tests\Integration\Models;

use App\Exceptions\DisplayException;
use App\Models\EggVariable;
use App\Services\Eggs\Sharing\EggExporterService;
use App\Services\Eggs\Sharing\EggImporterService;
use App\Services\Eggs\Sharing\EggUpdateImporterService;
use App\Services\Eggs\Variables\VariableCreationService;
use App\Services\Eggs\Variables\VariableUpdateService;
use App\Transformers\Api\Client\EggVariableTransformer;
use Illuminate\Http\UploadedFile;
use Tests\Integration\IntegrationTestCase;

class EggVariableTest extends IntegrationTestCase
{
    public function test_creation_rejects_duplicate_names_within_the_same_egg(): void
    {
        $server = $this->createServerModel();
        $variable = $server->egg->variables->first();
        $data = $variable->only(['name', 'description', 'env_variable', 'default_value', 'rules']);
        $otherEgg = $this->cloneEggAndVariables($server->egg);
        $otherEgg->variables()->delete();
        $created = app(VariableCreationService::class)->handle($otherEgg->id, $data);
        $this->assertSame($variable->env_variable, $created->env_variable);

        $this->expectException(DisplayException::class);
        $this->expectExceptionMessage(trans('exceptions.service.variables.env_not_unique', ['name' => $variable->env_variable]));
        app(VariableCreationService::class)->handle($server->egg_id, $data);
    }

    public function test_secret_metadata_round_trips_through_egg_files(): void
    {
        $server = $this->createServerModel();
        $egg = $this->cloneEggAndVariables($server->egg);
        $variable = $egg->variables()->firstOrFail();
        $variable->update(['is_secret' => true]);
        $exporter = app(EggExporterService::class);
        $data = json_decode($exporter->handle($egg->id), true);
        $this->assertTrue($data['variables'][0]['is_secret']);
        $upload = fn (array $data) => UploadedFile::fake()->createWithContent('egg.json', json_encode($data));
        $import = app(EggImporterService::class)->handle($upload($data), $egg->nest_id);
        $this->assertTrue($import->variables()->firstOrFail()->is_secret);
        $updater = app(EggUpdateImporterService::class);
        $data['variables'][0]['is_secret'] = false;
        $updater->handle($import, $upload($data));
        $this->assertFalse($import->variables()->firstOrFail()->is_secret);
        $data['variables'][0]['is_secret'] = true;
        $updater->handle($import, $upload($data));
        $this->assertTrue($import->variables()->firstOrFail()->is_secret);
        unset($data['variables'][0]['is_secret']);
        $updater->handle($import, $upload($data));
        $this->assertFalse($import->variables()->firstOrFail()->is_secret);
    }

    public function test_secret_service_input_normalizes_boolean_strings(): void
    {
        $server = $this->createServerModel();
        $data = ['name' => 'Secret', 'env_variable' => 'SECRET_TEST', 'rules' => 'nullable|string', 'is_secret' => 'false'];
        $variable = app(VariableCreationService::class)->handle($server->egg_id, $data);
        $this->assertFalse($variable->is_secret);
        $data['is_secret'] = 'true';
        app(VariableUpdateService::class)->handle($variable, $data);
        $this->assertTrue($variable->fresh()->is_secret);
    }

    public function test_null_secret_default_is_not_reported_as_set(): void
    {
        $variable = new EggVariable(['user_viewable' => true, 'is_secret' => true, 'default_value' => null]);
        $result = app(EggVariableTransformer::class)->transform($variable);
        $this->assertFalse($result['has_value']);
    }

    public function test_egg_relationship(): void
    {
        $server = $this->createServerModel();
        $variable = EggVariable::query()->where('egg_id', $server->egg_id)->firstOrFail();

        $this->assertTrue($variable->egg->is($server->egg));
    }
}

<?php

namespace Tests\Unit\Services\Eggs;

use App\Exceptions\Service\InvalidFileUploadException;
use App\Models\Egg;
use App\Services\Eggs\EggParserService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EggParserServiceTest extends TestCase
{
    private EggParserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EggParserService();
    }

    public function test_it_keeps_the_docker_images_of_a_ptdl_v2_egg()
    {
        $parsed = $this->parse([
            'meta' => ['version' => 'PTDL_v2', 'update_url' => null],
            'docker_images' => [
                'Python 3.12' => 'ghcr.io/parkervcp/yolks:python_3.12',
                'Python 3.11' => 'ghcr.io/parkervcp/yolks:python_3.11',
            ],
        ]);

        $this->assertEquals([
            'Python 3.12' => 'ghcr.io/parkervcp/yolks:python_3.12',
            'Python 3.11' => 'ghcr.io/parkervcp/yolks:python_3.11',
        ], $parsed['docker_images']);
    }

    public function test_it_keeps_the_docker_images_of_a_current_export()
    {
        $parsed = $this->parse([
            'meta' => ['version' => Egg::EXPORT_VERSION, 'update_url' => null],
            'docker_images' => ['Python 3.12' => 'ghcr.io/parkervcp/yolks:python_3.12'],
        ]);

        $this->assertEquals(['Python 3.12' => 'ghcr.io/parkervcp/yolks:python_3.12'], $parsed['docker_images']);
    }

    public function test_it_converts_the_single_image_of_a_ptdl_v1_egg()
    {
        $parsed = $this->parse([
            'meta' => ['version' => 'PTDL_v1', 'update_url' => null],
            'image' => 'quay.io/pterodactyl/core:python',
        ]);

        $this->assertEquals([
            'quay.io/pterodactyl/core:python' => 'quay.io/pterodactyl/core:python',
        ], $parsed['docker_images']);
        $this->assertArrayNotHasKey('image', $parsed);
    }

    public function test_it_converts_the_image_list_of_a_ptdl_v1_egg()
    {
        $parsed = $this->parse([
            'meta' => ['version' => 'PTDL_v1', 'update_url' => null],
            'images' => ['quay.io/pterodactyl/core:python', 'quay.io/pterodactyl/core:python-3.8'],
        ]);

        $this->assertEquals([
            'quay.io/pterodactyl/core:python' => 'quay.io/pterodactyl/core:python',
            'quay.io/pterodactyl/core:python-3.8' => 'quay.io/pterodactyl/core:python-3.8',
        ], $parsed['docker_images']);
        $this->assertArrayNotHasKey('images', $parsed);
    }

    public function test_it_falls_back_to_nil_for_a_ptdl_v1_egg_without_an_image()
    {
        $parsed = $this->parse(['meta' => ['version' => 'PTDL_v1', 'update_url' => null]]);

        $this->assertEquals(['nil' => 'nil'], $parsed['docker_images']);
    }

    public function test_it_rejects_an_unknown_egg_version()
    {
        $this->expectException(InvalidFileUploadException::class);

        $this->parse(['meta' => ['version' => 'PTDL_v9', 'update_url' => null]]);
    }

    public function test_it_can_always_import_the_version_it_exports()
    {
        $this->assertContains(Egg::EXPORT_VERSION, Egg::VERSIONS);
    }

    public function test_it_can_still_import_every_previous_egg_version()
    {
        foreach (['PTDL_v1', 'PTDL_v2', 'RCYL_v26'] as $version) {
            $this->assertContains($version, Egg::VERSIONS);
        }
    }

    /**
     * Parses an egg, filling in the fields that every version shares.
     *
     * @throws InvalidFileUploadException|\JsonException
     */
    private function parse(array $egg): array
    {
        $path = tempnam(sys_get_temp_dir(), 'egg').'.json';
        file_put_contents($path, json_encode(array_merge([
            'exported_at' => '2024-04-02T14:11:47+02:00',
            'name' => 'python generic',
            'author' => 'parker@parkervcp.com',
            'description' => 'A Generic Python Egg',
            'features' => null,
            'file_denylist' => [],
            'startup' => '/usr/local/bin/python /home/container/{{PY_FILE}}',
            'config' => ['files' => '{}', 'startup' => '{}', 'logs' => '{}', 'stop' => '^C'],
            'scripts' => ['installation' => ['script' => 'exit 0', 'container' => 'python:3.8-slim-bookworm', 'entrypoint' => 'bash']],
            'variables' => [[
                'name' => 'App py file',
                'description' => 'The file that starts the App.',
                'env_variable' => 'PY_FILE',
                'default_value' => 'app.py',
                'user_viewable' => true,
                'user_editable' => true,
                'rules' => 'required|string',
            ]],
        ], $egg), JSON_THROW_ON_ERROR));

        try {
            return $this->service->handle(new UploadedFile($path, 'egg.json', 'application/json', null, true));
        } finally {
            @unlink($path);
        }
    }
}

<?php

namespace Tests\Integration\Scramble;

use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Tests\TestCase;

class FractalResponseInferenceTest extends TestCase
{
    public function test_notification_docs_have_titles_distinct_write_schemas_and_response_fields(): void
    {
        $config = Scramble::configure();
        $previousRoutes = $config->routes();
        $config->routes(fn (Route $route) => str_contains($route->uri(), 'notifications'));
        try {
            $spec = app(Generator::class)->generate($config)->spec();
        } finally {
            $config->routes($previousRoutes);
        }
        $count = 0;
        foreach ($spec['paths'] as $operations) {
            foreach ($operations as $operation) {
                $this->assertNotEmpty($operation['summary']);
                $count++;
            }
        }
        $this->assertSame(11, $count);
        $create = $spec['components']['schemas']['CreateNotificationRequest'];
        $update = $spec['components']['schemas']['UpdateNotificationRequest'];
        $this->assertContains('title', $create['required']);
        $this->assertNotContains('title', $update['required'] ?? []);
        foreach (['title', 'body', 'url', 'status', 'extension', 'read'] as $field) {
            $this->assertArrayHasKey($field, $create['properties']);
            $this->assertArrayHasKey($field, $update['properties']);
        }
        $path = $spec['paths']['/application/users/{user}/notifications'];
        $this->assertStringEndsWith('/CreateNotificationRequest', $path['post']['requestBody']['content']['application/json']['schema']['$ref']);
        $single = $spec['paths']['/application/users/{user}/notifications/{notification}'];
        $this->assertStringEndsWith('/UpdateNotificationRequest', $single['patch']['requestBody']['content']['application/json']['schema']['$ref']);
        $this->assertArrayNotHasKey('requestBody', $single['delete']);
        $attributes = $path['post']['responses']['201']['content']['application/json']['schema']['properties']['attributes'];
        $this->assertSame('object', $attributes['properties']['data']['type']);
        foreach (['source', 'event', 'title', 'body', 'url', 'status', 'extension'] as $field) {
            $this->assertArrayHasKey($field, $attributes['properties']['data']['properties']);
        }
        foreach (['id', 'data', 'read_at', 'created_at'] as $field) {
            $this->assertArrayHasKey($field, $attributes['properties']);
        }
    }

    public function test_application_fractal_responses_include_the_serializer_envelope_and_transformer_fields(): void
    {
        $config = Scramble::configure();
        $previousRoutes = $config->routes();
        $config->routes(fn (Route $route) => in_array($route->getName(), [
            'api.application.nests',
            'api.application.nests.eggs',
            'api.application.nests.eggs.view',
            'api.application.users.view',
            'api:client.index',
        ], true));

        try {
            $spec = app(Generator::class)->generate($config)->spec();
        } finally {
            $config->routes($previousRoutes);
        }

        $item = $spec['paths']['/application/nests/{nest}/eggs/{egg}']['get']['responses']['200']['content']['application/json']['schema'];
        $this->assertSame('object', $item['type']);
        $this->assertContains('object', $item['required']);
        $this->assertContains('attributes', $item['required']);
        $this->assertSame('object', $item['properties']['attributes']['type']);
        $this->assertArrayHasKey('id', $item['properties']['attributes']['properties']);
        $this->assertArrayHasKey('uuid', $item['properties']['attributes']['properties']);
        $imageType = $item['properties']['attributes']['properties']['image']['type'];
        $this->assertContains('string', (array) $imageType);
        $this->assertTrue(
            $imageType === 'string' || $imageType === ['string', 'null'],
            'The image field should be documented as a string, nullable when Scramble infers nullability.',
        );

        $collection = $spec['paths']['/application/nests/{nest}/eggs']['get']['responses']['200']['content']['application/json']['schema'];
        $this->assertSame('list', $collection['properties']['object']['const'] ?? null);
        $this->assertSame('array', $collection['properties']['data']['type']);
        $this->assertSame('object', $collection['properties']['data']['items']['properties']['attributes']['type']);
        $this->assertArrayHasKey('docker_images', $collection['properties']['data']['items']['properties']['attributes']['properties']);

        $paginated = $spec['paths']['/application/nests']['get']['responses']['200']['content']['application/json']['schema'];
        $this->assertArrayHasKey('meta', $paginated['properties']);
        $this->assertArrayHasKey('pagination', $paginated['properties']['meta']['properties']);

        $clientOperation = collect($spec['paths'])->first(
            fn (array $path) => ($path['get']['operationId'] ?? null) === 'api:client.index',
        );
        $this->assertNotNull($clientOperation);
        $client = $clientOperation['get']['responses']['200']['content']['application/json']['schema'];
        $this->assertSame('array', $client['properties']['data']['type']);
        $this->assertArrayHasKey(
            'identifier',
            $client['properties']['data']['items']['properties']['attributes']['properties'],
        );
        $this->assertArrayHasKey('pagination', $client['properties']['meta']['properties']);

        $user = $spec['paths']['/application/users/{user}']['get']['responses']['200']['content']['application/json']['schema'];
        $userAttributes = $user['properties']['attributes'];
        $this->assertArrayHasKey('relationships', $userAttributes['properties']);
        $this->assertNotContains('relationships', $userAttributes['required']);

        $relationships = $userAttributes['properties']['relationships'];
        $this->assertArrayHasKey('servers', $relationships['properties']);
        $this->assertNotContains('servers', $relationships['required'] ?? []);
        $serverVariants = $relationships['properties']['servers']['anyOf'] ?? [];
        $this->assertTrue(collect($serverVariants)->contains(
            fn (array $variant) => ($variant['properties']['object']['const'] ?? null) === 'list'
                && isset($variant['properties']['data']['items']['properties']['attributes']),
        ));
    }
}

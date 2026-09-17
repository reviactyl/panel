<?php

namespace Tests\Integration;

use App\Events\ActivityLogged;
use App\Transformers\Api\Application\BaseTransformer;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Event;
use Tests\Assertions\AssertsActivityLogged;
use Tests\TestCase;
use Tests\Traits\Integration\CreatesTestModels;

abstract class IntegrationTestCase extends TestCase
{
    use AssertsActivityLogged;
    use CreatesTestModels;
    use DatabaseTruncation;

    protected $defaultHeaders = [
        'Accept' => 'application/json',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Event::fake(ActivityLogged::class);
    }

    /**
     * Return an ISO-8601 formatted timestamp to use in the API response.
     */
    protected function formatTimestamp(string $timestamp): string
    {
        return CarbonImmutable::createFromFormat(CarbonInterface::DEFAULT_TO_STRING_FORMAT, $timestamp)
            ->setTimezone(BaseTransformer::RESPONSE_TIMEZONE)
            ->toAtomString();
    }

    /**
     * Return the database connections that should be wrapped in a transaction for each test.
     *
     * @return array
     */
    protected function connectionsToTransact()
    {
        return [DB::getDriverName()];
    }
}

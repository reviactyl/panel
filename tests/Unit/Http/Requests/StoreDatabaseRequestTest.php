<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Api\Client\Servers\Databases\StoreDatabaseRequest;
use App\Models\Server;
use Illuminate\Routing\Route;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StoreDatabaseRequestTest extends TestCase
{
    #[DataProvider('databaseNames')]
    public function test_database_naming_matches_the_creation_form(string $name, bool $valid): void
    {
        $route = $this->createMock(Route::class);
        $route->method('parameter')->with('server')->willReturn(new Server());
        $request = new StoreDatabaseRequest();
        $request->setRouteResolver(fn () => $route);
        $rules = array_filter($request->rules()['database'], is_string(...));
        $factory = new Factory(new Translator(new ArrayLoader(), 'en'));

        $this->assertSame($valid, $factory->make(['database' => $name], ['database' => $rules])->passes());
    }

    public static function databaseNames(): array
    {
        return array_map(
            fn (array $case) => [$case['name'], $case['valid']],
            json_decode(file_get_contents(__DIR__.'/../../../Fixtures/database-names.json'), true, flags: JSON_THROW_ON_ERROR),
        );
    }
}

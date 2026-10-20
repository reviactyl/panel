<?php

namespace App\Http\Controllers\Api\Client\Servers;

use App\Exceptions\Model\DataValidationException;
use App\Exceptions\Repository\RecordNotFoundException;
use App\Facades\Activity;
use App\Http\Controllers\Api\Client\ClientApiController;
use App\Http\Requests\Api\Client\Servers\Startup\GetStartupRequest;
use App\Http\Requests\Api\Client\Servers\Startup\UpdateStartupVariableRequest;
use App\Models\Server;
use App\Repositories\Eloquent\ServerVariableRepository;
use App\Services\Servers\StartupCommandService;
use App\Transformers\Api\Client\EggVariableTransformer;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StartupController extends ClientApiController
{
    /**
     * StartupController constructor.
     */
    public function __construct(
        private StartupCommandService $startupCommandService,
        private ServerVariableRepository $repository,
    ) {
        parent::__construct();
    }

    /**
     * Returns the startup information for the server including all the variables.
     */
    public function index(GetStartupRequest $request, Server $server): array
    {
        $startup = $this->startupCommandService->handle($server);

        return $this->fractal->collection(
            $server->variables->keyBy('env_variable')->filter(fn ($variable) => $variable->user_viewable)->values()
        )
            ->transformWith($this->getTransformer(EggVariableTransformer::class))
            ->addMeta([
                'startup_command' => $startup,
                'docker_images' => $server->egg->docker_images,
                'raw_startup_command' => $server->startup,
            ])
            ->toArray();
    }

    /**
     * Updates a single variable for a server.
     *
     * @throws ValidationException
     * @throws DataValidationException
     * @throws RecordNotFoundException
     */
    public function update(UpdateStartupVariableRequest $request, Server $server): array
    {
        $variable = $server->variables->last(fn ($variable) => $variable->env_variable === $request->input('key'));

        if (is_null($variable) || ! $variable->user_viewable) {
            throw new BadRequestHttpException('The environment variable you are trying to edit does not exist.');
        } elseif (! $variable->user_editable) {
            throw new BadRequestHttpException('The environment variable you are trying to edit is read-only.');
        }

        $original = $variable->server_value;

        // Revalidate the variable value using the egg variable specific validation rules for it.
        $this->validate($request, ['value' => $variable->rules]);

        $this->repository->updateOrCreate([
            'server_id' => $server->id,
            'variable_id' => $variable->id,
        ], [
            'variable_value' => $request->input('value') ?? '',
        ]);

        $variable = $variable->refresh();
        $variable->server_value = $variable->is_secret ? ($request->input('value') ?? '') : $request->input('value');

        $server->unsetRelation('variables');
        $startup = $this->startupCommandService->handle($server);

        if ($original !== $request->input('value')) {
            Activity::event('server:startup.edit')
                ->subject($variable)
                ->property([
                    'variable' => $variable->env_variable,
                    'old' => $variable->is_secret ? '[hidden]' : $original,
                    'new' => $variable->is_secret ? '[hidden]' : ($request->input('value') ?? ''),
                ])
                ->log();
        }

        return $this->fractal->item($variable)
            ->transformWith($this->getTransformer(EggVariableTransformer::class))
            ->addMeta([
                'startup_command' => $startup,
                'raw_startup_command' => $server->startup,
            ])
            ->toArray();
    }
}

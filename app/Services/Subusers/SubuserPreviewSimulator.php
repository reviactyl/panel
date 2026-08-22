<?php

namespace App\Services\Subusers;

use App\Models\Permission;
use App\Models\Server;
use App\Models\Task;
use App\Repositories\Agent\DaemonFileRepository;
use App\Transformers\Api\Client\FileObjectTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubuserPreviewSimulator
{
    public function __construct(
        private readonly DaemonFileRepository $fileRepository,
        private readonly SubuserPreviewResourceSimulator $resourceSimulator,
        private readonly SubuserPreviewStateService $stateService,
    ) {}

    public function handle(Request $request, \Closure $next, SubuserPreviewContext $context): mixed
    {
        $serverParameter = $request->route()?->parameter('server');
        if ($serverParameter === null) {
            if ($request->is('api/client/subuser-preview*')) {
                return $next($request);
            }

            if ($request->isMethodSafe() && $request->is([
                'api/client',
                'api/client/permissions',
                'api/client/eggs',
                'api/client/extensions',
            ])) {
                return $this->resourceSimulator->readGlobal($request, $next, $context);
            }

            throw new AccessDeniedHttpException(trans('exceptions.subuser_preview.resource_unavailable'));
        }

        $isPreviewServer = $serverParameter instanceof Server
            ? $context->isServer($serverParameter)
            : $context->isServerIdentifier((string) $serverParameter);

        if (! $isPreviewServer) {
            throw new NotFoundHttpException(trans('exceptions.api.resource_not_found'));
        }

        $path = '/'.ltrim($request->route()?->uri() ?? '', '/');

        if ($request->isMethodSafe()) {
            return $this->handleRead($request, $next, $context, $path);
        }

        return $this->handleWrite($request, $context, $path);
    }

    private function handleRead(Request $request, \Closure $next, SubuserPreviewContext $context, string $path): mixed
    {
        if (str_ends_with($path, '/files/upload')) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.live_connection_unavailable'));
        }

        if (str_ends_with($path, '/files/contents')) {
            $this->authorize($context, Permission::ACTION_FILE_READ_CONTENT);
            $file = $this->normalizePath((string) $request->query('file'));
            $entry = ($context->session()->state ?? [])['files'][$file] ?? null;

            if (is_array($entry)) {
                if (($entry['deleted'] ?? false) === true || ($entry['is_file'] ?? true) === false) {
                    throw new NotFoundHttpException(trans('exceptions.subuser_preview.file_not_found'));
                }

                $content = array_key_exists('content', $entry)
                    ? $this->decodeContent($entry)
                    : $this->fileRepository
                        ->setServer($context->session()->server)
                        ->getContent((string) ($entry['source_path'] ?? $file), config('panel.files.max_edit_size'));

                return new Response($content, 200, ['Content-Type' => 'text/plain']);
            }

            $content = $this->fileRepository
                ->setServer($context->session()->server)
                ->getContent($file, config('panel.files.max_edit_size'));

            return new Response($content, 200, ['Content-Type' => 'text/plain']);
        }

        if (str_ends_with($path, '/files/download')) {
            $this->authorize($context, Permission::ACTION_FILE_READ_CONTENT);
            $file = $this->normalizePath((string) $request->query('file'));
            $entry = ($context->session()->state ?? [])['files'][$file] ?? null;
            if (is_array($entry)) {
                if (($entry['deleted'] ?? false) === true || ($entry['is_file'] ?? true) === false) {
                    throw new NotFoundHttpException(trans('exceptions.subuser_preview.file_not_found'));
                }

                $content = array_key_exists('content', $entry)
                    ? $this->decodeContent($entry)
                    : $this->fileRepository
                        ->setServer($context->session()->server)
                        ->getContent((string) ($entry['source_path'] ?? $file));

                return response()->json([
                    'object' => 'signed_url',
                    'attributes' => ['url' => 'data:application/octet-stream;base64,'.base64_encode($content)],
                ]);
            }
        }

        if (str_ends_with($path, '/backups/{backup}/download')) {
            $this->authorize($context, Permission::ACTION_BACKUP_DOWNLOAD);
            $backup = (string) $request->route('backup');
            if (array_key_exists($backup, ($context->session()->state ?? [])['resources']['backups'] ?? [])) {
                return response()->json([
                    'object' => 'signed_url',
                    'attributes' => ['url' => 'data:application/octet-stream;base64,'],
                ]);
            }
        }

        if (str_ends_with($path, '/files/list')) {
            $this->authorize($context, Permission::ACTION_FILE_READ);

            $directory = $this->normalizePath((string) $request->query('directory', '/'));
            $transformer = app(FileObjectTransformer::class);
            $entries = collect($this->fileRepository
                ->setServer($context->session()->server)
                ->getDirectory($directory))
                ->map(fn (array $entry) => [
                    'object' => 'file_object',
                    'attributes' => $transformer->transform($entry),
                ])
                ->all();

            return $this->mergeFileListing(
                $directory,
                response()->json(['object' => 'list', 'data' => $entries]),
                $context
            );
        }

        return $this->resourceSimulator->read($request, $next, $context, $path);
    }

    private function handleWrite(Request $request, SubuserPreviewContext $context, string $path): JsonResponse
    {
        if (str_ends_with($path, '/power')) {
            validator($request->all(), [
                'signal' => 'required|string|in:'.implode(',', Task::POWER_ACTIONS),
            ])->validate();
            $signal = (string) $request->input('signal');
            $permission = Task::permissionForAction(Task::ACTION_POWER, $signal);
            $this->authorize($context, $permission);

            $status = in_array($signal, ['stop', 'kill'], true) ? 'offline' : 'running';
            $this->updateState($context, fn (array $state) => array_replace($state, ['power_status' => $status]));

            return response()->json(['status' => $status]);
        }

        if (str_ends_with($path, '/command')) {
            $this->authorize($context, Permission::ACTION_CONTROL_CONSOLE);
            validator($request->all(), ['command' => 'required|string|min:1'])->validate();

            return response()->json([], 204);
        }

        if (str_ends_with($path, '/files/write')) {
            $permission = $request->isMethod('post') ? Permission::ACTION_FILE_CREATE : Permission::ACTION_FILE_UPDATE;
            $this->authorize($context, $permission);
            validator($request->query(), ['file' => 'required|string'])->validate();
            $file = $this->normalizePath((string) $request->query('file'));
            $content = $request->getContent();
            $maximum = (int) config('panel.files.max_edit_size');

            if ($maximum > 0 && strlen($content) > $maximum) {
                throw new BadRequestHttpException(trans('exceptions.subuser_preview.file_too_large'));
            }

            $this->putFile($context, $file, true, $content);

            return response()->json([], 204);
        }

        if (str_ends_with($path, '/files/create-folder')) {
            $this->authorize($context, Permission::ACTION_FILE_CREATE);
            validator($request->all(), [
                'root' => 'sometimes|nullable|string',
                'name' => 'required|string',
            ])->validate();
            $file = $this->joinPath((string) $request->input('root', '/'), (string) $request->input('name'));

            $this->putFile($context, $file, false);

            return response()->json([], 204);
        }

        if (str_ends_with($path, '/files/delete')) {
            $this->authorize($context, Permission::ACTION_FILE_DELETE);
            validator($request->all(), [
                'root' => 'required|nullable|string',
                'files' => 'required|array',
                'files.*' => 'string',
            ])->validate();
            $root = (string) $request->input('root', '/');

            $this->updateState($context, function (array $state) use ($root, $request) {
                foreach ((array) $request->input('files', []) as $name) {
                    $path = $this->joinPath($root, (string) $name);
                    $state['files'][$path] = ['deleted' => true];

                    foreach (array_keys($state['files'] ?? []) as $candidate) {
                        if (str_starts_with($candidate, $path.'/')) {
                            $state['files'][$candidate] = ['deleted' => true];
                        }
                    }
                }

                return $state;
            });

            return response()->json([], 204);
        }

        if (str_ends_with($path, '/files/rename')) {
            $this->authorize($context, Permission::ACTION_FILE_UPDATE);
            validator($request->all(), [
                'root' => 'required|nullable|string',
                'files' => 'required|array',
                'files.*' => 'array',
                'files.*.to' => 'required|string',
                'files.*.from' => 'required|string',
            ])->validate();
            $root = (string) $request->input('root', '/');

            $this->updateState($context, function (array $state) use ($root, $request) {
                foreach ((array) $request->input('files', []) as $file) {
                    $from = $this->joinPath($root, (string) Arr::get($file, 'from'));
                    $to = $this->joinPath($root, (string) Arr::get($file, 'to'));
                    $entry = $state['files'][$from] ?? [
                        'is_file' => true,
                        'source_path' => $from,
                    ];

                    $state['files'][$from] = ['deleted' => true];
                    $state['files'][$to] = array_replace($entry, [
                        'deleted' => false,
                        'name' => basename($to),
                        'modified_at' => Carbon::now()->toAtomString(),
                    ]);
                }

                return $state;
            });

            return response()->json([], 204);
        }

        if (str_ends_with($path, '/files/copy')) {
            $this->authorize($context, Permission::ACTION_FILE_CREATE);
            validator($request->all(), ['location' => 'required|string'])->validate();
            $source = $this->normalizePath((string) $request->input('location'));
            $extension = pathinfo($source, PATHINFO_EXTENSION);
            $name = pathinfo($source, PATHINFO_FILENAME).' copy'.($extension === '' ? '' : ".{$extension}");
            $target = $this->joinPath($this->parentPath($source), $name);

            $this->updateState($context, function (array $state) use ($source, $target) {
                $entry = $state['files'][$source] ?? ['is_file' => true, 'source_path' => $source];
                $state['files'][$target] = array_replace($entry, [
                    'name' => basename($target),
                    'deleted' => false,
                    'created_at' => Carbon::now()->toAtomString(),
                    'modified_at' => Carbon::now()->toAtomString(),
                ]);

                return $state;
            });

            return response()->json([], 204);
        }

        if (str_ends_with($path, '/files/compress')) {
            $this->authorize($context, Permission::ACTION_FILE_ARCHIVE);
            validator($request->all(), [
                'root' => 'sometimes|nullable|string',
                'files' => 'required|array|min:1',
                'files.*' => 'string',
            ])->validate();
            $root = (string) $request->input('root', '/');
            $first = (string) Arr::first((array) $request->input('files', []), null, 'archive');
            $archive = $this->joinPath($root, basename($first).'.tar.gz');
            $this->putFile($context, $archive, true, '');
            $entry = ($context->session()->state ?? [])['files'][$archive];

            return response()->json([
                'object' => 'file_object',
                'attributes' => $this->fileAttributes($archive, $entry),
            ]);
        }

        if (str_ends_with($path, '/files/decompress')) {
            $this->authorize($context, Permission::ACTION_FILE_CREATE);
            validator($request->all(), [
                'root' => 'sometimes|nullable|string',
                'file' => 'required|string',
            ])->validate();
            $root = (string) $request->input('root', '/');
            $name = preg_replace('/\.(tar\.gz|tar|zip|gz)$/i', '', (string) $request->input('file')) ?: 'extracted';
            $this->putFile($context, $this->joinPath($root, $name), false);

            return response()->json([], 204);
        }

        if (str_ends_with($path, '/files/chmod')) {
            $this->authorize($context, Permission::ACTION_FILE_UPDATE);
            validator($request->all(), [
                'root' => 'required|nullable|string',
                'files' => 'required|array',
                'files.*.file' => 'required|string',
                'files.*.mode' => 'required|numeric',
            ])->validate();
            $root = (string) $request->input('root', '/');
            $this->updateState($context, function (array $state) use ($request, $root) {
                foreach ((array) $request->input('files', []) as $file) {
                    $path = $this->joinPath($root, (string) Arr::get($file, 'file'));
                    $state['files'][$path] = array_replace($state['files'][$path] ?? [
                        'name' => basename($path),
                        'is_file' => true,
                        'source_path' => $path,
                    ], ['mode_bits' => str_pad((string) Arr::get($file, 'mode'), 4, '0', STR_PAD_LEFT)]);
                }

                return $state;
            });

            return response()->json([], 204);
        }

        if (str_ends_with($path, '/files/pull')) {
            $this->authorize($context, Permission::ACTION_FILE_CREATE);
            validator($request->all(), [
                'url' => 'required|string|url',
                'directory' => 'nullable|string',
                'filename' => 'nullable|string',
                'use_header' => 'boolean',
                'foreground' => 'boolean',
            ])->validate();
            $url = (string) $request->input('url');
            if (! str_starts_with(strtolower($url), 'https://') || preg_match('/\$(\(|\{)|`/', $url)) {
                throw new BadRequestHttpException(trans('exceptions.subuser_preview.unsafe_pull_url'));
            }
            $filename = (string) ($request->input('filename') ?: basename(parse_url($url, PHP_URL_PATH) ?: 'download'));
            $this->putFile($context, $this->joinPath((string) $request->input('directory', '/'), $filename), true, '');

            return response()->json([], 204);
        }

        if ($response = $this->resourceSimulator->write($request, $context, $path)) {
            return $response;
        }

        throw new ConflictHttpException(trans('exceptions.subuser_preview.action_unavailable'));
    }

    private function mergeFileListing(
        string $directory,
        JsonResponse $response,
        SubuserPreviewContext $context
    ): JsonResponse {
        $payload = $response->getData(true);
        $entries = collect($payload['data'] ?? [])->keyBy(fn (array $entry) => Arr::get($entry, 'attributes.name'));

        foreach (Arr::get($context->session()->state ?? [], 'files', []) as $path => $entry) {
            if ($this->parentPath($path) !== $directory) {
                continue;
            }

            $name = basename($path);
            if (($entry['deleted'] ?? false) === true) {
                $entries->forget($name);

                continue;
            }

            $isFile = (bool) ($entry['is_file'] ?? true);
            $entries->put($name, [
                'object' => 'file_object',
                'attributes' => [
                    'name' => $name,
                    'mode' => $isFile ? '-rw-r--r--' : 'drwxr-xr-x',
                    'mode_bits' => $entry['mode_bits'] ?? ($isFile ? '0644' : '0755'),
                    'size' => $isFile ? $this->contentSize($entry) : 0,
                    'is_file' => $isFile,
                    'is_symlink' => false,
                    'mimetype' => $isFile ? 'text/plain' : 'inode/directory',
                    'created_at' => $entry['created_at'] ?? Carbon::now()->toAtomString(),
                    'modified_at' => $entry['modified_at'] ?? Carbon::now()->toAtomString(),
                ],
            ]);
        }

        $payload['data'] = $entries->values()->all();
        $response->setData($payload);

        return $response;
    }

    private function putFile(SubuserPreviewContext $context, string $path, bool $isFile, ?string $content = null): void
    {
        $this->updateState($context, function (array $state) use ($path, $isFile, $content) {
            $now = Carbon::now()->toAtomString();
            $existing = $state['files'][$path] ?? [];
            $state['files'][$path] = array_replace($existing, [
                'name' => basename($path),
                'is_file' => $isFile,
                'content' => $isFile ? base64_encode($content ?? '') : null,
                'content_encoding' => $isFile ? 'base64' : null,
                'deleted' => false,
                'created_at' => $existing['created_at'] ?? $now,
                'modified_at' => $now,
            ]);

            return $state;
        });
    }

    private function fileAttributes(string $path, array $entry): array
    {
        $isFile = (bool) ($entry['is_file'] ?? true);

        return [
            'name' => basename($path),
            'mode' => $isFile ? '-rw-r--r--' : 'drwxr-xr-x',
            'mode_bits' => $entry['mode_bits'] ?? ($isFile ? '0644' : '0755'),
            'size' => $isFile ? $this->contentSize($entry) : 0,
            'is_file' => $isFile,
            'is_symlink' => false,
            'mimetype' => $isFile ? 'application/octet-stream' : 'inode/directory',
            'created_at' => $entry['created_at'] ?? Carbon::now()->toAtomString(),
            'modified_at' => $entry['modified_at'] ?? Carbon::now()->toAtomString(),
        ];
    }

    private function updateState(SubuserPreviewContext $context, callable $callback): void
    {
        $this->stateService->update($context, $callback);
    }

    private function decodeContent(array $entry): string
    {
        $content = (string) ($entry['content'] ?? '');
        if (($entry['content_encoding'] ?? null) !== 'base64') {
            return $content;
        }

        $decoded = base64_decode($content, true);

        return $decoded === false ? '' : $decoded;
    }

    private function contentSize(array $entry): int
    {
        return strlen($this->decodeContent($entry));
    }

    private function authorize(SubuserPreviewContext $context, ?string $permission): void
    {
        if (! $permission || ! $context->allows($permission)) {
            throw new AccessDeniedHttpException(trans('exceptions.subuser_preview.permission_denied'));
        }
    }

    private function joinPath(string $root, string $name): string
    {
        return $this->normalizePath(rtrim($root, '/').'/'.ltrim($name, '/'));
    }

    private function normalizePath(string $path): string
    {
        $parts = [];
        foreach (explode('/', str_replace('\\', '/', $path)) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($parts);

                continue;
            }

            $parts[] = $part;
        }

        return '/'.implode('/', $parts);
    }

    private function parentPath(string $path): string
    {
        $parent = dirname($path);

        return $parent === '.' ? '/' : $parent;
    }
}

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
use Illuminate\Support\Str;
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

    /**
     * Handles a subuser preview request by validating its server scope and dispatching it to the appropriate read or write operation.
     *
     * @param  Request  $request  The incoming API request.
     * @param  \Closure  $next  The next request handler.
     * @param  SubuserPreviewContext  $context  The preview context used to validate server access and permissions.
     * @return mixed The response produced by the request handler.
     */
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

    /**
     * Handles read-only preview API operations for files, backups, and delegated resources.
     *
     * @param  Request  $request  The incoming API request.
     * @param  \Closure  $next  The next request handler.
     * @param  SubuserPreviewContext  $context  The preview session and permission context.
     * @param  string  $path  The requested API route path.
     * @return mixed The response generated for the requested read operation.
     *
     * @throws ConflictHttpException If the request requires an unavailable live connection.
     * @throws NotFoundHttpException If the requested file or directory does not exist.
     */
    private function handleRead(Request $request, \Closure $next, SubuserPreviewContext $context, string $path): mixed
    {
        if (str_ends_with($path, '/files/pull')) {
            $this->authorize($context, Permission::ACTION_FILE_CREATE);

            return response()->json(['downloads' => []]);
        }

        if (str_ends_with($path, '/files/upload')) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.live_connection_unavailable'));
        }

        if (str_ends_with($path, '/files/contents')) {
            $this->authorize($context, Permission::ACTION_FILE_READ_CONTENT);
            $file = $this->normalizePath((string) $request->query('file'));
            $state = $context->session()->state ?? [];
            $entry = $state['files'][$file] ?? null;

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
                ->getContent($this->livePathForPreviewPath($state, $file), config('panel.files.max_edit_size'));

            return new Response($content, 200, ['Content-Type' => 'text/plain']);
        }

        if (str_ends_with($path, '/files/download')) {
            $this->authorize($context, Permission::ACTION_FILE_READ_CONTENT);
            $file = $this->normalizePath((string) $request->query('file'));
            $state = $context->session()->state ?? [];
            $entry = $state['files'][$file] ?? null;
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

            $livePath = $this->livePathForPreviewPath($state, $file);
            if ($livePath !== $file) {
                $content = $this->fileRepository
                    ->setServer($context->session()->server)
                    ->getContent($livePath);

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
            $state = $context->session()->state ?? [];
            $directoryEntry = $state['files'][$directory] ?? null;
            if (is_array($directoryEntry) && (($directoryEntry['deleted'] ?? false) || ($directoryEntry['is_file'] ?? true))) {
                throw new NotFoundHttpException(trans('exceptions.subuser_preview.file_not_found'));
            }

            $entries = [];
            if (! is_array($directoryEntry) || array_key_exists('source_path', $directoryEntry)) {
                $transformer = app(FileObjectTransformer::class);
                $entries = collect($this->fileRepository
                    ->setServer($context->session()->server)
                    ->getDirectory($this->livePathForPreviewPath($state, $directory)))
                    ->map(fn (array $entry) => [
                        'object' => 'file_object',
                        'attributes' => $transformer->transform($entry),
                    ])
                    ->all();
            }

            return $this->mergeFileListing(
                $directory,
                response()->json(['object' => 'list', 'data' => $entries]),
                $context
            );
        }

        return $this->resourceSimulator->read($request, $next, $context, $path);
    }

    /**
     * Handles supported write operations for the subuser preview.
     *
     * @param  Request  $request  The request containing the operation and its input data.
     * @param  SubuserPreviewContext  $context  The preview context used for authorization and state updates.
     * @param  string  $path  The requested API path.
     * @return JsonResponse The response representing the completed operation.
     */
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
            $files = (array) $request->input('files', []);
            $state = $context->session()->state ?? [];
            $resolved = [];
            foreach ($files as $file) {
                $from = $this->joinPath($root, (string) Arr::get($file, 'from'));
                $resolved[$from] = $this->resolveFileEntry($context, $state, $from);
            }

            $this->updateState($context, function (array $state) use ($files, $resolved, $root) {
                foreach ($files as $file) {
                    $from = $this->joinPath($root, (string) Arr::get($file, 'from'));
                    $to = $this->joinPath($root, (string) Arr::get($file, 'to'));
                    $entry = $state['files'][$from] ?? $resolved[$from];
                    if (($entry['deleted'] ?? false) === true) {
                        throw new NotFoundHttpException(trans('exceptions.subuser_preview.file_not_found'));
                    }

                    $filesBeforeRename = $state['files'] ?? [];

                    $state['files'][$from] = ['deleted' => true];
                    $state['files'][$to] = array_replace($entry, [
                        'deleted' => false,
                        'name' => basename($to),
                        'modified_at' => Carbon::now()->toAtomString(),
                    ]);

                    if (($entry['is_file'] ?? true) === false) {
                        foreach ($filesBeforeRename as $candidate => $descendant) {
                            if (! str_starts_with($candidate, $from.'/')) {
                                continue;
                            }

                            $target = $to.substr($candidate, strlen($from));
                            $state['files'][$candidate] = ['deleted' => true];
                            $state['files'][$target] = array_replace($descendant, [
                                'deleted' => false,
                                'name' => basename($target),
                            ]);
                        }
                    }
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
            $state = $context->session()->state ?? [];
            $resolved = $this->resolveFileEntry($context, $state, $source);

            $this->updateState($context, function (array $state) use ($resolved, $source, $target) {
                $entry = $state['files'][$source] ?? $resolved;
                if (($entry['deleted'] ?? false) === true) {
                    throw new NotFoundHttpException(trans('exceptions.subuser_preview.file_not_found'));
                }

                $state['files'][$target] = array_replace($entry, [
                    'name' => basename($target),
                    'deleted' => false,
                    'created_at' => Carbon::now()->toAtomString(),
                    'modified_at' => Carbon::now()->toAtomString(),
                ]);

                if (($entry['is_file'] ?? true) === false) {
                    $filesBeforeCopy = $state['files'] ?? [];
                    foreach ($filesBeforeCopy as $candidate => $descendant) {
                        if (! str_starts_with($candidate, $source.'/')) {
                            continue;
                        }

                        $copy = $target.substr($candidate, strlen($source));
                        $state['files'][$copy] = array_replace($descendant, [
                            'deleted' => false,
                            'name' => basename($copy),
                        ]);
                    }
                }

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

            return response()->json(['identifier' => 'preview_'.Str::lower(Str::random(8))], 202);
        }

        if ($response = $this->resourceSimulator->write($request, $context, $path)) {
            return $response;
        }

        throw new ConflictHttpException(trans('exceptions.subuser_preview.action_unavailable'));
    }

    /**
     * Merges preview-state file changes into a live directory listing.
     *
     * @param  string  $directory  The directory whose entries are being merged.
     * @param  JsonResponse  $response  The live directory listing response.
     * @param  SubuserPreviewContext  $context  The preview context containing simulated file state.
     * @return JsonResponse The updated directory listing response.
     */
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

    /**
     * Creates or updates a preview file or directory entry.
     *
     * @param  SubuserPreviewContext  $context  The preview context whose state is updated.
     * @param  string  $path  The entry path.
     * @param  bool  $isFile  Whether the entry is a file rather than a directory.
     * @param  string|null  $content  The file content to store.
     */
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

    /**
     * Builds API file metadata for a preview entry.
     *
     * @param  string  $path  The preview path used to derive the entry name.
     * @param  array  $entry  The preview entry containing file type, content, permissions, and timestamps.
     * @return array The file attributes for the preview entry.
     */
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

    /**
     * Applies a state update within the specified preview context.
     *
     * @param  SubuserPreviewContext  $context  The preview context whose state is updated.
     * @param  callable  $callback  The state update operation to execute.
     */
    private function updateState(SubuserPreviewContext $context, callable $callback): void
    {
        $this->stateService->update($context, $callback);
    }

    /**
     * Decodes a stored file entry's content when it uses base64 encoding.
     *
     * @param  array  $entry  The file entry containing content and optional encoding metadata.
     * @return string The decoded content, or an empty string for invalid base64 data.
     */
    private function decodeContent(array $entry): string
    {
        $content = (string) ($entry['content'] ?? '');
        if (($entry['content_encoding'] ?? null) !== 'base64') {
            return $content;
        }

        $decoded = base64_decode($content, true);

        return $decoded === false ? '' : $decoded;
    }

    /**
     * Calculates the decoded content size of a preview file entry.
     *
     * @param  array  $entry  The preview file entry containing encoded content.
     * @return int The content length in bytes.
     */
    private function contentSize(array $entry): int
    {
        return strlen($this->decodeContent($entry));
    }

    /**
     * Resolves a preview path to its stored or live file entry.
     *
     * @param  SubuserPreviewContext  $context  The preview context used to access the live server.
     * @param  array  $state  The current preview state.
     * @param  string  $path  The preview path to resolve.
     * @return array The resolved file entry.
     */
    private function resolveFileEntry(SubuserPreviewContext $context, array $state, string $path): array
    {
        if (array_key_exists($path, $state['files'] ?? [])) {
            $entry = $state['files'][$path];
            if (($entry['deleted'] ?? false) === true) {
                throw new NotFoundHttpException(trans('exceptions.subuser_preview.file_not_found'));
            }

            return $entry;
        }

        $livePath = $this->livePathForPreviewPath($state, $path);
        $entry = collect($this->fileRepository
            ->setServer($context->session()->server)
            ->getDirectory($this->parentPath($livePath)))
            ->first(fn (array $entry) => (string) Arr::get($entry, 'name') === basename($livePath));

        if (! is_array($entry)) {
            throw new NotFoundHttpException(trans('exceptions.subuser_preview.file_not_found'));
        }

        return [
            'name' => basename($path),
            'is_file' => (bool) Arr::get($entry, 'file', true),
            'source_path' => $livePath,
            'mode_bits' => Arr::get($entry, 'mode_bits'),
            'mimetype' => Arr::get($entry, 'mime'),
            'deleted' => false,
        ];
    }

    /**
     * Maps a preview path to its corresponding live server path.
     *
     * @param  array  $state  The preview state containing directory mappings.
     * @param  string  $path  The preview path to map.
     * @return string The normalized live server path.
     */
    private function livePathForPreviewPath(array $state, string $path): string
    {
        $path = $this->normalizePath($path);
        $match = null;
        foreach ($state['files'] ?? [] as $previewPath => $entry) {
            if (! is_array($entry)
                || ($entry['deleted'] ?? false) === true
                || ($entry['is_file'] ?? true) === true
                || ! isset($entry['source_path'])
                || ($path !== $previewPath && ! str_starts_with($path, $previewPath.'/'))
            ) {
                continue;
            }

            if ($match === null || strlen($previewPath) > strlen($match)) {
                $match = $previewPath;
            }
        }

        if ($match === null) {
            return $path;
        }

        return $this->normalizePath($state['files'][$match]['source_path'].substr($path, strlen($match)));
    }

    /**
     * Ensures the preview context grants the required permission.
     *
     * @param  SubuserPreviewContext  $context  The preview context used for authorization.
     * @param  string|null  $permission  The permission to check.
     *
     * @throws AccessDeniedHttpException If the permission is missing or denied.
     */
    private function authorize(SubuserPreviewContext $context, ?string $permission): void
    {
        if (! $permission || ! $context->allows($permission)) {
            throw new AccessDeniedHttpException(trans('exceptions.subuser_preview.permission_denied'));
        }
    }

    /**
     * Combines a root path and name into a normalized path.
     *
     * @param  string  $root  The root path.
     * @param  string  $name  The path name to append.
     * @return string The normalized combined path.
     */
    private function joinPath(string $root, string $name): string
    {
        return $this->normalizePath(rtrim($root, '/').'/'.ltrim($name, '/'));
    }

    /**
     * Normalizes a path by standardizing separators and resolving redundant components.
     *
     * @param  string  $path  The path to normalize.
     * @return string The normalized absolute-style path.
     */
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

    /**
     * Returns the parent directory of a path.
     *
     * @param  string  $path  The path whose parent directory to return.
     * @return string The parent directory, using `/` for paths in the root directory.
     */
    private function parentPath(string $path): string
    {
        $parent = dirname($path);

        return $parent === '.' ? '/' : $parent;
    }
}

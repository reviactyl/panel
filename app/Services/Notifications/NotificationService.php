<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NotificationService
{
    public static function rules(bool $creating = true): array
    {
        return [
            'title' => [$creating ? 'required' : 'sometimes', 'required', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'url' => ['sometimes', 'nullable', 'string', 'max:2048', 'regex:/^\/(?!\/)[^\\\\\x00-\x20]*$/'],
            'status' => ['sometimes', 'in:info,success,warning,danger'],
            'extension' => ['sometimes', 'nullable', 'string', 'max:191', 'regex:/^[a-z0-9][a-z0-9._-]*$/'],
            'read' => ['sometimes', 'boolean'],
        ];
    }

    /** @return MorphMany<DatabaseNotification, User> */
    public function query(User $user): MorphMany
    {
        $query = $user->notifications();
        if ($query->getQuery()->getGrammar() instanceof PostgresGrammar) {
            return $query->whereRaw("(data::jsonb->>'source' = ? OR data::jsonb->>'format' = ?)", ['reviactyl', 'filament']);
        }

        return $query->where(fn ($query) => $query->where('data->source', 'reviactyl')->orWhere('data->format', 'filament'));
    }

    public function create(User $user, array $data): DatabaseNotification
    {
        $data = Validator::make($data, self::rules())->validate();

        return $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => self::class,
            'data' => array_merge(['title' => '', 'body' => '', 'url' => '/', 'status' => 'info'], array_diff_key($data, ['read' => true]), ['source' => 'reviactyl', 'event' => 'custom']),
            'read_at' => ($data['read'] ?? false) ? now() : null,
        ]);
    }

    public function find(User $user, string $id): DatabaseNotification
    {
        return $this->query($user)->findOrFail($id);
    }

    public function update(User $user, string $id, array $data): DatabaseNotification
    {
        $data = Validator::make($data, self::rules(false))->validate();
        $notice = $this->find($user, $id);
        $content = array_diff_key($data, ['read' => true]);
        abort_if($content && ($notice->data['event'] ?? null) !== 'custom', 422, 'Only custom notification content can be edited.');
        if ($content) {
            $notice->data = array_merge($notice->data, $content);
        }
        if (array_key_exists('read', $data)) {
            $notice->read_at = $data['read'] ? now() : null;
        }
        $notice->save();

        return $notice;
    }

    public function delete(User $user, string $id): void
    {
        $this->find($user, $id)->delete();
    }

    public function serialize(DatabaseNotification $notice): array
    {
        $data = $notice->data;
        if (($data['format'] ?? null) === 'filament') {
            $data = [
                'event' => ($data['status'] ?? '') === 'danger' ? 'extension-failed' : 'extension',
                'title' => strip_tags($data['title'] ?? ''),
                'server' => strip_tags($data['body'] ?? ''),
                'url' => '/admin/extensions',
            ];
        }

        return ['id' => $notice->id, 'data' => $data, 'read_at' => $notice->read_at, 'created_at' => $notice->created_at];
    }
}

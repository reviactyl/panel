<?php

namespace App\Services\Servers;

use App\Models\Server;
use Illuminate\Support\Str;

class StartupCommandService
{
    /**
     * Generates a startup command for a given server instance.
     */
    public function handle(Server $server, bool $hideAllValues = false): string
    {
        $find = ['{{SERVER_MEMORY}}', '{{SERVER_IP}}', '{{SERVER_PORT}}'];
        $replace = [$server->memory, $server->allocation->ip, $server->allocation->port];

        foreach ($server->variables->keyBy('env_variable') as $variable) {
            $find[] = '{{'.$variable->env_variable.'}}';
            $replace[] = ($variable->user_viewable && ! $variable->is_secret && ! $hideAllValues) ? ($variable->server_value ?? $variable->default_value) : '[hidden]';
        }

        return Str::replace($find, $replace, $server->startup);
    }
}

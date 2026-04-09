<?php

declare(strict_types=1);

$env = [
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
    'CACHE_STORE' => 'file',
    'CACHE_DRIVER' => 'file',
    'SESSION_DRIVER' => 'file',
    'QUEUE_CONNECTION' => 'sync',
    'REDIS_CLIENT' => 'predis',
    'MAIL_MAILER' => 'array',
];

foreach ($env as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
  export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
fi

cp .env.example .env

php <<'PHP'
<?php

$vars = [
    'APP_NAME',
    'APP_ENV',
    'APP_KEY',
    'APP_DEBUG',
    'APP_URL',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
    'SESSION_DRIVER',
    'CACHE_STORE',
    'QUEUE_CONNECTION',
    'CORS_ALLOWED_ORIGINS',
];

$env = file_get_contents('.env');

foreach ($vars as $var) {
    $value = getenv($var);
    if ($value === false || $value === '') {
        continue;
    }

    $line = $var.'='.formatEnvValue($value);
    $pattern = '/^'.preg_quote($var, '/').'=.*$/m';

    if (preg_match($pattern, $env)) {
        $env = preg_replace($pattern, $line, $env);
    } else {
        $env = rtrim($env).PHP_EOL.$line.PHP_EOL;
    }
}

file_put_contents('.env', $env);

function formatEnvValue(string $value): string
{
    if (preg_match('/[\s#"\']/', $value)) {
        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }

    return $value;
}
PHP

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
  mkdir -p database
  touch database/database.sqlite
fi

php artisan config:clear --no-interaction 2>/dev/null || true

echo "Aguardando banco de dados (${DB_CONNECTION:-sqlite})..."
until php artisan migrate --force --no-interaction; do
  sleep 2
done

exec php artisan serve --host=0.0.0.0 --port=8000

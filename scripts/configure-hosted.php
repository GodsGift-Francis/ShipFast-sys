<?php

// CLI-only one-time configuration through stdin. Never install under public/.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
umask(0077);
$root = dirname(__DIR__);
if (is_file($root.'/.env')) {
    fwrite(STDERR, "Configuration already exists; refusing replacement.\n");
    exit(1);
}
try {
    $input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    foreach (['url', 'db_host', 'db_name', 'db_user', 'db_password'] as $key) {
        if (! isset($input[$key]) || ! is_string($input[$key]) || $input[$key] === '') {
            throw new RuntimeException('Missing required setting.');
        }
    }
    if (! filter_var($input['url'], FILTER_VALIDATE_URL) || parse_url($input['url'], PHP_URL_SCHEME) !== 'https' || parse_url($input['url'], PHP_URL_USER) || parse_url($input['url'], PHP_URL_QUERY)) {
        throw new RuntimeException('Use an HTTPS site URL.');
    }
    $encode = fn (string $value) => '"'.str_replace(['\\', '"', '$', "\r", "\n"], ['\\\\', '\\"', '\\$', '\\r', '\\n'], $value).'"';
    $values = ['APP_NAME' => 'ShipFast', 'APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => rtrim($input['url'], '/'), 'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)), 'APP_TIMEZONE' => 'UTC', 'LOG_CHANNEL' => 'single', 'LOG_LEVEL' => 'warning', 'DB_CONNECTION' => 'mysql', 'DB_HOST' => $input['db_host'], 'DB_PORT' => '3306', 'DB_DATABASE' => $input['db_name'], 'DB_USERNAME' => $input['db_user'], 'DB_PASSWORD' => $input['db_password'], 'SESSION_DRIVER' => 'database', 'SESSION_SECURE_COOKIE' => 'true', 'SESSION_HTTP_ONLY' => 'true', 'SESSION_SAME_SITE' => 'lax', 'SESSION_COOKIE' => 'shipfast_session', 'CACHE_STORE' => 'database', 'QUEUE_CONNECTION' => 'database', 'MAIL_MAILER' => 'log', 'MAIL_FROM_ADDRESS' => 'hello@example.invalid', 'MAIL_FROM_NAME' => 'ShipFast'];
    $text = '';
    foreach ($values as $key => $value) {
        $text .= $key.'='.$encode($value)."\n";
    }
    $handle = fopen($root.'/.env', 'x');
    if (! $handle) {
        throw new RuntimeException('Cannot create configuration.');
    }try {
        if (fwrite($handle, $text) !== strlen($text)) {
            throw new RuntimeException('Incomplete write.');
        }
    } finally {
        fclose($handle);
    }chmod($root.'/.env', 0600);
    echo "PRIVATE_CONFIGURATION_CREATED\n";
} catch (Throwable $e) {
    fwrite(STDERR,"Configuration setup failed. No settings were printed.\n");
    exit(1);
}

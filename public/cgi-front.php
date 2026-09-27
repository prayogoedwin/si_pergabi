<?php

$script = '/index.cgi';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';
$query = parse_url($uri, PHP_URL_QUERY);

if (str_starts_with($path, $script)) {
    $path = substr($path, strlen($script));
    if ($path === '' || $path[0] !== '/') {
        $path = '/'.$path;
    }

    $_SERVER['REQUEST_URI'] = $path.($query ? '?'.$query : '');
    $_SERVER['PATH_INFO'] = $path;
    $_SERVER['PHP_SELF'] = '/index.cgi'.$path;
    $_SERVER['SCRIPT_NAME'] = '/index.cgi';
}

$bootstrap = is_file(__DIR__.'/index.laravel.php')
    ? __DIR__.'/index.laravel.php'
    : __DIR__.'/index.php';

require $bootstrap;

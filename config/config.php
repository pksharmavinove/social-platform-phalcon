<?php

defined('APP_PATH') || define('APP_PATH', realpath('.'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');

$config = new \Phalcon\Config([
    'application' => [
        'title' => 'Social Media Platform',
        'description' => 'Social Media Platform',
        'controllersDir' => APP_PATH . '/controllers/',
        'libraryDir' => APP_PATH . '/library/',
        'modelsDir' => APP_PATH . '/models/',
        'migrationsDir' => APP_PATH . '/migrations/',
        'viewsDir' => APP_PATH . '/views/',
        'middlewaresDir' => APP_PATH . '/middlewares/',
        'baseUri' => '/',
    ],
]);

$configOverride = new \Phalcon\Config(include_once __DIR__ . '/../config/server.' . APPLICATION_ENV . '.php');

$config = $config->merge($configOverride);

return $config;

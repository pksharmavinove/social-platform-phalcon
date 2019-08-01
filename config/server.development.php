<?php

return [
    'database' => [
        'adapter' => 'Mysql', /* Possible Values: Mysql, Postgres, Sqlite */
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => 'Vinove#123',
        'dbname' => 'social_platform',
        'charset' => 'utf8',
    ],

    'log_database' => [
        'adapter'  => 'Mysql', /* Possible Values: Mysql, Postgres, Sqlite */
        'host'     => '127.0.0.1',
        'username' => 'root',
        'password' => 'Vinove#123',
        'dbname'   => 'social_platform',
        'charset'  => 'utf8',
    ],

    'authentication' => [
        'secret' => '1234567890', // This will sign the token. (still insecure)
        'encryption_key' => 'asdf1234dgdfg34535345gfsdfsdfg34', // Secure token with an ultra password
        'expiration_time' => 86400 * 7, // One week till token expires
        'iss' => 'social-media', // Token issuer eg. www.myproject.com
        'aud' => 'social-media', // Token audience eg. www.myproject.com
    ],
];

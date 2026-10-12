<?php
/**
 * MiniPress configuration.
 *
 * driver = 'sqlite'  -> zero setup, runs with PHP's built-in server (good for learning)
 * driver = 'mysql'   -> the real-world setup (XAMPP / WAMP / cPanel), same as WordPress
 *
 * Sinhala: driver kiyanne database eka mokakda kiyana eka. 'sqlite' nam file ekak witharai,
 * 'mysql' nam XAMPP eke MySQL server eka onalu.
 */
return [
    'driver' => getenv('MP_DRIVER') ?: 'sqlite',

    'sqlite_path' => __DIR__ . '/storage/minipress.sqlite',

    'mysql' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'minipress',
        'username' => 'root',
        'password' => '',   // XAMPP default root password is empty
    ],

    'site_name'    => 'MiniPress',
    'site_tagline' => 'A content engine you can read end to end',
];

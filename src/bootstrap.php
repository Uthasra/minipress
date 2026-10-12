<?php
declare(strict_types=1);

/**
 * Every page starts with: require __DIR__ . '/../../src/bootstrap.php';
 * This is the one place that loads classes and starts the session.
 */
date_default_timezone_set('Asia/Colombo');

require __DIR__ . '/helpers.php';
require __DIR__ . '/Database.php';
require __DIR__ . '/Auth.php';
require __DIR__ . '/Post.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,   // JavaScript cannot read the session cookie
        'cookie_samesite' => 'Lax',  // blocks most cross-site form posts
    ]);
}

$config = require __DIR__ . '/../config.php';

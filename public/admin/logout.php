<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

Auth::logout();
redirect('../index.php');

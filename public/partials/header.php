<?php
/**
 * Shared page top. WordPress does the same job with get_header().
 * $pageTitle and $bodyClass are set by the page that includes this file.
 */
$config    = require dirname(__DIR__, 2) . '/config.php';
$pageTitle = $pageTitle ?? $config['site_name'];
$bodyClass = $bodyClass ?? 'reading';
$base      = $bodyClass === 'admin' ? '..' : '.';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> — <?= e($config['site_name']) ?></title>
<link rel="stylesheet" href="<?= $base ?>/assets/style.css">
</head>
<body class="<?= e($bodyClass) ?>">
<header class="masthead">
  <a class="wordmark" href="<?= $base ?>/index.php"><?= e($config['site_name']) ?></a>
  <nav>
    <?php if (Auth::check()): ?>
      <a href="<?= $base ?>/admin/index.php">Dashboard</a>
      <a href="<?= $base ?>/admin/logout.php">Sign out</a>
    <?php else: ?>
      <a href="<?= $base ?>/admin/login.php">Sign in</a>
    <?php endif; ?>
  </nav>
</header>
<main>
<?php if ($message = flash()): ?>
  <p class="flash"><?= e($message) ?></p>
<?php endif; ?>

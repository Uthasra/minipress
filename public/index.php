<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';

/**
 * The front page: fetch the published posts, then print them.
 * In WordPress this pattern is called "The Loop".
 */
$posts = Post::published();

$pageTitle = 'Latest writing';
require __DIR__ . '/partials/header.php';
?>

<section class="intro">
  <h1><?= e($config['site_name']) ?></h1>
  <p><?= e($config['site_tagline']) ?></p>
</section>

<?php if (!$posts): ?>
  <p class="empty">Nothing published yet. Sign in and write the first post.</p>
<?php endif; ?>

<ol class="archive">
<?php foreach ($posts as $post): ?>
  <li>
    <time datetime="<?= e(date('Y-m-d', strtotime($post['created_at']))) ?>">
      <?= e(show_date($post['created_at'])) ?>
    </time>
    <h2><a href="post.php?slug=<?= urlencode($post['slug']) ?>"><?= e($post['title']) ?></a></h2>
    <p><?= e(excerpt($post['body'])) ?></p>
    <p class="byline">by <?= e($post['author']) ?></p>
  </li>
<?php endforeach; ?>
</ol>

<?php require __DIR__ . '/partials/footer.php'; ?>

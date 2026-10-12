<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';

// $_GET values are never trusted. Treat it as a string, pass it as a bound parameter.
$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
$post = $slug === '' ? null : Post::findPublishedBySlug($slug);

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/partials/header.php';
    echo '<section class="intro"><h1>No such post</h1>'
       . '<p>The link may be wrong, or the post is still a draft.</p>'
       . '<p><a href="index.php">Back to all posts</a></p></section>';
    require __DIR__ . '/partials/footer.php';
    exit;
}

$pageTitle = $post['title'];
require __DIR__ . '/partials/header.php';
?>

<article class="entry">
  <time datetime="<?= e(date('Y-m-d', strtotime($post['created_at']))) ?>">
    <?= e(show_date($post['created_at'])) ?>
  </time>
  <h1><?= e($post['title']) ?></h1>
  <p class="byline">by <?= e($post['author']) ?></p>
  <?= autop($post['body']) ?>
  <p class="back"><a href="index.php">All posts</a></p>
</article>

<?php require __DIR__ . '/partials/footer.php'; ?>

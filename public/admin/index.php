<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

Auth::requireLogin();

$user  = Auth::user();
$posts = Post::all();

$pageTitle = 'Dashboard';
$bodyClass = 'admin';
require dirname(__DIR__) . '/partials/header.php';
?>

<section class="panel">
  <div class="panel-head">
    <h1>Posts</h1>
    <a class="button" href="edit.php">Write a post</a>
  </div>
  <p class="hint">Signed in as <?= e($user['display_name']) ?>.</p>

  <?php if (!$posts): ?>
    <p class="empty">No posts yet.</p>
  <?php else: ?>
  <table class="list">
    <thead>
      <tr><th>Title</th><th>Status</th><th>Updated</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($posts as $post): ?>
      <tr>
        <td>
          <a href="edit.php?id=<?= (int) $post['id'] ?>"><?= e($post['title']) ?></a>
          <span class="slug">/<?= e($post['slug']) ?></span>
        </td>
        <td><span class="status status-<?= e($post['status']) ?>"><?= e($post['status']) ?></span></td>
        <td><?= e(show_date($post['updated_at'])) ?></td>
        <td class="row-actions">
          <?php if ($post['status'] === 'published'): ?>
            <a href="../post.php?slug=<?= urlencode($post['slug']) ?>">View</a>
          <?php endif; ?>
          <!-- Deleting changes data, so it is a POST form, not a link. -->
          <form method="post" action="delete.php" data-confirm="Delete &ldquo;<?= e($post['title']) ?>&rdquo;?">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
            <button type="submit" class="link-button">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>

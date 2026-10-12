<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

Auth::requireLogin();

$id      = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$editing = $id > 0 ? Post::find($id) : null;

if ($id > 0 && !$editing) {
    http_response_code(404);
    exit('Post not found.');
}

// Values shown in the form: the saved post, or empty fields for a new one.
$values = [
    'title'  => $editing['title']  ?? '',
    'slug'   => $editing['slug']   ?? '',
    'body'   => $editing['body']   ?? '',
    'status' => $editing['status'] ?? 'draft',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $values = [
        'title'  => trim((string) ($_POST['title']  ?? '')),
        'slug'   => trim((string) ($_POST['slug']   ?? '')),
        'body'   => trim((string) ($_POST['body']   ?? '')),
        'status' => (string) ($_POST['status'] ?? 'draft'),
    ];

    $errors = Post::validate($values);

    if (!$errors) {
        if ($editing) {
            Post::update($id, $values);
            flash('Post updated.');
        } else {
            Post::create($values, (int) $_SESSION['user_id']);
            flash('Post created.');
        }
        redirect('index.php');
    }
}

$pageTitle = $editing ? 'Edit post' : 'New post';
$bodyClass = 'admin';
require dirname(__DIR__) . '/partials/header.php';
?>

<section class="panel">
  <h1><?= $editing ? 'Edit post' : 'Write a post' ?></h1>

  <?php if ($errors): ?>
    <ul class="error">
      <?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form method="post" class="editor">
    <?= csrf_field() ?>

    <label for="title">Title</label>
    <input id="title" name="title" required value="<?= e($values['title']) ?>">

    <label for="slug">Link</label>
    <input id="slug" name="slug" value="<?= e($values['slug']) ?>"
           placeholder="made from the title if you leave this empty">

    <label for="body">Body</label>
    <textarea id="body" name="body" rows="16" required><?= e($values['body']) ?></textarea>
    <p class="hint" id="wordcount"></p>

    <label for="status">Status</label>
    <select id="status" name="status">
      <?php foreach (Post::STATUSES as $status): ?>
        <option value="<?= e($status) ?>" <?= $values['status'] === $status ? 'selected' : '' ?>>
          <?= e($status) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <div class="actions">
      <button type="submit"><?= $editing ? 'Save changes' : 'Create post' ?></button>
      <a href="index.php">Cancel</a>
    </div>
  </form>
</section>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>

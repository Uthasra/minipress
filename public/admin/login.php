<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

if (Auth::check()) {
    redirect('index.php');
}

$error = null;

// One file handles both the form (GET) and the submission (POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (Auth::attempt($username, $password)) {
        flash('Signed in.');
        redirect('index.php');
    }
    // Never say which half was wrong — that helps attackers guess usernames.
    $error = 'Those details did not match an account.';
}

$pageTitle = 'Sign in';
$bodyClass = 'admin';
require dirname(__DIR__) . '/partials/header.php';
?>

<section class="panel narrow">
  <h1>Sign in</h1>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

  <form method="post" autocomplete="off">
    <?= csrf_field() ?>
    <label for="username">Username</label>
    <input id="username" name="username" required autofocus
           value="<?= e($_POST['username'] ?? '') ?>">

    <label for="password">Password</label>
    <input id="password" name="password" type="password" required>

    <button type="submit">Sign in</button>
  </form>
  <p class="hint">First run: admin / admin123</p>
</section>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>

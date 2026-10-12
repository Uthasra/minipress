<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

Auth::requireLogin();

// Anything that changes data must arrive as POST with a valid token.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Use the delete button on the dashboard.');
}
csrf_verify();

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id > 0 && Post::find($id)) {
    Post::delete($id);
    flash('Post deleted.');
}

redirect('index.php');

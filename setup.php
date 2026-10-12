<?php
/**
 * Run once from the terminal:   php setup.php
 * Creates the tables and one admin account, then adds two sample posts.
 * Safe to run again — it only fills in what is missing.
 */
declare(strict_types=1);

require __DIR__ . '/src/helpers.php';
require __DIR__ . '/src/Database.php';

$pdo    = Database::pdo();
$driver = Database::driverName();
$now    = date('Y-m-d H:i:s');

echo "Driver: {$driver}\n";

if ($driver === 'mysql') {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        username      VARCHAR(60)  NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        display_name  VARCHAR(120) NOT NULL,
        created_at    DATETIME     NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        author_id  INT          NOT NULL,
        title      VARCHAR(180) NOT NULL,
        slug       VARCHAR(200) NOT NULL UNIQUE,
        body       MEDIUMTEXT   NOT NULL,
        status     VARCHAR(20)  NOT NULL DEFAULT 'draft',
        created_at DATETIME     NOT NULL,
        updated_at DATETIME     NOT NULL,
        INDEX idx_status_created (status, created_at),
        CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} else {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        username      TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        display_name  TEXT NOT NULL,
        created_at    TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        author_id  INTEGER NOT NULL REFERENCES users(id),
        title      TEXT NOT NULL,
        slug       TEXT NOT NULL UNIQUE,
        body       TEXT NOT NULL,
        status     TEXT NOT NULL DEFAULT 'draft',
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_status_created ON posts (status, created_at)");
}
echo "Tables ready.\n";

// --- the admin account ------------------------------------------------------
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute(['admin']);
$adminId = $stmt->fetchColumn();

if (!$adminId) {
    $insert = $pdo->prepare(
        'INSERT INTO users (username, password_hash, display_name, created_at) VALUES (?, ?, ?, ?)'
    );
    $insert->execute([
        'admin',
        password_hash('admin123', PASSWORD_DEFAULT), // change this after your first login
        'Site Admin',
        $now,
    ]);
    $adminId = (int) $pdo->lastInsertId();
    echo "Admin created  ->  username: admin   password: admin123\n";
} else {
    $adminId = (int) $adminId;
    echo "Admin already exists.\n";
}

// --- sample content ---------------------------------------------------------
if ((int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn() === 0) {
    $samples = [
        [
            'Why every CMS keeps posts in one table',
            'why-every-cms-keeps-posts-in-one-table',
            "WordPress stores pages, posts, menu items and attachments in a single table called wp_posts. One column, post_type, tells them apart.\n\nMiniPress does the simple version: a posts table with a status column. Once you understand this table, the WordPress admin screens stop looking like magic and start looking like SELECT queries.",
            'published',
        ],
        [
            'Draft: notes on templates',
            'draft-notes-on-templates',
            "A theme is just PHP files that print HTML. index.php lists posts, single.php shows one.\n\nThis post is a draft, so it is hidden from the front page but visible in the dashboard.",
            'draft',
        ],
    ];

    $insert = $pdo->prepare(
        'INSERT INTO posts (author_id, title, slug, body, status, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($samples as $i => [$title, $slug, $body, $status]) {
        $when = date('Y-m-d H:i:s', strtotime("-{$i} days"));
        $insert->execute([$adminId, $title, $slug, $body, $status, $when, $when]);
    }
    echo "Sample posts added.\n";
}

echo "\nDone. Start the site with:  php -S localhost:8000 -t public\n";

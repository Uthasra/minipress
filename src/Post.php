<?php
declare(strict_types=1);

/**
 * Everything that touches the `posts` table lives here.
 * Pages never write SQL themselves — they call Post::published(), Post::save() etc.
 * Sinhala: SQL ඔක්කොම එකම class එකක. Page වල SQL ලියන්නේ නෑ. ඒකට කියන්නේ "model" කියලා.
 */
final class Post
{
    public const STATUSES = ['draft', 'published'];

    /** Posts the public can see. */
    public static function published(int $limit = 20): array
    {
        $sql = 'SELECT p.*, u.display_name AS author
                FROM posts p
                JOIN users u ON u.id = p.author_id
                WHERE p.status = ?
                ORDER BY p.created_at DESC
                LIMIT ' . (int) $limit;

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute(['published']);
        return $stmt->fetchAll();
    }

    /** Everything, including drafts — admin only. */
    public static function all(): array
    {
        return Database::pdo()
            ->query('SELECT p.*, u.display_name AS author
                     FROM posts p JOIN users u ON u.id = p.author_id
                     ORDER BY p.updated_at DESC')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT p.*, u.display_name AS author
             FROM posts p JOIN users u ON u.id = p.author_id
             WHERE p.slug = ? AND p.status = ?'
        );
        // Two placeholders, two values — never string concatenation.
        $stmt->execute([$slug, 'published']);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data, int $authorId): int
    {
        $now  = date('Y-m-d H:i:s');
        $stmt = Database::pdo()->prepare(
            'INSERT INTO posts (author_id, title, slug, body, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $authorId,
            $data['title'],
            self::uniqueSlug($data['slug'] ?: $data['title']),
            $data['body'],
            $data['status'],
            $now,
            $now,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE posts SET title = ?, slug = ?, body = ?, status = ?, updated_at = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['title'],
            self::uniqueSlug($data['slug'] ?: $data['title'], $id),
            $data['body'],
            $data['status'],
            date('Y-m-d H:i:s'),
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** "my-post" -> "my-post-2" if the slug is already taken. */
    public static function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = slugify($source);
        $slug = $base;
        $n    = 1;

        $sql  = 'SELECT COUNT(*) FROM posts WHERE slug = ?';
        $args = [];
        if ($ignoreId !== null) {
            $sql   .= ' AND id <> ?';
            $args[] = $ignoreId;
        }
        $stmt = Database::pdo()->prepare($sql);

        while (true) {
            $stmt->execute(array_merge([$slug], $args));
            if ((int) $stmt->fetchColumn() === 0) {
                return $slug;
            }
            $slug = $base . '-' . (++$n);
        }
    }

    /** Returns a list of error messages; empty array means the data is valid. */
    public static function validate(array $data): array
    {
        $errors = [];
        if (trim($data['title']) === '') {
            $errors[] = 'Give the post a title.';
        }
        if (text_length($data['title']) > 180) {
            $errors[] = 'Keep the title under 180 characters.';
        }
        if (trim($data['body']) === '') {
            $errors[] = 'The post body is empty.';
        }
        if (!in_array($data['status'], self::STATUSES, true)) {
            $errors[] = 'Status must be draft or published.';
        }
        return $errors;
    }
}

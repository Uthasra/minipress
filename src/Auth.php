<?php
declare(strict_types=1);

/**
 * Login, logout and "is this person allowed in?".
 * Passwords are NEVER stored as plain text — only as a hash.
 * Sinhala: password එක database එකේ තියෙන්නේ hash එකක් විදියට. ආපහු කියවගන්න බෑ.
 */
final class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Stop session fixation: give the user a brand new session id on login.
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        $stmt = Database::pdo()->prepare('SELECT id, username, display_name FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('login.php');
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}

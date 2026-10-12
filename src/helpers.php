<?php
declare(strict_types=1);

/**
 * Small functions used by every page.
 */

/**
 * Escape output. EVERY variable printed into HTML goes through this.
 * Sinhala: user input කෙලින්ම HTML එකට දැම්මොත් XSS attack එකක් වෙනවා. e() එකෙන් ඒක නවත්වනවා.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

/**
 * CSRF token: proves the form was submitted from our own page,
 * not from an attacker's site while the admin is logged in.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Your session expired. Go back, reload the page and try again.');
    }
}

/** Turn "Hello World, 2026!" into "hello-world-2026" */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text) ?? '';
    return trim($text, '-') ?: 'post';
}

function excerpt(string $body, int $words = 28): string
{
    $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($body)) ?? '');
    $parts = explode(' ', $plain);
    if (count($parts) <= $words) {
        return $plain;
    }
    return implode(' ', array_slice($parts, 0, $words)) . '…';
}

function show_date(string $sqlDate): string
{
    return date('j F Y', strtotime($sqlDate));
}

/** Convert plain text into paragraphs — WordPress calls its version wpautop(). */
function autop(string $body): string
{
    $blocks = preg_split("/\n\s*\n/", trim($body)) ?: [];
    $html   = '';
    foreach ($blocks as $block) {
        $html .= '<p>' . nl2br(e(trim($block))) . '</p>';
    }
    return $html;
}

function flash(?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $out = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $out;
}

/**
 * Character count that is safe for Sinhala/Unicode text.
 * mbstring is normally on in XAMPP, but fall back to strlen if it is missing.
 */
function text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

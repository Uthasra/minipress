-- MySQL schema for MiniPress (phpMyAdmin / XAMPP).
-- Run this if you prefer SQL over `php setup.php`.

CREATE DATABASE IF NOT EXISTS minipress
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE minipress;

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(60)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name  VARCHAR(120) NOT NULL,
    created_at    DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS posts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    author_id  INT          NOT NULL,
    title      VARCHAR(180) NOT NULL,
    slug       VARCHAR(200) NOT NULL UNIQUE,
    body       MEDIUMTEXT   NOT NULL,
    status     VARCHAR(20)  NOT NULL DEFAULT 'draft',
    created_at DATETIME     NOT NULL,
    updated_at DATETIME     NOT NULL,
    INDEX idx_status_created (status, created_at),
    CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create the first admin from PHP, not SQL — the password must be hashed:
--   php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
-- then:
--   INSERT INTO users (username, password_hash, display_name, created_at)
--   VALUES ('admin', '<paste the hash here>', 'Site Admin', NOW());

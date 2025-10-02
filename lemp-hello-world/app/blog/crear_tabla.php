<?php
declare(strict_types=1);
require_once __DIR__.'/app/db.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = db();
echo "Conexión OK\n";

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin') NOT NULL DEFAULT 'admin',
  email_verified_at DATETIME NULL,
  reset_token VARCHAR(64) NULL,
  reset_expires_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  summary VARCHAR(300) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  cover_image VARCHAR(255) NULL,
  author_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

echo "Tablas listas.\n";

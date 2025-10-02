<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * CRUD de posts
 * Devuelven json_ok/json_error en operaciones mutantes
 * y arrays (no imprime) en listados para usarlos desde PHP.
 */

function posts_list(string $search = '', int $page = 1, int $perPage = 10): array {
    $pdo    = db();
    $offset = max(0, ($page - 1) * $perPage);
    $params = [];
    $where  = '';

    if ($search !== '') {
        $where = "WHERE p.title LIKE :q";
        $params[':q'] = '%'.$search.'%';
    }

    $stTotal = $pdo->prepare("SELECT COUNT(*) FROM posts p $where");
    $stTotal->execute($params);
    $count = (int)$stTotal->fetchColumn();

    $sql = "SELECT p.id, p.title, p.slug, p.summary,
                   DATE_FORMAT(p.created_at,'%Y-%m-%d %H:%i') AS created_at,
                   u.username AS author
            FROM posts p
            JOIN users u ON u.id = p.author_id
            $where
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset";
    $st = $pdo->prepare($sql);
    foreach ($params as $k => $v) $st->bindValue($k, $v, PDO::PARAM_STR);
    $st->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $st->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll();

    return [
        'rows'        => $rows,
        'page'        => $page,
        'per_page'    => $perPage,
        'total'       => $count,
        'total_pages' => max(1, (int)ceil($count / $perPage)),
    ];
}

function post_get_by_id(int $id): ?array {
    $pdo = db();
    $st  = $pdo->prepare(
        "SELECT p.id, p.title, p.slug, p.summary, p.content,
                DATE_FORMAT(p.created_at,'%Y-%m-%d %H:%i') AS created_at,
                u.username AS author
         FROM posts p
         JOIN users u ON u.id = p.author_id
         WHERE p.id = :id"
    );
    $st->execute([':id'=>$id]);
    $row = $st->fetch();
    return $row ?: null;
}

function post_create(array $data, int $author_id): int {
    $title   = trim($data['title']   ?? '');
    $summary = trim($data['summary'] ?? '');
    $content = trim($data['content'] ?? '');

    $errors = [];
    if ($title === '' || mb_strlen($title) > 160) $errors['title']   = '1–160 chars';
    if (mb_strlen($summary) > 300)                $errors['summary'] = 'máx 300';
    if ($content === '')                          $errors['content'] = 'requerido';
    if ($errors) json_error('Datos inválidos', 422, $errors);

    $slug = slugify($title);
    $pdo  = db();

    // slug único
    $base = $slug; $i = 2;
    while (true) {
        $chk = $pdo->prepare("SELECT 1 FROM posts WHERE slug = :s LIMIT 1");
        $chk->execute([':s'=>$slug]);
        if (!$chk->fetch()) break;
        $slug = $base.'-'.$i++;
    }

    $ins = $pdo->prepare(
        "INSERT INTO posts (title, slug, summary, content, author_id, created_at)
         VALUES (:t, :s, :sm, :c, :a, NOW())"
    );
    $ins->execute([
        ':t'=>$title, ':s'=>$slug, ':sm'=>$summary, ':c'=>$content, ':a'=>$author_id
    ]);

    return (int)$pdo->lastInsertId();
}

function post_update(array $data): void {
    $id      = (int)($data['id'] ?? 0);
    $title   = trim($data['title']   ?? '');
    $summary = trim($data['summary'] ?? '');
    $content = trim($data['content'] ?? '');

    if ($id <= 0) json_error('ID inválido', 422);

    $errors = [];
    if ($title === '' || mb_strlen($title) > 160) $errors['title']   = '1–160 chars';
    if (mb_strlen($summary) > 300)                $errors['summary'] = 'máx 300';
    if ($content === '')                          $errors['content'] = 'requerido';
    if ($errors) json_error('Datos inválidos', 422, $errors);

    $pdo = db();
    $upd = $pdo->prepare(
        "UPDATE posts
         SET title = :t, summary = :sm, content = :c, updated_at = NOW()
         WHERE id = :id"
    );
    $upd->execute([':t'=>$title, ':sm'=>$summary, ':c'=>$content, ':id'=>$id]);

    json_ok();
}

function post_delete(int $id): void {
    if ($id <= 0) json_error('ID inválido', 422);
    $pdo = db();
    $del = $pdo->prepare("DELETE FROM posts WHERE id = :id");
    $del->execute([':id'=>$id]);
    json_ok();
}

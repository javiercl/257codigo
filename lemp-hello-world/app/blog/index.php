<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers.php';
require_once __DIR__.'/app/posts.php';

$BASE = '/blog'; // <<--- base absoluta del blog

# API mínima para el modal "Ver Post"
if (($_GET['action'] ?? '') === 'getPost') {
    $id = (int)($_GET['id'] ?? 0);
    $post = post_get_by_id($id);
    if (!$post) json_error('No encontrado', 404);
    json_ok(['post' => $post]);
}

# Listado inicial
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$list   = posts_list($search, $page, 10);

$csrf = csrf_token();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Blog</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?= $BASE ?>/assets/css/styles.css">
</head>
<body>
<header class="topbar">
  <div class="brand">
    <div class="logo"></div>
    <div>Mi Blog</div>
    <span class="pill">Público</span>
  </div>
  <form method="get" class="search">
    <input class="input" type="text" name="q" placeholder="Buscar por título…" value="<?= e($search) ?>">
    <button class="btn" type="submit">Buscar</button>
  </form>
  <nav>
    <a class="btn ghost" href="<?= $BASE ?>/admin.php">Admin</a>
    <a class="btn ghost" href="/index.php">← Menú principal</a>
  </nav>
</header>

<main class="container grid">
  <?php foreach ($list['rows'] as $row): ?>
  <article class="card">
    <h2><?= e($row['title']) ?></h2>
    <small>Por <?= e($row['author']) ?> · <?= e($row['created_at']) ?></small>
    <p><?= e($row['summary']) ?></p>
    <button class="btn" data-open-post="<?= (int)$row['id'] ?>">Leer</button>
  </article>
  <?php endforeach; ?>
</main>

<footer class="pager">
  <?php if ($list['page'] > 1): ?>
    <a href="?q=<?= urlencode($search) ?>&page=<?= $list['page']-1 ?>">← Anterior</a>
  <?php endif; ?>
  <span>Página <?= $list['page'] ?> / <?= $list['total_pages'] ?></span>
  <?php if ($list['page'] < $list['total_pages']): ?>
    <a href="?q=<?= urlencode($search) ?>&page=<?= $list['page']+1 ?>">Siguiente →</a>
  <?php endif; ?>
</footer>

<!-- Modal Ver Post -->
<div id="modal-view" class="modal hidden">
  <div class="modal-content">
    <button class="modal-close" data-close>&times;</button>
    <h3 id="mv-title"></h3>
    <small id="mv-meta" class="muted"></small>
    <article id="mv-content" class="mt"></article>
  </div>
</div>

<script>
  const CSRF = <?= json_encode($csrf) ?>;
  const BASE = <?= json_encode($BASE) ?>;
</script>
<script src="<?= $BASE ?>/assets/js/main.js"></script>
<script>
// Abrir modal Ver Post
document.querySelectorAll('[data-open-post]').forEach(btn=>{
  btn.addEventListener('click', async ()=>{
    const id = btn.getAttribute('data-open-post');
    const r = await fetch(`${BASE}/index.php?action=getPost&id=${id}`);
    const j = await r.json();
    if (j.ok) {
      document.getElementById('mv-title').textContent = j.post.title;
      document.getElementById('mv-meta').textContent = `Por ${j.post.author} · ${j.post.created_at}`;
      document.getElementById('mv-content').textContent = j.post.content;
      openModal('#modal-view');
    } else {
      alert(j.error || 'Error');
    }
  });
});
</script>
</body>
</html>

<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers.php';
require_once __DIR__.'/app/auth.php';
require_once __DIR__.'/app/posts.php';

$BASE   = '/blog'; // <<--- base absoluta del blog
$action = $_GET['action'] ?? $_POST['action'] ?? '';

# --- Endpoints JSON SIEMPRE (POST o ?action=posts) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $action === 'posts') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf'] ?? '';
            csrf_check($token);

            switch ($action) {
                case 'login':
                    login_user(trim($_POST['login'] ?? ''), $_POST['password'] ?? '');
                    break;
                case 'register':
                    register_user(trim($_POST['username'] ?? ''), trim($_POST['email'] ?? ''), $_POST['password'] ?? '', $_POST['confirm'] ?? '');
                    break;
                case 'logout':
                    logout_user();
                    break;
                case 'createPost':
                    require_login();
                    $id = post_create($_POST, current_user()['id']);
                    json_ok(['id'=>$id]);
                    break;
                case 'updatePost':
                    require_login();
                    post_update($_POST);
                    break;
                case 'deletePost':
                    require_login();
                    post_delete((int)($_POST['id'] ?? 0));
                    break;
                default:
                    json_error('Acción no válida', 400);
            }
        }

        if ($action === 'posts') {
            require_login();
            $search = trim($_GET['search'] ?? '');
            $page   = max(1, (int)($_GET['page'] ?? 1));
            $data   = posts_list($search, $page, 10);
            json_ok(['data'=>$data['rows'], 'page'=>$data['page'], 'total_pages'=>$data['total_pages']]);
        }
    } catch (Throwable $e) {
        json_error($e->getMessage(), 500);
    }
    exit;
}

$u    = current_user();
$csrf = csrf_token();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Admin · Blog</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?= $BASE ?>/assets/css/styles.css">
</head>
<body>
<header class="topbar">
  <div class="brand">
    <div class="logo"></div>
    <div>Panel</div>
    <span class="pill">Admin</span>
  </div>
  <nav>
    <?php if ($u): ?>
      <span class="badge">Hola, <?= e($u['username']) ?></span>
      <button class="btn" id="btn-new">Nuevo post</button>
      <button class="btn ghost" id="btn-logout">Cerrar sesión</button>
    <?php else: ?>
      <button class="btn" id="btn-login">Iniciar sesión</button>
      <button class="btn ghost" id="btn-register">Crear cuenta</button>
    <?php endif; ?>
    <a class="btn ghost" href="<?= $BASE ?>/index.php">Ir al blog</a>
    <a class="btn ghost" href="/index.php">← Menú principal</a>
  </nav>
</header>

<main class="container">
<?php if (!$u): ?>
  <section class="center mt">
    <p>Para gestionar el blog, inicia sesión o crea una cuenta.</p>
  </section>
<?php else: ?>
  <section class="mt">
    <form id="form-search" class="search">
      <input class="input" type="text" id="q" placeholder="Buscar por título…">
      <button class="btn" type="submit">Buscar</button>
    </form>

    <table class="table mt" id="tbl">
      <thead><tr><th>ID</th><th>Título</th><th>Fecha</th><th>Acciones</th></tr></thead>
      <tbody></tbody>
    </table>
    <div class="pager" id="pager"></div>
  </section>
<?php endif; ?>
</main>

<!-- Modales -->
<div id="modal-login" class="modal hidden">
  <div class="modal-content">
    <button class="modal-close" data-close>&times;</button>
    <h3>Iniciar sesión</h3>
    <form id="f-login">
      <input class="input" type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input class="input" name="login" placeholder="Usuario o email" required>
      <input class="input" type="password" name="password" placeholder="Contraseña" required>
      <button class="btn mt" type="submit">Entrar</button>
    </form>
  </div>
</div>

<div id="modal-register" class="modal hidden">
  <div class="modal-content">
    <button class="modal-close" data-close>&times;</button>
    <h3>Crear cuenta</h3>
    <form id="f-register">
      <input class="input" type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input class="input" name="username" placeholder="Usuario (se normaliza automáticamente)" required>
      <input class="input" type="email" name="email" placeholder="Email" required>
      <input class="input" type="password" name="password" placeholder="Contraseña" required>
      <input class="input" type="password" name="confirm" placeholder="Confirmar contraseña" required>
      <label style="display:block;margin-top:.4rem;color:var(--muted)">
        <input type="checkbox" required> Acepto los términos
      </label>
      <button class="btn mt" type="submit">Registrar</button>
    </form>
  </div>
</div>

<div id="modal-post" class="modal hidden">
  <div class="modal-content">
    <button class="modal-close" data-close>&times;</button>
    <h3 id="mp-title">Nuevo post</h3>
    <form id="f-post">
      <input class="input" type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input class="input" type="hidden" name="id" id="p-id">
      <label>Título</label>
      <input class="input" name="title" id="p-title" required maxlength="160">
      <label>Resumen (máx 300)</label>
      <textarea class="input" name="summary" id="p-summary" maxlength="300"></textarea>
      <label>Contenido</label>
      <textarea class="input" name="content" id="p-content" required rows="6"></textarea>
      <button class="btn mt" type="submit">Guardar</button>
    </form>
  </div>
</div>

<div id="modal-del" class="modal hidden">
  <div class="modal-content">
    <button class="modal-close" data-close>&times;</button>
    <h3>Eliminar post</h3>
    <p id="del-text" style="color:var(--muted)"></p>
    <form id="f-del">
      <input class="input" type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input class="input" type="hidden" name="id" id="del-id">
      <button class="btn danger mt" type="submit">Eliminar</button>
    </form>
  </div>
</div>

<script>
  const CSRF  = <?= json_encode($csrf) ?>;
  const LOGGED= <?= json_encode((bool)$u) ?>;
  const BASE  = <?= json_encode($BASE) ?>;
</script>
<script src="<?= $BASE ?>/assets/js/main.js"></script>
<?php if ($u): ?>
<script>
// Cargar tabla
let PAGE=1, QUERY='';
async function loadPosts(page=1, q=''){
  const r = await fetch(`${BASE}/admin.php?action=posts&search=${encodeURIComponent(q)}&page=${page}`);
  const j = await r.json();
  const tbody = document.querySelector('#tbl tbody');
  tbody.innerHTML = '';
  if (j.ok) {
    PAGE = j.page;
    document.getElementById('pager').innerHTML =
      `<span>Página ${j.page} / ${j.total_pages}</span> ` +
      (j.page>1?`<button class="btn" onclick="loadPosts(${j.page-1}, '${q}')">Anterior</button>`:'') +
      (j.page<j.total_pages?` <button class="btn" onclick="loadPosts(${j.page+1}, '${q}')">Siguiente</button>`:'');
    j.data.forEach(row=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${row.id}</td>
                      <td>${escapeHtml(row.title)}</td>
                      <td>${row.created_at}</td>
                      <td>
                        <button class="btn sm" onclick="openEdit(${row.id}, '${escapeHtml(row.title)}', \`${escapeHtml(row.summary||'')}\`, \`${escapeHtml(row.content||'')}\`)">Editar</button>
                        <button class="btn sm danger" onclick="openDel(${row.id}, '${escapeHtml(row.title)}')">Eliminar</button>
                      </td>`;
      tbody.appendChild(tr);
    });
  } else alert(j.error || 'Error');
}
loadPosts();

document.getElementById('form-search').addEventListener('submit', e=>{
  e.preventDefault();
  QUERY = document.getElementById('q').value.trim();
  loadPosts(1, QUERY);
});

document.getElementById('btn-new').addEventListener('click', ()=>{
  document.getElementById('mp-title').textContent = 'Nuevo post';
  document.getElementById('p-id').value = '';
  document.getElementById('p-title').value = '';
  document.getElementById('p-summary').value = '';
  document.getElementById('p-content').value = '';
  openModal('#modal-post');
});

function openEdit(id, title, summary, content){
  document.getElementById('mp-title').textContent = 'Editar post';
  document.getElementById('p-id').value = id;
  document.getElementById('p-title').value = decodeHtml(title);
  document.getElementById('p-summary').value = decodeHtml(summary);
  document.getElementById('p-content').value = decodeHtml(content);
  openModal('#modal-post');
}

function openDel(id, title){
  document.getElementById('del-id').value = id;
  document.getElementById('del-text').textContent = `¿Eliminar "${title}"?`;
  openModal('#modal-del');
}

document.getElementById('f-post').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const isUpdate = !!fd.get('id');
  fd.append('action', isUpdate?'updatePost':'createPost');
  const r = await fetch(`${BASE}/admin.php`, {method:'POST', body:fd});
  const j = await r.json();
  if (j.ok) { closeModal('#modal-post'); loadPosts(PAGE, QUERY); }
  else alert(j.error || 'Error');
});

document.getElementById('f-del').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('action', 'deletePost');
  const r = await fetch(`${BASE}/admin.php`, {method:'POST', body:fd});
  const j = await r.json();
  if (j.ok) { closeModal('#modal-del'); loadPosts(PAGE, QUERY); }
  else alert(j.error || 'Error');
});

document.getElementById('btn-logout').addEventListener('click', async ()=>{
  const fd = new FormData();
  fd.append('csrf', CSRF);
  fd.append('action', 'logout');
  const r = await fetch(`${BASE}/admin.php`, {method:'POST', body:fd});
  const j = await r.json();
  if (j.ok) location.reload(); else alert(j.error || 'Error');
});
</script>

<?php else: ?>
<script>
document.getElementById('btn-login').addEventListener('click', ()=>openModal('#modal-login'));
document.getElementById('btn-register').addEventListener('click', ()=>openModal('#modal-register'));

document.getElementById('f-login').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const btn = e.target.querySelector('button[type="submit"]');
  btn.disabled = true;
  const fd = new FormData(e.target);
  fd.append('action', 'login');
  try {
    const r = await fetch(`${BASE}/admin.php`, { method:'POST', body: fd });
    const j = await r.json();
    if (j.ok) { location.reload(); }
    else { alert(j.error || 'Error'); }
  } catch (err) { alert('Error de red'); }
  finally { btn.disabled = false; }
});

document.getElementById('f-register').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const btn = e.target.querySelector('button[type="submit"]');
  btn.disabled = true;
  const fd = new FormData(e.target);
  fd.append('action', 'register');
  try {
    const r = await fetch(`${BASE}/admin.php`, { method:'POST', body: fd });
    const j = await r.json();
    if (j.ok) { location.reload(); }
    else {
      let msg = j.error || 'Error';
      if (j.errors) {
        const parts = [];
        for (const [k, v] of Object.entries(j.errors)) { parts.push(`${k}: ${v}`); }
        if (parts.length) msg += `\n\n• ` + parts.join('\n• ');
      }
      alert(msg);
    }
  } catch (err) { alert('Error de red'); }
  finally { btn.disabled = false; }
});
</script>
<?php endif; ?>
</body>
</html>

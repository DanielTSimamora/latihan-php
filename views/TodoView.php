<!DOCTYPE html>
<html>
<head>
    <title>PHP - Aplikasi Todolist</title>
    <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body{background:linear-gradient(180deg,#f7f9fc 0,#ffffff 60%)}
        .page-head{background:#fff;border-radius:18px;padding:22px 24px;box-shadow:0 8px 24px rgba(16,24,40,.06)}
        .table-wrap{background:#fff;border-radius:18px;box-shadow:0 8px 24px rgba(16,24,40,.06)}
        .table>tbody>tr:hover{background:#fafbff}
        .empty-state{padding:40px 16px;color:#7a8594}
        .empty-state .emo{font-size:44px;display:block;margin-bottom:8px}
        .toolbar .form-control{min-width:260px}
        .badge-soft-success{background:rgba(25,135,84,.1);color:#198754}
        .badge-soft-danger{background:rgba(220,53,69,.1);color:#dc3545}
    </style>
</head>
<body>
<div class="container-xxl py-5">

    <!-- Header -->
    <div class="page-head mb-4">
        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-1">Daftar Todo List</h1>
                <div class="small text-muted" id="metaCount"></div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodo">+ Tambah</button>
            </div>
        </div>
        <!-- progress ringkas -->
        <div class="mt-3">
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-muted">Progress selesai</span>
                <span class="text-muted" id="progressLabel">0%</span>
            </div>
            <div class="progress" style="height:10px">
                <div id="progressBar" class="progress-bar" role="progressbar" style="width:0%"></div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <form class="d-flex flex-wrap gap-2 align-items-center toolbar mb-3" method="get" action="index.php">
        <div class="btn-group" role="group" aria-label="Filter status">
            <?php
              $activeFilter = $_GET['filter'] ?? 'all';
              $activeQ = $_GET['q'] ?? '';
              function fbtn($v,$label,$activeFilter){ 
                $act = ($activeFilter === $v) ? 'active' : '';
                echo '<a href="index.php?filter='.$v.'&q='.urlencode($activeQ).'" class="btn btn-outline-secondary '.$act.'">'.$label.'</a>';
              }
            ?>
            <?php fbtn('all','Semua',$activeFilter); ?>
            <?php fbtn('done','Selesai',$activeFilter); ?>
            <?php fbtn('todo','Belum',$activeFilter); ?>
        </div>
        <input type="hidden" name="filter" value="<?= htmlspecialchars($activeFilter) ?>">
        <div class="ms-auto d-flex gap-2">
            <input type="search" name="q" id="searchBox" value="<?= htmlspecialchars($activeQ) ?>" class="form-control" placeholder="Cari judul/desk…">
            <button class="btn btn-outline-primary" type="submit">Cari</button>
        </div>
    </form>

    <!-- Alert error (judul duplikat) -->
    <?php if (!empty($err) && $err === 'dup'): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        Judul todo sudah ada. Gunakan judul lain.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Tabel -->
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px"></th> <!-- handle drag -->
                        <th style="width:56px">#</th>
                        <th>Judul</th>
                        <th style="width:26%">Deskripsi</th>
                        <th style="width:120px">Status</th>
                        <th style="width:180px">Dibuat</th>
                        <th style="width:220px" class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody id="todoBody">
                <?php if (!empty($todos)): ?>
                    <?php foreach ($todos as $i => $todo): ?>
                    <tr data-id="<?= $todo['id'] ?>" data-status="<?= $todo['is_finished'] ? 'done' : 'todo' ?>"
                        data-title="<?= htmlspecialchars($todo['title']) ?>"
                        data-desc="<?= htmlspecialchars($todo['description']) ?>"
                        data-created="<?= htmlspecialchars(date('d F Y - H:i', strtotime($todo['created_at']))) ?>"
                        data-updated="<?= htmlspecialchars(date('d F Y - H:i', strtotime($todo['updated_at']))) ?>">
                        <td class="text-muted"><span class="drag-handle" style="cursor:grab">⋮⋮</span></td>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($todo['title']) ?></td>
                        <td class="text-truncate" style="max-width:280px"><?= htmlspecialchars($todo['description']) ?></td>
                        <td>
                            <?php if ($todo['is_finished']): ?>
                                <span class="badge badge-soft-success">Selesai</span>
                            <?php else: ?>
                                <span class="badge badge-soft-danger">Belum</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><?= date('d F Y - H:i', strtotime($todo['created_at'])) ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info text-white" onclick="showDetail(this.closest('tr'))">Detail</button>
                                <button class="btn btn-warning"
                                    onclick="showModalEditTodo(
                                      <?= $todo['id'] ?>,
                                      '<?= htmlspecialchars(addslashes($todo['title'])) ?>',
                                      '<?= htmlspecialchars(addslashes($todo['description'])) ?>',
                                      <?= $todo['is_finished'] ? 1 : 0 ?>
                                    )">Ubah</button>
                                <button class="btn btn-danger"
                                    onclick="showModalDeleteTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['title'])) ?>')">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="border-0">
                        <td colspan="7">
                            <div class="empty-state text-center">
                                <span class="emo">📝</span>
                                <div class="fw-semibold mb-1">Belum ada data tersedia</div>
                                <div class="small mb-3">Mulai dengan menambahkan todo pertamamu.</div>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodo">Tambah Todo</button>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL ADD -->
<div class="modal fade" id="addTodo" tabindex="-1" aria-labelledby="addTodoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="addTodoLabel">Tambah Todo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="?page=create&filter=<?= urlencode($activeFilter) ?>&q=<?= urlencode($activeQ) ?>" method="POST">
        <div class="modal-body pt-0">
          <div class="form-floating mb-3">
            <input type="text" name="title" class="form-control" id="inputTitle" placeholder="Judul" required>
            <label for="inputTitle">Judul</label>
          </div>
          <div class="form-floating">
            <textarea name="description" class="form-control" id="inputDesc" placeholder="Deskripsi" style="height:120px"></textarea>
            <label for="inputDesc">Deskripsi</label>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="editTodo" tabindex="-1" aria-labelledby="editTodoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="editTodoLabel">Ubah Todo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="?page=update&filter=<?= urlencode($activeFilter) ?>&q=<?= urlencode($activeQ) ?>" method="POST">
        <input name="id" type="hidden" id="inputEditId">
        <div class="modal-body pt-0">
          <div class="form-floating mb-3">
            <input type="text" name="title" class="form-control" id="inputEditTitle" placeholder="Judul" required>
            <label for="inputEditTitle">Judul</label>
          </div>
          <div class="form-floating mb-3">
            <textarea name="description" class="form-control" id="inputEditDesc" placeholder="Deskripsi" style="height:120px"></textarea>
            <label for="inputEditDesc">Deskripsi</label>
          </div>
          <div class="form-floating">
            <select class="form-select" name="is_finished" id="selectEditStatus">
              <option value="0">Belum Selesai</option>
              <option value="1">Selesai</option>
            </select>
            <label for="selectEditStatus">Status</label>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL DELETE -->
<div class="modal fade" id="deleteTodo" tabindex="-1" aria-labelledby="deleteTodoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="deleteTodoLabel">Hapus Todo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0">
        <div class="mb-2">Kamu akan menghapus <strong class="text-danger" id="deleteTodoTitle"></strong>.</div>
        <div class="small text-muted">Tindakan ini tidak bisa dibatalkan.</div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <a id="btnDeleteTodo" class="btn btn-danger">Ya, Hapus</a>
      </div>
    </div>
  </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal fade" id="detailTodo" tabindex="-1" aria-labelledby="detailTodoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="detailTodoLabel">Detail Todo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0">
        <div class="mb-2"><strong id="dTitle"></strong></div>
        <div class="mb-3" id="dDesc"></div>
        <div class="small text-muted">
          <div>Dibuat: <span id="dCreated"></span></div>
          <div>Diupdate: <span id="dUpdated"></span></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
/* ------- Modals ------- */
function showModalEditTodo(id, title, desc, status) {
  document.getElementById("inputEditId").value = id;
  document.getElementById("inputEditTitle").value = title;
  document.getElementById("inputEditDesc").value = desc.replaceAll('\\n', "\n");
  document.getElementById("selectEditStatus").value = status;
  new bootstrap.Modal(document.getElementById("editTodo")).show();
}
function showModalDeleteTodo(id, title) {
  document.getElementById("deleteTodoTitle").innerText = title;
  document.getElementById("btnDeleteTodo").setAttribute("href", `?page=delete&id=${id}&filter=<?= urlencode($activeFilter) ?>&q=<?= urlencode($activeQ) ?>`);
  new bootstrap.Modal(document.getElementById("deleteTodo")).show();
}
function showDetail(tr){
  document.getElementById('dTitle').innerText   = tr.dataset.title || '';
  document.getElementById('dDesc').innerText    = tr.dataset.desc || '-';
  document.getElementById('dCreated').innerText = tr.dataset.created || '';
  document.getElementById('dUpdated').innerText = tr.dataset.updated || '';
  new bootstrap.Modal(document.getElementById("detailTodo")).show();
}

/* ------- Progress & meta ------- */
const bodyRows = Array.from(document.querySelectorAll('#todoBody tr[data-status]'));
const metaCount = document.getElementById('metaCount');
const bar = document.getElementById('progressBar');
const label = document.getElementById('progressLabel');

function refreshStats(){
  const total = bodyRows.length;
  const done  = bodyRows.filter(r => r.dataset.status === 'done' && r.style.display !== 'none').length;
  const shown = bodyRows.filter(r => r.style.display !== 'none').length;
  const pct   = total ? Math.round(done / total * 100) : 0;
  if (bar) bar.style.width = pct + '%';
  if (label) label.textContent = pct + '%';
  if (metaCount) metaCount.textContent = (shown || 0) + ' item ditampilkan';
}
refreshStats();

/* ------- Drag & Drop (persist ke DB) ------- */
const tbody = document.getElementById('todoBody');

function renumber(){
  Array.from(tbody.querySelectorAll('tr')).forEach((tr, idx) => {
    const noCell = tr.querySelector('td:nth-child(2)');
    if (noCell) noCell.textContent = idx + 1;
  });
  refreshStats();
}

if (tbody) {
  new Sortable(tbody, {
    handle: '.drag-handle',
    animation: 150,
    ghostClass: 'table-active',
    onEnd: function () {
      const ids = Array.from(tbody.querySelectorAll('tr[data-id]')).map(tr => tr.getAttribute('data-id'));
      const form = new FormData();
      ids.forEach(id => form.append('order[]', id));

      fetch('?page=reorder', { method: 'POST', body: form })
        .then(r => r.json())
        .then(res => { if (res.ok) renumber(); })
        .catch(console.error);
    }
  });
}
</script>
</body>
</html>

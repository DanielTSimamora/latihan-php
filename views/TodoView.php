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
        .status-pill{font-weight:600}
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
                <h1 class="h3 mb-1">Daftar Todo List Saya</h1>
                <div class="small text-muted" id="metaCount"></div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodo">+ Tambah</button>
            </div>
        </div>
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

    <!-- Toolbar (filter/search client-side) -->
    <div class="d-flex flex-wrap gap-2 align-items-center toolbar mb-3">
        <div class="btn-group" role="group" aria-label="Filter status">
            <button class="btn btn-outline-secondary active" data-filter="all">Semua</button>
            <button class="btn btn-outline-secondary" data-filter="done">Selesai</button>
            <button class="btn btn-outline-secondary" data-filter="todo">Belum</button>
        </div>
        <div class="ms-auto d-flex gap-2">
            <input type="search" id="searchBox" class="form-control" placeholder="Cari aktivitas…">
        </div>
    </div>

    <!-- Tabel -->
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px"></th> <!-- handle drag -->
                        <th style="width:56px">#</th>
                        <th>Aktivitas</th>
                        <th style="width:140px">Status</th>
                        <th style="width:220px">Tanggal Dibuat</th>
                        <th style="width:220px" class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody id="todoBody">
                <?php if (!empty($todos)): ?>
                    <?php foreach ($todos as $i => $todo): ?>
                    <tr data-id="<?= (int)$todo['id'] ?>" data-status="<?= !empty($todo['status']) ? 'done' : 'todo' ?>">
                        <td class="text-muted"><span class="drag-handle" style="cursor:grab">⋮⋮</span></td>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td>
                          <div class="fw-semibold"><?= htmlspecialchars($todo['activity']) ?></div>
                        </td>
                        <td>
                            <?php if (!empty($todo['status'])): ?>
                                <span class="badge badge-soft-success status-pill">Selesai</span>
                            <?php else: ?>
                                <span class="badge badge-soft-danger status-pill">Belum Selesai</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted">
                            <?= !empty($todo['created_at']) ? date('d F Y - H:i', strtotime($todo['created_at'])) : '-' ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info"
                                    onclick="showModalDetailTodo(<?= (int)$todo['id'] ?>)">Detail</button>
                                <button class="btn btn-warning"
                                    onclick="showModalEditTodo(<?= (int)$todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['activity'])) ?>', <?= (int)$todo['status'] ?>)">
                                    Ubah
                                </button>
                                <button class="btn btn-danger"
                                    onclick="showModalDeleteTodo(<?= (int)$todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['activity'])) ?>')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="border-0">
                        <td colspan="6">
                            <div class="empty-state text-center">
                                <span class="emo">📝</span>
                                <div class="fw-semibold mb-1">Belum ada data tersedia</div>
                                <div class="small mb-3">Mulai dengan menambahkan aktivitas pertamamu.</div>
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

<!-- MODAL ADD TODO -->
<div class="modal fade" id="addTodo" tabindex="-1" aria-labelledby="addTodoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="addTodoLabel">Tambah Data Todo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="?page=create" method="POST">
        <div class="modal-body pt-0">
          <div class="form-floating">
            <input type="text" name="activity" class="form-control" id="inputActivity" placeholder="Aktivitas" required>
            <label for="inputActivity">Aktivitas</label>
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

<!-- MODAL EDIT TODO -->
<div class="modal fade" id="editTodo" tabindex="-1" aria-labelledby="editTodoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="editTodoLabel">Ubah Data Todo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="?page=update" method="POST">
        <input name="id" type="hidden" id="inputEditTodoId">
        <div class="modal-body pt-0">
          <div class="form-floating mb-3">
            <input type="text" name="activity" class="form-control" id="inputEditActivity" placeholder="Aktivitas" required>
            <label for="inputEditActivity">Aktivitas</label>
          </div>
          <div class="form-floating">
            <select class="form-select" name="status" id="selectEditStatus">
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

<!-- MODAL DELETE TODO -->
<div class="modal fade" id="deleteTodo" tabindex="-1" aria-labelledby="deleteTodoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="deleteTodoLabel">Hapus Data Todo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0">
        <div class="mb-2">
          Kamu akan menghapus todo <strong class="text-danger" id="deleteTodoActivity"></strong>.
        </div>
        <div class="small text-muted">Tindakan ini tidak bisa dibatalkan.</div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <a id="btnDeleteTodo" class="btn btn-danger">Ya, Hapus</a>
      </div>
    </div>
  </div>
</div>

<!-- MODAL DETAIL TODO -->
<div class="modal fade" id="detailTodo" tabindex="-1" aria-labelledby="detailTodoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="detailTodoLabel">Detail Todo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0">
        <dl class="row mb-0">
          <dt class="col-4">ID</dt>
          <dd class="col-8" id="d_id">-</dd>

          <dt class="col-4">Aktivitas</dt>
          <dd class="col-8" id="d_activity">-</dd>

          <dt class="col-4">Status</dt>
          <dd class="col-8" id="d_status">-</dd>

          <dt class="col-4">Posisi</dt>
          <dd class="col-8" id="d_position">-</dd>

          <dt class="col-4">Dibuat</dt>
          <dd class="col-8" id="d_created_at">-</dd>

          <dt class="col-4">Diupdate</dt>
          <dd class="col-8" id="d_updated_at">-</dd>
        </dl>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function showModalEditTodo(todoId, activity, status) {
  document.getElementById("inputEditTodoId").value = todoId;
  document.getElementById("inputEditActivity").value = activity;
  document.getElementById("selectEditStatus").value = status;
  new bootstrap.Modal(document.getElementById("editTodo")).show();
}
function showModalDeleteTodo(todoId, activity) {
  document.getElementById("deleteTodoActivity").innerText = activity;
  document.getElementById("btnDeleteTodo").setAttribute("href", `?page=delete&id=${todoId}`);
  new bootstrap.Modal(document.getElementById("deleteTodo")).show();
}

/* ---- Format tanggal ---- */
function fmtDate(s){
  if(!s) return '-';
  const d = new Date(s);
  if (isNaN(d.getTime())) return s;
  return d.toLocaleString('id-ID', {
    day: '2-digit', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit', hour12: false
  }).replace(',', ' -');
}

/* ---- Modal Detail ---- */
function showModalDetailTodo(id){
  // reset placeholder
  document.getElementById('d_id').textContent = '...';
  document.getElementById('d_activity').textContent = '...';
  document.getElementById('d_status').textContent = '...';
  document.getElementById('d_position').textContent = '...';
  document.getElementById('d_created_at').textContent = '...';
  document.getElementById('d_updated_at').textContent = '...';

  fetch(`?page=detail&id=${id}`, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(res => {
      if(!res.ok){ throw new Error(res.msg || 'Gagal ambil detail'); }
      const t = res.data;
      document.getElementById('d_id').textContent = t.id ?? '-';
      document.getElementById('d_activity').textContent = t.activity ?? '-';
      document.getElementById('d_status').textContent = (String(t.status) === '1') ? 'Selesai' : 'Belum Selesai';
      document.getElementById('d_position').textContent = t.position ?? '-';
      document.getElementById('d_created_at').textContent = fmtDate(t.created_at);
      document.getElementById('d_updated_at').textContent = t.updated_at ? fmtDate(t.updated_at) : '-';
    })
    .catch(err => {
      document.getElementById('d_activity').textContent = 'Error: ' + err.message;
    })
    .finally(() => {
      new bootstrap.Modal(document.getElementById('detailTodo')).show();
    });
}

/* ---- Filter/Search/Progress (client-side) ---- */
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

document.querySelectorAll('[data-filter]').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    document.querySelectorAll('[data-filter]').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const which = btn.dataset.filter; // all, done, todo
    bodyRows.forEach(tr=>{
      tr.style.display =
        which === 'all' ? '' :
        (tr.dataset.status === which ? '' : 'none');
    });
    search();
  });
});

const sb = document.getElementById('searchBox');
sb && sb.addEventListener('input', search);
function search(){
  const q = (sb?.value || '').toLowerCase();
  bodyRows.forEach(tr=>{
    const text = tr.querySelector('td:nth-child(3) .fw-semibold')?.textContent.toLowerCase() || '';
    if(tr.style.display === 'none') return;
    tr.style.display = text.includes(q) ? '' : 'none';
  });
  refreshStats();
}

/* ---- Drag & Drop sorting (persist ke DB) ---- */
const tbody = document.getElementById('todoBody');

function renumber(){
  Array.from(tbody.querySelectorAll('tr[data-id]')).forEach((tr, i) => {
    const noCell = tr.querySelector('td:nth-child(2)'); // kolom nomor (#) di posisi ke-2
    if (noCell) noCell.textContent = i + 1;
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
        .then(res => {
          if (!res.ok) {
            console.error('Gagal simpan urutan');
            return;
          }
          renumber(); // update tampilan nomor setelah berhasil
        })
        .catch(console.error);
    }
  });
}
</script>
</body>
</html>

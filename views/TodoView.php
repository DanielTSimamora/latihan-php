<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>PHP - Aplikasi Todolist</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- (Opsional) Bootstrap Icons untuk ikon-ikon kecil -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --bg: #0b1020;
      --fg: #e7ebf3;
      --muted:#9aa3b2;
      --card:#121a33;
      --card-2:#0e1630;
      --primary:#6ea8fe; /* bootstrap primary 500-ish */
      --accent:#a78bfa;  /* purple-ish */
      --success-soft: rgba(25,135,84,.15);
      --danger-soft: rgba(220,53,69,.15);
      --ring: rgba(110,168,254,.45);
    }
    @media (prefers-color-scheme: light){
      :root{
        --bg:#f6f7fb; --fg:#101322; --muted:#657086;
        --card:#ffffff; --card-2:#ffffff;
        --ring: rgba(13,110,253,.25);
      }
    }
    html,body{background: radial-gradient(1200px 800px at 80% -100px, #3b82f6 0%, transparent 60%), radial-gradient(900px 600px at -10% -80px, #a78bfa 0%, transparent 55%), var(--bg); color: var(--fg);}
    .shell{max-width: 1100px; margin: 0 auto; padding: 56px 16px;}
    .glass{
      background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.02));
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,.12);
      border-radius: 18px;
      box-shadow: 0 10px 40px rgba(2,6,23,.35);
    }
    .header{
      padding: 22px 24px;
      background: var(--card);
      border-radius: 18px;
      box-shadow: 0 10px 32px rgba(2,6,23,.25);
      border: 1px solid rgba(255,255,255,.08);
    }
    .toolbar{
      display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
      margin-top: 14px;
    }
    .pill{
      border: 1px solid rgba(255,255,255,.15);
      color: var(--fg);
    }
    .pill.active{
      background: linear-gradient(180deg, var(--primary), #3b82f6);
      border-color: transparent;
      color: white;
      box-shadow: 0 6px 18px rgba(59,130,246,.35);
    }
    .progress-wrap{margin-top: 12px}
    .progress{height: 10px; background: rgba(255,255,255,.12)}
    .progress-bar{background: linear-gradient(90deg, var(--primary), var(--accent))}
    .meta{color: var(--muted)}

    /* List */
    .list-wrap{margin-top:16px}
    .todo-card{
      background: var(--card-2);
      border: 1px solid rgba(255,255,255,.10);
      border-radius: 16px;
      padding: 14px 14px;
      display: flex; gap: 12px; align-items: start;
      transition: transform .08s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .todo-card:hover{
      transform: translateY(-2px);
      border-color: rgba(110,168,254,.35);
      box-shadow: 0 14px 40px rgba(2,6,23,.35);
    }
    .drag-handle{
      cursor: grab; user-select: none;
      color: var(--muted);
      display:flex; align-items:center; padding: 6px 4px;
    }
    .todo-main{flex:1 1 auto; min-width: 0}
    .todo-title{font-weight: 600; letter-spacing:.2px}
    .todo-sub{font-size: .9rem; color: var(--muted)}
    .badge-soft-success{background: var(--success-soft); color:#198754; font-weight:600}
    .badge-soft-danger{background: var(--danger-soft); color:#dc3545; font-weight:600}
    .actions{display:flex; gap:8px}
    .btn-ghost{
      background: transparent; border: 1px solid rgba(255,255,255,.12); color: var(--fg);
    }
    .btn-ghost:hover{
      border-color: var(--ring); box-shadow: 0 0 0 4px var(--ring);
    }
    .empty{
      text-align:center; padding: 48px 16px; color: var(--muted)
    }
    .empty .emo{font-size:44px; display:block; margin-bottom:8px}
    .list-gap{display: grid; gap: 12px}

    /* Offcanvas tweak (detail) */
    .offcanvas{background: var(--card); color: var(--fg)}
    .dl-compact dt{color: var(--muted); width: 110px}
    .dl-compact dd{margin-left: 0}
  </style>
</head>
<body>
  <div class="shell">

    <!-- Header / Summary -->
    <div class="header">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
        <div>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check2-square fs-4 text-primary"></i>
            <h1 class="h4 m-0">Todo List</h1>
          </div>
          <div class="meta mt-1" id="metaCount">0 item</div>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodo">
            <i class="bi bi-plus-lg me-1"></i>Tambah
          </button>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar">
        <div class="btn-group">
          <button class="btn pill active" data-filter="all"><i class="bi bi-list-ul me-1"></i>Semua</button>
          <button class="btn pill" data-filter="done"><i class="bi bi-check2 me-1"></i>Selesai</button>
          <button class="btn pill" data-filter="todo"><i class="bi bi-hourglass-split me-1"></i>Belum</button>
        </div>
        <div class="ms-auto d-flex gap-2">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="searchBox" class="form-control" placeholder="Cari aktivitas…">
          </div>
        </div>
      </div>

      <!-- Progress -->
      <div class="progress-wrap">
        <div class="d-flex justify-content-between small mb-1">
          <span class="meta">Progress selesai</span>
          <span class="meta" id="progressLabel">0%</span>
        </div>
        <div class="progress">
          <div id="progressBar" class="progress-bar" role="progressbar" style="width:0%"></div>
        </div>
      </div>
    </div>

    <!-- List -->
    <div class="list-wrap">
      <div id="todoList" class="list-gap">
        <?php if (!empty($todos)): ?>
          <?php foreach ($todos as $todo): ?>
            <div class="todo-card" data-id="<?= (int)$todo['id'] ?>" data-status="<?= !empty($todo['status']) ? 'done' : 'todo' ?>">
              <div class="drag-handle" title="Drag untuk ubah urutan" aria-label="Drag handle">
                <i class="bi bi-grip-vertical fs-5"></i>
              </div>
              <div class="todo-main">
                <div class="todo-title text-truncate"><?= htmlspecialchars($todo['activity']) ?></div>
                <div class="todo-sub mt-1 d-flex flex-wrap gap-2 align-items-center">
                  <?php if (!empty($todo['status'])): ?>
                    <span class="badge badge-soft-success">Selesai</span>
                  <?php else: ?>
                    <span class="badge badge-soft-danger">Belum Selesai</span>
                  <?php endif; ?>
                  <span>•</span>
                  <span>Dibuat: <?= !empty($todo['created_at']) ? date('d F Y - H:i', strtotime($todo['created_at'])) : '-' ?></span>
                </div>
              </div>
              <div class="actions">
                <button class="btn btn-ghost btn-sm" onclick="openDetail(<?= (int)$todo['id'] ?>)">
                  <i class="bi bi-info-circle"></i>
                </button>
                <button class="btn btn-ghost btn-sm"
                  onclick="showModalEditTodo(<?= (int)$todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['activity'])) ?>', <?= (int)$todo['status'] ?>)">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-ghost btn-sm"
                  onclick="showModalDeleteTodo(<?= (int)$todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['activity'])) ?>')">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="glass empty">
            <span class="emo">📝</span>
            <div class="fw-semibold mb-1">Belum ada data tersedia</div>
            <div class="small mb-3">Mulai dengan menambahkan aktivitas pertamamu.</div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodo">
              <i class="bi bi-plus-lg me-1"></i>Tambah Todo
            </button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MODALS -->
  <!-- ADD -->
  <div class="modal fade" id="addTodo" tabindex="-1" aria-labelledby="addTodoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="addTodoLabel">Tambah Data Todo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
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

  <!-- EDIT -->
  <div class="modal fade" id="editTodo" tabindex="-1" aria-labelledby="editTodoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="editTodoLabel">Ubah Data Todo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
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

  <!-- DELETE -->
  <div class="modal fade" id="deleteTodo" tabindex="-1" aria-labelledby="deleteTodoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="deleteTodoLabel">Hapus Data Todo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
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

  <!-- OFFCANVAS DETAIL -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="detailPane" aria-labelledby="detailPaneLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="detailPaneLabel">Detail Todo</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>
    <div class="offcanvas-body">
      <dl class="row dl-compact">
        <dt class="col-4">ID</dt><dd class="col-8" id="d_id">-</dd>
        <dt class="col-4">Aktivitas</dt><dd class="col-8" id="d_activity">-</dd>
        <dt class="col-4">Status</dt><dd class="col-8" id="d_status">-</dd>
        <dt class="col-4">Posisi</dt><dd class="col-8" id="d_position">-</dd>
        <dt class="col-4">Dibuat</dt><dd class="col-8" id="d_created_at">-</dd>
        <dt class="col-4">Diupdate</dt><dd class="col-8" id="d_updated_at">-</dd>
      </dl>
    </div>
  </div>

  <script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
  <script>
    /* ==== Helpers ==== */
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
    function fmtDate(s){
      if(!s) return '-';
      const d = new Date(s);
      if (isNaN(d.getTime())) return s;
      return d.toLocaleString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit', hour12: false
      }).replace(',', ' -');
    }

    /* ==== Detail (Offcanvas) ==== */
    function openDetail(id){
      // reset
      ["d_id","d_activity","d_status","d_position","d_created_at","d_updated_at"]
        .forEach(k => document.getElementById(k).textContent = "...");
      fetch(`?page=detail&id=${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(res => {
          if(!res.ok) throw new Error(res.msg || 'Gagal ambil detail');
          const t = res.data;
          d_id.textContent = t.id ?? '-';
          d_activity.textContent = t.activity ?? '-';
          d_status.textContent = String(t.status) === '1' ? 'Selesai' : 'Belum Selesai';
          d_position.textContent = t.position ?? '-';
          d_created_at.textContent = fmtDate(t.created_at);
          d_updated_at.textContent = t.updated_at ? fmtDate(t.updated_at) : '-';
        })
        .catch(err => { d_activity.textContent = 'Error: ' + err.message; })
        .finally(() => {
          new bootstrap.Offcanvas(document.getElementById('detailPane')).show();
        });
    }

    /* ==== Filter / Search / Progress ==== */
    const cards = () => Array.from(document.querySelectorAll('#todoList .todo-card'));
    const metaCount = document.getElementById('metaCount');
    const bar = document.getElementById('progressBar');
    const label = document.getElementById('progressLabel');

    function refreshStats(){
      const all = cards();
      const total = all.length;
      const shownList = all.filter(el => el.style.display !== 'none');
      const doneShown = shownList.filter(el => el.dataset.status === 'done').length;
      const pct = total ? Math.round(doneShown / total * 100) : 0;
      if (bar) bar.style.width = pct + '%';
      if (label) label.textContent = pct + '%';
      if (metaCount) metaCount.textContent = (shownList.length || 0) + ' item ditampilkan';
    }
    refreshStats();

    document.querySelectorAll('[data-filter]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        document.querySelectorAll('[data-filter]').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const which = btn.dataset.filter; // all, done, todo
        cards().forEach(el=>{
          el.style.display = (which === 'all' ? '' : (el.dataset.status === which ? '' : 'none'));
        });
        applySearch(); // chain dengan search
      });
    });

    const sb = document.getElementById('searchBox');
    sb && sb.addEventListener('input', applySearch);
    function applySearch(){
      const q = (sb?.value || '').toLowerCase();
      const activeFilter = document.querySelector('[data-filter].active')?.dataset.filter || 'all';
      cards().forEach(el=>{
        // pertama cek filter
        const passFilter = (activeFilter === 'all') || (el.dataset.status === activeFilter);
        // lalu cek search
        const text = el.querySelector('.todo-title')?.textContent.toLowerCase() || '';
        const passSearch = text.includes(q);
        el.style.display = (passFilter && passSearch) ? '' : 'none';
      });
      refreshStats();
    }

    /* ==== Drag & Drop (persist ke DB) ==== */
    const list = document.getElementById('todoList');
    if (list){
      new Sortable(list, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd: function(){
          const ids = cards().map(el => el.getAttribute('data-id'));
          const form = new FormData();
          ids.forEach(id => form.append('order[]', id));
          fetch('?page=reorder', { method: 'POST', body: form })
            .then(r => r.json())
            .then(res => {
              if (!res.ok) console.error('Gagal simpan urutan');
            })
            .catch(console.error);
        }
      });
    }
  </script>
</body>
</html>

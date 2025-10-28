<?php /* views/TodoView.php */ ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Todo MVC</title>

  <!-- Bootstrap lokal -->
  <link rel="stylesheet" href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <style>
    .todo-item{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px;border:1px solid #eee;border-radius:12px;margin-bottom:8px;background:#fff}
    .todo-left{display:flex;align-items:center;gap:10px}
    .title.done{text-decoration:line-through;color:#999}
    .drag{opacity:.7}
  </style>

  <!-- SortableJS -->
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
</head>
<body class="container py-4">
  <h3 class="mb-3">Todo List</h3>

  <?php if($ok): ?><div class="alert alert-success"><?=$ok?></div><?php endif; ?>
  <?php if($err): ?><div class="alert alert-danger"><?=$err?></div><?php endif; ?>

  <form action="/?action=create" method="post" class="row g-2 mb-3">
    <div class="col-md-4"><input class="form-control" name="title" placeholder="Judul" required></div>
    <div class="col-md-6"><input class="form-control" name="description" placeholder="Deskripsi (opsional)"></div>
    <div class="col-md-2 d-grid"><button class="btn btn-dark">Tambah</button></div>
  </form>

  <form method="get" class="row g-2 mb-3">
    <input type="hidden" name="action" value="index">
    <div class="col-md-3">
      <select name="filter" class="form-select">
        <option value="all"       <?=$filter==='all'?'selected':''?>>Semua</option>
        <option value="unfinished"<?=$filter==='unfinished'?'selected':''?>>Belum selesai</option>
        <option value="finished"  <?=$filter==='finished'?'selected':''?>>Selesai</option>
      </select>
    </div>
    <div class="col-md-7"><input class="form-control" type="search" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Cari judul/desk"></div>
    <div class="col-md-2 d-grid"><button class="btn btn-outline-primary">Terapkan</button></div>
  </form>

  <ul id="todo-list" class="list-unstyled">
    <?php foreach ($todos as $t): ?>
      <li class="todo-item" data-id="<?=$t['id']?>">
        <div class="todo-left">
          <input type="checkbox" onchange="toggleFinish(<?=$t['id']?>, this.checked)" <?=$t['is_finished']?'checked':''?>>
          <div>
            <div class="title <?=$t['is_finished']?'done':''?>"><?=htmlspecialchars($t['title'])?></div>
            <?php if($t['description']): ?><small class="text-muted"><?=htmlspecialchars($t['description'])?></small><?php endif; ?>
          </div>
        </div>
        <div>
          <a class="btn btn-sm btn-light" href="/?action=detail&id=<?=$t['id']?>">Detail</a>
          <a class="btn btn-sm btn-outline-danger" href="/?action=delete&id=<?=$t['id']?>" onclick="return confirm('Hapus?')">Hapus</a>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>

<script>
// Drag & drop + persist
new Sortable(document.getElementById('todo-list'), {
  animation:150, ghostClass:'drag',
  onEnd: function(){
    const ids = [...document.querySelectorAll('#todo-list .todo-item')].map(li=>li.dataset.id);
    fetch('/?action=reorder', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ids}) })
      .then(r=>r.json()).then(res=>{ if(!res.ok) alert('Gagal simpan urutan'); })
      .catch(()=>alert('Gagal simpan urutan'));
  }
});

function toggleFinish(id, checked){
  fetch('/?action=toggle', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ id, is_finished: !!checked })
  }).then(r=>r.json()).then(res=>{
    if(!res.ok){ alert(res.msg||'Gagal update'); location.reload(); }
  }).catch(()=>{ alert('Gagal update'); location.reload(); });
}
</script>
<script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

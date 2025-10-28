<?php
// controllers/TodoController.php
require_once __DIR__.'/../models/TodoModel.php';

class TodoController {
  private $m;
  public function __construct(){ $this->m = new TodoModel(); }

  public function index() {
    $filter = $_GET['filter'] ?? 'all';
    $q      = trim($_GET['q'] ?? '');
    $todos  = $this->m->list($filter,$q);
    $ok     = $_GET['ok'] ?? null;
    $err    = $_GET['err'] ?? null;
    require __DIR__.'/../views/TodoView.php';
  }

  public function create() {
    try {
      $this->m->create(trim($_POST['title'] ?? ''), trim($_POST['description'] ?? ''));
      header("Location: /?ok=Berhasil tambah"); 
    } catch (Exception $e) {
      header("Location: /?err=".$e->getMessage());
    }
  }

  public function delete() { $this->m->delete((int)($_GET['id']??0)); header("Location: /?ok=Terhapus"); }

  public function toggle() {
    // AJAX JSON
    $in = json_decode(file_get_contents('php://input'), true);
    try {
      $this->m->toggle((int)($in['id']??0), !empty($in['is_finished']));
      echo json_encode(['ok'=>true]);
    } catch(Throwable $e){ http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }
  }

  public function reorder() {
    $in = json_decode(file_get_contents('php://input'), true);
    try {
      $this->m->reorder($in['ids'] ?? []);
      echo json_encode(['ok'=>true]);
    } catch(Throwable $e){ http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }
  }

  public function detail() {
    $todo = $this->m->detail((int)($_GET['id']??0));
    if(!$todo){ http_response_code(404); echo "Not found"; return; }
    // View mini detail sederhana:
    echo "<h2>Detail Todo</h2>
      <ul>
        <li><b>Judul:</b> ".htmlspecialchars($todo['title'])."</li>
        <li><b>Deskripsi:</b> ".nl2br(htmlspecialchars($todo['description']??''))."</li>
        <li><b>Status:</b> ".($todo['is_finished']?'Selesai':'Belum selesai')."</li>
        <li><b>Dibuat:</b> {$todo['created_at']}</li>
        <li><b>Diupdate:</b> {$todo['updated_at']}</li>
        <li><b>Urutan:</b> {$todo['sort_order']}</li>
      </ul>
      <p><a href='/'>← Kembali</a></p>";
  }
}

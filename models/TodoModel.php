<?php
// models/TodoModel.php
require_once __DIR__.'/../config.php';

class TodoModel {
  private $db;
  public function __construct() { $this->db = pdo(); }

  public function create($title, $desc) {
    // validasi unik
    $q = $this->db->prepare("SELECT 1 FROM todos WHERE lower(title)=lower(:t)");
    $q->execute([':t'=>$title]);
    if ($q->fetch()) throw new Exception('Judul sudah dipakai.');

    // set sort_order = max+1
    $max = (int)$this->db->query("SELECT COALESCE(MAX(sort_order),0) m FROM todos")->fetch()['m'];
    $stmt = $this->db->prepare("INSERT INTO todos (title,description,sort_order) VALUES (:t,:d,:o)");
    $stmt->execute([':t'=>$title, ':d'=>$desc, ':o'=>$max+1]);
  }

  public function list($filter='all', $q='') {
    $where=[]; $p=[];
    if ($filter==='finished')   $where[]="is_finished=true";
    if ($filter==='unfinished') $where[]="is_finished=false";
    if ($q!=='') { $where[]="(title ILIKE :q OR description ILIKE :q)"; $p[':q']="%$q%"; }
    $sql="SELECT * FROM todos".($where?" WHERE ".implode(" AND ",$where):"")." ORDER BY sort_order ASC, created_at DESC";
    $s=$this->db->prepare($sql); $s->execute($p);
    return $s->fetchAll();
  }

  public function toggle($id, $finished) {
    $s=$this->db->prepare("UPDATE todos SET is_finished=:f WHERE id=:id");
    $s->execute([':f'=>$finished? 'true':'false', ':id'=>$id]);
  }

  public function delete($id) {
    $s=$this->db->prepare("DELETE FROM todos WHERE id=:id");
    $s->execute([':id'=>$id]);
  }

  public function detail($id) {
    $s=$this->db->prepare("SELECT * FROM todos WHERE id=:id");
    $s->execute([':id'=>$id]); return $s->fetch();
  }

  public function update($id, $title, $desc) {
    // unik, exclude diri sendiri
    $q=$this->db->prepare("SELECT 1 FROM todos WHERE lower(title)=lower(:t) AND id<>:id");
    $q->execute([':t'=>$title, ':id'=>$id]);
    if ($q->fetch()) throw new Exception('Judul sudah dipakai.');
    $s=$this->db->prepare("UPDATE todos SET title=:t, description=:d WHERE id=:id");
    $s->execute([':t'=>$title, ':d'=>$desc, ':id'=>$id]);
  }

  public function reorder(array $ids) {
    $this->db->beginTransaction();
    try {
      $stmt=$this->db->prepare("UPDATE todos SET sort_order=:o WHERE id=:id");
      $o=1; foreach($ids as $id){ $stmt->execute([':o'=>$o++, ':id'=>(int)$id]); }
      $this->db->commit();
    } catch(Throwable $e){ $this->db->rollBack(); throw $e; }
  }
}

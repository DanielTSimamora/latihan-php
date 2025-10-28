<?php
// public/index.php
// Jalankan: php -S localhost:8000 -t public
require_once __DIR__.'/../controllers/TodoController.php';

$ctl = new TodoController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
  case 'create':  if($_SERVER['REQUEST_METHOD']==='POST') $ctl->create(); else header("Location: /"); break;
  case 'delete':  $ctl->delete(); break;
  case 'toggle':  $ctl->toggle(); break;      // JSON
  case 'reorder': $ctl->reorder(); break;     // JSON
  case 'detail':  $ctl->detail(); break;
  default:        $ctl->index();
}

<?php
require_once (__DIR__ . '/../models/TodoModel.php');

class TodoController
{
    private function currentFilter(): string {
        $f = $_GET['filter'] ?? 'all';
        return in_array($f, ['all','done','todo'], true) ? $f : 'all';
    }

    private function currentQuery(): string {
        return trim($_GET['q'] ?? '');
    }

    public function index()
    {
        $model  = new TodoModel();
        $filter = $this->currentFilter();
        $q      = $this->currentQuery();
        $todos  = $model->getTodos($filter, $q);

        // flash error via query string ?err=dup / ?err=fail
        $err = $_GET['err'] ?? null;

        include (__DIR__ . '/../views/TodoView.php');
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $desc  = trim($_POST['description'] ?? '');

            $model = new TodoModel();
            $ok = ($title !== '') ? $model->createTodo($title, $desc) : false;

            $redir = 'index.php?filter=' . urlencode($this->currentFilter()) . '&q=' . urlencode($this->currentQuery());
            header('Location: ' . $redir . ($ok ? '' : '&err=dup')); // dup = judul sudah ada / gagal
            return;
        }
        header('Location: index.php');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id    = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $desc  = trim($_POST['description'] ?? '');
            $stat  = (int)($_POST['is_finished'] ?? 0);

            $model = new TodoModel();
            $ok = ($id > 0 && $title !== '') ? $model->updateTodo($id, $title, $desc, $stat) : false;

            $redir = 'index.php?filter=' . urlencode($this->currentFilter()) . '&q=' . urlencode($this->currentQuery());
            header('Location: ' . $redir . ($ok ? '' : '&err=dup'));
            return;
        }
        header('Location: index.php');
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $model = new TodoModel();
            $model->deleteTodo($id);
        }
        $redir = 'index.php?filter=' . urlencode($this->currentFilter()) . '&q=' . urlencode($this->currentQuery());
        header('Location: ' . $redir);
    }

    /** Endpoint AJAX untuk persist urutan drag & drop */
    public function reorder()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Method not allowed']);
            return;
        }
        $order = $_POST['order'] ?? [];
        if (!is_array($order) || empty($order)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Order kosong']);
            return;
        }
        $model = new TodoModel();
        $ok = $model->saveOrder(array_map('intval', $order));
        echo json_encode(['ok' => $ok]);
    }
}

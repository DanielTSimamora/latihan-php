<?php
require_once (__DIR__ . '/../models/TodoModel.php');

class TodoController
{
    public function index()
    {
        $todoModel = new TodoModel();
        $todos = $todoModel->getAllTodos();
        include (__DIR__ . '/../views/TodoView.php');
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $activity = $_POST['activity'] ?? '';
            if (trim($activity) !== '') {
                $todoModel = new TodoModel();
                $todoModel->createTodo($activity);
            }
        }
        header('Location: index.php');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $activity = $_POST['activity'] ?? '';
            $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;
            if ($id !== null) {
                $todoModel = new TodoModel();
                $todoModel->updateTodo($id, $activity, $status);
            }
        }
        header('Location: index.php');
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $todoModel = new TodoModel();
            $todoModel->deleteTodo($id);
        }
        header('Location: index.php');
    }

    /** Endpoint untuk menyimpan urutan (drag & drop) */
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
        $todoModel = new TodoModel();
        $ok = $todoModel->saveOrder($order);
        echo json_encode(['ok' => $ok]);
    }
}

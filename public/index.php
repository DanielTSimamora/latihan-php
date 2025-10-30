<?php
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 'index';
}
include (__DIR__ . '/../controllers/TodoController.php');

$todoController = new TodoController();
switch ($page) {
    case 'index':
        $todoController->index();
        break;
    case 'create':
        $todoController->create();
        break;
    case 'update':
        $todoController->update();
        break;
    case 'delete':
        $todoController->delete();
        break;
    case 'reorder': // untuk sorting
        $todoController->reorder();
        break;
    case 'detail':  // JSON detail untuk modal
        $todoController->detail();
        break;
    default:
        $todoController->index();
        break;
}

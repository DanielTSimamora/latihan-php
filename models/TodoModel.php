<?php
require_once (__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASSWORD);
        if (!$this->conn) {
            die('Koneksi database gagal');
        }
    }

    /** Ambil list todo dengan filter & search, selalu terurut by position */
    public function getTodos(string $filter = 'all', string $q = '')
    {
        $where = [];
        $params = [];
        $i = 1;

        // filter status
        if ($filter === 'done') {
            $where[] = "is_finished = TRUE";
        } elseif ($filter === 'todo') {
            $where[] = "is_finished = FALSE";
        }

        // search pada title & description
        if ($q !== '') {
            $where[] = "(lower(title) LIKE $" . $i . " OR lower(description) LIKE $" . $i . ")";
            $params[] = '%' . mb_strtolower($q) . '%';
            $i++;
        }

        $sql = "SELECT id, title, description, is_finished, created_at, updated_at
                FROM todo";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY position ASC, created_at ASC, id ASC";

        $result = empty($params) ? pg_query($this->conn, $sql) : pg_query_params($this->conn, $sql, $params);

        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }

    /** Cek judul unik (case-insensitive). $excludeId untuk update */
    public function titleExists(string $title, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM todo WHERE lower(title) = $1";
        $params = [mb_strtolower($title)];

        if ($excludeId !== null) {
            $sql .= " AND id <> $2";
            $params[] = $excludeId;
        }

        $res = pg_query_params($this->conn, $sql, $params);
        return $res && pg_num_rows($res) > 0;
    }

    public function createTodo(string $title, string $description): bool
    {
        // validasi judul unik
        if ($this->titleExists($title)) return false;

        // position = max + 1
        $maxQ = pg_query($this->conn, "SELECT COALESCE(MAX(position), 0) AS maxpos FROM todo");
        $max = ($maxQ && pg_num_rows($maxQ) ? (int)pg_fetch_assoc($maxQ)['maxpos'] : 0) + 1;

        $sql = "INSERT INTO todo (title, description, is_finished, position, created_at, updated_at)
                VALUES ($1, $2, FALSE, $3, NOW(), NOW())";
        $res = pg_query_params($this->conn, $sql, [$title, $description, $max]);
        return $res !== false;
    }

    public function updateTodo(int $id, string $title, string $description, int $isFinished): bool
    {
        // validasi judul unik (kecuali dirinya sendiri)
        if ($this->titleExists($title, $id)) return false;

        $sql = "UPDATE todo
                SET title = $1,
                    description = $2,
                    is_finished = $3,
                    updated_at = NOW()
                WHERE id = $4";
        $res = pg_query_params($this->conn, $sql, [$title, $description, $isFinished ? 1 : 0, $id]);
        return $res !== false;
    }

    public function deleteTodo(int $id): bool
    {
        $sql = "DELETE FROM todo WHERE id = $1";
        $res = pg_query_params($this->conn, $sql, [$id]);
        return $res !== false;
    }

    public function getById(int $id): ?array
    {
        $res = pg_query_params($this->conn, "SELECT * FROM todo WHERE id = $1", [$id]);
        if ($res && pg_num_rows($res) > 0) {
            return pg_fetch_assoc($res);
        }
        return null;
    }

    /** Simpan urutan baru sesuai array id dari atas ke bawah */
    public function saveOrder(array $order): bool
    {
        pg_query($this->conn, 'BEGIN');
        try {
            $pos = 1;
            foreach ($order as $id) {
                $q = 'UPDATE todo SET position=$1 WHERE id=$2';
                $ok = pg_query_params($this->conn, $q, [$pos++, (int)$id]);
                if ($ok === false) throw new \Exception('update failed');
            }
            pg_query($this->conn, 'COMMIT');
            return true;
        } catch (\Throwable $e) {
            pg_query($this->conn, 'ROLLBACK');
            return false;
        }
    }
}

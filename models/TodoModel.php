<?php
require_once (__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    public function __construct()
    {
        // Inisialisasi koneksi database PostgreSQL
        $this->conn = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASSWORD);
        if (!$this->conn) {
            die('Koneksi database gagal');
        }
    }

    public function getAllTodos()
    {
        // Urut berdasarkan position agar konsisten setelah drag & drop
        $query = 'SELECT * FROM todo ORDER BY position ASC, created_at ASC, id ASC';
        $result = pg_query($this->conn, $query);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }

    public function createTodo($activity)
    {
        // Simpan ke posisi paling bawah (position = max + 1)
        $maxQ  = 'SELECT COALESCE(MAX(position),0) AS maxpos FROM todo';
        $maxR  = pg_query($this->conn, $maxQ);
        $max   = ($maxR && pg_num_rows($maxR) ? (int)pg_fetch_assoc($maxR)['maxpos'] : 0) + 1;

        $query = 'INSERT INTO todo (activity, position) VALUES ($1, $2)';
        $result = pg_query_params($this->conn, $query, [$activity, $max]);
        return $result !== false;
    }

    public function updateTodo($id, $activity, $status)
    {
        $query = 'UPDATE todo SET activity=$1, status=$2, updated_at=NOW() WHERE id=$3';
        $result = pg_query_params($this->conn, $query, [$activity, $status, $id]);
        return $result !== false;
    }

    public function deleteTodo($id)
    {
        $query = 'DELETE FROM todo WHERE id=$1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return $result !== false;
    }

    /** Simpan urutan baru sesuai array id dari atas ke bawah */
    public function saveOrder(array $order)
    {
        // Pakai transaction biar atomic
        pg_query($this->conn, 'BEGIN');
        try {
            $pos = 1;
            foreach ($order as $id) {
                $q = 'UPDATE todo SET position=$1 WHERE id=$2';
                $ok = pg_query_params($this->conn, $q, [$pos++, (int)$id]);
                if ($ok === false) {
                    throw new \Exception('update failed');
                }
            }
            pg_query($this->conn, 'COMMIT');
            return true;
        } catch (\Throwable $e) {
            pg_query($this->conn, 'ROLLBACK');
            return false;
        }
    }
}

<?php
require_once __DIR__ . '/Database.php';

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", 
            [$id]
        );
    }

    public function getAll($conditions = '', $params = []) {
        $sql = "SELECT * FROM {$this->table}";
        if ($conditions) {
            $sql .= " WHERE $conditions";
        }
        return $this->db->fetchAll($sql, $params);
    }

    public function create($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        return $this->db->update(
            $this->table, 
            $data, 
            "{$this->primaryKey} = ?", 
            [$id]
        );
    }

    public function delete($id) {
        return $this->db->delete(
            $this->table, 
            "{$this->primaryKey} = ?", 
            [$id]
        );
    }
}

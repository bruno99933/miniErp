<?php
// models/BaseModel.php

abstract class BaseModel {
    protected $conn;
    protected $table;

    public function __construct($table) {
        $this->conn = getDbConnection();
        $this->table = $table;
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Métodos abstratos para serem implementados pelos modelos específicos
    abstract public function create(array $data);
    abstract public function update($id, array $data);
}
?>
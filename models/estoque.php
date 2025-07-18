<?php
// models/Estoque.php

require_once __DIR__ . '/BaseModel.php';

class Estoque extends BaseModel {
    public function __construct() {
        parent::__construct('estoque');
    }

    public function create(array $data) {
        $stmt = $this->conn->prepare("INSERT INTO estoque (produto_id, variacao, quantidade) VALUES (:produto_id, :variacao, :quantidade)");
        $stmt->bindParam(':produto_id', $data['produto_id']);
        $stmt->bindParam(':variacao', $data['variacao']);
        $stmt->bindParam(':quantidade', $data['quantidade']);
        return $stmt->execute();
    }

    public function update($id, array $data) {
        $stmt = $this->conn->prepare("UPDATE estoque SET variacao = :variacao, quantidade = :quantidade WHERE id = :id");
        $stmt->bindParam(':variacao', $data['variacao']);
        $stmt->bindParam(':quantidade', $data['quantidade']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getByProdutoId($produto_id) {
        $stmt = $this->conn->prepare("SELECT * FROM estoque WHERE produto_id = :produto_id");
        $stmt->bindParam(':produto_id', $produto_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateQuantity($estoque_id, $quantidade_alteracao) {
        $stmt = $this->conn->prepare("UPDATE estoque SET quantidade = quantidade + :quantidade_alteracao WHERE id = :estoque_id");
        $stmt->bindParam(':quantidade_alteracao', $quantidade_alteracao);
        $stmt->bindParam(':estoque_id', $estoque_id);
        return $stmt->execute();
    }
}
?>
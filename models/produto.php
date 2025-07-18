<?php
// models/Produto.php

require_once __DIR__ . '/BaseModel.php';

class Produto extends BaseModel {
    public function __construct() {
        parent::__construct('produtos');
    }

    public function create(array $data) {
        $stmt = $this->conn->prepare("INSERT INTO produtos (nome, preco, descricao) VALUES (:nome, :preco, :descricao)");
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':preco', $data['preco']);
        $stmt->bindParam(':descricao', $data['descricao']);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function update($id, array $data) {
        $stmt = $this->conn->prepare("UPDATE produtos SET nome = :nome, preco = :preco, descricao = :descricao WHERE id = :id");
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':preco', $data['preco']);
        $stmt->bindParam(':descricao', $data['descricao']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getProdutoComEstoque($id) {
        $stmt = $this->conn->prepare("
            SELECT
                p.*,
                SUM(e.quantidade) as estoque_total
            FROM
                produtos p
            LEFT JOIN
                estoque e ON p.id = e.produto_id
            WHERE p.id = :id
            GROUP BY p.id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAllProdutosComEstoque() {
    $stmt = $this->conn->prepare("
        SELECT 
            p.id AS produto_id,
            p.nome,
            p.preco,
            p.descricao,
            e.id AS estoque_id,
            e.variacao,
            e.quantidade
        FROM produtos p
        LEFT JOIN estoque e ON e.produto_id = p.id
        ORDER BY p.id DESC, e.id ASC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $produtos = [];

    foreach ($result as $row) {
        $id = $row['produto_id'];

        if (!isset($produtos[$id])) {
            $produtos[$id] = [
                'id' => $id,
                'nome' => $row['nome'],
                'preco' => $row['preco'],
                'descricao' => $row['descricao'],
                'estoque' => []
            ];
        }

        if ($row['estoque_id']) {
            $produtos[$id]['estoque'][] = [
                'id' => $row['estoque_id'],
                'variacao' => $row['variacao'],
                'quantidade' => $row['quantidade']
            ];
        }
    }

    return array_values($produtos); // reindexa
}
}
?>
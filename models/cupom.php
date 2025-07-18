<?php
// models/Cupom.php

require_once __DIR__ . '/BaseModel.php';

class Cupom extends BaseModel {
    public function __construct() {
        parent::__construct('cupons');
    }

    public function create(array $data) {
        $stmt = $this->conn->prepare("INSERT INTO cupons (codigo, tipo_desconto, valor_desconto, valor_minimo_carrinho, data_validade, ativo) VALUES (:codigo, :tipo_desconto, :valor_desconto, :valor_minimo_carrinho, :data_validade, :ativo)");
        $stmt->bindParam(':codigo', $data['codigo']);
        $stmt->bindParam(':tipo_desconto', $data['tipo_desconto']);
        $stmt->bindParam(':valor_desconto', $data['valor_desconto']);
        $stmt->bindParam(':valor_minimo_carrinho', $data['valor_minimo_carrinho']);
        $stmt->bindParam(':data_validade', $data['data_validade']);
        $stmt->bindParam(':ativo', $data['ativo'], PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function update($id, array $data) {
        $stmt = $this->conn->prepare("UPDATE cupons SET codigo = :codigo, tipo_desconto = :tipo_desconto, valor_desconto = :valor_desconto, valor_minimo_carrinho = :valor_minimo_carrinho, data_validade = :data_validade, ativo = :ativo WHERE id = :id");
        $stmt->bindParam(':codigo', $data['codigo']);
        $stmt->bindParam(':tipo_desconto', $data['tipo_desconto']);
        $stmt->bindParam(':valor_desconto', $data['valor_desconto']);
        $stmt->bindParam(':valor_minimo_carrinho', $data['valor_minimo_carrinho']);
        $stmt->bindParam(':data_validade', $data['data_validade']);
        $stmt->bindParam(':ativo', $data['ativo'], PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getByCodigo($codigo) {
        $stmt = $this->conn->prepare("SELECT * FROM cupons WHERE codigo = :codigo AND ativo = TRUE AND (data_validade IS NULL OR data_validade >= CURDATE())");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>
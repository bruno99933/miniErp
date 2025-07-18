<?php
// models/Pedido.php

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Estoque.php'; // Para gerenciar o estoque

class Pedido extends BaseModel {
    private $estoqueModel;

    public function __construct() {
        parent::__construct('pedidos');
        $this->estoqueModel = new Estoque();
    }

    public function create(array $data) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
                INSERT INTO pedidos (
                    cliente_nome, cliente_email, cliente_cep, cliente_endereco,
                    cliente_numero, cliente_bairro, cliente_cidade, cliente_estado,
                    subtotal, frete, total, cupom_id, status
                ) VALUES (
                    :cliente_nome, :cliente_email, :cliente_cep, :cliente_endereco,
                    :cliente_numero, :cliente_bairro, :cliente_cidade, :cliente_estado,
                    :subtotal, :frete, :total, :cupom_id, :status
                )
            ");
            $stmt->bindParam(':cliente_nome', $data['cliente_nome']);
            $stmt->bindParam(':cliente_email', $data['cliente_email']);
            $stmt->bindParam(':cliente_cep', $data['cliente_cep']);
            $stmt->bindParam(':cliente_endereco', $data['cliente_endereco']);
            $stmt->bindParam(':cliente_numero', $data['cliente_numero']);
            $stmt->bindParam(':cliente_bairro', $data['cliente_bairro']);
            $stmt->bindParam(':cliente_cidade', $data['cliente_cidade']);
            $stmt->bindParam(':cliente_estado', $data['cliente_estado']);
            $stmt->bindParam(':subtotal', $data['subtotal']);
            $stmt->bindParam(':frete', $data['frete']);
            $stmt->bindParam(':total', $data['total']);
            $stmt->bindParam(':cupom_id', $data['cupom_id'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $data['status']);
            $stmt->execute();
            $pedido_id = $this->conn->lastInsertId();

            // Inserir itens do pedido e decrementar estoque
            foreach ($data['itens'] as $item) {
                $stmtItem = $this->conn->prepare("
                    INSERT INTO pedido_itens (pedido_id, produto_id, variacao, quantidade, preco_unitario)
                    VALUES (:pedido_id, :produto_id, :variacao, :quantidade, :preco_unitario)
                ");
                $stmtItem->bindParam(':pedido_id', $pedido_id);
                $stmtItem->bindParam(':produto_id', $item['produto_id']);
                $stmtItem->bindParam(':variacao', $item['variacao']);
                $stmtItem->bindParam(':quantidade', $item['quantidade']);
                $stmtItem->bindParam(':preco_unitario', $item['preco_unitario']);
                $stmtItem->execute();

                // Decrementar o estoque
                // Assumimos que 'variacao_id' ou um identificador único de estoque é passado,
                // 'variacao' e 'produto_id' para encontrar a entrada de estoque.
                $estoqueEntry = $this->conn->prepare("SELECT id FROM estoque WHERE produto_id = :produto_id AND variacao = :variacao LIMIT 1");
                $estoqueEntry->bindParam(':produto_id', $item['produto_id']);
                $estoqueEntry->bindParam(':variacao', $item['variacao']);
                $estoqueEntry->execute();
                $estoque_id = $estoqueEntry->fetchColumn();

                if ($estoque_id) {
                    $this->estoqueModel->updateQuantity($estoque_id, -$item['quantidade']);
                } else {
                    throw new Exception("Estoque para a variação '{$item['variacao']}' do produto {$item['produto_id']} não encontrado.");
                }
            }

            $this->conn->commit();
            return $pedido_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro ao criar pedido: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, array $data) {
        return false;
    }

    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare("UPDATE pedidos SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function deleteAndRestoreStock($id) {
        try {
            $this->conn->beginTransaction();

            // Obter itens do pedido antes de deletar
            $stmtItems = $this->conn->prepare("SELECT produto_id, variacao, quantidade FROM pedido_itens WHERE pedido_id = :pedido_id");
            $stmtItems->bindParam(':pedido_id', $id);
            $stmtItems->execute();
            $itens = $stmtItems->fetchAll();

            // Deletar itens do pedido
            $stmtDeleteItems = $this->conn->prepare("DELETE FROM pedido_itens WHERE pedido_id = :pedido_id");
            $stmtDeleteItems->bindParam(':pedido_id', $id);
            $stmtDeleteItems->execute();

            // Deletar o pedido
            $stmtDeletePedido = $this->conn->prepare("DELETE FROM pedidos WHERE id = :id");
            $stmtDeletePedido->bindParam(':id', $id);
            $stmtDeletePedido->execute();

            // Restaurar o estoque
            foreach ($itens as $item) {
                $estoqueEntry = $this->conn->prepare("SELECT id FROM estoque WHERE produto_id = :produto_id AND variacao = :variacao LIMIT 1");
                $estoqueEntry->bindParam(':produto_id', $item['produto_id']);
                $estoqueEntry->bindParam(':variacao', $item['variacao']);
                $estoqueEntry->execute();
                $estoque_id = $estoqueEntry->fetchColumn();

                if ($estoque_id) {
                    $this->estoqueModel->updateQuantity($estoque_id, $item['quantidade']); // Adiciona a quantidade de volta
                } else {
                    error_log("Erro ao restaurar estoque: Entrada de estoque para produto_id {$item['produto_id']} e variação '{$item['variacao']}' não encontrada.");
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro ao cancelar e restaurar estoque do pedido: " . $e->getMessage());
            return false;
        }
    }

    public function getPedidoDetails($id) {
        $stmt = $this->conn->prepare("
            SELECT
                p.*,
                ci.codigo as cupom_codigo
            FROM
                pedidos p
            LEFT JOIN
                cupons ci ON p.cupom_id = ci.id
            WHERE p.id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $pedido = $stmt->fetch();

        if ($pedido) {
            $stmtItems = $this->conn->prepare("
                SELECT
                    pi.*,
                    pr.nome as produto_nome
                FROM
                    pedido_itens pi
                JOIN
                    produtos pr ON pi.produto_id = pr.id
                WHERE pi.pedido_id = :pedido_id
            ");
            $stmtItems->bindParam(':pedido_id', $id);
            $stmtItems->execute();
            $pedido['itens'] = $stmtItems->fetchAll();
        }
        return $pedido;
    }
}
?>
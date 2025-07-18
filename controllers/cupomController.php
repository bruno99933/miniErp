<?php
// controllers/CupomController.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cupom.php';

class CupomController {
    private $cupomModel;

    public function __construct() {
        $this->cupomModel = new Cupom();
    }

    public function index() {
        $cupons = $this->cupomModel->getAll();
        require_once __DIR__ . '/../views/cupons/index.php';
    }

    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $codigo = strip_tags($_POST['codigo']);
            $tipo_desconto = strip_tags($_POST['tipo_desconto']);
            $valor_desconto = filter_input(INPUT_POST, 'valor_desconto', FILTER_VALIDATE_FLOAT);
            $valor_minimo_carrinho = filter_input(INPUT_POST, 'valor_minimo_carrinho', FILTER_VALIDATE_FLOAT);
            $data_validade = strip_tags($_POST['data_validade']);
            $ativo = isset($_POST['ativo']) ? 1 : 0; // Checkbox

            if (!$codigo || !$tipo_desconto || $valor_desconto === false || $valor_desconto < 0) {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Preencha todos os campos obrigatórios do cupom.'];
                header('Location: ?page=cupons');
                exit();
            }

            $cupomData = [
                'codigo' => $codigo,
                'tipo_desconto' => $tipo_desconto,
                'valor_desconto' => $valor_desconto,
                'valor_minimo_carrinho' => ($valor_minimo_carrinho === false || $valor_minimo_carrinho < 0) ? 0.00 : $valor_minimo_carrinho,
                'data_validade' => !empty($data_validade) ? $data_validade : null,
                'ativo' => $ativo
            ];

            if ($id) {
                if ($this->cupomModel->update($id, $cupomData)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Cupom atualizado com sucesso!'];
                } else {
                    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao atualizar cupom.'];
                }
            } else {
                if ($this->cupomModel->create($cupomData)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Cupom cadastrado com sucesso!'];
                } else {
                    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao cadastrar cupom. Verifique se o código já existe.'];
                }
            }
            header('Location: ?page=cupons');
            exit();
        }
    }

    public function editar() {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $cupom = null;
        if ($id) {
            $cupom = $this->cupomModel->getById($id);
        }
        $cupons = $this->cupomModel->getAll(); // Para listar todos na mesma página
        require_once __DIR__ . '/../views/cupons/index.php';
    }

    public function deletar() {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        if ($id) {
            if ($this->cupomModel->delete($id)) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Cupom excluído com sucesso!'];
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao excluir cupom.'];
            }
        }
        header('Location: ?page=cupons');
        exit();
    }
}
?>
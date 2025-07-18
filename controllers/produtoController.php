<?php
// controllers/ProdutoController.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Estoque.php';
require_once __DIR__ . '/../functions.php'; // Para funções de carrinho

class ProdutoController {
    private $produtoModel;
    private $estoqueModel;

    public function __construct() {
        $this->produtoModel = new Produto();
        $this->estoqueModel = new Estoque();
    }

    public function index() {
        $produtos = $this->produtoModel->getAllProdutosComEstoque();
        require_once __DIR__ . '/../views/produtos/index.php';
    }

    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $nome = strip_tags($_POST['nome']);
            $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
            $descricao = strip_tags($_POST['descricao']);

            if (!$nome || !$preco) {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Nome e Preço são obrigatórios.'];
                header('Location: ?page=produtos');
                exit();
            }

            $produtoData = [
                'nome' => $nome,
                'preco' => $preco,
                'descricao' => $descricao
            ];

            $variacoes = isset($_POST['variacao']) ? $_POST['variacao'] : [];
            $quantidades = isset($_POST['quantidade']) ? $_POST['quantidade'] : [];
            $estoque_ids = isset($_POST['estoque_id']) ? $_POST['estoque_id'] : [];

            if ($id) {
                // Atualizar produto existente
                $this->produtoModel->update($id, $produtoData);

                // Lidar com variações e estoque existentes
                $existingEstoque = $this->estoqueModel->getByProdutoId($id);
                $existingEstoqueIds = array_column($existingEstoque, 'id');

                foreach ($variacoes as $key => $variacao_nome) {
                    $quantidade = filter_var($quantidades[$key], FILTER_VALIDATE_INT);
                    $estoque_id = filter_var($estoque_ids[$key], FILTER_SANITIZE_NUMBER_INT);

                    if ($quantidade === false || $quantidade < 0) {
                        $quantidade = 0; // Garante que a quantidade seja um número não negativo
                    }

                    if ($variacao_nome === '') { // Ignora variações vazias
                        continue;
                    }

                    $estoqueData = [
                        'variacao' => $variacao_nome,
                        'quantidade' => $quantidade,
                        'produto_id' => $id // Necessário para criar novas variações
                    ];

                    if ($estoque_id && in_array($estoque_id, $existingEstoqueIds)) {
                        $this->estoqueModel->update($estoque_id, $estoqueData);
                        unset($existingEstoqueIds[array_search($estoque_id, $existingEstoqueIds)]);
                    } else {
                        // Nova variação para um produto existente
                        $estoqueData['produto_id'] = $id;
                        $this->estoqueModel->create($estoqueData);
                    }
                }
                // Excluir variações que foram removidas da tela
                foreach ($existingEstoqueIds as $id_to_delete) {
                    $this->estoqueModel->delete($id_to_delete);
                }

                $_SESSION['message'] = ['type' => 'success', 'text' => 'Produto atualizado com sucesso!'];

            } else {
                // Criar novo produto
                $produto_id = $this->produtoModel->create($produtoData);
                if ($produto_id) {
                    if (empty($variacoes) || (count($variacoes) == 1 && $variacoes[0] == '')) {
                        // Se não houver variações, cria uma entrada de estoque padrão para o produto
                        $this->estoqueModel->create([
                            'produto_id' => $produto_id,
                            'variacao' => 'Padrão', // Ou null, ou "Única"
                            'quantidade' => filter_var($quantidades[0] ?? 0, FILTER_VALIDATE_INT) // Pega a primeira quantidade, se houver
                        ]);
                    } else {
                        // Criar variações e estoque
                        foreach ($variacoes as $key => $variacao_nome) {
                            if ($variacao_nome !== '') {
                                $quantidade = filter_var($quantidades[$key], FILTER_VALIDATE_INT);
                                if ($quantidade === false || $quantidade < 0) {
                                    $quantidade = 0;
                                }
                                $this->estoqueModel->create([
                                    'produto_id' => $produto_id,
                                    'variacao' => $variacao_nome,
                                    'quantidade' => $quantidade
                                ]);
                            }
                        }
                    }
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Produto cadastrado com sucesso!'];
                } else {
                    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao cadastrar produto.'];
                }
            }
            header('Location: ?page=produtos');
            exit();
        }
    }

    public function editar() {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $produto = null;
        $estoque = [];

        if ($id) {
            $produto = $this->produtoModel->getById($id);
            $estoque = $this->estoqueModel->getByProdutoId($id);
        }
        require_once __DIR__ . '/../views/produtos/form.php';
    }

    public function deletar() {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        if ($id) {
            if ($this->produtoModel->delete($id)) {
                // O ON DELETE CASCADE na FK em `estoque` já cuida das variações
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Produto excluído com sucesso!'];
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao excluir produto.'];
            }
        }
        header('Location: ?page=produtos');
        exit();
    }

    public function adicionarAoCarrinho() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $produto_id = filter_input(INPUT_POST, 'produto_id', FILTER_SANITIZE_NUMBER_INT);
            $estoque_id = filter_input(INPUT_POST, 'estoque_id', FILTER_SANITIZE_NUMBER_INT); // ID da variação específica
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

            if ($produto_id && $estoque_id && $quantidade > 0) {
                $produtoInfo = $this->produtoModel->getById($produto_id);
                $estoqueInfo = $this->estoqueModel->getById($estoque_id);

                if ($produtoInfo && $estoqueInfo) {
                    $item = [
                        'id' => $produtoInfo['id'],
                        'nome' => $produtoInfo['nome'],
                        'preco' => $produtoInfo['preco'],
                        'variacao' => $estoqueInfo['variacao']
                    ];
                    if (adicionarAoCarrinho($item, $quantidade, $estoque_id)) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => 'Produto adicionado ao carrinho!'];
                    } else {
                        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Estoque insuficiente ou erro ao adicionar ao carrinho.'];
                    }
                } else {
                    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Produto ou variação não encontrados.'];
                }
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Dados inválidos para adicionar ao carrinho.'];
            }
        }
        header('Location: ?page=produtos'); // Redireciona de volta para a tela de produtos
        exit();
    }
}
?>
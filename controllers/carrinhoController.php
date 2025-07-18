<?php
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Cupom.php';
require_once __DIR__ . '/../functions.php'; // Inclui as funções de carrinho e cálculo de frete

class CarrinhoController {
    public function index() {
        $produtosModel = new Produto();
        $cupomModel = new Cupom();

        $carrinho = $_SESSION['carrinho'] ?? [];
        $cupomAplicado = $_SESSION['cupom'] ?? null;
        $itensNoCarrinhoParaView = []; // Novo array para passar para a view

        $subtotal = 0; // Inicializa o subtotal

        // Itera sobre o carrinho, onde $chave_complexa é a chave única (ex: "3-hash")
        // e $itemData é o array completo do item (produto_id, nome, preco, variacao, quantidade, etc.)
        foreach ($carrinho as $chave_complexa => $itemData) {
            // Garante que a quantidade é um inteiro
            $quantidade = (int)($itemData['quantidade'] ?? 0);
            
            // Garante que o preco é um float
            $preco = (float)($itemData['preco'] ?? 0.00);

            // Calcula o subtotal do item
            $itemSubtotal = $preco * $quantidade;
            $subtotal += $itemSubtotal; // Acumula no subtotal geral

            // Prepara o item para ser enviado para a view
            $itensNoCarrinhoParaView[] = [
                'chave_complexa' => $chave_complexa, // Adiciona a chave complexa para uso no link de remover
                'produto_id' => $itemData['produto_id'],
                'nome' => $itemData['nome'],
                'preco' => $preco,
                'variacao' => $itemData['variacao'] ?? 'Padrão', // Garante que variação exista
                'estoque_id' => $itemData['estoque_id'] ?? null,
                'quantidade' => $quantidade,
                'subtotal_item' => $itemSubtotal // Subtotal individual do item
            ];
        }

        $frete = calcularFrete($subtotal); // Função de functions.php
        $total = $subtotal + $frete;
        $desconto = 0;
        $totalComDesconto = $total; // Inicializa com o total antes do desconto
        $_SESSION['totalComDesconto'] = $total;

        if ($cupomAplicado) {
            $cupom = $cupomModel->getByCodigo($cupomAplicado);
            if ($cupom && $cupom['ativo'] && strtotime($cupom['data_validade']) >= time()) {
                if ($total >= (float)($cupom['valor_minimo_carrinho'] ?? 0)) {
                    $desconto = $cupom['tipo_desconto'] === 'percentual'
                        ? ($total * ((float)($cupom['valor_desconto'] ?? 0) / 100))
                        : (float)($cupom['valor_desconto'] ?? 0);
                    $totalComDesconto = max(0, $total - $desconto);
                    $_SESSION['totalComDesconto'] = $totalComDesconto;
                    $_SESSION['cupom'] = $cupomAplicado;
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Cupom aplicado com sucesso.'];
                } else {
                    $_SESSION['message'] = ['type' => 'warning', 'text' => 'Valor mínimo não atingido para o cupom.'];
                    unset($_SESSION['cupom']); // Remove o cupom se não atender ao mínimo
                }
            } else {
                unset($_SESSION['cupom']);
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Cupom inválido ou expirado.'];
            }
        }
        
        // Passa todas as variáveis necessárias para a view
        // O array $produtos agora é $itensNoCarrinhoParaView para ser mais descritivo
        $produtos = $itensNoCarrinhoParaView; // Mantendo o nome $produtos para compatibilidade com a view atual
        
        // As variáveis $subtotal, $frete, $total, $desconto, $totalComDesconto
        // já estão disponíveis no escopo e serão passadas para a view via require_once
        
        require_once __DIR__ . '/../views/carrinho/index.php';
    }

    public function remover() {
        // Agora esperamos a chave COMPLETA do item no carrinho, que é uma STRING
        $chave_item_para_remover = strip_tags($_GET['id']);

        if ($chave_item_para_remover && isset($_SESSION['carrinho'][$chave_item_para_remover])) {
            unset($_SESSION['carrinho'][$chave_item_para_remover]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Produto removido com sucesso.'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro: Item não encontrado no carrinho ou ID inválido.'];
        }
        
        header('Location: ?page=carrinho');
        exit();
    }

    public function aplicarCupom() {
        $codigo = filter_input(INPUT_POST, 'cupom', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $_SESSION['cupom'] = $codigo;
        header('Location: ?page=carrinho');
        exit();
    }

    public function limpar() {
        unset($_SESSION['carrinho'], $_SESSION['cupom']);
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Carrinho limpo!'];
        header('Location: ?page=carrinho');
        exit();
    }

    public function buscarCep() {
        header('Content-Type: application/json');

        $cep = filter_input(INPUT_GET, 'cep', FILTER_UNSAFE_RAW);
        $cep = is_string($cep) ? trim($cep) : '';

        if (empty($cep) || strlen(preg_replace('/\D/', '', $cep)) !== 8) {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'CEP inválido. Por favor, digite 8 dígitos.'];
            echo json_encode(['success' => false, 'message' => $_SESSION['message']['text']]);
            exit();
        }

        $cep = preg_replace('/\D/', '', $cep);
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $response = @file_get_contents($url);

        if ($response === FALSE) {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao comunicar com a API ViaCEP. Tente novamente mais tarde.'];
            echo json_encode(['success' => false, 'message' => $_SESSION['message']['text']]);
            exit();
        }

        $data = json_decode($response, true);

        if (isset($data['erro']) && $data['erro']) {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'CEP não encontrado. Verifique o número ou preencha manualmente.'];
            echo json_encode(['success' => false, 'message' => $_SESSION['message']['text']]);
        } else {
            echo json_encode(['success' => true, 'data' => $data]);
        }
        exit();
    }
}
<?php
// controllers/PedidoController.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Produto.php'; // Para pegar informações do produto para o email
require_once __DIR__ . '/../models/Cupom.php';
require_once __DIR__ . '/../functions.php';

class PedidoController
{
    private $pedidoModel;
    private $cupomModel;

    public function __construct()
    {
        $this->pedidoModel = new Pedido();
        $this->cupomModel = new Cupom();
    }

    public function finalizarPedido()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Seu carrinho está vazio!'];
                header('Location: ?page=produtos');
                exit();
            }

            $cliente_nome = strip_tags($_POST['cliente_nome']);
            $cliente_email = filter_input(INPUT_POST, 'cliente_email', FILTER_VALIDATE_EMAIL);
            $cliente_cep = strip_tags($_POST['cliente_cep']);
            $cliente_endereco = strip_tags($_POST['cliente_endereco']);
            $cliente_numero = strip_tags($_POST['cliente_numero']);
            $cliente_bairro = strip_tags($_POST['cliente_bairro']);
            $cliente_cidade = strip_tags($_POST['cliente_cidade']);
            $cliente_estado = strip_tags($_POST['cliente_estado']);
            $cupom_codigo = strip_tags($_POST['cupom_codigo']);

            if (!$cliente_nome || !$cliente_email || !$cliente_cep || !$cliente_endereco || !$cliente_numero) {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Por favor, preencha todos os campos obrigatórios do cliente.'];
                header('Location: ?page=checkout'); // Redireciona de volta para o checkout
                exit();
            }

            $subtotal = calcularSubtotalCarrinho();
            $frete = calcularFrete($subtotal);
            $total = $subtotal + $frete;
            $cupom_id = null;

            if (!empty($_SESSION['cupom'])) {
                $cupom = $this->cupomModel->getByCodigo($_SESSION['cupom']);
                if ($cupom && $subtotal >= $cupom['valor_minimo_carrinho']) {
                    if ($cupom['tipo_desconto'] === 'percentual') {
                        $desconto = $total * ($cupom['valor_desconto'] / 100);
                    } else { // fixo
                        $desconto = $cupom['valor_desconto'];
                    }
                    $total = max(0, $total - $desconto); // Garante que o total não seja negativo
                    $cupom_id = $cupom['id'];
                } else {
                    $_SESSION['message'] = ['type' => 'warning', 'text' => 'Cupom inválido ou não atende ao valor mínimo.'];
                    header('Location: ?page=checkout');
                    exit();
                }
            }

            $itens_pedido = [];
            foreach ($_SESSION['carrinho'] as $item) {
                $itens_pedido[] = [
                    'produto_id' => $item['produto_id'],
                    'variacao' => $item['variacao'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                ];
            }



            $pedidoData = [
                'cliente_nome' => $cliente_nome,
                'cliente_email' => $cliente_email,
                'cliente_cep' => $cliente_cep,
                'cliente_endereco' => $cliente_endereco,
                'cliente_numero' => $cliente_numero,
                'cliente_bairro' => $cliente_bairro,
                'cliente_cidade' => $cliente_cidade,
                'cliente_estado' => $cliente_estado,
                'subtotal' => $subtotal,
                'frete' => $frete,
                'total' => $total,
                'cupom_id' => $cupom_id,
                'status' => 'pendente',
                'itens' => $itens_pedido
            ];

            $cepLimpo = preg_replace('/\D/', '', $cliente_cep); // Remove caracteres não numéricos

            if (strlen($cepLimpo) !== 8) {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'CEP inválido. Por favor, digite 8 dígitos numéricos.'];
                header('Location: ?page=checkout');
                exit();
            }

            $url = "https://viacep.com.br/ws/{$cepLimpo}/json/";
            $response = @file_get_contents($url); // Usar @ para suprimir warnings em caso de falha na requisição

            if ($response === FALSE) {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao verificar o CEP. Tente novamente ou verifique sua conexão.'];
                header('Location: ?page=checkout');
                exit();
            }

            $data = json_decode($response, true);

            // Verifica se a API retornou erro ou se o CEP não foi encontrado
            if (isset($data['erro']) && $data['erro'] === true) {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'CEP não encontrado. Por favor, verifique o CEP digitado ou preencha o endereço manualmente.'];
                header('Location: ?page=checkout');
                exit();
            }

            $pedido_id = $this->pedidoModel->create($pedidoData);

            if ($pedido_id) {
                // Obter detalhes completos do pedido para o email
                $pedido_completo = $this->pedidoModel->getPedidoDetails($pedido_id);
                $cliente_data_email = [
                    'nome' => $cliente_nome,
                    'email' => $cliente_email
                ];

                enviarEmailConfirmacaoPedido($pedido_completo, $cliente_data_email);
                limparCarrinho();
                $_SESSION['message'] = ['type' => 'success', 'text' => "Pedido #{$pedido_id} finalizado com sucesso! Um e-mail de confirmação foi enviado."];
                header('Location: ?page=pedidos');
                exit();
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao finalizar o pedido. Tente novamente.'];
                header('Location: ?page=checkout');
                exit();
            }
        }
        require_once __DIR__ . '/../views/checkout/index.php';
    }

    public function index()
    {
        require_once __DIR__ . '/../models/Pedido.php';
        $pedidoModel = new Pedido();
        $pedidos = $pedidoModel->getAll();
        require_once __DIR__ . '/../views/pedidos/index.php';
    }

    public function buscarCep()
    {
        header('Content-Type: application/json'); // Garante que a resposta será JSON

        $cep = filter_input(INPUT_GET, 'cep', FILTER_UNSAFE_RAW); // Pega o CEP via GET da requisição AJAX
        $cep = is_string($cep) ? trim($cep) : ''; // Garante que é string e remove espaços

        if (empty($cep) || strlen(preg_replace('/\D/', '', $cep)) !== 8) {
            // CEP inválido ou vazio
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'CEP inválido. Por favor, digite 8 dígitos.'];
            echo json_encode(['success' => false, 'message' => $_SESSION['message']['text']]);
            exit();
        }

        $cep = preg_replace('/\D/', '', $cep); // Limpa o CEP para a consulta ViaCEP
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        // Faz a requisição à API ViaCEP
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['erro']) && $data['erro']) {
            // CEP não encontrado
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'CEP não encontrado. Verifique o número ou preencha manualmente.'];
            echo json_encode(['success' => false, 'message' => $_SESSION['message']['text']]);
        } else {
            // CEP encontrado com sucesso
            echo json_encode(['success' => true, 'data' => $data]);
        }
        exit(); // Finaliza a execução para evitar que o PHP continue processando HTML
    }
}
?>
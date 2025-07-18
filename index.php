<?php
session_start();

require_once __DIR__ . '/controllers/produtoController.php';
require_once __DIR__ . '/controllers/cupomController.php';
require_once __DIR__ . '/controllers/carrinhoController.php';
require_once __DIR__ . '/controllers/pedidoController.php';

$page = $_GET['page'] ?? 'produtos';
$action = $_GET['action'] ?? 'index';

switch ($page) {
    case 'produtos':
        $controller = new ProdutoController();
        break;
    case 'cupons':
        $controller = new CupomController();
        break;
    case 'carrinho':
        $controller = new CarrinhoController();
        break;
    case 'checkout':
        $controller = new PedidoController();
        break;
    case 'pedidos':
        $controller = new PedidoController();
        break;
    default:
        http_response_code(404);
        echo 'Página não encontrada';
        exit;
}

// Verifica se a action existe
if (method_exists($controller, $action)) {
    include __DIR__ . '/views/includes/header.php';
    $controller->$action();
    include __DIR__ . '/views/includes/footer.php';
} else {
    http_response_code(404);
    include __DIR__ . '/views/includes/header.php';
    echo 'Ação não encontrada';
    include __DIR__ . '/views/includes/footer.php';
}

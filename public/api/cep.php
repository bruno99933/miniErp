<?php
// public/api/cep.php
// Este arquivo é APENAS para a requisição AJAX de CEP.

// Iniciar a sessão se for necessário para acessar $_SESSION['message']
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/pedidoController.php'; // Ajuste o caminho conforme sua estrutura

// O endpoint API só responde a requisições GET com o parâmetro 'cep'
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['cep'])) {
    $controller = new pedidoController();
    $controller->buscarCep(); // Este método já imprime JSON e chama exit()
} else {
    // Se a requisição não for válida (ex: acesso direto ao arquivo)
    http_response_code(400); // Bad Request
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit();
}
?>
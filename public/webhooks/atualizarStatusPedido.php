<?php
// Define que o script só deve ser acessado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método não permitido
    echo json_encode(['success' => false, 'message' => 'Método não permitido. Somente POST é aceito.']);
    exit();
}

// Inclui os arquivos necessários
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Pedido.php';

// Configuração de log de erros (opcional, mas recomendado para depuração)
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/../../logs/webhook_errors.log');

// Lê o corpo da requisição POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Verifica se os dados necessários estão presentes e são válidos
if (empty($data) || !isset($data['pedido_id']) || !isset($data['novo_status'])) {
    http_response_code(400); // Requisição inválida
    echo json_encode(['success' => false, 'message' => 'Dados inválidos. Parâmetros "pedido_id" e "novo_status" são obrigatórios.']);
    error_log("Webhook Error: Dados recebidos inválidos. Input: " . $input);
    exit();
}

$pedidoId = filter_var($data['pedido_id'], FILTER_SANITIZE_NUMBER_INT);
$novoStatus = strip_tags($data['novo_status']);

// Validação adicional dos dados
if ($pedidoId === false || $pedidoId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do pedido inválido.']);
    error_log("Webhook Error: ID do pedido inválido: " . $data['pedido_id']);
    exit();
}

if (empty($novoStatus)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status inválido ou vazio.']);
    error_log("Webhook Error: Status vazio para pedido ID: " . $pedidoId);
    exit();
}

// Opcional: Lista de status permitidos para maior segurança
$statusPermitidos = ['pendente','processando','enviado','entregue','cancelado'];
if (!in_array($novoStatus, $statusPermitidos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status não permitido.']);
    error_log("Webhook Error: Tentativa de status não permitido: " . $novoStatus . " para pedido ID: " . $pedidoId);
    exit();
}


try {
    // Instancia o modelo de Pedido
    $pedidoModel = new Pedido();

    // Chama o método para atualizar o status
    $updated = $pedidoModel->updateStatus($pedidoId, $novoStatus);

    if ($updated) {
        http_response_code(200); // OK
        echo json_encode(['success' => true, 'message' => "Status do pedido {$pedidoId} atualizado para '{$novoStatus}' com sucesso."]);
        error_log("Webhook Success: Pedido {$pedidoId} atualizado para '{$novoStatus}'.");
    } else {
        http_response_code(500); // Erro interno do servidor
        echo json_encode(['success' => false, 'message' => 'Falha ao atualizar o status do pedido no banco de dados.']);
        error_log("Webhook Error: Falha no updateStatus para pedido ID: {$pedidoId}, status: {$novoStatus}.");
    }

} catch (PDOException $e) {
    // Captura erros de banco de dados
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor (PDO): ' . $e->getMessage()]);
    error_log("Webhook PDO Error: " . $e->getMessage() . " - Pedido ID: {$pedidoId}, Status: {$novoStatus}");
} catch (Exception $e) {
    // Captura outros erros gerais
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
    error_log("Webhook General Error: " . $e->getMessage() . " - Pedido ID: {$pedidoId}, Status: {$novoStatus}");
}

exit();
?>
<?php
// functions.php

// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Calcula o frete com base no subtotal.
 * @param float $subtotal
 * @return float
 */
function calcularFrete($subtotal) {
    if ($subtotal > 200.00) {
        return 0.00; // Frete grátis
    } elseif ($subtotal >= 52.00 && $subtotal <= 166.59) {
        return 15.00; // Frete R$15,00
    } else {
        return 20.00; // Frete R$20,00
    }
}

/**
 * Adiciona um produto ao carrinho na sessão.
 * @param array $produto Dados do produto (id, nome, preco, variacao, estoque_id)
 * @param int $quantidade Quantidade a ser adicionada
 * @return bool True se adicionado, false caso contrário (sem estoque)
 */
function adicionarAoCarrinho($produto, $quantidade, $estoque_id) {
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }

    $produto_key = $produto['id'] . '-' . md5($produto['variacao']); // Chave única para produto+variação

    // Verifica se já existe no carrinho e qual o estoque total disponível para esta variação
    $estoqueModel = new Estoque();
    $estoqueInfo = $estoqueModel->getById($estoque_id);

    if (!$estoqueInfo || $estoqueInfo['quantidade'] < $quantidade) {
        return false; // Sem estoque suficiente
    }

    if (isset($_SESSION['carrinho'][$produto_key])) {
        // Se já existe, verifica se a nova quantidade excede o estoque disponível
        if (($_SESSION['carrinho'][$produto_key]['quantidade'] + $quantidade) > $estoqueInfo['quantidade']) {
            return false; // Excederia o estoque
        }
        $_SESSION['carrinho'][$produto_key]['quantidade'] += $quantidade;
    } else {
        $_SESSION['carrinho'][$produto_key] = [
            'produto_id' => $produto['id'],
            'nome' => $produto['nome'],
            'preco' => $produto['preco'],
            'variacao' => $produto['variacao'],
            'estoque_id' => $estoque_id,
            'quantidade' => $quantidade,
        ];
    }
    return true;
}

/**
 * Atualiza a quantidade de um item no carrinho.
 * @param string $produto_key Chave do produto no carrinho
 * @param int $nova_quantidade Nova quantidade
 * @return bool True se atualizado, false caso contrário (sem estoque)
 */
function atualizarQuantidadeCarrinho($produto_key, $nova_quantidade) {
    if (isset($_SESSION['carrinho'][$produto_key])) {
        $item = $_SESSION['carrinho'][$produto_key];
        $estoqueModel = new Estoque();
        $estoqueInfo = $estoqueModel->getById($item['estoque_id']);

        if ($nova_quantidade <= 0) {
            unset($_SESSION['carrinho'][$produto_key]);
            return true;
        }

        if (!$estoqueInfo || $estoqueInfo['quantidade'] < $nova_quantidade) {
            return false; // Sem estoque suficiente
        }

        $_SESSION['carrinho'][$produto_key]['quantidade'] = $nova_quantidade;
        return true;
    }
    return false;
}

/**
 * Calcula o subtotal do carrinho.
 * @return float
 */
function calcularSubtotalCarrinho() {
    $subtotal = 0;
    if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }
    }
    return $subtotal;
}

/**
 * Limpa o carrinho.
 */
function limparCarrinho() {
    unset($_SESSION['carrinho']);
    unset($_SESSION['cupom']);
}

/**
 * Envia um e-mail de confirmação de pedido.
 * @param array $pedido Detalhes do pedido
 * @param array $cliente Detalhes do cliente
 * @return bool
 */
function enviarEmailConfirmacaoPedido($pedido, $cliente) {
    $to = $cliente['email'];
    $subject = "Confirmação do seu Pedido #" . $pedido['id'];
    $headers = "From: seuemail@gmail.com\r\n"; // Substitua pelo seu email
    $headers .= "Reply-To: seuemail@gmail.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $message = "<html><body>";
    $message .= "<h1>Obrigado pelo seu pedido, " . htmlspecialchars($cliente['nome']) . "!</h1>";
    $message .= "<p>Seu pedido #<strong>" . $pedido['id'] . "</strong> foi recebido e está sendo processado.</p>";
    $message .= "<h2>Detalhes do Pedido:</h2>";
    $message .= "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; border-collapse: collapse;'>";
    $message .= "<thead><tr><th>Produto</th><th>Variação</th><th>Qtd</th><th>Preço Unit.</th><th>Subtotal</th></tr></thead><tbody>";
    foreach ($pedido['itens'] as $item) {
        $message .= "<tr>";
        $message .= "<td>" . htmlspecialchars($item['produto_nome']) . "</td>";
        $message .= "<td>" . htmlspecialchars($item['variacao']) . "</td>";
        $message .= "<td>" . htmlspecialchars($item['quantidade']) . "</td>";
        $message .= "<td>R$" . number_format($item['preco_unitario'], 2, ',', '.') . "</td>";
        $message .= "<td>R$" . number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') . "</td>";
        $message .= "</tr>";
    }
    $message .= "</tbody></table>";
    $message .= "<p><strong>Subtotal:</strong> R$" . number_format($pedido['subtotal'], 2, ',', '.') . "</p>";
    $message .= "<p><strong>Frete:</strong> R$" . number_format($pedido['frete'], 2, ',', '.') . "</p>";
    if (isset($pedido['cupom_codigo']) && $pedido['cupom_codigo']) {
        $message .= "<p><strong>Desconto Cupom (" . htmlspecialchars($pedido['cupom_codigo']) . "):</strong> R$" . number_format($pedido['subtotal'] + $pedido['frete'] - $pedido['total'], 2, ',', '.') . "</p>";
    }
    $message .= "<p><strong>Total:</strong> R$" . number_format($pedido['total'], 2, ',', '.') . "</p>";

    $message .= "<h2>Endereço de Entrega:</h2>";
    $message .= "<p>";
    $message .= htmlspecialchars($cliente['nome']) . "<br>";
    $message .= htmlspecialchars($pedido['cliente_endereco']) . ", " . htmlspecialchars($pedido['cliente_numero']) . "<br>";
    $message .= htmlspecialchars($pedido['cliente_bairro']) . "<br>";
    $message .= htmlspecialchars($pedido['cliente_cidade']) . " - " . htmlspecialchars($pedido['cliente_estado']) . "<br>";
    $message .= htmlspecialchars($pedido['cliente_cep']);
    $message .= "</p>";
    $message .= "</body></html>";

    // Para um ambiente de produção, seria melhor usar PHPMailer
    return mail($to, $subject, $message, $headers);
}

// Para usar PHPMailer
// 1. Instalar via Composer: composer require phpmailer/phpmailer
// 2. No arquivo, incluir:
/*
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

function enviarEmailConfirmacaoPedidoComPHPMailer($pedido, $cliente) {
    $mail = new PHPMailer(true);
    try {
        // Configurações do servidor SMTP (substitua pelos seus dados)
        // $mail->isSMTP();
        // $mail->Host = 'smtp.example.com';
        // $mail->SMTPAuth = true;
        // $mail->Username = 'seu_email@example.com';
        // $mail->Password = 'sua_senha_do_email';
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        // $mail->Port = 465;

        $mail->setFrom('seu_email@example.com', 'Nome da Sua Loja'); // Substitua
        $mail->addAddress($cliente['email'], $cliente['nome']);
        $mail->addReplyTo('seu_email@example.com', 'Nome da Sua Loja');

        $mail->isHTML(true);
        $mail->Subject = "Confirmação do seu Pedido #" . $pedido['id'];

        $message = "<html><body>..."; // Conteúdo HTML do e-mail, como acima
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: {$mail->ErrorInfo}");
        return false;
    }
}

para este caso, permanecerei com a função nativa do PHP
*/
?>
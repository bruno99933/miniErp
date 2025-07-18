-- Criando banco de dados

CREATE DATABASE mini_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE mini_erp;

-- Tabela de Produtos

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    descricao TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Estoque

CREATE TABLE estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    variacao VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, -- Ex: "Cor: Azul", "Tamanho: G"
    quantidade INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Tabela de Cupons

CREATE TABLE cupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
    tipo_desconto ENUM('percentual', 'fixo') NOT NULL,
    valor_desconto DECIMAL(5, 2) NOT NULL,
    valor_minimo_carrinho DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    data_validade DATE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Pedidos

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_nome VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    cliente_email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    cliente_cep VARCHAR(10) NOT NULL,
    cliente_endereco VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    cliente_numero VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    cliente_bairro VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    cliente_cidade VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    cliente_estado VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    subtotal DECIMAL(10, 2) NOT NULL,
    frete DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    cupom_id INT,
    status ENUM('pendente', 'processando', 'enviado', 'entregue', 'cancelado') NOT NULL DEFAULT 'pendente',
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cupom_id) REFERENCES cupons(id) ON DELETE SET NULL
);

-- Tabela de Itens do Pedido (Adicional, para detalhar cada item no pedido)

CREATE TABLE pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    variacao VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);
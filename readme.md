📦 Mini ERP PHP Simples
Este projeto é um sistema de Mini ERP (Enterprise Resource Planning) desenvolvido em PHP, focado em gerenciar produtos, estoque, cupons e pedidos. Ele serve como uma base para pequenas empresas ou projetos que precisam de um controle básico de vendas e inventário.

✨ Funcionalidades Principais
- Gestão de Produtos: Adicione, edite e visualize produtos com suas respectivas informações.

- Controle de Estoque: Gerencie o estoque por produto e por variações (ex: cor, tamanho).

- Gerenciamento de Cupons: Crie e gerencie cupons de desconto (percentual ou fixo, com valor mínimo de carrinho e validade).

- Controle de carrinho por sessão.

- Processamento de Pedidos: Permite a criação de pedidos, cálculo de subtotal, frete e total com desconto.

- Busca de CEP: Preenchimento automático de endereço usando a API ViaCEP no checkout.

- Confirmação de E-mail: Envio de e-mails de confirmação de pedido via PHPMailer.

- Webhooks para Status: Um endpoint de webhook para atualização programática do status dos pedidos.

-----------------------------------------------------------------------------------------------------------------------------------------

🚀 Como Começar
Siga estes passos para configurar e executar o projeto em sua máquina local.

Pré-requisitos
Certifique-se de que você tem instalado:

- Servidor Web: Apache ou Nginx (MAMP, XAMPP, WAMP são ótimas opções para Windows/macOS).

- PHP: Versão 7.4 ou superior (recomendado PHP 8.x+).

- MySQL/MariaDB: Banco de dados relacional.


1. Clonar o Repositório
Bash

git clone https://github.com/bruno99933/miniErp.git
cd miniErp
(Substitua seu-usuario/seu-projeto.git pelo caminho real do seu repositório).

2. Configurar o Servidor Web
Coloque a pasta seu-projeto dentro do diretório htdocs (MAMP/XAMPP) ou configure um Virtual Host para apontar para a pasta public/ do seu projeto.

3. Configurar o Banco de Dados
Crie um banco de dados MySQL e execute o script SQL abaixo para criar as tabelas necessárias.

SQL

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

5. Configurar o Arquivo de Conexão com o Banco de Dados
Edite o arquivo config/database.php com as credenciais do seu banco de dados:

<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'seu_banco_de_dados'); // O nome que você escolheu no passo 4
define('DB_USER', 'root');
define('DB_PASS', 'sua_senha_do_mysql'); // A senha do seu usuário MySQL
define('DB_CHARSET', 'utf8mb4'); // Manter utf8mb4 para suporte completo a caracteres

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}
?>

6. Acessar o Projeto
Após configurar tudo, você pode acessar o projeto no seu navegador:

Se você configurou um Virtual Host: http://seuprojeto.local

Se você colocou na pasta htdocs: http://localhost/mini_erp/

🗂 Estrutura de Pastas
config/: Arquivos de configuração (ex: database.php).

controllers/: Lógica de negócio e manipulação de requisições.

models/: Interação com o banco de dados (classes Produto, Pedido, Cupom, Estoque).

public/: Arquivos acessíveis via navegador (api e webhooks).

api/: Endpoints de API (ex: cep.php).

webhooks/: Endpoints para webhooks externos.

views/: Arquivos HTML/PHP para renderizar as páginas.

includes/: Partes reutilizáveis do HTML (cabeçalho, rodapé).

functions.php: Funções utilitárias globais (cálculo de frete, subtotal, etc.).

logs/: Pasta para logs de erro (garanta permissão de escrita).

Desenvolvido por Bruno Castelo


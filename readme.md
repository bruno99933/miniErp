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

git clone https://github.com/bruno99933/miniErp.git
cd miniErp
(Substitua seu-usuario/seu-projeto.git pelo caminho real do seu repositório).

2. Configurar o Servidor Web

Coloque a pasta seu-projeto dentro do diretório htdocs (MAMP/XAMPP) ou configure um Virtual Host para apontar para a pasta public/ do seu projeto.

3. Configurar o Banco de Dados

Crie um banco de dados MySQL e execute o script SQL no arquivo database/schema.sql.

5. Configurar o Arquivo de Conexão com o Banco de Dados

Edite o arquivo config/database.php com as credenciais do seu banco de dados:

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

-----------------------------------------------------------------------------------------------------------------------------------------

Desenvolvido por Bruno Castelo


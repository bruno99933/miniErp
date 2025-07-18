<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Mini ERP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .container {
            margin-top: 30px;
        }
        /* Estilos adicionais para centralizar em mobile */
        @media (max-width: 991.98px) { /* Ponto de quebra 'lg' do Bootstrap */
            .navbar-nav {
                width: 100%;
                justify-content: center; /* Centraliza itens em telas pequenas */
                margin-top: 10px; /* Adiciona um pequeno espaço do brand */
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Mini ERP</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="?page=produtos">Produtos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=cupons">Cupons</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=pedidos">Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=carrinho">Carrinho</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
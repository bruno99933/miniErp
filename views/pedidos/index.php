<div class="container mt-4">
    <h2 class="mb-4">Lista de Pedidos</h2>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">Nenhum pedido encontrado.</div>
    <?php else: ?>
        <?php foreach ($pedidos as $pedido): ?>
            <?php
                $pedidoDetalhado = $pedidoModel->getPedidoDetails($pedido['id']);
            ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <strong>Pedido #<?= $pedidoDetalhado['id'] ?></strong>
                    <span>Status: <span class="badge bg-warning text-dark"><?= ucfirst($pedidoDetalhado['status']) ?></span></span>
                </div>
                <div class="card-body">
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($pedidoDetalhado['cliente_nome']) ?> (<?= htmlspecialchars($pedidoDetalhado['cliente_email']) ?>)</p>
                    <p><strong>Endereço:</strong> <?= htmlspecialchars($pedidoDetalhado['cliente_endereco']) ?>, Nº <?= htmlspecialchars($pedidoDetalhado['cliente_numero']) ?>, <?= htmlspecialchars($pedidoDetalhado['cliente_bairro']) ?> - <?= htmlspecialchars($pedidoDetalhado['cliente_cidade']) ?>/<?= htmlspecialchars($pedidoDetalhado['cliente_estado']) ?> (<?= htmlspecialchars($pedidoDetalhado['cliente_cep']) ?>)</p>
                    <p><strong>Subtotal:</strong> R$ <?= number_format($pedidoDetalhado['subtotal'], 2, ',', '.') ?></p>
                    <p><strong>Frete:</strong> R$ <?= number_format($pedidoDetalhado['frete'], 2, ',', '.') ?></p>
                    <p><strong>Total:</strong> <span class="fw-bold">R$ <?= number_format($pedidoDetalhado['total'], 2, ',', '.') ?></span></p>
                    <?php if (!empty($pedidoDetalhado['cupom_codigo'])): ?>
                        <p><strong>Cupom:</strong> <?= htmlspecialchars($pedidoDetalhado['cupom_codigo']) ?></p>
                    <?php endif; ?>

                    <hr>
                    <h5>Itens do Pedido:</h5>
                    <ul class="list-group">
                        <?php foreach ($pedidoDetalhado['itens'] as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($item['produto_nome']) ?> <?= !empty($item['variacao']) ? "({$item['variacao']})" : '' ?>
                                <span><?= $item['quantidade'] ?> × R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

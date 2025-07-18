<div class="container mt-4">
    <h2 class="mb-4">Carrinho de Compras</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['message']['type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']['text']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (empty($produtos)): ?>
        <p>Seu carrinho está vazio.</p>
        <a href="?page=produtos" class="btn btn-primary">Adicionar Produtos</a>
    <?php else: ?>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Produto</th>
                    <th>Variação</th>
                    <th class="text-center">Quantidade</th>
                    <th class="text-end">Preço Unitário</th>
                    <th class="text-end">Subtotal Item</th>
                    <th class="text-center">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nome']) ?></td>
                        <td><?= htmlspecialchars($item['variacao'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['quantidade']) ?></td>
                        <td class="text-end">R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                        <td class="text-end">R$ <?= number_format($item['subtotal_item'], 2, ',', '.') ?></td>
                        <td class="text-center">
                            <a href="?page=carrinho&action=remover&id=<?= htmlspecialchars($item['chave_complexa']) ?>" class="btn btn-sm btn-danger">Remover</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form method="post" action="?page=carrinho&action=aplicarCupom" class="d-flex gap-2 mb-3">
            <input type="text" name="cupom" class="form-control" placeholder="Cupom de desconto" value="<?= htmlspecialchars($_SESSION['cupom'] ?? '') ?>">
            <button type="submit" class="btn btn-outline-primary">Aplicar</button>
        </form>

        <div class="text-end mb-3">
            <p><strong>Subtotal do Carrinho:</strong> R$ <?= number_format($subtotal, 2, ',', '.') ?></p>
            <p><strong>Frete:</strong> R$ <?= number_format($frete, 2, ',', '.') ?></p>
            <p><strong>Desconto:</strong> R$ <?= number_format($desconto, 2, ',', '.') ?></p>
            <h4><strong>Total a Pagar:</strong> R$ <?= number_format($totalComDesconto, 2, ',', '.') ?></h4>
        </div>

        <div class="d-flex justify-content-between">
            <a href="?page=carrinho&action=limpar" class="btn btn-warning">Esvaziar Carrinho</a>
            <a href="?page=checkout&action=finalizarPedido" class="btn btn-success">Finalizar Pedido</a>
        </div>
    <?php endif; ?>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Produtos</h2>
    <a href="?page=produtos&action=editar" class="btn btn-success">Novo Produto</a>
</div>

<?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message']['type'] ?>">
        <?= $_SESSION['message']['text'] ?>
    </div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<?php if (!empty($produtos)): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Descrição</th>
                    <th>Variações</th>
                    <th>Estoque Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?= htmlspecialchars($produto['nome']) ?></td>
                        <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                        <td><?= nl2br(htmlspecialchars($produto['descricao'] ?? '')) ?></td>
                        <td>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($produto['estoque'] as $var): ?>
                                    <li><strong><?= $var['variacao'] ?>:</strong> <?= $var['quantidade'] ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td>
                            <?= array_sum(array_column($produto['estoque'], 'quantidade')) ?>
                        </td>
                        <td>
                            <a href="?page=produtos&action=editar&id=<?= $produto['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="?page=produtos&action=deletar&id=<?= $produto['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>

                            <!-- Formulário de Adicionar ao Carrinho -->
                            <form action="?page=produtos&action=adicionarAoCarrinho" method="post" class="d-inline mt-2">
                                <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                                <div class="input-group input-group-sm mt-1">
                                    <select name="estoque_id" class="form-select form-select-sm" required>
                                        <?php foreach ($produto['estoque'] as $var): ?>
                                            <option value="<?= $var['id'] ?>">
                                                <?= $var['variacao'] ?> - <?= $var['quantidade'] ?> un.
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" name="quantidade" min="1" value="1" class="form-control form-control-sm" style="max-width: 70px;">
                                    <button type="submit" class="btn btn-sm btn-success">Adicionar</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>Nenhum produto cadastrado ainda.</p>
<?php endif; ?>

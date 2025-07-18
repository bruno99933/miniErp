<!-- views/cupons/index.php -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Cupons de Desconto</h2>
    <a href="?page=cupons&action=editar" class="btn btn-success">Novo Cupom</a>
</div>

<?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message']['type'] ?>">
        <?= $_SESSION['message']['text'] ?>
    </div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Código</th>
            <th>Tipo</th>
            <th>Valor</th>
            <th>Valor Mínimo</th>
            <th>Validade</th>
            <th>Ativo</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cupons as $cupom): ?>
            <tr>
                <td><?= htmlspecialchars($cupom['codigo']) ?></td>
                <td><?= $cupom['tipo_desconto'] === 'percentual' ? 'Percentual (%)' : 'Valor Fixo (R$)' ?></td>
                <td>
                    <?= $cupom['tipo_desconto'] === 'percentual'
                        ? $cupom['valor_desconto'] . '%'
                        : 'R$ ' . number_format($cupom['valor_desconto'], 2, ',', '.') ?>
                </td>
                <td>R$ <?= number_format($cupom['valor_minimo_carrinho'], 2, ',', '.') ?></td>
                <td><?= $cupom['data_validade'] ? date('d/m/Y', strtotime($cupom['data_validade'])) : '-' ?></td>
                <td><?= $cupom['ativo'] ? 'Sim' : 'Não' ?></td>
                <td>
                    <a href="?page=cupons&action=editar&id=<?= $cupom['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="?page=cupons&action=deletar&id=<?= $cupom['id'] ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Tem certeza que deseja excluir este cupom?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($cupons)): ?>
            <tr>
                <td colspan="7" class="text-center">Nenhum cupom cadastrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
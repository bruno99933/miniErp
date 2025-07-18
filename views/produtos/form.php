
<h2><?= isset($produto) ? 'Editar Produto' : 'Cadastrar Produto' ?></h2>

<?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message']['type'] ?>">
        <?= $_SESSION['message']['text'] ?>
    </div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<form action="?page=produtos&action=salvar" method="POST">
    <?php if (!empty($produto['id'])): ?>
        <input type="hidden" name="id" value="<?= $produto['id'] ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="nome" class="form-label">Nome do Produto</label>
        <input type="text" class="form-control" id="nome" name="nome" required value="<?= $produto['nome'] ?? '' ?>">
    </div>

    <div class="mb-3">
        <label for="preco" class="form-label">Preço</label>
        <input type="number" step="0.01" class="form-control" id="preco" name="preco" required value="<?= $produto['preco'] ?? '' ?>">
    </div>

    <div class="mb-3">
        <label for="descricao" class="form-label">Descrição</label>
        <textarea class="form-control" name="descricao" id="descricao"><?= $produto['descricao'] ?? '' ?></textarea>
    </div>

    <h5>Variações de Estoque</h5>
    <div id="variacoes-container">
        <?php if (!empty($estoque)): ?>
            <?php foreach ($estoque as $index => $item): ?>
                <div class="row mb-2">
                    <input type="hidden" name="estoque_id[]" value="<?= $item['id'] ?>">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="variacao[]" placeholder="Ex: Tamanho M" value="<?= $item['variacao'] ?>">
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="quantidade[]" placeholder="Quantidade" value="<?= $item['quantidade'] ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-remove-variacao">Remover</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="row mb-2">
                <input type="hidden" name="estoque_id[]" value="">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="variacao[]" placeholder="Ex: Tamanho M">
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control" name="quantidade[]" placeholder="Quantidade">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-remove-variacao">Remover</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <button type="button" class="btn btn-secondary mb-3" id="add-variacao">Adicionar Variação</button>

    <div class="mb-3">
        <button type="submit" class="btn btn-success">Salvar Produto</button>
        <a href="?page=produtos" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<script>
    document.getElementById('add-variacao').addEventListener('click', function () {
        const container = document.getElementById('variacoes-container');
        const row = document.createElement('div');
        row.classList.add('row', 'mb-2');
        row.innerHTML = `
            <input type="hidden" name="estoque_id[]" value="">
            <div class="col-md-6">
                <input type="text" class="form-control" name="variacao[]" placeholder="Ex: Tamanho M">
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control" name="quantidade[]" placeholder="Quantidade">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-remove-variacao">Remover</button>
            </div>
        `;
        container.appendChild(row);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-remove-variacao')) {
            e.target.closest('.row').remove();
        }
    });
</script>
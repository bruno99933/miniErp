<!-- views/cupons/form.php -->
<div class="container mt-4">
    <h2><?= isset($cupom['id']) ? 'Editar Cupom' : 'Novo Cupom' ?></h2>

    <form method="POST" action="?page=cupons&action=salvar">
        <input type="hidden" name="id" value="<?= $cupom['id'] ?? '' ?>">

        <div class="form-group mb-2">
            <label for="codigo">Código</label>
            <input type="text" name="codigo" id="codigo" class="form-control" required value="<?= htmlspecialchars($cupom['codigo'] ?? '') ?>">
        </div>

        <div class="form-group mb-2">
            <label for="tipo_desconto">Tipo de Desconto</label>
            <select name="tipo_desconto" id="tipo_desconto" class="form-control" required>
                <option value="percentual" <?= (isset($cupom['tipo_desconto']) && $cupom['tipo_desconto'] === 'percentual') ? 'selected' : '' ?>>Percentual (%)</option>
                <option value="valor_fixo" <?= (isset($cupom['tipo_desconto']) && $cupom['tipo_desconto'] === 'valor_fixo') ? 'selected' : '' ?>>Valor Fixo (R$)</option>
            </select>
        </div>

        <div class="form-group mb-2">
            <label for="valor_desconto">Valor do Desconto</label>
            <input type="number" step="0.01" name="valor_desconto" id="valor_desconto" class="form-control" required value="<?= $cupom['valor_desconto'] ?? '' ?>">
        </div>

        <div class="form-group mb-2">
            <label for="valor_minimo_carrinho">Valor Mínimo do Carrinho</label>
            <input type="number" step="0.01" name="valor_minimo_carrinho" id="valor_minimo_carrinho" class="form-control" value="<?= $cupom['valor_minimo_carrinho'] ?? '' ?>">
        </div>

        <div class="form-group mb-2">
            <label for="data_validade">Data de Validade</label>
            <input type="date" name="data_validade" id="data_validade" class="form-control" value="<?= $cupom['data_validade'] ?? '' ?>">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="ativo" name="ativo" value="1" <?= (isset($cupom['ativo']) && $cupom['ativo']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="?page=cupons" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

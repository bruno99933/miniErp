<?php
$carrinho = $_SESSION['carrinho'] ?? [];
$subtotal = calcularSubtotalCarrinho();
$frete = calcularFrete($subtotal);
$total = $subtotal + $frete;
?>

<div class="container mt-5">
    <h2 class="mb-4">Finalizar Pedido</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message']['type'] ?>">
            <?= $_SESSION['message']['text'] ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (empty($carrinho)): ?>
        <div class="alert alert-info">Seu carrinho está vazio. <a href="?page=produtos">Ver produtos</a></div>
    <?php else: ?>
        <form method="POST" action="?page=checkout&action=finalizarPedido">
            <div class="row">
                <div class="col-md-6">
                    <h4>Dados do Cliente</h4>
                    <div class="form-group mb-3"> <label for="cliente_nome">Nome*</label>
                        <input type="text" name="cliente_nome" id="cliente_nome" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="cliente_email">Email*</label>
                        <input type="email" name="cliente_email" id="cliente_email" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="cliente_cep">CEP*</label>
                        <input type="text" name="cliente_cep" id="cliente_cep" class="form-control" required maxlength="9">
                    </div>
                    <div class="form-group mb-3">
                        <label for="cliente_endereco">Endereço*</label>
                        <input type="text" name="cliente_endereco" id="cliente_endereco" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="cliente_numero">Número*</label>
                        <input type="text" name="cliente_numero" id="cliente_numero" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="cliente_bairro">Bairro</label>
                        <input type="text" name="cliente_bairro" id="cliente_bairro" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label for="cliente_cidade">Cidade</label>
                        <input type="text" name="cliente_cidade" id="cliente_cidade" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label for="cliente_estado">Estado</label>
                        <input type="text" name="cliente_estado" id="cliente_estado" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label for="cupom_codigo">Cupom de Desconto</label>
                        <input type="text" name="cupom_codigo" id="cupom_codigo" class="form-control" value="<?= htmlspecialchars($_SESSION['cupom'] ?? '') ?>" disabled>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4>Resumo do Carrinho</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Variação</th>
                                <th>Qtd</th>
                                <th>Preço</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrinho as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['nome']) ?></td>
                                    <td><?= htmlspecialchars($item['variacao'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($item['quantidade']) ?></td>
                                    <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p><strong>Subtotal:</strong> R$ <?= number_format($subtotal, 2, ',', '.') ?></p>
                    <p><strong>Frete:</strong> R$ <?= number_format($frete, 2, ',', '.') ?></p>
                    <p><strong>Total:</strong> R$ <?= number_format($total, 2, ',', '.') ?></p>
                    <p><strong>Total com desconto:</strong> R$ <?= number_format($_SESSION['totalComDesconto'], 2, ',', '.') ?></p>

                    <button type="submit" class="btn btn-success btn-block mt-3">Finalizar Pedido</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cepInput = document.getElementById('cliente_cep');
        const enderecoInput = document.getElementById('cliente_endereco');
        const bairroInput = document.getElementById('cliente_bairro');
        const cidadeInput = document.getElementById('cliente_cidade');
        const estadoInput = document.getElementById('cliente_estado');

        // Função para limpar os campos de endereço
        function limparFormularioEndereco() {
            enderecoInput.value = "";
            bairroInput.value = "";
            cidadeInput.value = "";
            estadoInput.value = "";
        }

        // Event listener para o campo de CEP
        if (cepInput) {

            function aplicarMascaraCEP(value) {
                if (!value) return "";
                value = value.replace(/\D/g, ''); // Remove tudo que não é dígito
                if (value.length > 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5, 8);
                } else if (value.length > 8) { // Limita a 8 dígitos + hífen
                    value = value.substring(0, 9);
                }
                return value;
            }

            cepInput.addEventListener('input', function () {
                this.value = aplicarMascaraCEP(this.value);
            });

            cepInput.addEventListener('blur', function () { // Usa 'blur' para disparar quando o campo perde o foco
                let cep = this.value.replace(/\D/g, ''); // Remove caracteres não numéricos

                if (cep.length === 8) {
                    // Limpa e desabilita temporariamente enquanto busca
                    limparFormularioEndereco(false); 
                    enderecoInput.placeholder = "Buscando CEP...";
                    
                    // Requisição AJAX para o seu backend
                    fetch(`public/api/cep.php?cep=${cep}`)
                        .then(response => response.json())
                        .then(result => {
                            enderecoInput.placeholder = ""; // Remove o placeholder de busca

                            if (result.success) {
                                const data = result.data;
                                enderecoInput.value = data.logradouro;
                                bairroInput.value = data.bairro;
                                cidadeInput.value = data.localidade;
                                cidadeInput.readOnly = true; 
                                estadoInput.value = data.uf;
                                estadoInput.readOnly = true;
                            } else {
                                // Backend já setou a $_SESSION['message'], apenas limpamos e alertamos
                                limparFormularioEndereco();
                            }
                        })
                        .catch(error => {
                            console.error('Erro na requisição AJAX de CEP:', error);
                            limparFormularioEndereco();
                            enderecoInput.placeholder = "";
                        });
                } else if (cep.length > 0 && cep.length < 8) {
                    limparFormularioEndereco();
                } else {
                    limparFormularioEndereco(); // Limpa se o campo for esvaziado
                }
            });
        }
    });
</script>
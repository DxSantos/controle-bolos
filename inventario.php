<?php
require 'config.php';
require 'includes/verifica_permissao.php';
include 'includes/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if (!verificaPermissao('inventario')) {
    echo "<div class='alert alert-danger m-4 text-center'>🚫 Sem permissão.</div>";
    include 'includes/footer.php';
    exit;
}

// Código do inventário
$codigo_inventario = 'INV-' . date('Ymd-His');

// Tipos
$tipos = $pdo->query("SELECT * FROM tipos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Produtos por tipo
$produtos_por_tipo = [];
foreach ($tipos as $tipo) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.nome, IFNULL(s.saldo, 0) AS saldo_atual
        FROM produtos p
        LEFT JOIN saldo_produtos s ON s.produto_id = p.id
        WHERE p.tipo = ?
        ORDER BY p.nome
    ");
    $stmt->execute([$tipo['id']]);
    $produtos_por_tipo[$tipo['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
html, body {
    height: 100%;
}

.container-inventario {
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.inventario-body {
    flex: 1;
    overflow-y: auto;
}

.card-tipo {
    height: 100%;
}

.lista-produtos {
    max-height: 55vh;
    overflow-y: auto;
}

.qtd-control {
    display: flex;
    align-items: center;
    gap: 5px;
}

.qtd-control input {
    width: 80px;
    text-align: center;
}
</style>

<div class="container-fluid container-inventario py-3">

    <div class="alert alert-info text-center">
        <strong>Código do Inventário:</strong> <?= $codigo_inventario ?>
    </div>

    <div class="inventario-body row g-3">

        <?php foreach ($tipos as $tipo): ?>
            <div class="col-md-6 col-lg-4">
                <form method="POST" action="salvar_inventario.php">
                    
                    <input type="hidden" name="codigo_inventario" value="<?= $codigo_inventario ?>">
                    <input type="hidden" name="tipo_id" value="<?= $tipo['id'] ?>">

                    <div class="card card-tipo shadow-sm">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <strong><?= htmlspecialchars($tipo['nome']) ?></strong>
                            <button type="submit" class="btn btn-success btn-sm">
                                💾 Salvar
                            </button>
                        </div>

                        <div class="card-body lista-produtos">

                            <?php if (empty($produtos_por_tipo[$tipo['id']])): ?>
                                <p class="text-muted">Nenhum produto.</p>
                            <?php else: ?>
                                <?php foreach ($produtos_por_tipo[$tipo['id']] as $p): ?>
                                    <div class="border rounded p-2 mb-2">
                                        <div class="fw-bold">
                                            <?= htmlspecialchars($p['nome']) ?>
                                        </div>

                                        <small class="text-muted">
                                            Saldo atual: <?= $p['saldo_atual'] ?>
                                        </small>

                                        <input type="hidden"
                                               name="saldo_anterior[<?= $p['id'] ?>]"
                                               value="<?= $p['saldo_atual'] ?>">

                                        <div class="qtd-control mt-2">
                                            <button type="button" class="btn btn-outline-secondary btn-minus">−</button>

                                            <input type="number"
                                                   name="saldo_inventario[<?= $p['id'] ?>]"
                                                   class="form-control"
                                                   step="1"
                                                   min="0"
                                                   placeholder="—">

                                            <button type="button" class="btn btn-outline-secondary btn-plus">+</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </div>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<script>
document.querySelectorAll('.btn-plus').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        input.value = input.value === '' ? 1 : parseInt(input.value) + 1;
    });
});

document.querySelectorAll('.btn-minus').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.nextElementSibling;
        if (input.value === '') return;
        input.value = Math.max(0, parseInt(input.value) - 1);
    });
});
</script>

<?php include 'includes/footer.php'; ?>

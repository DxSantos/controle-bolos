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

// Produtos
$produtos = $pdo->query("
    SELECT p.id, p.nome, IFNULL(s.saldo, 0) AS saldo_atual
    FROM produtos p
    LEFT JOIN saldo_produtos s ON s.produto_id = p.id
    ORDER BY p.nome
")->fetchAll(PDO::FETCH_ASSOC);

// Código único do inventário
$codigo_inventario = 'INV-' . date('Ymd-His');
?>

<div class="container py-4">

    <h3 class="mb-4">📦 Inventário de Estoque</h3>

    <form method="POST" action="salvar_inventario.php">

        <input type="hidden" name="codigo_inventario" value="<?= $codigo_inventario ?>">

        <div class="alert alert-info">
            <strong>Código do Inventário:</strong> <?= $codigo_inventario ?>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Produto</th>
                    <th>Saldo Atual</th>
                    <th>Saldo Inventário</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td>
                            <?= number_format($p['saldo_atual'], 0, ',', '.') ?>
                            <input type="hidden" name="saldo_anterior[<?= $p['id'] ?>]" value="<?= $p['saldo_atual'] ?>">
                        </td>
                        <td>
                            <input type="number"
                                   name="saldo_inventario[<?= $p['id'] ?>]"
                                   class="form-control"
                                   step="1"
                                   min="0"
                                   value="0">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">
            💾 Salvar Inventário
        </button>

    </form>
</div>

<?php include 'includes/footer.php'; ?>

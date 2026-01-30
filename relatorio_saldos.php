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

if (!verificaPermissao('relatorios')) {
    echo "<div class='alert alert-danger m-4 text-center'>
            🚫 Você não tem permissão para acessar este relatório.
          </div>";
    include 'includes/footer.php';
    exit;
}

// =================== FILTROS ===================
$tipoFiltro = isset($_GET['tipo_id']) && $_GET['tipo_id'] !== ''
    ? (int)$_GET['tipo_id']
    : null;

// =================== BUSCA TIPOS ===================
$tipos = $pdo->query("
    SELECT id, nome
    FROM tipos
    ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);

// =================== BUSCA SALDOS ===================
$sql = "
    SELECT
        p.id,
        p.nome AS produto,
        p.tipo_id,
        p.subtipo_id,
        t.nome AS tipo_nome,
        st.nome AS subtipo_nome,
        COALESCE(sp.saldo, 0) AS saldo
    FROM produtos p
    INNER JOIN tipos t ON t.id = p.tipo_id
    LEFT JOIN subtipos st ON st.id = p.subtipo_id
    LEFT JOIN saldo_produtos sp ON sp.produto_id = p.id
";

$params = [];

if ($tipoFiltro) {
    $sql .= " WHERE p.tipo_id = :tipo_id";
    $params[':tipo_id'] = $tipoFiltro;
}

$sql .= " ORDER BY t.nome, st.nome, p.nome";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =================== AGRUPAMENTO ===================
$relatorio = [];

foreach ($dados as $row) {
    $tipo = $row['tipo_nome'];
    $subtipo = $row['subtipo_nome'] ?? 'Sem Subtipo';

    $relatorio[$tipo][$subtipo][] = [
        'produto' => $row['produto'],
        'saldo'   => $row['saldo']
    ];
}
?>

<div class="container py-4">

    <h3 class="mb-4">📊 Relatório de Saldos por Tipo</h3>

    <!-- FILTRO -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <label class="form-label">Tipo</label>
            <select name="tipo_id" class="form-select">
                <option value="">Todos os tipos</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($tipoFiltro == $t['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button class="btn btn-primary">Filtrar</button>
            <a href="relatorio_saldos.php" class="btn btn-secondary">Limpar</a>
        </div>
    </form>

    <!-- CARDS -->
    <div class="row">
        <?php if (empty($relatorio)): ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    Nenhum dado encontrado.
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($relatorio as $tipo => $subtipos): ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white fw-bold">
                        <?= htmlspecialchars($tipo) ?>
                    </div>
                    <div class="card-body">

                        <?php foreach ($subtipos as $subtipo => $produtos): ?>
                            <h6 class="mt-3 text-secondary">
                                <?= htmlspecialchars($subtipo) ?>
                            </h6>

                            <table class="table table-sm table-bordered mb-3">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th width="100">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos as $p): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['produto']) ?></td>
                                            <td class="text-end fw-bold">
                                                <?= $p['saldo'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

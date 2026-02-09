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

if (!verificaPermissao('analitico')) {
    echo "<div class='alert alert-danger m-4 text-center'>
            🚫 Você não tem permissão.
          </div>";
    include 'includes/footer.php';
    exit;
}


// ==================== FILTROS ====================

$tipo_id    = $_GET['tipo_id']    ?? '';
$produto_id = $_GET['produto_id'] ?? '';
$subtipo_id = $_GET['subtipo_id'] ?? '';

// Carregar combos
$tipos     = $pdo->query("SELECT id,nome FROM tipos ORDER BY nome")->fetchAll();
$produtos  = $pdo->query("SELECT id,nome FROM produtos ORDER BY nome")->fetchAll();
$subtipos  = $pdo->query("SELECT id,nome FROM subtipos ORDER BY nome")->fetchAll();

/*
BUSCA TODOS OS DADOS JÁ ORGANIZADOS
*/
$sql = "
SELECT
    p.id            AS produto_id,
    p.nome          AS produto,
    p.tipo_id,
    t.nome          AS tipo,
    s.id            AS subtipo_id,
    s.nome          AS subtipo,
    COALESCE(sp.saldo,0) AS saldo
FROM produtos p
JOIN tipos t ON t.id = p.tipo_id
LEFT JOIN subtipos s ON s.id = p.subtipo_id
LEFT JOIN saldo_produtos sp ON sp.produto_id = p.id
WHERE 1=1
";

$params = [];

if (!empty($tipo_id)) {
    $sql .= " AND p.tipo_id = :tipo_id";
    $params[':tipo_id'] = $tipo_id;
}

if (!empty($produto_id)) {
    $sql .= " AND p.id = :produto_id";
    $params[':produto_id'] = $produto_id;
}

if (!empty($subtipo_id)) {
    $sql .= " AND p.subtipo_id = :subtipo_id";
    $params[':subtipo_id'] = $subtipo_id;
}

$sql .= " 
ORDER BY 
    t.nome,
    p.nome,
    FIELD(s.nome,'subtipo_id IS NULL') ASC,
    s.id
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($dados)) {
    echo "<div class='alert alert-info m-4 text-center'>
            ℹ️ Nenhum dado encontrado para os filtros selecionados.
          </div>";
    include 'includes/footer.php';
    exit;
}

/*
ORGANIZA EM ESTRUTURA:
TIPO
  PRODUTO
     SUBTIPO => SALDO
*/

$relatorio = [];

foreach ($dados as $row) {

    $tipo = $row['tipo'];
    $produto = $row['produto'];
    $subtipo = $row['subtipo'] ?? 'Saldo Sem Subtipo';

    $relatorio[$tipo]['produtos'][$produto][$subtipo] = $row['saldo'];
    $relatorio[$tipo]['subtipos'][$subtipo] = true;
}


?>

<style>
    body {
        background: #f8f9fa;
    }

    .card-tipo {
        border-radius: 10px;
        box-shadow: 0 3px 8px rgba(0, 0, 0, .1);
        margin-bottom: 25px;
    }

    .table th {
        background: #f1f1f1;
        text-align: center;
    }

    .table td {
        text-align: center;
    }

    .produto-col {
        text-align: left;
        font-weight: 600;
    }
</style>

<div class="container py-4">

    <h3 class="mb-4">📊 Relatório de Saldos por Tipo / Produto / Subtipo</h3>


    <form method="GET" class="row g-2 mb-4">

        <div class="col-md-3">
            <label>Tipo</label>
            <select name="tipo_id" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($tipo_id == $t['id']) ? 'selected' : '' ?>>
                        <?= $t['nome'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label>Subtipo</label>
            <select name="subtipo_id" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($subtipos as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($subtipo_id == $s['id']) ? 'selected' : '' ?>>
                        <?= $s['nome'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end gap-2">
    <button class="btn btn-primary w-100">Filtrar</button>
    <a href="relatorio_analitico.php" class="btn btn-secondary w-100">Limpar</a>
</div>

<div class="col-md-3 d-flex align-items-end gap-2">
    <a class="btn btn-success w-100"
       href="export_relatorio_analitico_excel.php?tipo_id=<?= $tipo_id ?>&subtipo_id=<?= $subtipo_id ?>">
        Exportar Excel
    </a>

    <a class="btn btn-danger w-100"
       target="_blank"
       href="export_relatorio_analitico_pdf.php?tipo_id=<?= $tipo_id ?>&subtipo_id=<?= $subtipo_id ?>">
        Exportar PDF
    </a>
</div>

    </form>


    <?php foreach ($relatorio as $tipoNome => $dadosTipo): ?>

        <div class="card card-tipo">

            <div class="card-header bg-primary text-white">
                <strong><?= htmlspecialchars($tipoNome) ?></strong>
            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead>
                            <tr>
                                <th>Produto</th>

                                <?php foreach ($dadosTipo['subtipos'] as $subtipoNome => $x): ?>
                                    <th><?= htmlspecialchars($subtipoNome) ?></th>
                                <?php endforeach; ?>

                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($dadosTipo['produtos'] as $produtoNome => $subtipos): ?>

                                <tr>
                                    <td class="produto-col"><?= htmlspecialchars($produtoNome) ?></td>

                                    <?php foreach ($dadosTipo['subtipos'] as $subtipoNome => $x): ?>

                                        <td>
                                            <?= $subtipos[$subtipoNome] ?? 0 ?>
                                        </td>

                                    <?php endforeach; ?>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    <?php endforeach; ?>

</div>

<?php include 'includes/footer.php'; ?>
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
    echo "<div class='alert alert-danger m-4 text-center'>🚫 Sem permissão.</div>";
    include 'includes/footer.php';
    exit;
}

/* ================= FILTROS ================= */

$data_ini  = $_GET['data_ini']  ?? '';
$data_fim  = $_GET['data_fim']  ?? '';
$tipo_id   = $_GET['tipo_id']   ?? '';
$produto_id= $_GET['produto_id']?? '';
$subtipo_id= $_GET['subtipo_id']?? '';

/* ================= COMBOS ================= */

$tipos = $pdo->query("SELECT id,nome FROM tipos ORDER BY nome")->fetchAll();
$produtos = $pdo->query("
    SELECT p.id,
           CONCAT(p.nome, IF(s.nome IS NOT NULL, CONCAT(' - ',s.nome),'')) AS nome
    FROM produtos p
    LEFT JOIN subtipos s ON p.subtipo_id=s.id
    ORDER BY p.nome
")->fetchAll();

$subtipos = $pdo->query("SELECT id,nome FROM subtipos ORDER BY nome")->fetchAll();

/* ================= SQL BASE ================= */

$where = " WHERE 1=1 ";
$params = [];

if ($data_ini && $data_fim) {
    $where .= " AND DATE(m.data) BETWEEN :di AND :df ";
    $params[':di'] = $data_ini;
    $params[':df'] = $data_fim;
}

if ($tipo_id) {
    $where .= " AND p.tipo_id=:tipo ";
    $params[':tipo'] = $tipo_id;
}

if ($produto_id) {
    $where .= " AND p.id=:prod ";
    $params[':prod'] = $produto_id;
}

if ($subtipo_id) {
    $where .= " AND p.subtipo_id=:sub ";
    $params[':sub'] = $subtipo_id;
}

/* ================= ENTRADAS ================= */

$sqlEntradas = "
SELECT 
    m.id,
    'ENTRADA' AS movimento,
    p.nome AS produto,
    t.nome AS tipo,
    s.nome AS subtipo,
    m.quantidade,
    m.data
FROM controle_entrada m
JOIN produtos p ON p.id=m.produto_id
LEFT JOIN tipos t ON p.tipo_id=t.id
LEFT JOIN subtipos s ON p.subtipo_id=s.id
$where
";

/* ================= SAÍDAS ================= */

$sqlSaidas = "
SELECT 
    m.id,
    'SAIDA' AS movimento,
    p.nome AS produto,
    t.nome AS tipo,
    s.nome AS subtipo,
    m.quantidade,
    m.data
FROM controle_saida m
JOIN produtos p ON p.id=m.produto_id
LEFT JOIN tipos t ON p.tipo_id=t.id
LEFT JOIN subtipos s ON p.subtipo_id=s.id
$where
";

/* ================= UNION ================= */

$sql = "$sqlEntradas UNION ALL $sqlSaidas ORDER BY data DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Relatório de Entradas e Saídas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container py-4">

<h4 class="mb-3">📊 Relatório de Entradas e Saídas</h4>

<form class="row g-2 mb-4">

    <div class="col-md-2">
        <label>Data Inicial</label>
        <input type="date" name="data_ini" value="<?= $data_ini ?>" class="form-control">
    </div>

    <div class="col-md-2">
        <label>Data Final</label>
        <input type="date" name="data_fim" value="<?= $data_fim ?>" class="form-control">
    </div>

    <div class="col-md-2">
        <label>Tipo</label>
        <select name="tipo_id" class="form-select">
            <option value="">Todos</option>
            <?php foreach($tipos as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $tipo_id==$t['id']?'selected':'' ?>>
                    <?= $t['nome'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label>Produto</label>
        <select name="produto_id" class="form-select">
            <option value="">Todos</option>
            <?php foreach($produtos as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $produto_id==$p['id']?'selected':'' ?>>
                    <?= $p['nome'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label>Subtipo</label>
        <select name="subtipo_id" class="form-select">
            <option value="">Todos</option>
            <?php foreach($subtipos as $s): ?>
                <option value="<?= $s['id'] ?>" <?= $subtipo_id==$s['id']?'selected':'' ?>>
                    <?= $s['nome'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-12 d-flex gap-2 mt-2">
        <button class="btn btn-primary">Filtrar</button>
        <a href="relatorio_movimentos.php" class="btn btn-secondary">Limpar</a>
    </div>

</form>

<div class="table-responsive">
<table class="table table-bordered table-sm table-striped">
<thead class="table-light">
<tr>
    <th>Data</th>
    <th>Movimento</th>
    <th>Produto</th>
    <th>Subtipo</th>
    <th>Tipo</th>
    <th>Quantidade</th>
</tr>
</thead>

<tbody>

<?php if($dados): ?>
<?php foreach($dados as $d): ?>
<tr>
    <td><?= date('d/m/Y H:i', strtotime($d['data'])) ?></td>
    <td>
        <span class="badge <?= $d['movimento']=='ENTRADA'?'bg-success':'bg-danger' ?>">
            <?= $d['movimento'] ?>
        </span>
    </td>
    <td><?= $d['produto'] ?></td>
    <td><?= $d['subtipo'] ?? '-' ?></td>
    <td><?= $d['tipo'] ?></td>
    <td><?= $d['quantidade'] ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr>
<td colspan="6" class="text-center">Nenhum registro encontrado</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>

</div>

</body>
</html>

<?php include 'includes/footer.php'; ?>

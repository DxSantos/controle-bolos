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
    echo "<div class='alert alert-danger m-4 text-center'>Sem permissão</div>";
    include 'includes/footer.php';
    exit;
}

/* ================== FILTROS ================== */

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim    = $_GET['data_fim'] ?? date('Y-m-d');

/* ================== ENTRADAS POR TIPO ================== */

$sqlEntrada = "
SELECT 
    t.nome AS tipo,
    DATE(e.data) AS dia,
    SUM(e.quantidade) AS total
FROM controle_entrada e
JOIN produtos p ON p.id = e.produto_id
JOIN tipos t ON t.id = p.tipo_id
WHERE DATE(e.data) BETWEEN :inicio AND :fim
GROUP BY t.nome, DATE(e.data)
ORDER BY DATE(e.data)
";

$stmt = $pdo->prepare($sqlEntrada);
$stmt->execute([
    ':inicio' => $data_inicio,
    ':fim' => $data_fim
]);

$entradas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================== SAÍDAS POR TIPO ================== */

$sqlSaida = "
SELECT 
    t.nome AS tipo,
    DATE(s.data) AS dia,
    SUM(s.quantidade) AS total
FROM controle_saida s
JOIN produtos p ON p.id = s.produto_id
JOIN tipos t ON t.id = p.tipo_id
WHERE DATE(s.data) BETWEEN :inicio AND :fim
GROUP BY t.nome, DATE(s.data)
ORDER BY DATE(s.data)
";

$stmt = $pdo->prepare($sqlSaida);
$stmt->execute([
    ':inicio' => $data_inicio,
    ':fim' => $data_fim
]);

$saidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================== ORGANIZA DADOS ================== */

$datas = [];
$tipos = [];

foreach ($entradas as $r) {
    $datas[$r['dia']] = true;
    $tipos[$r['tipo']] = true;
}

foreach ($saidas as $r) {
    $datas[$r['dia']] = true;
    $tipos[$r['tipo']] = true;
}

$datas = array_keys($datas);
sort($datas);

$tipos = array_keys($tipos);

/* Matrizes */

$entradaMatrix = [];
$saidaMatrix = [];

foreach ($tipos as $t) {
    foreach ($datas as $d) {
        $entradaMatrix[$t][$d] = 0;
        $saidaMatrix[$t][$d] = 0;
    }
}

foreach ($entradas as $r) {
    $entradaMatrix[$r['tipo']][$r['dia']] = $r['total'];
}

foreach ($saidas as $r) {
    $saidaMatrix[$r['tipo']][$r['dia']] = $r['total'];
}

?>

<div class="container py-4">

<h3 class="mb-4">📊 Dashboard de Entradas e Saídas por Tipo</h3>

<form class="row g-3 mb-4">

<div class="col-md-3">
<label>Data Inicial</label>
<input type="date" name="data_inicio" value="<?= $data_inicio ?>" class="form-control">
</div>

<div class="col-md-3">
<label>Data Final</label>
<input type="date" name="data_fim" value="<?= $data_fim ?>" class="form-control">
</div>

<div class="col-md-2 d-flex align-items-end">
<button class="btn btn-primary w-100">Filtrar</button>
</div>

</form>

<div class="row">

<div class="col-md-6">
<h5>Entradas</h5>
<canvas id="graficoEntradas"></canvas>
</div>

<div class="col-md-6">
<h5>Saídas</h5>
<canvas id="graficoSaidas"></canvas>
</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const datas = <?= json_encode($datas) ?>;
const tipos = <?= json_encode($tipos) ?>;

const entradaMatrix = <?= json_encode($entradaMatrix) ?>;
const saidaMatrix = <?= json_encode($saidaMatrix) ?>;

function gerarDatasets(matrix){

    return tipos.map(tipo => ({
        label: tipo,
        data: datas.map(d => matrix[tipo][d]),
        borderWidth: 2,
        fill:false
    }));
}

/* ENTRADAS */

new Chart(document.getElementById('graficoEntradas'), {
    type: 'line',
    data: {
        labels: datas,
        datasets: gerarDatasets(entradaMatrix)
    },
    options:{
        responsive:true,
        plugins:{
            legend:{ position:'bottom' }
        }
    }
});

/* SAIDAS */

new Chart(document.getElementById('graficoSaidas'), {
    type: 'line',
    data: {
        labels: datas,
        datasets: gerarDatasets(saidaMatrix)
    },
    options:{
        responsive:true,
        plugins:{
            legend:{ position:'bottom' }
        }
    }
});

</script>

<?php include 'includes/footer.php'; ?>

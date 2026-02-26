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

if (!verificaPermissao('movimentacao')) {
    echo "<div class='alert alert-danger m-4 text-center'>
            🚫 Você não tem permissão para acessar esta página.
          </div>";
    include 'includes/footer.php';
    exit;
}

$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim    = $_GET['data_fim'] ?? '';
$produto_id  = $_GET['produto_id'] ?? '';
$tipo_id     = $_GET['tipo_id'] ?? '';
$subtipo_id  = $_GET['subtipo_id'] ?? '';

$where = [];
$params = [];

if ($data_inicio && $data_fim) {
    $where[] = "DATE(m.data) BETWEEN :inicio AND :fim";
    $params[':inicio'] = $data_inicio;
    $params[':fim'] = $data_fim;
}

if ($produto_id) {
    $where[] = "p.id = :produto";
    $params[':produto'] = $produto_id;
}

if ($tipo_id) {
    $where[] = "p.tipo_id = :tipo";
    $params[':tipo'] = $tipo_id;
}

if ($subtipo_id) {
    $where[] = "p.subtipo_id = :subtipo";
    $params[':subtipo'] = $subtipo_id;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
SELECT 
    t.nome as tipo,
    p.id as produto_id,
    p.nome as produto,
    DATE(m.data) as data,
    SUM(m.entrada) as total_entrada,
    SUM(m.saida) as total_saida
FROM (
    SELECT produto_id, quantidade as entrada, 0 as saida, data
    FROM controle_entrada
    UNION ALL
    SELECT produto_id, 0 as entrada, quantidade as saida, data
    FROM controle_saida
) m
JOIN produtos p ON p.id = m.produto_id
JOIN tipos t ON t.id = p.tipo_id
$where_sql
GROUP BY t.nome, p.id, DATE(m.data)
ORDER BY t.nome, p.nome, data
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   ORGANIZA MATRIZ
=================================*/

$datas = [];
$matriz = [];

foreach ($dados as $row) {

    $tipo = $row['tipo'];
    $produto = $row['produto'];
    $data = $row['data'];

    $datas[$data] = $data;

    $matriz[$tipo][$produto][$data]['entrada'] = $row['total_entrada'];
    $matriz[$tipo][$produto][$data]['saida']   = $row['total_saida'];
}

ksort($datas);
?>

<div class="container-fluid">

<?php foreach ($matriz as $tipo => $produtos): ?>

<div class="card mb-4 shadow">
    <div class="card-header bg-dark text-white fw-bold">
        Tipo: <?= $tipo ?>
    </div>

    <div class="card-body table-responsive">

        <table class="table table-bordered table-sm align-middle text-center">

            <thead class="table-secondary">
                <tr>
                    <th rowspan="2" class="text-start">Produto</th>

                    <?php foreach ($datas as $data): ?>
                        <th colspan="2">
                            <?= date('d/m', strtotime($data)) ?>
                        </th>
                    <?php endforeach; ?>

                    <th colspan="2" class="bg-dark text-white">TOTAL</th>
                </tr>

                <tr>
                    <?php foreach ($datas as $data): ?>
                        <th class="text-success">E</th>
                        <th class="text-danger">S</th>
                    <?php endforeach; ?>

                    <th class="text-success bg-dark text-white">E</th>
                    <th class="text-danger bg-dark text-white">S</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach ($produtos as $produto => $movs): 

                $total_prod_entrada = 0;
                $total_prod_saida = 0;
            ?>

                <tr>
                    <td class="text-start fw-bold"><?= $produto ?></td>

                    <?php foreach ($datas as $data):

                        $entrada = $movs[$data]['entrada'] ?? 0;
                        $saida   = $movs[$data]['saida'] ?? 0;

                        $total_prod_entrada += $entrada;
                        $total_prod_saida += $saida;
                    ?>

                        <td class="text-success">
                            <?= $entrada > 0 ? number_format($entrada, 2, ',', '.') : '-' ?>
                        </td>

                        <td class="text-danger">
                            <?= $saida > 0 ? number_format($saida, 2, ',', '.') : '-' ?>
                        </td>

                    <?php endforeach; ?>

                    <td class="text-success fw-bold bg-light">
                        <?= number_format($total_prod_entrada, 2, ',', '.') ?>
                    </td>

                    <td class="text-danger fw-bold bg-light">
                        <?= number_format($total_prod_saida, 2, ',', '.') ?>
                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>
</div>

<?php endforeach; ?>

</div>

<?php include 'includes/footer.php'; ?>

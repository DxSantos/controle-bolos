<?php
require 'config.php';

$produto_id   = $_GET['produto_id'] ?? 0;
$data_inicio  = $_GET['data_inicio'] ?? null;
$data_fim     = $_GET['data_fim'] ?? null;

$whereData = '';
$params = [$produto_id];

if ($data_inicio && $data_fim) {
    $whereData = " AND DATE(data) BETWEEN ? AND ?";
    $params[] = $data_inicio;
    $params[] = $data_fim;
} elseif ($data_inicio) {
    $whereData = " AND DATE(data) >= ?";
    $params[] = $data_inicio;
} elseif ($data_fim) {
    $whereData = " AND DATE(data) <= ?";
    $params[] = $data_fim;
}

/* ENTRADAS */
$stmtE = $pdo->prepare("
    SELECT DATE(data) d, SUM(quantidade) total
    FROM controle_entrada
    WHERE produto_id = ? $whereData
    GROUP BY DATE(data)
");
$stmtE->execute($params);

/* SAÍDAS */
$stmtS = $pdo->prepare("
    SELECT DATE(data) d, SUM(quantidade) total
    FROM controle_saida
    WHERE produto_id = ? $whereData
    GROUP BY DATE(data)
");
$stmtS->execute($params);

$map = [];

foreach ($stmtE as $e) {
    $map[$e['d']]['entrada'] = (int)$e['total'];
}
foreach ($stmtS as $s) {
    $map[$s['d']]['saida'] = (int)$s['total'];
}

ksort($map);

//formatar data para o formato brasileiro
$map = array_combine(
    array_map(fn($d) => date('d/m/Y', strtotime($d)), array_keys($map)),
    $map
);

echo json_encode([
    'datas'    => array_keys($map),
    'entradas' => array_map(fn($v) => $v['entrada'] ?? 0, $map),
    'saidas'   => array_map(fn($v) => $v['saida'] ?? 0, $map),
]);

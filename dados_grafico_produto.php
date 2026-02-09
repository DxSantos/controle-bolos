<?php
require 'config.php';

$produto_id = $_GET['produto_id'] ?? 0;

/* ENTRADAS */
$entradas = $pdo->prepare("
    SELECT DATE(data) as d, SUM(quantidade) total
    FROM controle_entrada
    WHERE produto_id = ?
    GROUP BY DATE(data)
");
$entradas->execute([$produto_id]);

/* SAÍDAS */
$saidas = $pdo->prepare("
    SELECT DATE(data) as d, SUM(quantidade) total
    FROM controle_saida
    WHERE produto_id = ?
    GROUP BY DATE(data)
");
$saidas->execute([$produto_id]);

$map = [];

foreach ($entradas as $e) {
    $map[$e['d']]['entrada'] = $e['total'];
}
foreach ($saidas as $s) {
    $map[$s['d']]['saida'] = $s['total'];
}

ksort($map);

//DATAS FORMATADAS PARA EXIBIÇÃO NO GRÁFICO
$map = array_combine(
    array_map(fn($d) => date('d/m/Y', strtotime($d)), array_keys($map)),
    $map
);

$response = [
    'datas'    => array_keys($map),
    'entradas' => array_map(fn($v) => $v['entrada'] ?? 0, $map),
    'saidas'   => array_map(fn($v) => $v['saida'] ?? 0, $map),
];

header('Content-Type: application/json');
echo json_encode($response);

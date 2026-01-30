<?php
ob_start(); // 🔥 MUITO IMPORTANTE

require_once 'config.php';
require 'includes/verifica_permissao.php';
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

if (empty($_SESSION['usuario_id'])) {
    die("Acesso negado");
}

if (!verificaPermissao('relatorios')) {
    die("Sem permissão");
}

/* ================= CONFIG DOMPDF ================= */

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

/* ================= FILTROS ================= */

$tipo_id    = $_GET['tipo_id']    ?? '';
$subtipo_id = $_GET['subtipo_id'] ?? '';

/* ================= SQL ================= */

$sql = "
SELECT
    p.nome AS produto,
    t.nome AS tipo,
    s.nome AS subtipo,
    COALESCE(sp.saldo,0) AS saldo
FROM produtos p
JOIN tipos t ON t.id = p.tipo_id
LEFT JOIN subtipos s ON s.id = p.subtipo_id
LEFT JOIN saldo_produtos sp ON sp.produto_id = p.id
WHERE 1=1
";

$params = [];

if ($tipo_id !== '') {
    $sql .= " AND p.tipo_id = :tipo_id";
    $params[':tipo_id'] = $tipo_id;
}

if ($subtipo_id !== '') {
    $sql .= " AND p.subtipo_id = :subtipo_id";
    $params[':subtipo_id'] = $subtipo_id;
}

$sql .= " ORDER BY 
t.nome, 
p.nome, 
FIELD(s.nome,'subtipo_id IS NULL') ASC,
s.id
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= ORGANIZA ================= */

$relatorio = [];

foreach ($dados as $row) {

    $tipo    = $row['tipo'];
    $produto = $row['produto'];
    $subtipo = $row['subtipo'] ?: 'SEM SUBTIPO';

    $relatorio[$tipo]['produtos'][$produto][$subtipo] = $row['saldo'];
    $relatorio[$tipo]['subtipos'][$subtipo] = true;
}

/* ================= HTML ================= */

$html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
h1 { text-align:center; }
h2 { background:#0d6efd; color:#fff; padding:6px; }
table { width:100%; border-collapse: collapse; margin-bottom:20px; }
th, td { border:1px solid #000; padding: 5px 10px; text-align:center; }
th { background:#f1f1f1; }
.produto { text-align:left; font-weight:bold; }
</style>
</head>
<body>

<h1>Relatório Analítico de Saldos</h1>
';

foreach ($relatorio as $tipo => $dadosTipo) {

    $html .= "<h2>" . htmlspecialchars($tipo) . "</h2>";
    $html .= "<table><thead><tr>";
    $html .= "<th>Produto</th>";

    foreach ($dadosTipo['subtipos'] as $subtipo => $x) {
        $html .= "<th>" . htmlspecialchars($subtipo) . "</th>";
    }

    $html .= "</tr></thead><tbody>";

    foreach ($dadosTipo['produtos'] as $produto => $subtipos) {

        $html .= "<tr>";
        $html .= "<td class='produto'>" . htmlspecialchars($produto) . "</td>";

        foreach ($dadosTipo['subtipos'] as $subtipo => $x) {
            $html .= "<td>" . ($subtipos[$subtipo] ?? 0) . "</td>";
        }

        $html .= "</tr>";
    }

    $html .= "</tbody></table>";
}

$html .= '</body></html>';

/* ================= LIMPA QUALQUER SAÍDA ================= */

ob_end_clean();

/* ================= GERA PDF ================= */

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("relatorio_analitico.pdf", ["Attachment" => false]);
exit;
/* ================= FIM DO ARQUIVO ================= */
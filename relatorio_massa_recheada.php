



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <title>Document</title>
</head>
<body>
    <?php
require 'config.php';
require 'includes/verifica_permissao.php';

// include 'includes/header.php';

// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// if (empty($_SESSION['usuario_id'])) {
//     header('Location: login.php');
//     exit;
// }

// if (!verificaPermissao('analitico')) {
//     echo "<div class='alert alert-danger m-4 text-center'>🚫 Sem permissão.</div>";
//     include 'includes/footer.php';
//     exit;
// }

/*
BUSCA SOMENTE TORTA RECHEADA
*/
$sql = "
SELECT
    p.nome AS produto,
    s.nome AS subtipo,
    COALESCE(sp.saldo,0) AS saldo
FROM produtos p
JOIN tipos t ON t.id = p.tipo_id
LEFT JOIN subtipos s ON s.id = p.subtipo_id
LEFT JOIN saldo_produtos sp ON sp.produto_id = p.id
WHERE t.nome = 'TORTA RECHEADA'
ORDER BY p.nome, s.nome
";

$stmt = $pdo->query($sql);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$dados) {
    echo "<div class='alert alert-info m-4 text-center'>
            Nenhum dado encontrado para TORTA RECHEADA.
          </div>";
    include 'includes/footer.php';
    exit;
}

/*
ORGANIZA:
Produto
   Subtipo => saldo
*/
$relatorio = [];
$subtiposGerais = [];

foreach ($dados as $d) {

    $produto = $d['produto'];
    $subtipo = $d['subtipo'] ?? 'SEM SUBTIPO';

    $relatorio[$produto][$subtipo] = $d['saldo'];
    $subtiposGerais[$subtipo] = true;
}

/* ORDEM PADRÃO DOS SUBTIPOS */
$ordem = ['MINI','P','M','G','SEM SUBTIPO'];

uksort($subtiposGerais, function($a,$b) use ($ordem){
    $pa = array_search($a,$ordem);
    $pb = array_search($b,$ordem);
    if ($pa === false) return 1;
    if ($pb === false) return -1;
    return $pa <=> $pb;
});
?>

<style>
body{
    background:#f8f9fa;
}
.card-rel{
    border-radius:10px;
    box-shadow:0 3px 8px rgba(0,0,0,.1);
}
.table th{
    background:#f1f1f1;
    text-align:center;
}
.table td{
    text-align:center;
}
.produto-col{
    text-align:left;
    font-weight:600;
}
</style>

<div class="container py-4">

    <h3 class="mb-4">📊 Estoque - Torta Recheada</h3>

    <div class="card card-rel">

        <div class="card-header bg-primary text-white">
            TORTA RECHEADA
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-sm mb-0">

                    <thead>
                        <tr>
                            <th>Produto</th>

                            <?php foreach ($subtiposGerais as $sub => $x): ?>
                                <th><?= htmlspecialchars($sub) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($relatorio as $produto => $subs): ?>

                            <tr>
                                <td class="produto-col"><?= htmlspecialchars($produto) ?></td>

                                <?php foreach ($subtiposGerais as $sub => $x): ?>

                                    <td>
                                        <?= $subs[$sub] ?? 0 ?>
                                    </td>

                                <?php endforeach; ?>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>
</body>
</html>
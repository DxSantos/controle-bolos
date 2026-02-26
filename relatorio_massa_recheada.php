<?php

$tipoSelecionado = $_GET['tipo'] ?? 'TORTA RECHEADA';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <title>Relatório de Torta Recheada</title>
</head>

<body style="padding: 10px;">
    <?php
    require 'config.php';
    require 'includes/verifica_permissao.php';


    $sql = "
SELECT
    p.nome AS produto,
    s.nome AS subtipo,
    p.quantidade_minima,
    COALESCE(sp.saldo,0) AS saldo
FROM produtos p
JOIN tipos t ON t.id = p.tipo_id 
LEFT JOIN subtipos s ON s.id = p.subtipo_id
LEFT JOIN saldo_produtos sp ON sp.produto_id = p.id
WHERE t.nome = :tipo
ORDER BY p.nome, s.nome
";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':tipo' => $tipoSelecionado]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
ORGANIZA:
Produto
   Subtipo => saldo
*/
    $relatorio = [];
    $subtiposGerais = [];

    foreach ($dados as $d) {

        $produto = $d['produto'];
        $quantidade_minima = $d['quantidade_minima'];
        $subtipo = $d['subtipo'] ?? 'SEM SUBTIPO';

        $relatorio[$produto][$subtipo] = [
            'saldo' => $d['saldo'],
            'min' => $quantidade_minima
        ];
        $subtiposGerais[$subtipo] = true;
    }

    $alertas = 0; // Contador de produtos abaixo do mínimo

    foreach ($dados as $d) {

        $produto = $d['produto'];
        $subtipo = $d['subtipo'] ?? 'SEM SUBTIPO';

        $relatorio[$produto][$subtipo] = [
            'saldo' => $d['saldo'],
            'min'   => $d['quantidade_minima']
        ];

        $subtiposGerais[$subtipo] = true;

        if ($d['saldo'] < $d['quantidade_minima']) {
            $alertas++;
        }
    }


    /* ORDEM PADRÃO DOS SUBTIPOS */
    $ordem = ['MINI', 'P', 'M', 'G', 'SEM SUBTIPO'];

    uksort($subtiposGerais, function ($a, $b) use ($ordem) {
        $pa = array_search($a, $ordem);
        $pb = array_search($b, $ordem);
        if ($pa === false) return 1;
        if ($pb === false) return -1;
        return $pa <=> $pb;
    });
    ?>

    <style>
        body {
            background: #f8f9fa;
        }

        .card-rel {
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, .1);
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

        .estoque-baixo {
            background: #f8d7da !important;
            color: #842029;
            font-weight: 600;
        }
    </style>

    <div class="container py-3">

        <h3 class="mb-4">📊 Estoque - <?= htmlspecialchars($tipoSelecionado) ?></h3>

        <?php
        $tipoSelecionado = $_GET['tipo'] ?? 'TORTA RECHEADA';
        ?>

        <form method="GET" class="mb-2 p-3 bg-white rounded shadow-sm">

            <div class="row">

                <div class="d-flex justify-content-center align-items-center gap-5">
                    <label class="form-label">Tipo</label>

                    <select name="tipo" class="form-select" style="width: 30%; " onchange="this.form.submit()">

                        <option value="TORTA RECHEADA"
                            <?= $tipoSelecionado == 'TORTA RECHEADA' ? 'selected' : '' ?>>
                            Torta Recheada
                        </option>

                        <option value="MASSA PARA TORTA"
                            <?= $tipoSelecionado == 'MASSA PARA TORTA' ? 'selected' : '' ?>>
                            Massa para Torta
                        </option>

                    </select>

                    <?php if ($alertas > 0): ?>
                        <div class="alert alert-danger d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                            <div>
                                <strong>Atenção!</strong> <?= $alertas ?> produto(s) estão abaixo do estoque mínimo.
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </div>

        </form>




        <div class="card card-rel">

            <div class="card-header bg-primary text-white">
                <?= htmlspecialchars($tipoSelecionado) ?>
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

                                        <?php
                                        $valor = $subs[$sub]['saldo'] ?? 0;
                                        $min   = $subs[$sub]['min']   ?? 0;
                                        $classe = ($valor < $min) ? 'estoque-baixo' : '';
                                        ?>

                                        <td style="padding: 0px;" class="<?= $classe ?>">
                                            <span class="text-muted" style="font-size:0.8em;">
                                                (Mín: <?= $min ?>)
                                            </span><br>
                                            <div style="font-size: 1.6em;"><?= $valor ?></div>
                                            <span class="text-muted" style="font-size:0.8em;">
                                                (Ideal: <?= ($min <= 3) ? $min : $min + 2; ?>)
                                            </span>
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
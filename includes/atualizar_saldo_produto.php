<?php
function atualizarSaldoProduto(PDO $pdo, int $produto_id)
{
    // Busca inventário e data do último inventário
    $stmtBase = $pdo->prepare("
        SELECT inventario, data_ultimo_inventario
        FROM saldo_produtos
        WHERE produto_id = ?
    ");
    $stmtBase->execute([$produto_id]);
    $base = $stmtBase->fetch(PDO::FETCH_ASSOC);

    if (!$base) {
        return;
    }

    $inventario = (int)$base['inventario'];
    $dataInv = $base['data_ultimo_inventario'];

    // Soma entradas após inventário
    $stmtEntrada = $pdo->prepare("
        SELECT IFNULL(SUM(quantidade), 0)
        FROM controle_entrada
        WHERE produto_id = ?
          AND data > ?
    ");
    $stmtEntrada->execute([$produto_id, $dataInv]);
    $entradas = (int)$stmtEntrada->fetchColumn();

    // Soma saídas após inventário
    $stmtSaida = $pdo->prepare("
        SELECT IFNULL(SUM(quantidade), 0)
        FROM controle_saida
        WHERE produto_id = ?
          AND data > ?
    ");
    $stmtSaida->execute([$produto_id, $dataInv]);
    $saidas = (int)$stmtSaida->fetchColumn();

    // Calcula saldo final
    $saldo = $inventario + $entradas - $saidas;

    // Atualiza saldo_produtos
    $stmtUpdate = $pdo->prepare("
        UPDATE saldo_produtos
        SET
            entradas = ?,
            saidas = ?,
            saldo = ?
        WHERE produto_id = ?
    ");

    $stmtUpdate->execute([
        $entradas,
        $saidas,
        $saldo,
        $produto_id
    ]);
}

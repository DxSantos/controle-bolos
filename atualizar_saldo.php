<?php
require 'config.php';
date_default_timezone_set('America/Sao_Paulo');

// ----- Pega a data do último inventário -----
$stmtUltimoInventario = $pdo->query("SELECT MAX(data_inventario) as ultima_data FROM inventario_log");
$ultima_data = $stmtUltimoInventario->fetchColumn();
if (!$ultima_data) {
    // Se não houver inventário, considera data antiga
    $ultima_data = '1900-01-01 00:00:00';
}

// ----- Lista todos os produtos -----
$produtos = $pdo->query("SELECT id FROM produtos")->fetchAll(PDO::FETCH_ASSOC);

foreach ($produtos as $produto) {
    $produto_id = $produto['id'];

    // Soma entradas após o último inventário
    $stmtEntrada = $pdo->prepare("
        SELECT COALESCE(SUM(quantidade),0) 
        FROM controle_entrada 
        WHERE produto_id = ? AND CONCAT(data,' ',hora) > ?
    ");
    $stmtEntrada->execute([$produto_id, $ultima_data]);
    $soma_entradas = (int)$stmtEntrada->fetchColumn();

    // Soma saídas após o último inventário
    $stmtSaida = $pdo->prepare("
        SELECT COALESCE(SUM(quantidade),0) 
        FROM controle_saida 
        WHERE produto_id = ? AND CONCAT(data,' ',hora) > ?
    ");
    $stmtSaida->execute([$produto_id, $ultima_data]);
    $soma_saidas = (int)$stmtSaida->fetchColumn();

    // Saldo do último inventário
    $stmtSaldoInv = $pdo->prepare("
        SELECT saldo_inventario 
        FROM inventario_log 
        WHERE produto_id = ? 
        ORDER BY data_inventario DESC 
        LIMIT 1
    ");
    $stmtSaldoInv->execute([$produto_id]);
    $saldo_inventario = (int)$stmtSaldoInv->fetchColumn();

    // Calcula saldo final
    $saldo_final = $saldo_inventario + $soma_entradas - $soma_saidas;

    // Atualiza saldo_produtos
    $stmtUpdate = $pdo->prepare("
        UPDATE saldo_produtos 
        SET entradas = ?, saidas = ?, saldo = ? 
        WHERE produto_id = ?
    ");
    $stmtUpdate->execute([$soma_entradas, $soma_saidas, $saldo_final, $produto_id]);
}

// Redireciona de volta para o formulário
$retorno = $_GET['retorno'] ?? 'form_quantidade.php';
header("Location: $retorno?msg=Saldo atualizado com sucesso!");
exit;

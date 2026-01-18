<?php
require 'config.php';
require 'includes/funcoes_estoque.php';

session_start();

if (empty($_SESSION['usuario_id'])) {
    die('Usuário não autenticado');
}

$codigo = $_POST['codigo_inventario'] ?? '';
$novoInventario = $_POST['saldo_inventario'] ?? [];

if (!$codigo || empty($novoInventario)) {
    die('Dados inválidos');
}

try {
    $pdo->beginTransaction();

    // 1️⃣ Buscar último inventário do produto
    $stmtUltimoInv = $pdo->prepare("
        SELECT saldo_inventario
        FROM inventario_log
        WHERE produto_id = ?
        ORDER BY data_inventario DESC
        LIMIT 1
    ");

    // 2️⃣ Inserir inventário
    $stmtInsertInv = $pdo->prepare("
        INSERT INTO inventario_log
            (codigo_inventario, produto_id, saldo_anterior, saldo_inventario)
        VALUES
            (:codigo, :produto, :saldo_anterior, :saldo_inventario)
    ");

    // 3️⃣ UPSERT em saldo_produtos (🔥 CORREÇÃO)
    $stmtSaldo = $pdo->prepare("
        INSERT INTO saldo_produtos
            (produto_id, inventario, data_ultimo_inventario)
        VALUES
            (:produto, :inventario, NOW())
        ON DUPLICATE KEY UPDATE
            inventario = VALUES(inventario),
            data_ultimo_inventario = VALUES(data_ultimo_inventario)
    ");

    foreach ($novoInventario as $produto_id => $saldo_novo) {

    $saldo_novo = (int)$saldo_novo;

    // Busca último inventário
    $stmtUltimoInv->execute([$produto_id]);
    $saldoAnterior = $stmtUltimoInv->fetchColumn();
    if ($saldoAnterior === false) {
        $saldoAnterior = 0;
    }

    // Salva log de inventário
    $stmtInsertInv->execute([
        ':codigo'           => $codigo,
        ':produto'          => $produto_id,
        ':saldo_anterior'   => $saldoAnterior,
        ':saldo_inventario' => $saldo_novo
    ]);

    // Atualiza / insere inventário base
    $stmtSaldo->execute([
        ':produto'    => $produto_id,
        ':inventario' => $saldo_novo
    ]);

    // 🔥 ATUALIZA ENTRADAS, SAÍDAS E SALDO
    atualizarSaldoProduto($pdo, $produto_id);
}



    $pdo->commit();

    header('Location: inventario.php?sucesso=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    echo "<h3>Erro ao salvar inventário</h3>";
    echo "<p>{$e->getMessage()}</p>";
}

<?php
require 'config.php';
require 'includes/atualizar_saldo_produto.php';

session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: form_quantidade.php?msg=auth');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$tipo = $_POST['tipo_registro'] ?? '';

if (!in_array($tipo, ['entrada', 'saida'])) {
    header('Location: form_quantidade.php?msg=tipo');
    exit;
}

// Busca valores guardados
$stmt = $pdo->prepare("
    SELECT produto_id, quantidade
    FROM valores_guardados
    WHERE usuario_id = ?
      AND tipo = ?
");
$stmt->execute([$usuario_id, $tipo]);
$valores = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$valores) {
    header("Location: form_quantidade.php?msg=vazio");
    exit;
}

$tabelaDestino = ($tipo === 'entrada') ? 'controle_entrada' : 'controle_saida';

$pdo->beginTransaction();

try {
    foreach ($valores as $item) {

        $stmtInsert = $pdo->prepare("
            INSERT INTO {$tabelaDestino}
            (usuario_id, produto_id, quantidade, data)
            VALUES (?, ?, ?, NOW())
        ");
        $stmtInsert->execute([
            $usuario_id,
            $item['produto_id'],
            $item['quantidade']
        ]);

        atualizarSaldoProduto($pdo, $item['produto_id']);
    }

    // Limpa apenas o tipo salvo
    $stmtClear = $pdo->prepare("
        DELETE FROM valores_guardados
        WHERE usuario_id = ?
          AND tipo = ?
    ");
    $stmtClear->execute([$usuario_id, $tipo]);

    $pdo->commit();

    header("Location: form_quantidade.php?msg=sucesso");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: form_quantidade.php?msg=erro");
    exit;
}

<?php
require 'config.php';
session_start();

if (empty($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$tipo = $_GET['tipo'] ?? '';

if (!in_array($tipo, ['entrada', 'saida'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Tipo inválido']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT produto_id, quantidade
    FROM valores_guardados
    WHERE usuario_id = ? AND tipo = ?
");

$stmt->execute([$usuario_id, $tipo]);

$valores = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $valores[$row['produto_id']] = (int)$row['quantidade'];
}

echo json_encode([
    'status' => 'ok',
    'valores' => $valores
]);

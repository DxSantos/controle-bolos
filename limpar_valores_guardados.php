<?php
require 'config.php';
session_start();

if (empty($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$tipo = $_POST['tipo'] ?? '';

$tipo = $tipo === 'entradas' ? 'entrada' : ($tipo === 'saidas' ? 'saida' : $tipo);

if (!in_array($tipo, ['entrada', 'saida'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Tipo inválido']);
    exit;
}

$stmt = $pdo->prepare("
    DELETE FROM valores_guardados
    WHERE usuario_id = ? AND tipo = ?
");

$stmt->execute([$usuario_id, $tipo]);

echo json_encode(['status' => 'ok']);

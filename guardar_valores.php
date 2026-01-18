<?php
require 'config.php';
session_start();

header('Content-Type: application/json');

if (empty($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (
    !$data ||
    empty($data['quantidades']) ||
    empty($data['tipo']) ||
    !in_array($data['tipo'], ['entrada', 'saida'])
) {
    echo json_encode(['status' => 'erro', 'msg' => 'Dados inválidos']);
    exit;
}

$tipo = $data['tipo']; // 🔥 JÁ VEM CORRETO
$quantidades = $data['quantidades'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO valores_guardados
            (usuario_id, produto_id, quantidade, tipo, data_guardado)
        VALUES
            (:usuario_id, :produto_id, :quantidade, :tipo, NOW())
        ON DUPLICATE KEY UPDATE
            quantidade = VALUES(quantidade),
            data_guardado = NOW()
    ");

    foreach ($quantidades as $produto_id => $quantidade) {

        if ((int)$quantidade <= 0) {
            continue;
        }

        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':produto_id' => (int)$produto_id,
            ':quantidade' => (float)$quantidade,
            ':tipo'       => $tipo
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'erro', 'msg' => $e->getMessage()]);
}

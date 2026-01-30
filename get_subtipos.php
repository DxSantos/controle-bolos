<?php
require 'config.php';

$tipo_id = $_GET['tipo_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT id, nome
    FROM subtipos
    WHERE tipo_id = ? AND ativo = 1
    ORDER BY nome
");
$stmt->execute([$tipo_id]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

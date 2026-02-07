<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: estoque_seguranca.php");
    exit;
}

$minimos = $_POST['minimo'] ?? [];
$maximos = $_POST['maximo'] ?? [];

$sql = "
INSERT INTO estoque_seguranca (produto_id, minimo, maximo)
VALUES (:produto_id, :minimo, :maximo)
ON DUPLICATE KEY UPDATE
    minimo = VALUES(minimo),
    maximo = VALUES(maximo)
";

$stmt = $pdo->prepare($sql);

foreach ($minimos as $produto_id => $minimo) {

    $maximo = $maximos[$produto_id] ?? 0;

    $stmt->execute([
        ':produto_id' => $produto_id,
        ':minimo' => (int)$minimo,
        ':maximo' => (int)$maximo
    ]);
}

header("Location: estoque_seguranca.php?msg=salvo");
exit;

<?php
require 'config.php';

if (!empty($_POST['nome'])) {
    $nome = mb_strtoupper(trim($_POST['nome']), 'UTF-8'); // garante maiúsculas

    // Atualizar se tiver ID
    if (!empty($_POST['id'])) {
        $id = (int) $_POST['id'];
        $sql = $pdo->prepare("UPDATE subtipos SET nome = ? WHERE id = ?");
        $sql->execute([$nome, $id]);
    } else {
        // Inserir novo
        $sql = $pdo->prepare("INSERT INTO subtipos (nome) VALUES (?)");
        $sql->execute([$nome]);
    }
}

header("Location: subtipo_cadastro.php");
exit;
?>

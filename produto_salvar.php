<?php
session_start();
require 'config.php';

$id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nome       = mb_strtoupper(trim($_POST['nome']), 'UTF-8');
$tipo_id    = (int) $_POST['tipo_id'];
$subtipo_id = !empty($_POST['subtipo_id']) ? (int) $_POST['subtipo_id'] : null;
$quantidade_minima = (int) $_POST['quantidade_minima'];



/* ===== VALIDAÇÃO DE DUPLICIDADE (nome + tipo + subtipo) ===== */

if ($id > 0) {
    // Edição
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM produtos 
        WHERE UPPER(nome) = ?
          AND tipo_id = ?
          AND quantidade_minima = ?
          AND (
                (subtipo_id IS NULL AND ? IS NULL)
                OR subtipo_id = ?
              )
          AND id != ?
    ");
    $stmt->execute([
        $nome,
        $tipo_id,
        $quantidade_minima,
        $subtipo_id,
        $subtipo_id,
        $id
    ]);
} else {
    // Novo cadastro
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM produtos 
        WHERE UPPER(nome) = ?
          AND tipo_id = ?
          AND quantidade_minima = ?
          AND (
                (subtipo_id IS NULL AND ? IS NULL)
                OR subtipo_id = ?
              )
    ");
    $stmt->execute([
        $nome,
        $tipo_id,
        $quantidade_minima,
        $subtipo_id,
        $subtipo_id
    ]);
}

if ($stmt->fetchColumn() > 0) {
    $_SESSION['msg'] = 'Erro: Já existe um produto com este nome, tipo e subtipo!';
    $_SESSION['msg_tipo'] = 'danger';
    header("Location: produto_cadastro.php" . ($id > 0 ? "?editar=$id" : ""));
    exit;
}


/* ===== SALVAR ===== */
if ($id > 0) {
    $sql = "UPDATE produtos 
            SET nome = ?, tipo_id = ?, subtipo_id = ?, quantidade_minima = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $tipo_id, $subtipo_id, $quantidade_minima, $id]);
    $_SESSION['msg'] = "Produto atualizado com sucesso!";
} else {
    $sql = "INSERT INTO produtos (nome, tipo_id, subtipo_id, quantidade_minima)
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $tipo_id, $subtipo_id, $quantidade_minima]);
    $_SESSION['msg'] = "Produto cadastrado com sucesso!";
}

$_SESSION['msg_tipo'] = 'success';
header("Location: produto_cadastro.php");
exit;

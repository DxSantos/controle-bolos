<?php
session_start();
require 'config.php';

// Recebe dados do formulário
$id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nome = strtoupper(trim($_POST['nome']));
$tipo = (int)$_POST['tipo'];

// ----- VALIDAÇÃO DE DUPLICIDADE -----
if ($id > 0) {
    // Edição: exclui o próprio registro
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE UPPER(nome) = ? AND id != ?");
    $stmt->execute([$nome, $id]);
} else {
    // Novo cadastro
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE UPPER(nome) = ?");
    $stmt->execute([$nome]);
}

if ($stmt->fetchColumn() > 0) {
    $_SESSION['msg'] = "Erro: Já existe um produto com este nome!";
    $_SESSION['msg_tipo'] = 'danger';
    header("Location: produto_cadastro.php" . ($id > 0 ? "?editar=$id" : ""));
    exit;
}

// ----- SALVAR OU ATUALIZAR -----
if ($id > 0) {
    // Atualizar
    $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, tipo = ? WHERE id = ?");
    $stmt->execute([$nome, $tipo, $id]);
    $_SESSION['msg'] = "Produto atualizado com sucesso!";
} else {
    // Inserir novo
    $stmt = $pdo->prepare("INSERT INTO produtos (nome, tipo) VALUES (?, ?)");
    $stmt->execute([$nome, $tipo]);
    $_SESSION['msg'] = "Produto cadastrado com sucesso!";
}

$_SESSION['msg_tipo'] = 'success';
header("Location: produto_cadastro.php");
exit;

<?php
require 'config.php';
require 'includes/verifica_permissao.php';
include 'includes/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Bloqueia se o usuário não tiver permissão "produtos"
if (!verificaPermissao('produtos')) {
    echo "<div class='alert alert-danger m-4 text-center'>
            🚫 Você não tem permissão para acessar esta página.
          </div>";
    include 'includes/footer.php';
    exit;
}

// ----- MENSAGEM DE RETORNO -----
$msg = '';
if (!empty($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    $msg_tipo = $_SESSION['msg_tipo'] ?? 'info';
    unset($_SESSION['msg'], $_SESSION['msg_tipo']);
}

// ----- EDITAR REGISTRO -----
$edit = false;
$produto = ['id' => '', 'nome' => '', 'tipo' => ''];

if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        $edit = true;
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// ----- EXCLUIR REGISTRO -----
if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];
    $pdo->prepare("DELETE FROM produtos WHERE id = ?")->execute([$id]);
    $_SESSION['msg'] = 'Produto excluído com sucesso!';
    $_SESSION['msg_tipo'] = 'success';
    header("Location: produto_cadastro.php");
    exit;
}

// ----- LISTAR TIPOS -----
$tipos = $pdo->query("SELECT * FROM tipos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// ----- PESQUISA -----
$busca = isset($_GET['busca']) ? strtoupper(trim($_GET['busca'])) : '';

// ----- LISTAGEM -----
$sql = "SELECT p.id, p.nome, t.nome AS tipo_nome 
        FROM produtos p 
        LEFT JOIN tipos t ON p.tipo = t.id
        WHERE UPPER(p.nome) LIKE :busca OR UPPER(t.nome) LIKE :busca
        ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['busca' => "%$busca%"]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            overflow: hidden; /* sem scroll externo */
            background-color: #f8f9fa;
        }
        .main-container {
            display: flex;
            flex-direction: row;
            height: 100%;
            padding: 30px;
            gap: 30px;
            align-items: flex-start;
        }
        /* Formulário */
        form {
            flex: 1;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: fit-content;
        }
        .form-control, .form-select {
            height: 45px;
            text-transform: uppercase;
        }

        /* Listagem */
        .listagem {
            flex: 2;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 100%; /* ocupa toda a altura da tela */
        }

        /* Barra de pesquisa fixa no topo */
        .search-bar {
            flex: 0 0 auto; /* altura fixa, não cresce */
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }

        .search-bar .form-control,
        .search-bar .btn {
            height: 45px; /* mesma altura do input do formulário */
            text-transform: uppercase;
        }

        .search-bar .btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Tabela ocupa o restante da altura */
        .scroll-lista {
            flex: 1 1 auto; /* ocupa o resto do espaço */
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .scroll-lista::-webkit-scrollbar {
            width: 8px;
        }
        .scroll-lista::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }
        .scroll-lista::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        /* Tabela estilizada */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f1f3f5;
        }
        .table-hover tbody tr:hover {
            background-color: #d9edf7;
            transition: background-color 0.3s;
        }
        .table th, .table td {
            vertical-align: middle;
            padding: 12px 15px;
        }

        /* Responsivo: formulário acima em telas pequenas */
        @media (max-width: 991px) {
            .main-container {
                flex-direction: column;
                padding: 15px;
            }
            .listagem {
                margin-top: 20px;
                height: auto; /* altura automática para mobile */
            }
        }
    </style>
</head>
<body>
<div class="main-container">

    <!-- ===== FORMULÁRIO ===== -->
    <form method="POST" action="produto_salvar.php">
        <h4 class="mb-3"><?= $edit ? 'Editar Produto' : 'Cadastro de Produto' ?></h4>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_tipo ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <input type="hidden" name="id" value="<?= $produto['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Nome do Produto:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>" required class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo:</label>
            <select name="tipo" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($t['id'] == $produto['tipo']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success"><?= $edit ? 'Atualizar' : 'Salvar' ?></button>
            <?php if ($edit): ?>
                <a href="produto_cadastro.php" class="btn btn-secondary">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ===== LISTAGEM ===== -->
    <div class="listagem">
        <h4>Produtos Cadastrados</h4>

        <!-- Barra de Pesquisa -->
        <form method="GET" class="search-bar" role="search">
            <input type="text" name="busca" class="form-control" placeholder="Pesquisar por nome ou tipo..." value="<?= htmlspecialchars($busca) ?>">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a href="produto_cadastro.php" class="btn btn-secondary">Limpar Busca</a>
        </form>

        <!-- Lista com Scroll -->
        <div class="scroll-lista">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th style="width:150px">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (count($produtos) > 0): ?>
                    <?php foreach ($produtos as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nome']) ?></td>
                            <td><?= htmlspecialchars($row['tipo_nome']) ?></td>
                            <td>
                                <a href="?editar=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                                <a href="?excluir=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Deseja realmente excluir este produto?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Nenhum produto encontrado</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

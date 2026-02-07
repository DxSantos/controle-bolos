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

if (!verificaPermissao('produtos')) {
    echo "<div class='alert alert-danger m-4 text-center'>🚫 Sem permissão.</div>";
    include 'includes/footer.php';
    exit;
}

/* ===== MENSAGEM ===== */
$msg = $_SESSION['msg'] ?? '';
$msg_tipo = $_SESSION['msg_tipo'] ?? 'info';
unset($_SESSION['msg'], $_SESSION['msg_tipo']);

/* ===== EDITAR ===== */
$edit = false;
$registro = ['id' => '', 'produto_id' => '', 'minimo' => '', 'maximo' => ''];

if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM estoque_seguranca WHERE id=?");
    $stmt->execute([$_GET['editar']]);
    if ($stmt->rowCount()) {
        $edit = true;
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/* ===== EXCLUIR ===== */
if (isset($_GET['excluir'])) {
    $pdo->prepare("DELETE FROM estoque_seguranca WHERE id=?")
        ->execute([$_GET['excluir']]);
    $_SESSION['msg'] = "Registro excluído!";
    $_SESSION['msg_tipo'] = "success";
    header("Location: estoque_seguranca_cadastro.php");
    exit;
}

// ----- LISTAR PRODUTOS COM SUBTIPO -----
$sql = "
SELECT 
    p.id,
    p.nome AS produto_nome,
    s.nome AS subtipo_nome
FROM produtos p
LEFT JOIN subtipos s ON p.subtipo_id = s.id
ORDER BY p.nome, s.nome
";

$produtos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);


/* ===== PRODUTOS ===== */
$$produtos = $pdo->query("
    SELECT 
        p.id,
        CONCAT(
            p.nome,
            IF(s.nome IS NOT NULL, CONCAT(' - ', s.nome), '')
        ) AS nome_completo
    FROM produtos p
    LEFT JOIN subtipos s ON p.subtipo_id = s.id
    ORDER BY p.nome
")->fetchAll(PDO::FETCH_ASSOC);


/* ===== BUSCA ===== */
$busca = $_GET['busca'] ?? '';

$stmt = $pdo->prepare("
SELECT es.*, p.nome AS produto, t.nome AS tipo
FROM estoque_seguranca es
JOIN produtos p ON p.id=es.produto_id
LEFT JOIN tipos t ON p.tipo_id=t.id
WHERE UPPER(p.nome) LIKE :b
ORDER BY p.nome
");
$stmt->execute(['b' => "%" . strtoupper($busca) . "%"]);
$lista = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Estoque de Segurança</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
            background: #f8f9fa;
        }

        .main-container {
            display: flex;
            height: 100%;
            gap: 30px;
            padding: 30px
        }

        form {
            flex: 1;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1)
        }

        .listagem {
            flex: 2;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
            display: flex;
            flex-direction: column
        }

        .scroll-lista {
            flex: 1;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px
        }
    </style>
</head>

<body>

    <div class="main-container">

        <!-- ===== FORMULÁRIO ===== -->
        <form method="POST" action="estoque_seguranca_salvar.php">

            <h4><?= $edit ? 'Editar' : 'Cadastro' ?> Estoque de Segurança</h4>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msg_tipo ?>"><?= $msg ?></div>
            <?php endif; ?>

            <input type="hidden" name="id" value="<?= $registro['id'] ?>">

            <div class="mb-3">
    <label class="form-label">Produto:</label>

    <select name="produto_id" class="form-select" required>
        <option value="">Selecione o produto</option>

        <?php foreach ($produtos as $p): ?>

            <?php
                $label = $p['produto_nome'];

                if (!empty($p['subtipo_nome'])) {
                    $label .= " - " . $p['subtipo_nome'];
                }
            ?>

            <option value="<?= $p['id'] ?>">
                <?= htmlspecialchars($label) ?>
            </option>

        <?php endforeach; ?>
    </select>
</div>


            <div class="mb-3">
                <label>Estoque Mínimo</label>
                <input type="number" name="minimo" class="form-control"
                    value="<?= $registro['minimo'] ?>" required>
            </div>

            <div class="mb-3">
                <label>Estoque Máximo</label>
                <input type="number" name="maximo" class="form-control"
                    value="<?= $registro['maximo'] ?>" required>
            </div>

            <button class="btn btn-success">
                <?= $edit ? 'Atualizar' : 'Salvar' ?>
            </button>

            <?php if ($edit): ?>
                <a href="estoque_seguranca_cadastro.php" class="btn btn-secondary">Cancelar</a>
            <?php endif; ?>

        </form>

        <!-- ===== LISTAGEM ===== -->
        <div class="listagem">

            <h4>Produtos com Estoque de Segurança</h4>

            <form class="d-flex gap-2 mb-2">
                <input type="text" name="busca" class="form-control"
                    placeholder="Buscar produto..."
                    value="<?= htmlspecialchars($busca) ?>">
                <button class="btn btn-primary">Buscar</button>
                <a href="estoque_seguranca_cadastro.php" class="btn btn-secondary">Limpar</a>
            </form>

            <div class="scroll-lista">

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Tipo</th>
                            <th>Mínimo</th>
                            <th>Máximo</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($lista as $l): ?>
                            <tr>
                                <td><?= $l['produto'] ?></td>
                                <td><?= $l['tipo'] ?></td>
                                <td><?= $l['minimo'] ?></td>
                                <td><?= $l['maximo'] ?></td>
                                <td>
                                    <a class="btn btn-warning btn-sm"
                                        href="?editar=<?= $l['id'] ?>">✏️</a>

                                    <a class="btn btn-danger btn-sm"
                                        href="?excluir=<?= $l['id'] ?>"
                                        onclick="return confirm('Excluir?')">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$lista): ?>
                            <tr>
                                <td colspan="5" class="text-center">Nenhum registro</td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
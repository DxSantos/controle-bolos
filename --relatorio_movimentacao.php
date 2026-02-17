<?php
require 'config.php';
require 'includes/verifica_permissao.php';
include 'includes/header.php';

date_default_timezone_set('America/Sao_Paulo');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔐 Login
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// 🔐 Permissão
if (!verificaPermissao('inventario')) {
    echo "<div class='alert alert-danger m-4 text-center'>
            🚫 Você não tem permissão para acessar esta página.
          </div>";
    include 'includes/footer.php';
    exit;
}

/* =======================
   FILTROS
======================= */
$tipo_mov   = $_GET['tipo_mov'] ?? '';
$produto_id = $_GET['produto_id'] ?? '';
$data_ini   = $_GET['data_ini'] ?? '';
$data_fim   = $_GET['data_fim'] ?? '';

// Produtos
$produtos = $pdo->query("
    SELECT id, nome 
    FROM produtos 
    ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);

/* =======================
   CONSULTA UNIFICADA
======================= */
$sql = "
    SELECT 
        e.data AS data_mov,
        'ENTRADA' AS tipo_mov,
        p.nome AS produto,
        e.quantidade
    FROM controle_entrada e
    JOIN produtos p ON p.id = e.produto_id

    UNION ALL

    SELECT 
        s.data AS data_mov,
        'SAÍDA' AS tipo_mov,
        p.nome AS produto,
        s.quantidade
    FROM controle_saida s
    JOIN produtos p ON p.id = s.produto_id
";

$condicoes = [];
$params = [];

// Filtros externos
if ($tipo_mov) {
    $condicoes[] = "tipo_mov = :tipo_mov";
    $params[':tipo_mov'] = strtoupper($tipo_mov);
}

if ($produto_id) {
    $condicoes[] = "produto_id = :produto_id";
    $params[':produto_id'] = $produto_id;
}

if ($data_ini) {
    $condicoes[] = "DATE(data_mov) >= :data_ini";
    $params[':data_ini'] = $data_ini;
}

if ($data_fim) {
    $condicoes[] = "DATE(data_mov) <= :data_fim";
    $params[':data_fim'] = $data_fim;
}

// Aplica filtros
if ($condicoes) {
    $sql = "
        SELECT * FROM (
            $sql
        ) AS movimentos
        WHERE " . implode(' AND ', $condicoes);
}

$sql .= " ORDER BY data_mov DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <h3 class="mb-4">📊 Relatório de Movimentação</h3>

    <!-- 🔎 FILTROS -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3">
            <label>Tipo</label>
            <select name="tipo_mov" class="form-control">
                <option value="">Todos</option>
                <option value="entrada" <?= $tipo_mov == 'entrada' ? 'selected' : '' ?>>Entrada</option>
                <option value="saida" <?= $tipo_mov == 'saida' ? 'selected' : '' ?>>Saída</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Produto</label>
            <select name="produto_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($produtos as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $produto_id == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label>Data inicial</label>
            <input type="date" name="data_ini" value="<?= $data_ini ?>" class="form-control">
        </div>

        <div class="col-md-2">
            <label>Data final</label>
            <input type="date" name="data_fim" value="<?= $data_fim ?>" class="form-control">
        </div>

        <div class="col-md-2 d-flex align-items-end gap-2">
            <button class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <!-- 📋 TABELA -->
     
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Produto</th>
                    <th class="text-end">Quantidade</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($movimentos): ?>
                    <?php foreach ($movimentos as $m): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($m['data_mov'])) ?></td>
                            <td>
                                <span class="badge <?= $m['tipo_mov'] == 'ENTRADA' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $m['tipo_mov'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($m['produto']) ?></td>
                            <td class="text-end fw-bold"><?= number_format($m['quantidade'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Nenhuma movimentação encontrada
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

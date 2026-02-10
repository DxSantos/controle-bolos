<?php
require 'config.php';
include 'includes/header.php';

/* TIPOS */
$tipos = $pdo->query("
    SELECT DISTINCT t.id, t.nome
    FROM tipos t
    JOIN produtos p ON p.tipo_id = t.id
    ORDER BY t.nome
")->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container py-4">

    <h3 class="mb-3" id="tgrafi">📦 Entradas x Saídas</h3>

    <form id="filtroDatas" class="row g-2 mb-4">

    <div class="col-md-3">
        <label class="form-label">Data inicial</label>
        <input type="date" id="data_inicio" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">Data final</label>
        <input type="date" id="data_fim" class="form-control">
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button type="button" class="btn btn-secondary w-100"
            onclick="limparDatas()">Limpar datas</button>
    </div>

</form>


    <div class="row g-3">

        <?php foreach ($tipos as $tipo): ?>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <?= htmlspecialchars($tipo['nome']) ?>
                    </div>

                    <ul class="list-group list-group-flush">

                        <?php
                        $produtos = $pdo->prepare("
                            SELECT 
                                p.id,
                                CONCAT(
                                    p.nome,
                                    IF(s.nome IS NOT NULL, CONCAT(' - ', s.nome), '')
                                ) AS nome
                            FROM produtos p
                            LEFT JOIN subtipos s ON s.id = p.subtipo_id
                            WHERE p.tipo_id = ?
                            ORDER BY p.nome
                        ");
                        $produtos->execute([$tipo['id']]);
                        ?>

                        <?php foreach ($produtos as $p): ?>
                            <li class="list-group-item produto-item"
                                data-id="<?= $p['id'] ?>"
                                data-nome="<?= htmlspecialchars($p['nome']) ?>"
                                style="cursor:pointer">
                                📊 <?= htmlspecialchars($p['nome']) ?>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                </div>
            </div>

        <?php endforeach; ?>

    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalGrafico" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloProduto"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <canvas id="graficoProduto" height="150" ></canvas>
            </div>

        </div>
    </div>
</div>

<script>
let chart = null;

function limparDatas() {
    document.getElementById('data_inicio').value = '';
    document.getElementById('data_fim').value = '';
}

document.querySelectorAll('.produto-item').forEach(item => {
    item.addEventListener('click', () => {

        const produtoId = item.dataset.id;
        const nome = item.dataset.nome;

        const dataInicio = document.getElementById('data_inicio').value;
        const dataFim = document.getElementById('data_fim').value;

        document.getElementById('tituloProduto').innerText = nome;

        let url = `dados_grafico_produto.php?produto_id=${produtoId}`;

        if (dataInicio) url += `&data_inicio=${dataInicio}`;
        if (dataFim) url += `&data_fim=${dataFim}`;

        fetch(url)
            .then(res => res.json())
            .then(dados => {

                const ctx = document.getElementById('graficoProduto').getContext('2d');

                if (chart) chart.destroy();

                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dados.datas,
                        datasets: [
                            {
                                label: 'Entradas',
                                data: dados.entradas,
                                borderColor: 'blue',
                                tension: 0.3
                            },
                            {
                                label: 'Saídas',
                                data: dados.saidas,
                                borderColor: 'red',
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });

                const modal = new bootstrap.Modal(
                    document.getElementById('modalGrafico')
                );
                modal.show();
            });
    });
});
</script>


<?php include 'includes/footer.php'; ?>
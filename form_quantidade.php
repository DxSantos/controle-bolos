<?php
date_default_timezone_set('America/Sao_Paulo');
require 'config.php';
require 'includes/verifica_permissao.php';
include 'includes/header.php';

// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redireciona se não estiver logado
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Bloqueia se o usuário não tiver permissão "movimentacao"
if (!verificaPermissao('movimentacao')) {
    echo "<div class='alert alert-danger m-4 text-center'>
            🚫 Você não tem permissão para acessar esta página.
          </div>";
    include 'includes/footer.php';
    exit;
}

// ----- PERMISSÕES DE USUÁRIO BOTÕES -----
$canSaida = verificaPermissao('saidas'); // 🔹 checa se o usuário pode mexer em saídas
$canEntrada = verificaPermissao('entradas'); // 🔹 checa se o usuário pode mexer em entradas
$guardaValores = verificaPermissao('guardar_valores'); // 🔹 checa se o usuário pode guardar valores temporariamente
$salvarBanco = verificaPermissao('salvar_banco'); // 🔹 checa se o usuário pode salvar no banco


// ----- LISTAR TIPOS E PRODUTOS -----
$tipos = $pdo->query("SELECT * FROM tipos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Agrupa produtos por tipo
$produtos_por_tipo = [];
foreach ($tipos as $tipo) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE tipo_id = ? ORDER BY nome");
    $stmt->execute([$tipo['id']]);
    $produtos_por_tipo[$tipo['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}



?>



<body>

    <div class="container py-4">



        <form id="form_quantidade" method="POST" action="salvar_banco.php">


            <h3 class="mb-4">Controle de Estoque - Entrada / Saída</h3>

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">

                <div id="modo-container" class="btn-group" role="group" aria-label="Modos">
                    <?php if ($canSaida): ?>
                        <button type="button" class="btn btn-outline-danger modo-btn active" data-modo="saidas">Saídas</button>
                    <?php endif; ?>

                    <?php if ($canEntrada): ?>
                        <button type="button" class="btn btn-outline-primary modo-btn" data-modo="entradas">Entradas</button>
                    <?php endif; ?>

                </div>

                <div class="mt-3 d-flex gap-2">
                    <?php if ($guardaValores): ?>
                        <button type="button" id="btn-guardar" class="btn btn-warning">Guardar Valores</button>
                    <?php endif; ?>

                    <?php if ($salvarBanco): ?>
                        <button type="submit" class="btn btn-success">Salvar no Banco</button>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Alerta Bootstrap -->
            <div id="alerta-salvo" class="alert alert-success alert-dismissible fade" role="alert" style="display:none;">
                <strong>Sucesso!</strong> Valores salvos no banco.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Botão valores guardados -->
            <div class="mb-3">
                <button type="button" id="btn-ver-valores" class="btn btn-info" style="display:none;">
                    Valores Guardados
                </button>
            </div>

            <input type="hidden" name="tipo_registro" id="tipo_registro" value="saida">

            <div class="row">
                <?php foreach ($tipos as $tipo): ?>
                    <div class="col">
                        <div class="produto-card card">
                            <div class="card-header text-white card-header-tipo" style="background-color: #dc3545;">
                                <?= htmlspecialchars($tipo['nome']) ?>
                            </div>
                            <div class="card-body">

                                <?php
                                $produtos = $produtos_por_tipo[$tipo['id']] ?? [];

                                $dados = [];
$subtipos = [];

// Monta estrutura
foreach ($produtos as $p) {

    $subtipo = 'SEM SUBTIPO';

    if (!empty($p['subtipo_id'])) {
        $st = $pdo->prepare("SELECT nome FROM subtipos WHERE id=?");
        $st->execute([$p['subtipo_id']]);
        $subtipo = $st->fetchColumn() ?: 'SEM SUBTIPO';
    }

    // Guarda subtipo existente
    $subtipos[$subtipo] = true;

    // Agrupa produto
    $dados[$p['nome']][$subtipo] = [
        'id' => $p['id']
    ];
}

// Ordem desejada
$ordemPadrao = ['MINI', 'P', 'M', 'G', 'SEM SUBTIPO'];

uksort($subtipos, function ($a, $b) use ($ordemPadrao) {

    $pa = array_search($a, $ordemPadrao);
    $pb = array_search($b, $ordemPadrao);

    if ($pa === false) return 1;
    if ($pb === false) return -1;

    return $pa <=> $pb;
});


                                if ($dados):

                                    // Coleta SOMENTE subtipos que existem neste TIPO
                                    $subtipos = [];

                                    foreach ($produtos as $p) {

                                        if (!empty($p['subtipo_id'])) {

                                            $st = $pdo->prepare("SELECT nome FROM subtipos WHERE id=?");
                                            $st->execute([$p['subtipo_id']]);
                                            $nomeSub = $st->fetchColumn();

                                            if ($nomeSub) {
                                                $subtipos[$nomeSub] = true;
                                            }
                                        }
                                    }

                                    // Se existir produto sem subtipo
                                    foreach ($produtos as $p) {
                                        if (empty($p['subtipo_id'])) {
                                            $subtipos['SEM SUBTIPO'] = true;
                                            break;
                                        }
                                    }


                                    // ORDEM PERSONALIZADA
                                    $ordemPadrao = ['MINI', 'P', 'M', 'G'];

                                    uksort($subtipos, function ($a, $b) use ($ordemPadrao) {

                                        $pa = array_search($a, $ordemPadrao);
                                        $pb = array_search($b, $ordemPadrao);

                                        if ($pa === false) return 1;
                                        if ($pb === false) return -1;

                                        return $pa <=> $pb;
                                    });

                                ?>

                                    <table class="table table-bordered table-sm">

                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <?php foreach ($subtipos as $s => $x): ?>
                                                    <th class="text-center"><?= htmlspecialchars($s) ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>

                                        <tbody>

<?php foreach ($dados as $produtoNome => $subs): ?>
<tr>

    <td><strong><?= htmlspecialchars($produtoNome) ?></strong></td>

    <?php foreach ($subtipos as $subtipo => $x): ?>
        <td class="text-center">

            <?php if (isset($subs[$subtipo])):

                $pid = $subs[$subtipo]['id'];

                $stSaldo = $pdo->prepare(
                    "SELECT saldo FROM saldo_produtos WHERE produto_id=?"
                );
                $stSaldo->execute([$pid]);
                $saldo = $stSaldo->fetchColumn() ?? 0;
            ?>

                <small class="text-muted">Saldo: <?= $saldo ?></small>

                <div class="quantidade-control">

                    <button type="button" class="btn btn-outline-secondary btn-minus">-</button>

                    <input type="number"
                           name="quantidade[<?= $pid ?>]"
                           value="0"
                           min="0">

                    <button type="button" class="btn btn-outline-secondary btn-plus">+</button>

                </div>

            <?php else: ?>
                <!-- célula vazia -->
            <?php endif; ?>

        </td>
    <?php endforeach; ?>

</tr>
<?php endforeach; ?>

</tbody>


                                    </table>

                                <?php else: ?>
                                    <p class="text-muted">Nenhum produto cadastrado neste tipo.</p>
                                <?php endif; ?>

                            </div>


                        </div>
                    </div>
                <?php endforeach; ?>
            </div>



        </form>



    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {

            const form = document.getElementById('form_quantidade');
            const btnGuardar = document.getElementById('btn-guardar');
            const btnVerValores = document.getElementById('btn-ver-valores');
            const resumoBody = document.getElementById('resumo-body');
            const tipoRegistroInput = document.getElementById('tipo_registro');
            const cardHeaders = document.querySelectorAll('.card-header-tipo');
            const alertaSalvo = document.getElementById('alerta-salvo');

            const modalResumo = new bootstrap.Modal(
                document.getElementById('modalResumo')
            );

            let tipoAtual = 'saida';

            // ================= CORES =================
            function atualizarCores() {
                let cor = tipoAtual === 'entrada' ? '#0d6efd' : '#dc3545';
                cardHeaders.forEach(h => h.style.backgroundColor = cor);
            }

            atualizarCores();
            await carregarValoresGuardados(tipoAtual);

            // ================= MODOS =================
            document.querySelectorAll('.modo-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    document.querySelectorAll('.modo-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    tipoAtual = btn.dataset.modo === 'entradas' ? 'entrada' : 'saida';
                    tipoRegistroInput.value = tipoAtual;

                    atualizarCores();
                    await carregarValoresGuardados(tipoAtual);
                });
            });

            // ================= + / - =================
            document.querySelectorAll('.btn-plus').forEach(btn => {
                btn.onclick = () => {
                    const input = btn.previousElementSibling;
                    input.value = parseInt(input.value || 0) + 1;
                };
            });

            document.querySelectorAll('.btn-minus').forEach(btn => {
                btn.onclick = () => {
                    const input = btn.nextElementSibling;
                    input.value = Math.max(0, parseInt(input.value || 0) - 1);
                };
            });

            // ================= COLETAR =================
            function coletarValoresDaTela() {
                const valores = {};
                document.querySelectorAll('input[name^="quantidade"]').forEach(input => {
                    const v = parseInt(input.value || 0);
                    if (v > 0) {
                        const id = input.name.match(/\[(\d+)\]/)[1];
                        valores[id] = v;
                    }
                });
                return valores;
            }

            // ================= GUARDAR =================
            btnGuardar.addEventListener('click', async () => {
                const valores = coletarValoresDaTela();
                if (!Object.keys(valores).length) return;

                const res = await fetch('guardar_valores.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        quantidades: valores,
                        tipo: tipoAtual
                    })
                });

                const json = await res.json();
                if (json.status !== 'ok') {
                    alert(json.msg);
                    return;
                }

                montarResumo(valores);
                btnVerValores.style.display = 'inline-block';
                modalResumo.show();
            });

            // ================= RESUMO =================
            function montarResumo(valores) {
    resumoBody.innerHTML = '';

    for (const id in valores) {

        const input = document.querySelector(`input[name="quantidade[${id}]"]`);
        if (!input) continue;

        const td = input.closest('td');
        const tr = input.closest('tr');

        // Nome do produto (primeira coluna da linha)
        const nomeProduto = tr.querySelector('td').innerText.trim();

        // Descobrir índice da coluna
        const colIndex = Array.from(tr.children).indexOf(td);

        // Pegar nome do subtipo no THEAD
        const tabela = tr.closest('table');
        const subtipo = tabela.querySelectorAll('thead th')[colIndex].innerText.trim();

        resumoBody.innerHTML += `
            <tr>
                <td>${nomeProduto} - <strong>${subtipo}</strong></td>
                <td>${valores[id]}</td>
            </tr>
        `;
    }
}



            // ================= CARREGAR =================
            async function carregarValoresGuardados(tipo) {
                const res = await fetch(`get_valores_guardados.php?tipo=${tipo}`);
                const json = await res.json();

                resumoBody.innerHTML = '';
                btnVerValores.style.display = 'none';

                document.querySelectorAll('input[name^="quantidade"]').forEach(i => i.value = 0);

                if (json.status === 'ok' && Object.keys(json.valores).length > 0) {
                    btnVerValores.style.display = 'inline-block';
                    montarResumo(json.valores);

                    for (const id in json.valores) {
                        const input = document.querySelector(`input[name="quantidade[${id}]"]`);
                        if (input) input.value = json.valores[id];
                    }
                }
            }

            // ================= BOTÃO VALORES GUARDADOS =================
            btnVerValores.addEventListener('click', () => {
                modalResumo.show();
            });

            // ================= SUBMIT =================
            form.addEventListener('submit', () => {
                alertaSalvo.style.display = 'block';
                alertaSalvo.classList.add('show');
            });

        });
    </script>


    <!-- Modal Resumo -->

    <div class="modal fade" id="modalResumo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalResumoTitulo">
                        Resumo de Valores Guardados
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody id="resumo-body"></tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>

            </div>
        </div>
    </div>



</body>

<?php include 'includes/footer.php'; ?>
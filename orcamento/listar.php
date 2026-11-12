<?php
session_start();
require_once '../config/conexao.php';


if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem = "<div class='sucesso'>" . $_SESSION['mensagem_sucesso'] . "</div>";
    unset($_SESSION['mensagem_sucesso']);
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$mensagem = "";

// 1. PROCESSAR A APROVAÇÃO OU RECUSA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] == 'funcionario') {
    $orcamento_id = $_POST['orcamento_id'];
    $nova_acao = $_POST['acao'];

    $stmt = $conn->prepare("UPDATE orcamentos SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $nova_acao, $orcamento_id);

    if ($stmt->execute()) {
        $mensagem = "<div class='sucesso'>Orçamento #$orcamento_id atualizado para: <strong>" . strtoupper($nova_acao) . "</strong>!</div>";
    } else {
        $mensagem = "<div class='erro'>Erro ao atualizar o orçamento.</div>";
    }
    $stmt->close();
}

// Lógica do Filtro
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$where_extra = "";

if ($filtro != '') {
    // Filtra pelo modelo do carro, placa ou nome do serviço
    $filtro_seguro = $conn->real_escape_string($filtro);
    $where_extra = " AND (v.modelo LIKE '%$filtro_seguro%' OR v.placa LIKE '%$filtro_seguro%' OR s.nome LIKE '%$filtro_seguro%')";
}

// 2. BUSCAR ORÇAMENTOS (Com GROUP_CONCAT para juntar os serviços)
$id_usuario = $_SESSION['usuario_id'];
$sql = "SELECT o.id, o.valor_total, o.status, o.data_criacao, 
               v.modelo, v.placa, 
               GROUP_CONCAT(s.nome SEPARATOR ', ') AS nomes_servicos 
        FROM orcamentos o
        JOIN veiculos v ON o.veiculo_id = v.id
        LEFT JOIN orcamento_itens oi ON o.id = oi.orcamento_id
        LEFT JOIN servicos s ON oi.servico_id = s.id
        WHERE v.usuario_id = $id_usuario $where_extra
        GROUP BY o.id
        ORDER BY o.data_criacao DESC";

$resultado = $conn->query($sql);

include_once '../includes/header.php';
?>

<h2>Meus Orçamentos</h2>
<?php echo $mensagem; ?>

<!-- Barra de Ferramentas (Ações e Filtro) -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
    
    <!-- Lado Esquerdo: Voltar e Novo -->
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="../dashboard.php" class="btn" style="background-color: #6c757d; padding: 10px 15px; font-size: 14px; text-decoration: none; display: inline-block; margin: 0;">← Voltar ao Menu</a>
        <a href="cadastrar.php" class="btn" style="margin: 0;">+ Novo Orçamento</a>
    </div>

    <!-- Lado Direito: Formulário de Filtro -->
    <form action="listar.php" method="GET" style="display: flex; gap: 0; margin: 0; padding: 0; box-shadow: none; background: transparent; max-width: 100%;">
        <input type="text" name="filtro" placeholder="Filtrar por veículo, placa ou serviço..." value="<?= htmlspecialchars($filtro ?? '') ?>" style="margin: 0; border-radius: 6px 0 0 6px; border-right: none; width: 300px; padding: 10px 15px;">
        <button type="submit" style="background-color: #F28C28; margin: 0; border-radius: 0 6px 6px 0; padding: 10px 20px;">Filtrar</button>
        <?php if(isset($filtro) && $filtro != ''): ?>
            <a href="listar.php" style="color: #dc3545; text-decoration: none; font-weight: bold; margin-left: 15px; display: flex; align-items: center;">Limpar</a>
        <?php endif; ?>
    </form>
</div>

<?php echo $mensagem; ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Veículo</th>
                <th>Serviços</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['modelo']) ?> (<?= htmlspecialchars($row['placa']) ?>)</td>
                        <td><?= htmlspecialchars($row['nomes_servicos'] ?? 'Nenhum serviço') ?></td>
                        <td>R$ <?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?></td>

                        <td>
                            <?php if ($row['status'] == 'pendente'): ?>
                                <span class="badge pendente">PENDENTE</span>
                            <?php elseif ($row['status'] == 'aprovado'): ?>
                                <span class="badge aprovado">APROVADO</span>
                            <?php elseif ($row['status'] == 'em_andamento'): ?>
                                <span class="badge" style="background:#cce5ff; color:#004085;">EM ANDAMENTO</span>
                            <?php elseif ($row['status'] == 'concluido'): ?>
                                <span class="badge" style="background:#155724; color:white;">CONCLUÍDO</span>
                            <?php else: ?>
                                <span class="badge recusado">RECUSADO</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($_SESSION['tipo_usuario'] == 'funcionario'): ?>
                                <!-- Se for FUNCIONÁRIO: Mostra os botões de ação -->
                                <?php if ($row['status'] == 'pendente'): ?>
                                    <form action="listar.php" method="POST" style="display: inline-block;">
                                        <input type="hidden" name="orcamento_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="acao" value="aprovado">
                                        <button type="submit" class="btn" style="background:#28a745; padding:5px 10px; font-size:12px;">Aprovar</button>
                                    </form>
                                    <form action="listar.php" method="POST" style="display: inline-block;">
                                        <input type="hidden" name="orcamento_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="acao" value="recusado">
                                        <button type="submit" class="btn" style="background:#dc3545; padding:5px 10px; font-size:12px;">Recusar</button>
                                    </form>
                                <?php else: ?>
                                    <em style="color: #888;">Finalizado</em>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Se for CLIENTE: Apenas um traço -->
                                <em style="color: #888;">-</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Nenhum orçamento cadastrado ainda.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once '../includes/footer.php'; ?>
<?php
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$mensagem = "";

// 1. PROCESSAR A APROVAÇÃO OU RECUSA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && isset($_POST['orcamento_id'])) {
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

// 2. BUSCAR ORÇAMENTOS (Com GROUP_CONCAT para juntar os serviços)
$id_usuario = $_SESSION['usuario_id'];
$sql = "SELECT o.id, o.valor_total, o.status, o.data_criacao, 
               v.modelo, v.placa, 
               GROUP_CONCAT(s.nome SEPARATOR ', ') AS nomes_servicos 
        FROM orcamentos o
        JOIN veiculos v ON o.veiculo_id = v.id
        LEFT JOIN orcamento_itens oi ON o.id = oi.orcamento_id
        LEFT JOIN servicos s ON oi.servico_id = s.id
        WHERE v.usuario_id = $id_usuario
        GROUP BY o.id
        ORDER BY o.data_criacao DESC";

$resultado = $conn->query($sql);

include_once '../includes/header.php';
?>

<h2>Meus Orçamentos</h2>
<?php echo $mensagem; ?>

<div style="margin-bottom: 20px;">
    <a href="cadastrar.php" class="btn">+ Novo Orçamento</a>
</div>

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
                            <?php else: ?>
                                <span class="badge recusado">RECUSADO</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($row['status'] == 'pendente'): ?>
                                <form action="listar.php" method="POST" style="display: inline-block;">
                                    <input type="hidden" name="orcamento_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="acao" value="aprovado">
                                    <button type="submit" class="btn" style="background-color: #28a745; padding: 5px 10px; font-size: 13px;">Aprovar</button>
                                </form>

                                <form action="listar.php" method="POST" style="display: inline-block;">
                                    <input type="hidden" name="orcamento_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="acao" value="recusado">
                                    <button type="submit" class="btn" style="background-color: #dc3545; padding: 5px 10px; font-size: 13px;">Recusar</button>
                                </form>
                            <?php else: ?>
                                <em style="color: #888;">Concluído</em>
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
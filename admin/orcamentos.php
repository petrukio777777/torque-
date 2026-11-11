<?php
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'funcionario') {
    header("Location: ../dashboard.php");
    exit;
}

$mensagem = "";

// Processar Aprovação ou Recusa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao'])) {
    $orcamento_id = $_POST['orcamento_id'];
    $nova_acao = $_POST['acao'];

    $stmt = $conn->prepare("UPDATE orcamentos SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $nova_acao, $orcamento_id);
    if ($stmt->execute()) {
        $mensagem = "<div class='sucesso'>Orçamento #$orcamento_id alterado para: <strong>" . strtoupper($nova_acao) . "</strong></div>";
    }
    $stmt->close();
}

// BUSCA APENAS OS PENDENTES
$sql = "SELECT o.id, o.valor_total, o.data_criacao, v.modelo, v.placa, u.nome AS nome_cliente,
               GROUP_CONCAT(s.nome SEPARATOR ', ') AS nomes_servicos 
        FROM orcamentos o
        JOIN veiculos v ON o.veiculo_id = v.id
        JOIN usuarios u ON v.usuario_id = u.id
        LEFT JOIN orcamento_itens oi ON o.id = oi.orcamento_id
        LEFT JOIN servicos s ON oi.servico_id = s.id
        WHERE o.status = 'pendente'
        GROUP BY o.id
        ORDER BY o.data_criacao DESC";

$resultado = $conn->query($sql);

include_once '../includes/header.php';
?>

<h2>Orçamentos Pendentes</h2>
<?php echo $mensagem; ?>

<div style="margin-bottom: 20px;">
    <a href="dash.php" class="btn" style="background-color: #6c757d; padding: 8px 15px; font-size: 14px; text-decoration: none; display: inline-block;">← Voltar ao Menu</a>

</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Veículo</th>
                <th>Serviços</th>
                <th>Valor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nome_cliente']) ?></td>
                        <td><?= htmlspecialchars($row['modelo']) ?> (<?= htmlspecialchars($row['placa']) ?>)</td>
                        <td><?= htmlspecialchars($row['nomes_servicos'] ?? '-') ?></td>
                        <td>R$ <?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                        <td>
                            <!-- Apenas Aprovar ou Recusar -->
                            <form action="orcamentos.php" method="POST" style="display: inline-block;">
                                <input type="hidden" name="orcamento_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="acao" value="aprovado">
                                <button type="submit" class="btn" style="background:#28a745; padding:5px 10px; font-size:12px;">Aprovar</button>
                            </form>
                            <form action="orcamentos.php" method="POST" style="display: inline-block;">
                                <input type="hidden" name="orcamento_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="acao" value="recusado">
                                <button type="submit" class="btn" style="background:#dc3545; padding:5px 10px; font-size:12px;">Recusar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Nenhum orçamento pendente no momento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once '../includes/footer.php'; ?>
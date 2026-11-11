<?php
session_start();
require_once '../config/conexao.php';

if(!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'funcionario') {
    header("Location: ../dashboard.php");
    exit;
}

// Busca apenas os CONCLUÍDOS
$sql = "SELECT o.id AS orcamento_id, o.valor_total, o.data_criacao,
               v.modelo, v.placa, v.cor, v.ano,
               u.nome AS nome_cliente, u.telefone,
               GROUP_CONCAT(s.nome SEPARATOR ', ') AS servicos_realizados
        FROM orcamentos o
        JOIN veiculos v ON o.veiculo_id = v.id
        JOIN usuarios u ON v.usuario_id = u.id
        LEFT JOIN orcamento_itens oi ON o.id = oi.orcamento_id
        LEFT JOIN servicos s ON oi.servico_id = s.id
        WHERE o.status = 'concluido'
        GROUP BY o.id
        ORDER BY o.data_criacao DESC";

$resultado = $conn->query($sql);

include_once '../includes/header.php';
?>

<h2>Histórico de Serviços Finalizados</h2>

<div style="margin-bottom: 20px;">
        <a href="dash.php" class="btn" style="background-color: #6c757d; padding: 8px 15px; font-size: 14px; text-decoration: none; display: inline-block;">← Voltar ao Menu</a>


<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Orçamento</th>
                <th>Data/Hora</th>
                <th>Cliente / Contato</th>
                <th>Veículo</th>
                <th>Serviços Realizados</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['orcamento_id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?></td>
                        <td><strong><?= htmlspecialchars($row['nome_cliente']) ?></strong><br><small><?= htmlspecialchars($row['telefone']) ?></small></td>
                        <td><?= htmlspecialchars($row['modelo']) ?> (<?= htmlspecialchars($row['placa']) ?>)<br><small><?= htmlspecialchars($row['cor']) ?> / <?= htmlspecialchars($row['ano']) ?></small></td>
                        <td><?= htmlspecialchars($row['servicos_realizados']) ?></td>
                        <td>R$ <?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center;">Nenhum serviço finalizado ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once '../includes/footer.php'; ?>
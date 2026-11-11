<?php
session_start();
require_once '../config/conexao.php';

if(!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'funcionario') {
    header("Location: ../dashboard.php");
    exit;
}

$mensagem = "";

// Processar ações (Iniciar ou Finalizar)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao'])) {
    $orcamento_id = $_POST['orcamento_id'];
    
    if ($_POST['acao'] == 'iniciar') {
        $novo_status = 'em_andamento';
        $texto = "Serviço Iniciado!";
    } elseif ($_POST['acao'] == 'finalizar') {
        $novo_status = 'concluido';
        $texto = "Serviço Finalizado com Sucesso!";
    }

    $stmt = $conn->prepare("UPDATE orcamentos SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $novo_status, $orcamento_id);
    
    if ($stmt->execute()) {
        $mensagem = "<div class='sucesso'>$texto (Orçamento #$orcamento_id)</div>";
    }
    $stmt->close();
}

// Busca serviços Aprovados ou Em Andamento
$sql = "SELECT o.id AS orcamento_id, o.status, o.valor_total, 
               v.modelo, v.placa, v.cor, v.ano,
               u.nome AS nome_cliente, u.telefone,
               GROUP_CONCAT(s.nome SEPARATOR ', ') AS servicos_realizados
        FROM orcamentos o
        JOIN veiculos v ON o.veiculo_id = v.id
        JOIN usuarios u ON v.usuario_id = u.id
        LEFT JOIN orcamento_itens oi ON o.id = oi.orcamento_id
        LEFT JOIN servicos s ON oi.servico_id = s.id
        WHERE o.status IN ('aprovado', 'em_andamento')
        GROUP BY o.id
        ORDER BY o.data_criacao DESC";

$resultado = $conn->query($sql);

include_once '../includes/header.php';
?>

<h2>Serviços na Oficina</h2>
<?php echo $mensagem; ?>

<div style="margin-bottom: 20px;">
        <a href="dash.php" class="btn" style="background-color: #6c757d; padding: 8px 15px; font-size: 14px; text-decoration: none; display: inline-block;">← Voltar ao Menu</a>

</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Orçamento</th>
                <th>Cliente / Contato</th>
                <th>Veículo</th>
                <th>Serviços</th>
                <th>Status</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['orcamento_id'] ?></td>
                        <td><strong><?= htmlspecialchars($row['nome_cliente']) ?></strong><br><small><?= htmlspecialchars($row['telefone']) ?></small></td>
                        <td><?= htmlspecialchars($row['modelo']) ?> (<?= htmlspecialchars($row['placa']) ?>)</td>
                        <td><?= htmlspecialchars($row['servicos_realizados']) ?></td>
                        <td>
                            <?php if($row['status'] == 'aprovado'): ?>
                                <span class="badge aprovado">AGUARDANDO INÍCIO</span>
                            <?php else: ?>
                                <span class="badge" style="background:#cce5ff; color:#004085;">EM ANDAMENTO</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['status'] == 'aprovado'): ?>
                                <!-- Botão para Iniciar -->
                                <form action="veiculos.php" method="POST">
                                    <input type="hidden" name="orcamento_id" value="<?= $row['orcamento_id'] ?>">
                                    <input type="hidden" name="acao" value="iniciar">
                                    <button type="submit" class="btn" style="background:#004085; padding:5px 10px; font-size:12px;">Iniciar Serviço</button>
                                </form>
                            <?php else: ?>
                                <!-- Botão para Finalizar -->
                                <form action="veiculos.php" method="POST">
                                    <input type="hidden" name="orcamento_id" value="<?= $row['orcamento_id'] ?>">
                                    <input type="hidden" name="acao" value="finalizar">
                                    <button type="submit" class="btn" style="background:#155724; padding:5px 10px; font-size:12px;">Finalizar e Arquivar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center;">Nenhum serviço na bancada no momento.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once '../includes/footer.php'; ?>
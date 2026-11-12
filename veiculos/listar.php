<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}
require_once '../config/conexao.php';
$mensagem = "";

// Verifica se o usuário clicou em Excluir
if (isset($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    $id_usuario = $_SESSION['usuario_id'];
    
    // Só exclui se o carro for do usuário logado
    $stmt = $conn->prepare("DELETE FROM veiculos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id_excluir, $id_usuario);

    if ($stmt->execute()) {
        $mensagem = "<div class='sucesso' style='color: green;'>Veículo excluído com sucesso!</div>";
    } else {
        $mensagem = "<div class='erro' style='color: red;'>Erro ao excluir. Talvez existam orçamentos vinculados.</div>";
    }
    $stmt->close();
}

$id_usuario = $_SESSION['usuario_id'];
$sql = "SELECT * FROM veiculos WHERE usuario_id = $id_usuario ORDER BY id DESC";
$resultado = $conn->query($sql);

include_once '../includes/header.php';
?>

<h2>Meus Veículos</h2>
<?php echo $mensagem; ?>

<!-- Barra de Ferramentas (Ações e Filtro) -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">

    <!-- Lado Esquerdo: Voltar e Novo -->
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="../dashboard.php" class="btn" style="background-color: #6c757d; padding: 10px 15px; font-size: 14px; text-decoration: none; display: inline-block; margin: 0;">← Voltar ao Menu</a>
        <a href="cadastrar.php" class="btn" style="margin: 0;">+ Adicionar novo veiculo</a>
    </div>

</div>

<?php echo $mensagem; ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Ano</th>
                <th>Placa</th>
                <th>Cor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['marca']) ?></td>
                        <td><?= htmlspecialchars($row['modelo']) ?></td>
                        <td><?= htmlspecialchars($row['ano']) ?></td>
                        <td><?= htmlspecialchars($row['placa']) ?></td>
                        <td><?= htmlspecialchars($row['cor']) ?></td>
                        <td>
                            <a href="editar.php?id=<?= $row['id'] ?>" class="action-link edit">Editar</a> |
                            <a href="listar.php?excluir=<?= $row['id'] ?>" class="action-link delete" onclick="return confirm('Tem certeza que deseja excluir este veículo?');">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Nenhum veículo cadastrado ainda.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once '../includes/footer.php'; ?>
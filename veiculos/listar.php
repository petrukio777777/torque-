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
    // Prepara a exclusão (o ON DELETE CASCADE no banco vai apagar os orçamentos desse carro também)
    $stmt = $conn->prepare("DELETE FROM veiculos WHERE id = ?");
    $stmt->bind_param("i", $id_excluir);

    if ($stmt->execute()) {
        $mensagem = "<div class='sucesso' style='color: green;'>Veículo excluído com sucesso!</div>";
    } else {
        $mensagem = "<div class='erro' style='color: red;'>Erro ao excluir. Talvez existam orçamentos vinculados.</div>";
    }
    $stmt->close();
}

// Busca todos os veículos do banco
$sql = "SELECT * FROM veiculos ORDER BY id DESC";
$resultado = $conn->query($sql);

include_once '../includes/header.php';
?>

<h2>Meus Veículos</h2>
<?php echo $mensagem; ?>

<div style="margin-bottom: 20px;">
    <a href="cadastrar.php" class="btn">+ Adicionar Novo Veículo</a>
</div>
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
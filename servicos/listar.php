<?php
session_start();
require_once '../config/conexao.php';

// PROTEÇÃO
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$mensagem = "";

// Excluir serviço
if (isset($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    $stmt = $conn->prepare("DELETE FROM servicos WHERE id = ?");
    $stmt->bind_param("i", $id_excluir);

    if ($stmt->execute()) {
        $mensagem = "<div class='sucesso' style='color: green;'>Serviço excluído com sucesso!</div>";
    } else {
        $mensagem = "<div class='erro' style='color: red;'>Erro ao excluir. Pode haver orçamentos vinculados a este serviço.</div>";
    }
    $stmt->close();
}

$sql = "SELECT * FROM servicos ORDER BY id DESC";
$resultado = $conn->query($sql);

include_once '../includes/header.php';
?>

<h2>Serviços Oferecidos</h2>
<?php echo $mensagem; ?>

<a href="cadastrar.php" style="display: inline-block; margin-bottom: 15px; padding: 10px; background-color: #0F4C5C; color: white; text-decoration: none;">+ Adicionar Novo Serviço</a>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Valor Base</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= htmlspecialchars($row['descricao']) ?></td>
                        <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                        <td>
                            <a href="editar.php?id=<?= $row['id'] ?>" class="action-link edit">Editar</a> |
                            <a href="listar.php?excluir=<?= $row['id'] ?>" class="action-link delete" onclick="return confirm('Tem certeza que deseja excluir este serviço?');">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Nenhum serviço cadastrado ainda.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include_once '../includes/footer.php'; ?>
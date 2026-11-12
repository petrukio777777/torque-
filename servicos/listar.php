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

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
    
    <!-- Lado Esquerdo: Voltar e Adicionar (só para admin) -->
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="../dashboard.php" class="btn" style="background-color: #6c757d; padding: 10px 15px; font-size: 14px; text-decoration: none; display: inline-block; margin: 0;">← Voltar ao Menu</a>
        
        <!-- O botão de Adicionar só aparece se for funcionário -->
        <?php if(isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] == 'funcionario'): ?>
            <a href="cadastrar.php" class="btn" style="margin: 0;">+ Adicionar Novo Serviço</a>
        <?php endif; ?>
    </div>

    <!-- Lado Direito: Formulário de Filtro (opcional, mas deixa profissional) -->
    <form action="listar.php" method="GET" style="display: flex; gap: 0; margin: 0; padding: 0; box-shadow: none; background: transparent; max-width: 100%;">
        <input type="text" name="filtro" placeholder="Buscar serviço..." value="<?= htmlspecialchars($_GET['filtro'] ?? '') ?>" style="margin: 0; border-radius: 6px 0 0 6px; border-right: none; width: 250px; padding: 10px 15px;">
        <button type="submit" style="background-color: #F28C28; margin: 0; border-radius: 0 6px 6px 0; padding: 10px 20px;">Buscar</button>
        <?php if(isset($_GET['filtro']) && $_GET['filtro'] != ''): ?>
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
                <th>Nome</th>
                <th>Descrição</th>
                <th>Valor Base</th>
                <?php if ($_SESSION['tipo_usuario'] == 'funcionario'): ?> <th>Ações</th> <?php endif; ?>
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
                            <?php if ($_SESSION['tipo_usuario'] == 'funcionario'): ?>
                                <a href="editar.php?id=<?= $row['id'] ?>" class="action-link edit">Editar</a> |
                                <a href="listar.php?excluir=<?= $row['id'] ?>" class="action-link delete" onclick="return confirm('Excluir?');">Excluir</a>
                            <?php else: ?>
                                <em style="color: #888;">-</em>
                            <?php endif; ?>
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
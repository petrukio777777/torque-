<?php
session_start();
require_once '../config/conexao.php';

// PROTEÇÃO
if(!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$mensagem = "";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM servicos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $servico = $stmt->get_result()->fetch_assoc();

    if (!$servico) { die("Serviço não encontrado."); }
} else {
    header("Location: listar.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];

    $stmt = $conn->prepare("UPDATE servicos SET nome=?, descricao=?, valor=? WHERE id=?");
    $stmt->bind_param("ssdi", $nome, $descricao, $valor, $id);

    if ($stmt->execute()) {
        $mensagem = "<div class='sucesso' style='color: green;'>Serviço atualizado com sucesso!</div>";
        $servico['nome'] = $nome; $servico['descricao'] = $descricao; $servico['valor'] = $valor;
    } else {
        $mensagem = "<div class='erro' style='color: red;'>Erro ao atualizar: " . $stmt->error . "</div>";
    }
}

include_once '../includes/header.php';
?>

<h2>Editar Serviço</h2>
<?php echo $mensagem; ?>

<form action="editar.php?id=<?= $servico['id'] ?>" method="POST" style="display: flex; flex-direction: column; max-width: 400px; gap: 10px;">
    <input type="hidden" name="id" value="<?= $servico['id'] ?>">
    
    <label>Nome:</label>
    <input type="text" name="nome" value="<?= htmlspecialchars($servico['nome']) ?>" required>
    
    <label>Descrição:</label>
    <textarea name="descricao" rows="3" required><?= htmlspecialchars($servico['descricao']) ?></textarea>
    
    <label>Valor Base (R$):</label>
    <input type="number" step="0.01" name="valor" value="<?= htmlspecialchars($servico['valor']) ?>" required min="0">
    
    <button type="submit" style="margin-top: 10px; cursor: pointer;">Atualizar Serviço</button>
</form>

<?php include_once '../includes/footer.php'; ?>
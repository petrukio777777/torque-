<?php
session_start();
require_once '../config/conexao.php';

// PROTEÇÃO
if(!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];

    $stmt = $conn->prepare("INSERT INTO servicos (nome, descricao, valor) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $nome, $descricao, $valor); // s=string, d=decimal

    if ($stmt->execute()) {
        $mensagem = "<div class='sucesso' style='color: green;'>Serviço cadastrado com sucesso!</div>";
    } else {
        $mensagem = "<div class='erro' style='color: red;'>Erro ao cadastrar: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

include_once '../includes/header.php';
?>

<h2>Cadastrar Novo Serviço</h2>
<?php echo $mensagem; ?>

<form action="cadastrar.php" method="POST" style="display: flex; flex-direction: column; max-width: 400px; gap: 10px;">
    <label>Nome do Serviço (ex: Troca de Óleo):</label>
    <input type="text" name="nome" required>
    
    <label>Descrição:</label>
    <textarea name="descricao" rows="3" required></textarea>
    
    <label>Valor Base (R$):</label>
    <input type="number" step="0.01" name="valor" required min="0">
    
    <button type="submit" style="margin-top: 10px; cursor: pointer;">Salvar Serviço</button>
</form>

<?php include_once '../includes/footer.php'; ?>
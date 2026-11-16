<?php
session_start();
require_once '../config/conexao.php';

// PROTEÇÃO
if (!isset($_SESSION['usuario_id'])) {
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

<div style="display: flex; flex-direction: column; align-items: center; width: 100%;">

    <h2 style="text-align: center; width: 100%; max-width: 500px;">Cadastrar Novo Serviço</h2>

    <!-- Botão de Voltar centralizado -->
    <div style="width: 100%; max-width: 500px; display: flex; justify-content: center; margin-bottom: 20px;">
        <a href="listar.php" class="btn" style="background-color: #6c757d; padding: 8px 15px; font-size: 14px; text-decoration: none; display: inline-block;">← Voltar para Serviços</a>
    </div>

    <div style="width: 100%; max-width: 500px; margin-bottom: 20px;">
        <?php echo $mensagem; ?>
    </div>

    <?php echo $mensagem; ?>

    <form action="cadastrar.php" method="POST" style="display: flex; flex-direction: column; width: 100%; max-width: 500px; gap: 15px; margin: 0 auto;">
        <label>Nome do Serviço (ex: Troca de Óleo):</label>
        <input type="text" name="nome" required>

        <label>Descrição:</label>
        <textarea name="descricao" rows="3" required></textarea>

        <label>Valor Base (R$):</label>
        <input type="number" step="0.01" name="valor" required min="0">

        <button type="submit" style="margin-top: 10px; cursor: pointer;">Salvar Serviço</button>

        <button type="submit" style="margin-top: 10px; cursor: pointer;">Salvar Serviço</button>
    </form>
</div>

<?php include_once '../includes/footer.php'; ?>
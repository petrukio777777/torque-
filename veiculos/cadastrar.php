<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once '../config/conexao.php';

$mensagem = "";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['usuario_id'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $ano = $_POST['ano'];
    $placa = strtoupper($_POST['placa']); // Deixa a placa em maiúsculo
    $cor = $_POST['cor'];

    // Prepara a inserção (Prepared Statements para evitar SQL Injection)
    $stmt = $conn->prepare("INSERT INTO veiculos (usuario_id, marca, modelo, ano, placa, cor) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $usuario_id, $marca, $modelo, $ano, $placa, $cor);

    if ($stmt->execute()) {
        $mensagem = "<div class='sucesso'>Veículo cadastrado com sucesso!</div>";
    } else {
        $mensagem = "<div class='erro'>Erro ao cadastrar: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

include_once '../includes/header.php';
?>

<div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
    <div style="width: 100%; max-width: 500px; text-align: left;">
        <h2 style="text-align: center;">Cadastrar Novo Veículo</h2>
    </div>

    <!-- Botão de Voltar centralizado -->
    <div style="width: 100%; max-width: 500px; display: flex; justify-content: center; margin-bottom: 20px;">
        <a href="listar.php" class="btn" style="background-color: #6c757d; padding: 8px 15px; font-size: 14px; text-decoration: none; display: inline-block;">← Voltar para Meus Veículos</a>
    </div>

    <?php echo $mensagem; ?>

    <!-- Formulário -->
    <form action="cadastrar.php" method="POST" style="display: flex; flex-direction: column; width: 100%; max-width: 500px; gap: 15px; margin: 0 auto;">
        <label>Marca:</label>
        <input type="text" name="marca" required>

        <label>Modelo:</label>
        <input type="text" name="modelo" required>

        <label>Ano:</label>
        <input type="number" name="ano" required min="1900" max="2099">

        <label>Placa:</label>
        <input type="text" name="placa" required maxlength="8" placeholder="ABC1234">

        <label>Cor:</label>
        <input type="text" name="cor" required>

        <button type="submit">Salvar Veículo</button>
    </form>
</div>

<?php include_once '../includes/footer.php'; ?>
<?php
session_start();

if(!isset($_SESSION['usuario_id'])) {
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

<h2>Cadastrar Novo Veículo</h2>

<?php echo $mensagem; ?>

<form action="cadastrar.php" method="POST">
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

<?php include_once '../includes/footer.php'; ?>
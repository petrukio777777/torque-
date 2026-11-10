<?php
session_start();

if(!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}
require_once '../config/conexao.php';

$mensagem = "";

// 1. Buscar os dados do veículo para preencher o formulário
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM veiculos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $veiculo = $resultado->fetch_assoc();

    if (!$veiculo) {
        die("Veículo não encontrado.");
    }
} else {
    header("Location: listar.php");
    exit;
}

// 2. Processar a atualização quando o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $ano = $_POST['ano'];
    $placa = strtoupper($_POST['placa']);
    $cor = $_POST['cor'];

    $stmt = $conn->prepare("UPDATE veiculos SET marca=?, modelo=?, ano=?, placa=?, cor=? WHERE id=?");
    $stmt->bind_param("ssissi", $marca, $modelo, $ano, $placa, $cor, $id);

    if ($stmt->execute()) {
        $mensagem = "<div class='sucesso' style='color: green;'>Veículo atualizado com sucesso!</div>";
        // Atualiza os dados na tela para mostrar o valor novo
        $veiculo['marca'] = $marca;
        $veiculo['modelo'] = $modelo;
        $veiculo['ano'] = $ano;
        $veiculo['placa'] = $placa;
        $veiculo['cor'] = $cor;
    } else {
        $mensagem = "<div class='erro' style='color: red;'>Erro ao atualizar: " . $stmt->error . "</div>";
    }
}

include_once '../includes/header.php';
?>

<h2>Editar Veículo</h2>
<?php echo $mensagem; ?>

<form action="editar.php?id=<?= $veiculo['id'] ?>" method="POST" style="display: flex; flex-direction: column; max-width: 400px; gap: 10px;">
    <input type="hidden" name="id" value="<?= $veiculo['id'] ?>">
    
    <label>Marca:</label>
    <input type="text" name="marca" value="<?= htmlspecialchars($veiculo['marca']) ?>" required>
    
    <label>Modelo:</label>
    <input type="text" name="modelo" value="<?= htmlspecialchars($veiculo['modelo']) ?>" required>
    
    <label>Ano:</label>
    <input type="number" name="ano" value="<?= htmlspecialchars($veiculo['ano']) ?>" required min="1900" max="2099">
    
    <label>Placa:</label>
    <input type="text" name="placa" value="<?= htmlspecialchars($veiculo['placa']) ?>" required maxlength="8">
    
    <label>Cor:</label>
    <input type="text" name="cor" value="<?= htmlspecialchars($veiculo['cor']) ?>" required>
    
    <button type="submit" style="margin-top: 10px; cursor: pointer;">Atualizar Veículo</button>
</form>

<?php include_once '../includes/footer.php'; ?>
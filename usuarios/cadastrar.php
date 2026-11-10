<?php
session_start();
require_once '../config/conexao.php';
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
    $telefone = $_POST['telefone'];

    // Verifica se o email já existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $mensagem = "<div class='erro' style='color: red;'>Este e-mail já está cadastrado.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, telefone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $email, $senha, $telefone);

        if ($stmt->execute()) {
             $_SESSION['mensagem_sucesso'] = "Cadastro realizado com sucesso! Faça seu login.";
              header("Location: login.php");
            exit; 
        } else {
            $mensagem = "<div class='erro' style='color: red;'>Erro ao cadastrar: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
include_once '../includes/header.php';
?>

<h2>Criar Conta</h2>
<?php echo $mensagem; ?>

<form action="cadastrar.php" method="POST" style="display: flex; flex-direction: column; max-width: 400px; gap: 10px;">
    <label>Nome Completo:</label>
    <input type="text" name="nome" required>
    
    <label>E-mail:</label>
    <input type="email" name="email" required>
    
    <label>Senha:</label>
    <input type="password" name="senha" required>
    
    <label>Telefone:</label>
    <input type="text" name="telefone" required>
    
    <button type="submit" style="margin-top: 10px; cursor: pointer;">Cadastrar</button>
</form>

<?php include_once '../includes/footer.php'; ?>
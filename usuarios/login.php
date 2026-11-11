<?php
session_start();
require_once '../config/conexao.php';
$mensagem = "";
// Verifica se veio uma mensagem de sucesso do cadastro
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem = "<div class='sucesso' style='color: green; font-weight: bold;'>" . $_SESSION['mensagem_sucesso'] . "</div>";
    unset($_SESSION['mensagem_sucesso']); // Apaga a mensagem para não ficar aparecendo toda hora
}

// Se o usuário já estiver logado, manda ele direto para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../dash.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Busca o usuário pelo e-mail
    $stmt = $conn->prepare("SELECT id, nome, senha, tipo_usuario FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
   
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verifica se a senha digitada bate com a senha criptografada no banco
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario']; // Salva o cargo

            // Redirecionamento Inteligente
            if ($_SESSION['tipo_usuario'] == 'funcionario') {
                header("Location: ../admin/dash.php"); // Vai para a oficina
            } else {
                header("Location: ../dashboard.php"); // Vai para o cliente
            }
            exit;
        } else {
            $mensagem = "<div class='erro' style='color: red;'>Senha incorreta.</div>";
        }
    } else {
        $mensagem = "<div class='erro' style='color: red;'>E-mail não encontrado.</div>";
    }
    $stmt->close();
}

include_once '../includes/header.php';
?>

<h2>Login no Torque+</h2>
<?php echo $mensagem; ?>

<form action="login.php" method="POST" style="display: flex; flex-direction: column; max-width: 400px; gap: 10px;">
    <label>E-mail:</label>
    <input type="email" name="email" required>

    <label>Senha:</label>
    <input type="password" name="senha" required>

    <button type="submit" style="margin-top: 10px; cursor: pointer;">Entrar</button>
</form>

<p style="margin-top: 15px;">Ainda não tem conta? <a href="cadastrar.php">Cadastre-se aqui</a></p>

<?php include_once '../includes/footer.php'; ?>
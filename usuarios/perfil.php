<?php
session_start();
require_once '../config/conexao.php';

// PROTEÇÃO
if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$id_usuario = $_SESSION['usuario_id'];

// 1. BUSCAR DADOS DO USUÁRIO
$stmt = $conn->prepare("SELECT nome, email, telefone, data_cadastro FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// 2. PROCESSAR ALTERAÇÃO DE SENHA
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Verifica se a nova senha e a confirmação são iguais
    if ($nova_senha !== $confirmar_senha) {
        $mensagem = "<div class='erro'>As novas senhas não coincidem.</div>";
    } else {
        // Busca a senha atual no banco para comparar
        $stmt_senha = $conn->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmt_senha->bind_param("i", $id_usuario);
        $stmt_senha->execute();
        $senha_banco = $stmt_senha->get_result()->fetch_assoc()['senha'];

        if (password_verify($senha_atual, $senha_banco)) {
            // Senha atual correta! Atualiza para a nova criptografada
            $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt_update = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt_update->bind_param("si", $nova_senha_hash, $id_usuario);
            
            if ($stmt_update->execute()) {
                $mensagem = "<div class='sucesso'>Senha alterada com sucesso!</div>";
            } else {
                $mensagem = "<div class='erro'>Erro ao alterar a senha.</div>";
            }
        } else {
            $mensagem = "<div class='erro'>A senha atual está incorreta.</div>";
        }
    }
}

include_once '../includes/header.php';
?>

<h2>Meu Perfil</h2>

<div style="display: flex; gap: 30px; flex-wrap: wrap;">
    <!-- Cartão de Informações -->
    <div style="flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); height: fit-content;">
        <h3 style="color: #0F4C5C; margin-bottom: 15px;">Informações Pessoais</h3>
        <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?></p>
        <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
        <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone']) ?></p>
        <p><strong>Membro desde:</strong> <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?></p>
    </div>

    <!-- Formulário de Alterar Senha -->
    <div style="flex: 1; min-width: 300px;">
        <h3 style="color: #0F4C5C; margin-bottom: 15px;">Alterar Senha</h3>
        <?php echo $mensagem; ?>
        
        <form action="perfil.php" method="POST" style="margin: 0; box-shadow: none; padding: 0; background: transparent;">
            <label>Senha Atual:</label>
            <input type="password" name="senha_atual" required>
            
            <label>Nova Senha:</label>
            <input type="password" name="nova_senha" required>
            
            <label>Confirmar Nova Senha:</label>
            <input type="password" name="confirmar_senha" required>
            
            <button type="submit">Alterar Senha</button>
        </form>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
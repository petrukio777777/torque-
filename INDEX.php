<?php
session_start();

// PROTEÇÃO: Se não estiver logado, vai para o login
if(!isset($_SESSION['usuario_id'])) {
    header("Location: usuarios/login.php");
    exit;
}

require_once 'config/conexao.php';

// 1. BUSCAR ESTATÍSTICAS PARA O DASHBOARD
// Contar quantos veículos o usuário tem
$id_usuario = $_SESSION['usuario_id'];
$sql_veiculos = $conn->query("SELECT COUNT(*) as total FROM veiculos WHERE usuario_id = $id_usuario");
$total_veiculos = $sql_veiculos->fetch_assoc()['total'];

// Contar orçamentos pendentes (Juntando com veículos para saber se é do usuário logado)
$sql_pendentes = $conn->query("SELECT COUNT(*) as total FROM orcamentos o JOIN veiculos v ON o.veiculo_id = v.id WHERE v.usuario_id = $id_usuario AND o.status = 'pendente'");
$total_pendentes = $sql_pendentes->fetch_assoc()['total'];

// Contar total de serviços oferecidos (isso é global, não por usuário)
$sql_servicos = $conn->query("SELECT COUNT(*) as total FROM servicos");
$total_servicos = $sql_servicos->fetch_assoc()['total'];

include_once 'includes/header.php';
?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .card {
        background-color: #fff;
        border-left: 5px solid #0F4C5C;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .card h3 {
        margin-top: 0;
        color: #333;
        font-size: 16px;
        text-transform: uppercase;
    }
    .card .numero {
        font-size: 36px;
        font-weight: bold;
        color: #0F4C5C;
        margin: 10px 0;
    }
    .card.alerta {
        border-left-color: #F28C28;
    }
    .card.alerta .numero {
        color: #F28C28;
    }
    .boas-vindas {
        background: linear-gradient(90deg, #0F4C5C, #123B4A);
        color: white;
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
</style>

<div class="boas-vindas">
    
    <h1 style="color: white; font-size: 28px; margin-bottom: 10px;">Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h1>
    <p style="color: white;">Bem-vindo ao painel de controle do seu Torque+. Aqui está o resumo do seu sistema hoje.</p>
</div>

<div class="dashboard-grid">
    <!-- Card Veículos -->
    <div class="card">
        <h3>Meus Veículos</h3>
        <div class="numero"><?= $total_veiculos ?></div>
        <a href="veiculos/listar.php" style="color: #0F4C5C; text-decoration: none; font-weight: bold;">Gerenciar Veículos →</a>
    </div>

    <!-- Card Orçamentos Pendentes -->
    <div class="card alerta">
        <h3>Orçamentos Pendentes</h3>
        <div class="numero"><?= $total_pendentes ?></div>
        <a href="orcamento/listar.php" style="color: #F28C28; text-decoration: none; font-weight: bold;">Ver Orçamentos →</a>
    </div>

    <!-- Card Serviços -->
    <div class="card">
        <h3>Serviços Disponíveis</h3>
        <div class="numero"><?= $total_servicos ?></div>
        <a href="servicos/listar.php" style="color: #0F4C5C; text-decoration: none; font-weight: bold;">Ver Serviços →</a>
    </div>
</div>

<div style="margin-top: 40px; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h3>Ações Rápidas</h3>
    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
        <a href="veiculos/cadastrar.php" style="padding: 10px 20px; background-color: #0F4C5C; color: white; text-decoration: none; border-radius: 5px;">+ Novo Veículo</a>
        <a href="orcamento/cadastrar.php" style="padding: 10px 20px; background-color: #F28C28; color: white; text-decoration: none; border-radius: 5px;">+ Novo Orçamento</a>
        <a href="servicos/listar.php" style="padding: 10px 20px; background-color: #123B4A; color: white; text-decoration: none; border-radius: 5px;">Veja nossos serviços</a>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
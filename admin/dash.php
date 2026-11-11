<?php
session_start();
require_once '../config/conexao.php';

// PROTEÇÃO E VERIFICAÇÃO DE ADMIN
if(!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

// Verifica se é o admin (você pode cadastrar esse e-mail no sistema depois)
if(!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'funcionario') {
    header("Location: ../dashboard.php"); // Se for cliente, expulsa
    exit;
}

$total_oficina = $conn->query("SELECT COUNT(*) as total FROM orcamentos WHERE status IN ('aprovado', 'em_andamento')")->fetch_assoc()['total'];
$total_historico = $conn->query("SELECT COUNT(*) as total FROM orcamentos WHERE status = 'concluido'")->fetch_assoc()['total'];
$total_pendentes = $conn->query("SELECT COUNT(*) as total FROM orcamentos WHERE status = 'pendente'")->fetch_assoc()['total'];
$total_aprovados = $conn->query("SELECT COUNT(*) as total FROM orcamentos WHERE status = 'aprovado'")->fetch_assoc()['total'];
$total_veiculos = $conn->query("SELECT COUNT(*) as total FROM veiculos")->fetch_assoc()['total'];

include_once '../includes/header.php';
?>

<style>
    /* CSS específico do Admin */
    .admin-badge { background-color: #F28C28; color: white; padding: 5px 10px; border-radius: 5px; font-weight: bold; font-size: 12px; }
    .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
    .admin-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid #F28C28; }
    .admin-card h3 { margin: 0; color: #666; font-size: 14px; text-transform: uppercase; }
    .admin-card .numero { font-size: 40px; font-weight: bold; color: #123B4A; margin: 10px 0; }
    
    /* Responsividade para tabelas do admin */
    .table-responsive { overflow-x: auto; width: 100%; }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Painel do Funcionário</h2>
    <span class="admin-badge">MODO ADMINISTRADOR</span>
</div>

<div class="admin-grid">
    <!-- Card 1: Pendentes -->
    <div class="admin-card">
        <h3>Orçamentos Pendentes</h3>
        <div class="numero" style="color: #F28C28;"><?= $total_pendentes ?></div>
        <a href="orcamentos.php" class="btn btn-laranja" style="font-size: 14px; padding: 8px 15px;">Aprovar/Recusar</a>
    </div>

    <!-- Card 2: Oficina (Mostra o número de carros lá) -->
    <div class="admin-card">
        <h3>Serviços na Bancada</h3>
        <div class="numero" style="color: #004085;"><?= $total_oficina ?></div>
        <a href="veiculos.php" class="btn" style="font-size: 14px; padding: 8px 15px; background: #004085;">Acessar Oficina</a>
    </div>

    <!-- Card 3: Histórico (Mostra o número de carros finalizados) -->
    <div class="admin-card">
        <h3>Histórico de Serviços</h3>
        <div class="numero" style="color: #28a745;"><?= $total_historico ?></div>
        <a href="historico.php" class="btn" style="font-size: 14px; padding: 8px 15px; background: #28a745;">Ver Arquivados</a>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
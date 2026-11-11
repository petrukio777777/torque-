<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Torque+ | Painel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <header>
        <h1>Torque+</h1>

        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../veiculos/listar.php">Veículos</a>
            <a href="../servicos/listar.php">Serviços</a>
            <a href="../orcamento/listar.php">Orçamentos</a>


            <?php if (isset($_SESSION['usuario_nome'])): ?>
                <div class="user-info">
                    <span>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</span>

                    <!-- Se for funcionário, mostra o atalho do painel -->
                    <?php if ($_SESSION['tipo_usuario'] == 'funcionario'): ?>
                        <a href="../admin/dash.php" style="color: #F28C28; font-weight: bold; margin-left: 10px; text-decoration: none;">⚙️ Painel Admin</a>
                    <?php endif; ?>

                    <a href="../usuarios/logout.php" class="btn-sair" style="margin-left: 15px;">Sair</a>
                </div>
            <?php else: ?>
                <a href="../usuarios/login.php" class="btn-sair">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
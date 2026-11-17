<?php
session_start();
require_once '../config/conexao.php';

// Proteção: Apenas usuários logados podem ver
if(!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$mensagem = "";

// Lógica do Filtro
$filtro = isset($_GET['filtro']) ? trim($_GET['filtro']) : '';
$where_extra = "";

if ($filtro != '') {
    $filtro_seguro = $conn->real_escape_string($filtro);
    // Busca pelo nome OU descrição
    $where_extra = " WHERE nome LIKE '%$filtro_seguro%' OR descricao LIKE '%$filtro_seguro%'";
}

// Executa a query (agora com o filtro se ele existir)
$sql = "SELECT * FROM servicos $where_extra ORDER BY id DESC";
$resultado = $conn->query($sql);

// Se a query der erro, mostra na tela (só para debug)
if (!$resultado) {
    die("Erro na consulta SQL: " . $conn->error);
}

include_once '../includes/header.php';
?>

<h2>Serviços Oferecidos</h2>

<?php echo $mensagem; ?>

<!-- Barra de Ferramentas (Ações e Filtro) -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
    
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="../dashboard.php" class="btn" style="background-color: #6c757d; padding: 10px 15px; font-size: 14px; text-decoration: none; display: inline-block; margin: 0;">← Voltar ao Menu</a>
        
        <?php if(isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] == 'funcionario'): ?>
            <a href="cadastrar.php" class="btn" style="margin: 0;">+ Adicionar Novo Serviço</a>
        <?php endif; ?>
    </div>

    <form action="listar.php" method="GET" style="display: flex; gap: 0; margin: 0; padding: 0; box-shadow: none; background: transparent; max-width: 100%;">
        <input type="text" name="filtro" placeholder="Buscar serviço..." value="<?= htmlspecialchars($filtro) ?>" style="margin: 0; border-radius: 6px 0 0 6px; border-right: none; width: 250px; padding: 10px 15px;">
        <button type="submit" style="background-color: #F28C28; margin: 0; border-radius: 0 6px 6px 0; padding: 10px 20px;">Buscar</button>
        <?php if($filtro != ''): ?>
            <a href="listar.php" class="btn" style="background-color: #dc3545; margin-left: 10px; padding: 10px 15px; text-decoration: none;">Limpar</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Valor Base</th>
                <?php if(isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] == 'funcionario'): ?>
                    <th>Ações</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= htmlspecialchars($row['descricao']) ?></td>
                        <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                        
                        <?php if(isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] == 'funcionario'): ?>
                            <td>
                                <a href="editar.php?id=<?= $row['id'] ?>" class="action-link edit">Editar</a> | 
                                <a href="listar.php?excluir=<?= $row['id'] ?>" class="action-link delete" onclick="return confirm('Tem certeza que deseja excluir este serviço?');">Excluir</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] == 'funcionario') ? '5' : '4' ?>" style="text-align: center;">
                        <?php if ($filtro != ''): ?>
                            Nenhum serviço encontrado para a busca "<?= htmlspecialchars($filtro) ?>".
                        <?php else: ?>
                            Nenhum serviço cadastrado ainda.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once '../includes/footer.php'; ?>
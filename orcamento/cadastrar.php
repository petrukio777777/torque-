<?php
session_start();
require_once '../config/conexao.php';

// PROTEÇÃO
if(!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $veiculo_id = $_POST['veiculo_id'];
    // Verifica se algum serviço foi selecionado
    $servicos_selecionados = isset($_POST['servicos']) ? $_POST['servicos'] : []; 
    
    if (empty($servicos_selecionados)) {
        $mensagem = "<div class='erro'>Selecione pelo menos um serviço.</div>";
    } else {
        $valor_total = 0;

        // 1. Insere o orçamento principal (valor inicial 0)
        $stmt = $conn->prepare("INSERT INTO orcamentos (veiculo_id, valor_total) VALUES (?, ?)");
        $valor_inicial = 0;
        $stmt->bind_param("id", $veiculo_id, $valor_inicial);
        
        if ($stmt->execute()) {
            $orcamento_id = $conn->insert_id;
            $stmt->close();

            // 2. Loop para inserir os itens e calcular o total
            foreach ($servicos_selecionados as $servico_id) {
                $stmt_servico = $conn->prepare("SELECT valor FROM servicos WHERE id = ?");
                $stmt_servico->bind_param("i", $servico_id);
                $stmt_servico->execute();
                $res_servico = $stmt_servico->get_result()->fetch_assoc();
                $valor_servico = $res_servico['valor'];
                $valor_total += $valor_servico;

                $stmt_item = $conn->prepare("INSERT INTO orcamento_itens (orcamento_id, servico_id, valor_unitario) VALUES (?, ?, ?)");
                $stmt_item->bind_param("iid", $orcamento_id, $servico_id, $valor_servico);
                $stmt_item->execute();
                $stmt_item->close();
                $stmt_servico->close();
            }

            // 3. Atualiza o valor total
            $stmt_update = $conn->prepare("UPDATE orcamentos SET valor_total = ? WHERE id = ?");
            $stmt_update->bind_param("di", $valor_total, $orcamento_id);
            $stmt_update->execute();
            $stmt_update->close();

            $mensagem = "<div class='sucesso'>Orçamento criado com sucesso! Valor Total: R$ " . number_format($valor_total, 2, ',', '.') . "</div>";
        } else {
            $mensagem = "<div class='erro'>Erro ao criar orçamento: " . $stmt->error . "</div>";
        }
    }
}

// Buscar veículos
$id_usuario = $_SESSION['usuario_id'];
$veiculos = $conn->query("SELECT id, modelo, placa FROM veiculos WHERE usuario_id = $id_usuario ORDER BY modelo");

// Buscar serviços
$servicos = $conn->query("SELECT id, nome, valor FROM servicos ORDER BY nome");

include_once '../includes/header.php';
?>

<h2>Criar Novo Orçamento</h2>
<?php echo $mensagem; ?>

<form action="cadastrar.php" method="POST" style="display: flex; flex-direction: column; max-width: 500px; gap: 15px;">
    
    <div>
        <label>Veículo:</label>
        <select name="veiculo_id" required style="margin-top: 5px;">
            <option value="">Selecione o veículo...</option>
            <?php while($v = $veiculos->fetch_assoc()): ?>
                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['modelo']) ?> - <?= htmlspecialchars($v['placa']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div>
        <label>Selecione os Serviços:</label>
        <div style="background: #fafafa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; max-height: 200px; overflow-y: auto; margin-top: 5px;">
            <?php while($s = $servicos->fetch_assoc()): ?>
                <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-weight: normal; cursor: pointer;">
                    <!-- Os checkboxes usam name="servicos[]" para enviar um array de IDs -->
                    <input type="checkbox" name="servicos[]" value="<?= $s['id'] ?>" data-valor="<?= $s['valor'] ?>" class="checkbox-servico" style="width: 20px; height: 20px; cursor: pointer;">
                    <span><?= htmlspecialchars($s['nome']) ?> <strong>(R$ <?= number_format($s['valor'], 2, ',', '.') ?>)</strong></span>
                </label>
            <?php endwhile; ?>
        </div>
    </div>

    <div>
        <label>Valor Total (R$):</label>
        <input type="text" name="valor_total" id="valor_total" readonly value="0,00" style="background-color: #e9ecef; cursor: not-allowed; font-weight: bold; font-size: 18px; color: #0F4C5C;">
    </div>

    <button type="submit">Gerar Orçamento</button>
</form>

<script>
// Script para calcular o total sempre que um checkbox for marcado/desmarcado
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.checkbox-servico');
    const inputTotal = document.getElementById('valor_total');

    function calcularTotal() {
        let total = 0;
        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                total += parseFloat(checkbox.getAttribute('data-valor'));
            }
        });
        // Formata o valor para o padrão brasileiro (ex: 1.200,50)
        inputTotal.value = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Adiciona o evento de "change" (quando muda o estado) em cada checkbox
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', calcularTotal);
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>
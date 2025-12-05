<?php
// Sistemas_de_orçamento.php - ARQUIVO ÚNICO QUE USA SEUS OUTROS ARQUIVOS
session_start();

// =================== INCLUIR SEUS ARQUIVOS ===================
require_once 'config.php';           // Sua conexão mysqli
require_once 'Orcamento.php';        // Sua classe Orcamento
require_once 'OrcamentoDAO.php';     // Seu DAO
require_once 'OrcamentoController.php'; // Seu controller (se quiser usar)

// =================== INICIALIZAR ===================
$dao = new OrcamentoDAO();
$mensagem = '';
$tipo_mensagem = '';
$orcamento_editando = null;

// =================== PROCESSAR AÇÕES ===================
// AÇÃO: SALVAR (CREATE/UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'salvar') {
        $orcamento = new Orcamento(
            $_POST['cliente'],
            $_POST['servico'],
            $_POST['valor'],
            $_POST['descricao']
        );
        
        if (!empty($_POST['id'])) {
            $orcamento->setId($_POST['id']);
            $orcamento->setStatus($_POST['status']);
        } else {
            $orcamento->setStatus('pendente');
        }
        
        if ($dao->salvar($orcamento)) {
            $mensagem = 'Orçamento salvo com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao salvar orçamento!';
            $tipo_mensagem = 'danger';
        }
    }
}

// AÇÃO: EXCLUIR (DELETE)
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    if ($dao->excluir($_GET['id'])) {
        $mensagem = 'Orçamento excluído com sucesso!';
        $tipo_mensagem = 'success';
    } else {
        $mensagem = 'Erro ao excluir orçamento!';
        $tipo_mensagem = 'danger';
    }
}

// AÇÃO: EDITAR (CARREGAR DADOS)
if (isset($_GET['acao']) && $_GET['acao'] === 'editar' && isset($_GET['id'])) {
    $orcamento_editando = $dao->buscarPorId($_GET['id']);
}

// =================== OBTER DADOS ===================
$orcamentos = $dao->listar();

// =================== HTML/VIEW ===================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Orçamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .status-pendente { background-color: #fff3cd; padding: 5px 10px; border-radius: 5px; color: #856404; }
        .status-aprovado { background-color: #d1ecf1; padding: 5px 10px; border-radius: 5px; color: #0c5460; }
        .status-recusado { background-color: #f8d7da; padding: 5px 10px; border-radius: 5px; color: #721c24; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn-action { margin: 2px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-primary">Sistema de Orçamentos</h1>
            <p class="text-muted">CRUD completo usando seus arquivos existentes</p>
        </div>
        
        <!-- MENSAGENS -->
        <?php if($mensagem): ?>
        <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show">
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- FORMULÁRIO -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square"></i>
                    <?= $orcamento_editando ? 'EDITAR ORÇAMENTO' : 'NOVO ORÇAMENTO' ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="id" value="<?= $orcamento_editando['id'] ?? '' ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente *</label>
                            <input type="text" class="form-control" name="cliente" 
                                   value="<?= htmlspecialchars($orcamento_editando['cliente'] ?? '') ?>" 
                                   placeholder="Nome do cliente" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serviço *</label>
                            <input type="text" class="form-control" name="servico" 
                                   value="<?= htmlspecialchars($orcamento_editando['servico'] ?? '') ?>" 
                                   placeholder="Descrição do serviço" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Valor (R$) *</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" name="valor" 
                                       step="0.01" min="0" value="<?= $orcamento_editando['valor'] ?? '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" <?= empty($orcamento_editando) ? 'disabled' : '' ?>>
                                <option value="pendente" <?= ($orcamento_editando['status'] ?? 'pendente') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="aprovado" <?= ($orcamento_editando['status'] ?? '') == 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
                                <option value="recusado" <?= ($orcamento_editando['status'] ?? '') == 'recusado' ? 'selected' : '' ?>>Recusado</option>
                            </select>
                            <?php if(empty($orcamento_editando)): ?>
                            <small class="text-muted">Status padrão: Pendente</small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="descricao" rows="3" 
                                      placeholder="Detalhes do serviço..."><?= htmlspecialchars($orcamento_editando['descricao'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-save"></i>
                                <?= $orcamento_editando ? 'ATUALIZAR' : 'SALVAR' ?>
                            </button>
                            <?php if($orcamento_editando): ?>
                            <a href="?" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> CANCELAR
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- LISTA DE ORÇAMENTOS -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i>
                    LISTA DE ORÇAMENTOS (<?= count($orcamentos) ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if(empty($orcamentos)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Nenhum orçamento cadastrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orcamentos as $orcamento): ?>
                                <tr>
                                    <td><strong>#<?= $orcamento['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($orcamento['cliente']) ?></td>
                                    <td><?= htmlspecialchars($orcamento['servico']) ?></td>
                                    <td class="fw-bold text-primary">
                                        R$ <?= number_format($orcamento['valor'], 2, ',', '.') ?>
                                    </td>
                                    <td>
                                        <span class="status-<?= $orcamento['status'] ?>">
                                            <i class="bi bi-circle-fill"></i>
                                            <?= strtoupper($orcamento['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($orcamento['data_criacao'])) ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('H:i', strtotime($orcamento['data_criacao'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?acao=editar&id=<?= $orcamento['id'] ?>" 
                                               class="btn btn-warning btn-sm btn-action">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?acao=excluir&id=<?= $orcamento['id'] ?>" 
                                               class="btn btn-danger btn-sm btn-action"
                                               onclick="return confirm('Tem certeza que deseja excluir o orçamento #<?= $orcamento['id'] ?>?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- RESUMO ESTATÍSTICO -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up"></i>
                    RESUMO ESTATÍSTICO
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    // Calcular estatísticas
                    $total_orcamentos = count($orcamentos);
                    
                    $aprovados = array_filter($orcamentos, function($o) {
                        return $o['status'] === 'aprovado';
                    });
                    
                    $pendentes = array_filter($orcamentos, function($o) {
                        return $o['status'] === 'pendente';
                    });
                    
                    $recusados = array_filter($orcamentos, function($o) {
                        return $o['status'] === 'recusado';
                    });
                    
                    $valor_total = array_sum(array_column($orcamentos, 'valor'));
                    $valor_aprovado = array_sum(array_column($aprovados, 'valor'));
                    ?>
                    
                    <div class="col-md-3">
                        <div class="card text-center text-white bg-primary">
                            <div class="card-body">
                                <h2><?= $total_orcamentos ?></h2>
                                <p class="mb-0">TOTAL</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-center text-white bg-success">
                            <div class="card-body">
                                <h2><?= count($aprovados) ?></h2>
                                <p class="mb-0">APROVADOS</p>
                                <small>R$ <?= number_format($valor_aprovado, 2, ',', '.') ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-center text-white bg-warning">
                            <div class="card-body">
                                <h2><?= count($pendentes) ?></h2>
                                <p class="mb-0">PENDENTES</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-center text-white bg-secondary">
                            <div class="card-body">
                                <h2>R$ <?= number_format($valor_total, 2, ',', '.') ?></h2>
                                <p class="mb-0">VALOR TOTAL</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- RODAPÉ -->
        <div class="mt-4 text-center text-muted">
            <hr>
            <p>
                <i class="bi bi-code-slash"></i>
                Sistema desenvolvido com PHP + MySQL
                <br>
                <small>Arquivos utilizados: config.php, Orcamento.php, OrcamentoDAO.php</small>
            </p>
        </div>
    </div>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Habilitar campo status apenas se estiver editando
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.querySelector('select[name="status"]');
            const idInput = document.querySelector('input[name="id"]');
            
            if(idInput && idInput.value) {
                statusSelect.disabled = false;
            }
        });
    </script>
</body>
</html>
<?php
require_once 'config.php';
require_once 'Orcamento.php';
require_once 'OrcamentoDAO.php';

// Verifica se é uma requisição AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $dao = new OrcamentoDAO();
        
        switch ($_POST['action']) {
            case 'salvar':
                $orcamento = new Orcamento(
                    $_POST['cliente'],
                    $_POST['servico'],
                    $_POST['valor'],
                    $_POST['descricao']
                );
                
                if (!empty($_POST['id'])) {
                    $orcamento->setId($_POST['id']);
                }
                
                if (isset($_POST['status'])) {
                    $orcamento->setStatus($_POST['status']);
                }
                
                $result = $dao->salvar($orcamento);
                echo json_encode(['success' => $result, 'message' => 'Orçamento salvo com sucesso!']);
                break;
                
            case 'listar':
                $orcamentos = $dao->listar();
                echo json_encode($orcamentos);
                break;
                
            case 'excluir':
                $result = $dao->excluir($_POST['id']);
                echo json_encode(['success' => $result, 'message' => 'Orçamento excluído com sucesso!']);
                break;
                
            case 'buscar':
                $orcamento = $dao->buscarPorId($_POST['id']);
                echo json_encode($orcamento);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Orçamentos - CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.85) !important;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .carousel-item {
            height: 300px;
            background-size: cover;
            background-position: center;
        }
        
        .carousel-caption {
            background: rgba(0,0,0,0.6);
            border-radius: 10px;
            padding: 15px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border: none;
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            border: none;
        }
        
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            margin-top: 40px;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
        }
        
        .status-pendente {
            background-color: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .status-aprovado {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .status-recusado {
            background-color: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .valor-orcamento {
            font-weight: bold;
            color: var(--secondary-color);
        }
        
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .alert-message {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }
        
        .action-buttons .btn {
            margin: 0 2px;
        }
        
        @media (max-width: 768px) {
            .carousel-item {
                height: 200px;
            }
            
            .display-4 {
                font-size: 2rem;
            }
            
            .action-buttons .btn {
                margin: 2px;
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-file-invoice-dollar me-2"></i>ORÇFÁCIL
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#lista-orcamentos">
                            <i class="fas fa-list me-1"></i>Orçamentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#form-orcamento">
                            <i class="fas fa-plus me-1"></i>Novo Orçamento
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#relatorios">
                            <i class="fas fa-chart-bar me-1"></i>Relatórios
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mensagens de Alerta -->
    <div id="alertContainer"></div>

    <!-- Carrossel de Imagens -->
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active" style="background-image: url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80')">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="display-4">Sistema de Orçamentos</h2>
                    <p class="lead"> facil e rapido </p>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80')">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="display-4">Controle Total</h2>
                    <p class="lead">Crie, edite e exclua orçamentos diretamente</p>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80')">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="display-4">Interface Integrada</h2>
                    <p class="lead">Todas as operções em uma única página</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- Conteúdo Principal -->
    <div class="container my-5">
        
        <!-- Formulário de Orçamento -->
        <div class="form-section" id="form-orcamento">
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-4">
                        <i class="fas fa-edit me-2"></i>
                        <span id="formTitle">Novo Orçamento</span>
                    </h2>
                </div>
            </div>
            
            <form id="orcamentoForm">
                <input type="hidden" id="orcamentoId" name="id">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cliente" class="form-label">Cliente *</label>
                        <input type="text" class="form-control" id="cliente" name="cliente" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="servico" class="form-label">Serviço *</label>
                        <input type="text" class="form-control" id="servico" name="servico" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="valor" class="form-label">Valor (R$) *</label>
                        <input type="number" class="form-control" id="valor" name="valor" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="pendente">Pendente</option>
                            <option value="aprovado">Aprovado</option>
                            <option value="recusado">Recusado</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição do Serviço</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        <span id="submitText">Salvar Orçamento</span>
                    </button>
                    <button type="button" class="btn btn-secondary" id="btnCancelar" style="display: none;">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de Orçamentos -->
        <div class="form-section" id="lista-orcamentos">
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-4">
                        <i class="fas fa-list me-2"></i>Lista de Orçamentos
                    </h2>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Valor</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaOrcamentos">
                        
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Relatórios -->
        <div class="form-section" id="relatorios">
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-4">
                        <i class="fas fa-chart-bar me-2"></i>Relatórios
                    </h2>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 id="totalOrcamentos" class="text-primary">0</h3>
                            <p class="card-text">Total de Orçamentos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 id="totalAprovados" class="text-success">0</h3>
                            <p class="card-text">Aprovados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 id="totalPendentes" class="text-warning">0</h3>
                            <p class="card-text">Pendentes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 id="valorTotal" class="text-info">R$ 0,00</h3>
                            <p class="card-text">Valor Total</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Sistema de Orçamentos</h5>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Funcionalidades</h5>
                    <ul class="list-unstyled">
                        <li>✅ Criar Orçamentos</li>
                        <li>✅ Listar Orçamentos</li>
                        <li>✅ Editar Orçamentos</li>
                        <li>✅ Excluir Orçamentos</li>
                    </ul>
                
            <hr class="bg-light">
            <div class="row">
                <div class="col-12 text-center">
                    <p>&copy; 2025 orçfacil</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir este orçamento?</p>
                    <p><strong id="modalOrcamentoInfo"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Excluir</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        
        class SistemaOrcamentosPHP {
            constructor() {
                this.init();
            }
            
            init() {
                this.carregarOrcamentos();
                this.configurarEventos();
                this.atualizarRelatorios();
            }
            
            configurarEventos() {
                document.getElementById('orcamentoForm').addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.salvarOrcamento();
                });
                
                document.getElementById('btnCancelar').addEventListener('click', () => {
                    this.cancelarEdicao();
                });
                
                document.getElementById('confirmDelete').addEventListener('click', () => {
                    this.excluirOrcamentoConfirmado();
                });
            }
            
            async carregarOrcamentos() {
                try {
                    const response = await this.fazerRequisicao('listar');
                    this.exibirOrcamentos(response);
                    this.atualizarRelatorios(response);
                } catch (error) {
                    this.mostrarMensagem('Erro ao carregar orçamentos: ' + error, 'danger');
                }
            }
            
            async salvarOrcamento() {
                const formData = new FormData(document.getElementById('orcamentoForm'));
                const dados = {
                    action: 'salvar',
                    id: formData.get('id'),
                    cliente: formData.get('cliente'),
                    servico: formData.get('servico'),
                    valor: formData.get('valor'),
                    descricao: formData.get('descricao'),
                    status: formData.get('status')
                };
                
                try {
                    const response = await this.fazerRequisicao('salvar', dados);
                    this.mostrarMensagem(response.message, response.success ? 'success' : 'danger');
                    
                    if (response.success) {
                        this.limparFormulario();
                        this.carregarOrcamentos();
                    }
                } catch (error) {
                    this.mostrarMensagem('Erro ao salvar orçamento: ' + error, 'danger');
                }
            }
            
            async editarOrcamento(id) {
                try {
                    const orcamento = await this.fazerRequisicao('buscar', {id: id});
                    
                    document.getElementById('orcamentoId').value = orcamento.id;
                    document.getElementById('cliente').value = orcamento.cliente;
                    document.getElementById('servico').value = orcamento.servico;
                    document.getElementById('valor').value = orcamento.valor;
                    document.getElementById('descricao').value = orcamento.descricao;
                    document.getElementById('status').value = orcamento.status;
                    
                    document.getElementById('formTitle').textContent = 'Editar Orçamento';
                    document.getElementById('submitText').textContent = 'Atualizar Orçamento';
                    document.getElementById('btnCancelar').style.display = 'block';
                    
                    document.getElementById('form-orcamento').scrollIntoView({ behavior: 'smooth' });
                } catch (error) {
                    this.mostrarMensagem('Erro ao carregar orçamento: ' + error, 'danger');
                }
            }
            
            async excluirOrcamentoConfirmado() {
                if (this.orcamentoParaExcluir) {
                    try {
                        const response = await this.fazerRequisicao('excluir', {id: this.orcamentoParaExcluir});
                        this.mostrarMensagem(response.message, response.success ? 'success' : 'danger');
                        
                        if (response.success) {
                            this.carregarOrcamentos();
                            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
                            modal.hide();
                        }
                    } catch (error) {
                        this.mostrarMensagem('Erro ao excluir orçamento: ' + error, 'danger');
                    }
                    
                    this.orcamentoParaExcluir = null;
                }
            }
            
            async fazerRequisicao(action, dados = {}) {
                const formData = new FormData();
                formData.append('action', action);
                
                for (const key in dados) {
                    if (dados[key] !== null && dados[key] !== undefined) {
                        formData.append(key, dados[key]);
                    }
                }
                
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                
                return await response.json();
            }
            
            // Mantenha os outros métodos (exibirOrcamentos, formatarStatus, etc.) do seu código original
            // ... 
        }

        // Inicialize o sistema
        const sistema = new SistemaOrcamentosPHP();
    </script>
</body>
</html>
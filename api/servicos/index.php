<?php
require_once __DIR__ . '/../../dao/ServicoDAO.php';
require_once __DIR__ . '/../../models/Servico.php';

$method = $_SERVER['REQUEST_METHOD'];
$dao = new ServicoDAO();
$user = $GLOBALS['user'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $servico = $dao->buscarPorId($_GET['id']);
            if ($servico) {
                echo json_encode($servico);
            } else {
                http_response_code(404);
                echo json_encode(['erro' => 'Serviço não encontrado']);
            }
        } else {
            $filtros = [
                'categoria_id' => $_GET['categoria'] ?? null,
                'prestador_id' => $_GET['prestador'] ?? null,
                'busca' => $_GET['busca'] ?? null
            ];
            $servicos = $dao->listar($filtros);
            echo json_encode($servicos);
        }
        break;
        
    case 'POST':
        // Apenas prestadores podem criar serviços
        if ($user['tipo'] !== 'prestador' && $user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Apenas prestadores podem criar serviços']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $servico = new Servico(
            $user['tipo'] === 'admin' ? ($data['prestador_id'] ?? $user['user_id']) : $user['user_id'],
            $data['categoria_id'],
            $data['titulo'],
            $data['preco']
        );
        $servico->setDescricao($data['descricao'] ?? '');
        $servico->setDuracao($data['duracao'] ?? 60);
        $servico->setFotos($data['fotos'] ?? '');
        
        if ($dao->salvar($servico)) {
            http_response_code(201);
            echo json_encode(['mensagem' => 'Serviço criado', 'id' => $servico->getId()]);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao criar serviço']);
        }
        break;
        
    case 'PUT':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID não fornecido']);
            exit;
        }
        
        $servicoExistente = $dao->buscarPorId($id);
        if (!$servicoExistente) {
            http_response_code(404);
            echo json_encode(['erro' => 'Serviço não encontrado']);
            exit;
        }
        
        // Verificar permissão (dono do serviço ou admin)
        if ($servicoExistente['prestador_id'] != $user['user_id'] && $user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Você não tem permissão para editar este serviço']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $servico = new Servico();
        $servico->setId($id);
        $servico->setCategoriaId($data['categoria_id'] ?? $servicoExistente['categoria_id']);
        $servico->setTitulo($data['titulo'] ?? $servicoExistente['titulo']);
        $servico->setDescricao($data['descricao'] ?? $servicoExistente['descricao']);
        $servico->setPreco($data['preco'] ?? $servicoExistente['preco']);
        $servico->setDuracao($data['duracao'] ?? $servicoExistente['duracao']);
        $servico->setFotos($data['fotos'] ?? $servicoExistente['fotos']);
        $servico->setAtivo($data['ativo'] ?? $servicoExistente['ativo']);
        
        if ($dao->salvar($servico)) {
            echo json_encode(['mensagem' => 'Serviço atualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar']);
        }
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID não fornecido']);
            exit;
        }
        
        $servicoExistente = $dao->buscarPorId($id);
        if (!$servicoExistente) {
            http_response_code(404);
            echo json_encode(['erro' => 'Serviço não encontrado']);
            exit;
        }
        
        // Verificar permissão
        if ($servicoExistente['prestador_id'] != $user['user_id'] && $user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Permissão negada']);
            exit;
        }
        
        if ($dao->excluir($id)) {
            echo json_encode(['mensagem' => 'Serviço excluído']);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido']);
}
?>
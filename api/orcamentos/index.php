<?php
require_once __DIR__ . '/../../dao/OrcamentoDAO.php';
require_once __DIR__ . '/../../models/Orcamento.php';

$method = $_SERVER['REQUEST_METHOD'];
$dao = new OrcamentoDAO();
$user = $GLOBALS['user'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $orcamento = $dao->buscarPorId($_GET['id']);
            if ($orcamento) {
                echo json_encode($orcamento);
            } else {
                http_response_code(404);
                echo json_encode(['erro' => 'Orçamento não encontrado']);
            }
        } else {
            if ($user['tipo'] === 'cliente') {
                $orcamentos = $dao->listarPorCliente($user['user_id']);
            } elseif ($user['tipo'] === 'prestador') {
                $orcamentos = $dao->listarPorPrestador($user['user_id']);
            } else {
                // Admin pode ver todos (implementar)
                $orcamentos = [];
            }
            echo json_encode($orcamentos);
        }
        break;
        
    case 'POST':
        if ($user['tipo'] !== 'cliente' && $user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Apenas clientes podem solicitar orçamentos']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['prestador_id'], $data['descricao'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados incompletos']);
            exit;
        }
        
        $orcamento = new Orcamento(
            $user['user_id'],
            $data['prestador_id'],
            $data['descricao']
        );
        $orcamento->setServicoId($data['servico_id'] ?? null);
        $orcamento->setFotos($data['fotos'] ?? '');
        
        if ($dao->salvar($orcamento)) {
            http_response_code(201);
            echo json_encode(['mensagem' => 'Orçamento solicitado com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao solicitar orçamento']);
        }
        break;
        
    case 'PUT':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID não fornecido']);
            exit;
        }
        
        $orcamentoExistente = $dao->buscarPorId($id);
        if (!$orcamentoExistente) {
            http_response_code(404);
            echo json_encode(['erro' => 'Orçamento não encontrado']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Verificar permissão e ação adequada
        if ($user['tipo'] === 'prestador' && $orcamentoExistente['prestador_id'] == $user['user_id']) {
            // Prestador respondendo
            $orcamento = new Orcamento();
            $orcamento->setId($id);
            $orcamento->setValorProposto($data['valor_proposto']);
            $orcamento->setPrazoDias($data['prazo_dias']);
            $orcamento->setObservacoesPrestador($data['observacoes'] ?? '');
            $orcamento->setStatus('respondido');
            
        } elseif ($user['tipo'] === 'cliente' && $orcamentoExistente['cliente_id'] == $user['user_id']) {
            // Cliente aprovando/recusando
            $orcamento = new Orcamento();
            $orcamento->setId($id);
            $orcamento->setStatus($data['status']); // 'aprovado' ou 'recusado'
            
        } else {
            http_response_code(403);
            echo json_encode(['erro' => 'Permissão negada']);
            exit;
        }
        
        if ($dao->salvar($orcamento)) {
            echo json_encode(['mensagem' => 'Orçamento atualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido']);
}
?>
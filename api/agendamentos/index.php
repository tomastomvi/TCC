<?php
require_once __DIR__ . '/../../dao/AgendamentoDAO.php';
require_once __DIR__ . '/../../dao/ServicoDAO.php';
require_once __DIR__ . '/../../models/Agendamento.php';

$method = $_SERVER['REQUEST_METHOD'];
$dao = new AgendamentoDAO();
$servicoDAO = new ServicoDAO();
$user = $GLOBALS['user'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $agendamento = $dao->buscarPorId($_GET['id']);
            if ($agendamento) {
                echo json_encode($agendamento);
            } else {
                http_response_code(404);
                echo json_encode(['erro' => 'Agendamento não encontrado']);
            }
        } else {
            if ($user['tipo'] === 'cliente') {
                $agendamentos = $dao->listarPorCliente($user['user_id']);
            } elseif ($user['tipo'] === 'prestador') {
                $agendamentos = $dao->listarPorPrestador($user['user_id']);
            } else {
                // Admin pode ver todos (implementar)
                $agendamentos = [];
            }
            echo json_encode($agendamentos);
        }
        break;
        
    case 'POST':
        if ($user['tipo'] !== 'cliente' && $user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Apenas clientes podem criar agendamentos']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar dados
        if (!isset($data['servico_id'], $data['data_hora'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados incompletos']);
            exit;
        }
        
        // Buscar serviço para obter prestador e duração
        $servico = $servicoDAO->buscarPorId($data['servico_id']);
        if (!$servico) {
            http_response_code(404);
            echo json_encode(['erro' => 'Serviço não encontrado']);
            exit;
        }
        
        // Verificar disponibilidade
        if (!$dao->verificarDisponibilidade($servico['prestador_id'], $data['data_hora'], $servico['duracao'])) {
            http_response_code(409);
            echo json_encode(['erro' => 'Horário indisponível']);
            exit;
        }
        
        $agendamento = new Agendamento(
            $user['user_id'],
            $data['servico_id'],
            $servico['prestador_id'],
            $data['data_hora']
        );
        $agendamento->setObservacoes($data['observacoes'] ?? '');
        $agendamento->setOrcamentoId($data['orcamento_id'] ?? null);
        
        if ($dao->salvar($agendamento)) {
            http_response_code(201);
            echo json_encode(['mensagem' => 'Agendamento criado com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao criar agendamento']);
        }
        break;
        
    case 'PUT':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID não fornecido']);
            exit;
        }
        
        $agendamentoExistente = $dao->buscarPorId($id);
        if (!$agendamentoExistente) {
            http_response_code(404);
            echo json_encode(['erro' => 'Agendamento não encontrado']);
            exit;
        }
        
        // Verificar permissão (cliente ou prestador envolvido, ou admin)
        if ($agendamentoExistente['cliente_id'] != $user['user_id'] && 
            $agendamentoExistente['prestador_id'] != $user['user_id'] && 
            $user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Permissão negada']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $agendamento = new Agendamento();
        $agendamento->setId($id);
        $agendamento->setStatus($data['status'] ?? $agendamentoExistente['status']);
        $agendamento->setObservacoes($data['observacoes'] ?? $agendamentoExistente['observacoes']);
        
        if ($dao->salvar($agendamento)) {
            echo json_encode(['mensagem' => 'Agendamento atualizado']);
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
<?php
require_once __DIR__ . '/../../dao/AvaliacaoDAO.php';
require_once __DIR__ . '/../../dao/AgendamentoDAO.php';
require_once __DIR__ . '/../../dao/UsuarioDAO.php';
require_once __DIR__ . '/../../models/Avaliacao.php';

$method = $_SERVER['REQUEST_METHOD'];
$dao = new AvaliacaoDAO();
$agendamentoDAO = new AgendamentoDAO();
$usuarioDAO = new UsuarioDAO();
$user = $GLOBALS['user'];

switch ($method) {
    case 'GET':
        if (isset($_GET['prestador_id'])) {
            $avaliacoes = $dao->listarPorPrestador($_GET['prestador_id']);
            echo json_encode($avaliacoes);
        } else {
            http_response_code(400);
            echo json_encode(['erro' => 'Informe o prestador_id']);
        }
        break;
        
    case 'POST':
        if ($user['tipo'] !== 'cliente' && $user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Apenas clientes podem avaliar']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['agendamento_id'], $data['nota'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados incompletos']);
            exit;
        }
        
        // Verificar se o agendamento existe e pertence ao cliente
        $agendamento = $agendamentoDAO->buscarPorId($data['agendamento_id']);
        if (!$agendamento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Agendamento não encontrado']);
            exit;
        }
        
        if ($agendamento['cliente_id'] != $user['user_id'] && $user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Você só pode avaliar seus próprios agendamentos']);
            exit;
        }
        
        // Verificar se já avaliou
        $avaliacaoExistente = $dao->buscarPorAgendamento($data['agendamento_id']);
        if ($avaliacaoExistente) {
            http_response_code(409);
            echo json_encode(['erro' => 'Você já avaliou este agendamento']);
            exit;
        }
        
        $avaliacao = new Avaliacao(
            $data['agendamento_id'],
            $user['user_id'],
            $agendamento['prestador_id'],
            $data['nota']
        );
        $avaliacao->setComentario($data['comentario'] ?? '');
        
        if ($dao->salvar($avaliacao)) {
            // Atualizar média do prestador
            $usuarioDAO->atualizarAvaliacaoMedia($agendamento['prestador_id']);
            
            http_response_code(201);
            echo json_encode(['mensagem' => 'Avaliação registrada']);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao registrar avaliação']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido']);
}
?>
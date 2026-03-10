<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../dao/UsuarioDAO.php';
require_once __DIR__ . '/../../dao/AgendamentoDAO.php';
require_once __DIR__ . '/../../dao/OrcamentoDAO.php';
require_once __DIR__ . '/../../dao/ServicoDAO.php';

// Verificar autenticação (o usuário já vem do middleware)
$user = $GLOBALS['user'];
$method = $_SERVER['REQUEST_METHOD'];

$dao = new UsuarioDAO();
$agendamentoDAO = new AgendamentoDAO();
$orcamentoDAO = new OrcamentoDAO();
$servicoDAO = new ServicoDAO();

switch ($method) {
    case 'GET':
        // Buscar perfil do usuário logado
        $perfil = $dao->buscarPorId($user['user_id']);
        
        if (!$perfil) {
            http_response_code(404);
            echo json_encode(['erro' => 'Usuário não encontrado']);
            exit;
        }
        
        // Remover campos sensíveis
        unset($perfil['senha']);
        
        // Adicionar estatísticas baseadas no tipo de usuário
        if ($perfil['tipo'] === 'cliente') {
            // Para clientes: últimos agendamentos e orçamentos
            $perfil['ultimos_agendamentos'] = $agendamentoDAO->listarPorCliente($user['user_id'], 5);
            $perfil['ultimos_orcamentos'] = $orcamentoDAO->listarPorCliente($user['user_id'], 5);
            
            // Contadores
            $perfil['total_agendamentos'] = $agendamentoDAO->contarPorCliente($user['user_id']);
            $perfil['total_orcamentos'] = $orcamentoDAO->contarPorCliente($user['user_id']);
            
        } elseif ($perfil['tipo'] === 'prestador') {
            // Para prestadores: serviços, próximos agendamentos, orçamentos pendentes
            $perfil['meus_servicos'] = $servicoDAO->listar(['prestador_id' => $user['user_id']]);
            $perfil['proximos_agendamentos'] = $agendamentoDAO->listarProximosPorPrestador($user['user_id'], 10);
            $perfil['orcamentos_pendentes'] = $orcamentoDAO->listarPendentesPorPrestador($user['user_id'], 10);
            
            // Contadores
            $perfil['total_agendamentos'] = $agendamentoDAO->contarPorPrestador($user['user_id']);
            $perfil['total_orcamentos_pendentes'] = $orcamentoDAO->contarPendentesPorPrestador($user['user_id']);
            $perfil['total_servicos'] = count($perfil['meus_servicos']);
            $perfil['avaliacao_media'] = $perfil['avaliacao_media'] ?? 0;
            
        } elseif ($perfil['tipo'] === 'admin') {
            // Para admin: estatísticas gerais da plataforma
            $perfil['estatisticas'] = [
                'total_usuarios' => $dao->contarTodos(),
                'total_prestadores' => $dao->contarPorTipo('prestador'),
                'total_clientes' => $dao->contarPorTipo('cliente'),
                'total_agendamentos' => $agendamentoDAO->contarTodos(),
                'total_orcamentos' => $orcamentoDAO->contarTodos(),
                'total_servicos' => $servicoDAO->contarTodos()
            ];
        }
        
        echo json_encode($perfil);
        break;
        
    case 'PUT':
        // Atualizar perfil do usuário logado
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Buscar usuário atual
        $usuarioExistente = $dao->buscarPorId($user['user_id']);
        if (!$usuarioExistente) {
            http_response_code(404);
            echo json_encode(['erro' => 'Usuário não encontrado']);
            exit;
        }
        
        // Criar objeto para atualização
        $usuario = new Usuario();
        $usuario->setId($user['user_id']);
        $usuario->setNome($data['nome'] ?? $usuarioExistente['nome']);
        $usuario->setTelefone($data['telefone'] ?? $usuarioExistente['telefone']);
        $usuario->setEndereco($data['endereco'] ?? $usuarioExistente['endereco']);
        $usuario->setLatitude($data['latitude'] ?? $usuarioExistente['latitude']);
        $usuario->setLongitude($data['longitude'] ?? $usuarioExistente['longitude']);
        $usuario->setDescricao($data['descricao'] ?? $usuarioExistente['descricao']);
        
        // Se tiver nova foto
        if (isset($data['foto'])) {
            $usuario->setFoto($data['foto']);
        }
        
        // Se tiver nova senha
        if (isset($data['senha']) && !empty($data['senha'])) {
            // Verificar senha atual se fornecida
            if (isset($data['senha_atual'])) {
                if (!password_verify($data['senha_atual'], $usuarioExistente['senha'])) {
                    http_response_code(401);
                    echo json_encode(['erro' => 'Senha atual incorreta']);
                    exit;
                }
            }
            $usuario->setSenha($data['senha']);
        } else {
            // Manter senha existente (não alterar)
            $usuario->setSenha($usuarioExistente['senha']);
        }
        
        if ($dao->salvar($usuario)) {
            // Buscar perfil atualizado
            $perfilAtualizado = $dao->buscarPorId($user['user_id']);
            unset($perfilAtualizado['senha']);
            
            echo json_encode([
                'mensagem' => 'Perfil atualizado com sucesso',
                'perfil' => $perfilAtualizado
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar perfil']);
        }
        break;
        
    case 'POST':
        // Upload de foto de perfil (multipart/form-data)
        if (!isset($_FILES['foto'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Nenhuma foto enviada']);
            exit;
        }
        
        $arquivo = $_FILES['foto'];
        
        // Validar tipo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Tipo de arquivo não permitido. Apenas imagens']);
            exit;
        }
        
        // Validar tamanho (máx 5MB)
        if ($arquivo['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['erro' => 'Arquivo muito grande. Máximo 5MB']);
            exit;
        }
        
        // Criar diretório se não existir
        $diretorio = __DIR__ . '/../../uploads/perfil/' . $user['user_id'] . '/';
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }
        
        // Gerar nome único
        $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'perfil_' . time() . '.' . $extensao;
        $caminhoCompleto = $diretorio . $nomeArquivo;
        
        if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            $url = '/uploads/perfil/' . $user['user_id'] . '/' . $nomeArquivo;
            
            // Atualizar foto no banco
            $usuario = new Usuario();
            $usuario->setId($user['user_id']);
            $usuario->setFoto($url);
            $usuario->setSenha(''); // Não alterar senha
            
            if ($dao->salvar($usuario)) {
                echo json_encode([
                    'mensagem' => 'Foto de perfil atualizada',
                    'url' => $url
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['erro' => 'Erro ao atualizar foto no banco']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao fazer upload']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido']);
}
?>
<?php
require_once __DIR__ . '/../../dao/UsuarioDAO.php';
require_once __DIR__ . '/../../models/Usuario.php';

$method = $_SERVER['REQUEST_METHOD'];
$dao = new UsuarioDAO();

// Verificar permissões (apenas admin pode listar todos usuários)
$user = $GLOBALS['user']; // vindo do middleware

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Buscar um usuário específico
            $usuario = $dao->buscarPorId($_GET['id']);
            if ($usuario) {
                echo json_encode($usuario);
            } else {
                http_response_code(404);
                echo json_encode(['erro' => 'Usuário não encontrado']);
            }
        } elseif (isset($_GET['tipo']) && $_GET['tipo'] === 'prestadores') {
            // Listar prestadores com filtros
            $categoria_id = $_GET['categoria'] ?? null;
            $latitude = $_GET['lat'] ?? null;
            $longitude = $_GET['lng'] ?? null;
            $raio = $_GET['raio'] ?? 10;
            $prestadores = $dao->listarPrestadores($categoria_id, $latitude, $longitude, $raio);
            echo json_encode($prestadores);
        } else {
            // Se for admin, pode listar todos
            if ($user['tipo'] === 'admin') {
                // Implementar listagem geral
                echo json_encode(['mensagem' => 'Listagem geral (implementar)']);
            } else {
                http_response_code(403);
                echo json_encode(['erro' => 'Acesso negado']);
            }
        }
        break;
        
    case 'PUT':
        // Atualizar perfil do próprio usuário
        $id = $_GET['id'] ?? $user['user_id'];
        
        // Verificar permissão (só pode editar a si mesmo ou admin)
        if ($id != $user['user_id'] && $user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Você só pode editar seu próprio perfil']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $usuario = new Usuario();
        $usuario->setId($id);
        $usuario->setNome($data['nome'] ?? '');
        $usuario->setTelefone($data['telefone'] ?? '');
        $usuario->setEndereco($data['endereco'] ?? '');
        $usuario->setLatitude($data['latitude'] ?? null);
        $usuario->setLongitude($data['longitude'] ?? null);
        $usuario->setDescricao($data['descricao'] ?? '');
        
        if ($dao->salvar($usuario)) {
            echo json_encode(['mensagem' => 'Perfil atualizado com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar perfil']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido']);
}
?>
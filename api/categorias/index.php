<?php
require_once __DIR__ . '/../../dao/CategoriaDAO.php';
require_once __DIR__ . '/../../models/Categoria.php';

$method = $_SERVER['REQUEST_METHOD'];
$dao = new CategoriaDAO();
$user = $GLOBALS['user'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $categoria = $dao->buscarPorId($_GET['id']);
            if ($categoria) {
                echo json_encode($categoria);
            } else {
                http_response_code(404);
                echo json_encode(['erro' => 'Categoria não encontrada']);
            }
        } else {
            $categorias = $dao->listar();
            echo json_encode($categorias);
        }
        break;
        
    case 'POST':
        // Apenas admin pode criar categorias
        if ($user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Acesso negado']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $categoria = new Categoria($data['nome'], $data['icone'] ?? '');
        $categoria->setDescricao($data['descricao'] ?? '');
        
        if ($dao->salvar($categoria)) {
            http_response_code(201);
            echo json_encode(['mensagem' => 'Categoria criada', 'id' => $categoria->getId()]);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao criar categoria']);
        }
        break;
        
    case 'PUT':
        // Apenas admin
        if ($user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Acesso negado']);
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID não fornecido']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $categoria = new Categoria();
        $categoria->setId($id);
        $categoria->setNome($data['nome']);
        $categoria->setIcone($data['icone']);
        $categoria->setDescricao($data['descricao']);
        
        if ($dao->salvar($categoria)) {
            echo json_encode(['mensagem' => 'Categoria atualizada']);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar']);
        }
        break;
        
    case 'DELETE':
        // Apenas admin
        if ($user['tipo'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['erro' => 'Acesso negado']);
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID não fornecido']);
            exit;
        }
        
        if ($dao->excluir($id)) {
            echo json_encode(['mensagem' => 'Categoria excluída']);
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
<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../dao/UsuarioDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validações básicas
if (!isset($data['nome'], $data['email'], $data['senha'], $data['tipo'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados incompletos']);
    exit;
}

// Verificar se email já existe
$dao = new UsuarioDAO();
if ($dao->buscarPorEmail($data['email'])) {
    http_response_code(409);
    echo json_encode(['erro' => 'Email já cadastrado']);
    exit;
}

// Criar usuário
$usuario = new Usuario($data['nome'], $data['email'], $data['senha'], $data['tipo']);
$usuario->setTelefone($data['telefone'] ?? '');
$usuario->setEndereco($data['endereco'] ?? '');
$usuario->setLatitude($data['latitude'] ?? null);
$usuario->setLongitude($data['longitude'] ?? null);
$usuario->setDescricao($data['descricao'] ?? '');
$usuario->setSenha($data['senha']); // já faz hash

if ($dao->salvar($usuario)) {
    require_once __DIR__ . '/../../config/auth.php';
    $token = generateJWT($usuario->getId(), $usuario->getTipo());
    
    echo json_encode([
        'mensagem' => 'Usuário cadastrado com sucesso',
        'token' => $token,
        'usuario' => [
            'id' => $usuario->getId(),
            'nome' => $usuario->getNome(),
            'email' => $usuario->getEmail(),
            'tipo' => $usuario->getTipo()
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao cadastrar usuário']);
}
?>
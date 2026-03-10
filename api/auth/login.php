<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../dao/UsuarioDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email'], $data['senha'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Email e senha obrigatórios']);
    exit;
}

$dao = new UsuarioDAO();
$usuario = $dao->buscarPorEmail($data['email']);

if (!$usuario || !password_verify($data['senha'], $usuario['senha'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Credenciais inválidas']);
    exit;
}

$token = generateJWT($usuario['id'], $usuario['tipo']);

echo json_encode([
    'mensagem' => 'Login realizado com sucesso',
    'token' => $token,
    'usuario' => [
        'id' => $usuario['id'],
        'nome' => $usuario['nome'],
        'email' => $usuario['email'],
        'tipo' => $usuario['tipo'],
        'foto' => $usuario['foto']
    ]
]);
?>
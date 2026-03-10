<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'config/database.php';
require_once 'middlewares/auth.php';

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string e caminho base
$request = strtok($request, '?');
$base = '/servicehub-backend';
$request = str_replace($base, '', $request);
$parts = explode('/', trim($request, '/'));

$resource = $parts[0] ?? '';
$id = $parts[1] ?? null;

// Rotas públicas
if ($resource === 'auth') {
    require_once "api/auth/{$parts[1]}.php";
    exit;
}

// Rotas protegidas - verificar token
$user = verificarToken();

// Roteamento
switch ($resource) {
    case 'usuarios':
        require_once 'api/usuarios/index.php';
        break;
    case 'categorias':
        require_once 'api/categorias/index.php';
        break;
    case 'servicos':
        require_once 'api/servicos/index.php';
        break;
    case 'agendamentos':
        require_once 'api/agendamentos/index.php';
        break;
    case 'orcamentos':
        require_once 'api/orcamentos/index.php';
        break;
    case 'avaliacoes':
        require_once 'api/avaliacoes/index.php';
        break;
    case 'upload':
        require_once 'api/upload/index.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['erro' => 'Recurso não encontrado']);
}
?>
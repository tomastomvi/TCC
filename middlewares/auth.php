<?php
require_once __DIR__ . '/../config/auth.php';

function verificarToken() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['erro' => 'Token não fornecido']);
        exit;
    }
    
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $payload = validateJWT($token);
    
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['erro' => 'Token inválido ou expirado']);
        exit;
    }
    
    return $payload;
}
?>
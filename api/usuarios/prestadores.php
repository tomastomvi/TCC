<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../dao/UsuarioDAO.php';

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

// Receber parâmetros de filtro
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
$latitude = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$longitude = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$raio = isset($_GET['raio']) ? intval($_GET['raio']) : 10; // raio em km
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : null;
$limite = isset($_GET['limite']) ? intval($_GET['limite']) : 50;
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina - 1) * $limite;

$dao = new UsuarioDAO();
$prestadores = $dao->listarPrestadores($categoria_id, $latitude, $longitude, $raio, $busca, $limite, $offset);

// Contar total para paginação
$total = $dao->contarPrestadores($categoria_id, $latitude, $longitude, $raio, $busca);

echo json_encode([
    'pagina' => $pagina,
    'limite' => $limite,
    'total' => $total,
    'total_paginas' => ceil($total / $limite),
    'dados' => $prestadores
]);
?>
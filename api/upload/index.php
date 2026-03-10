<?php
require_once __DIR__ . '/../../utils/upload.php';

$user = $GLOBALS['user'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

// Verificar se arquivo foi enviado
if (!isset($_FILES['arquivo'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Nenhum arquivo enviado']);
    exit;
}

$arquivo = $_FILES['arquivo'];
$tipo = $_POST['tipo'] ?? 'foto'; // foto, documento, etc

// Configurações
$diretorio = __DIR__ . '/../../uploads/' . $user['user_id'] . '/';
if (!file_exists($diretorio)) {
    mkdir($diretorio, 0777, true);
}

$extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
$nomeArquivo = uniqid() . '.' . $extensao;
$caminhoCompleto = $diretorio . $nomeArquivo;

// Validar tipo (apenas imagens)
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

if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
    $url = '/uploads/' . $user['user_id'] . '/' . $nomeArquivo;
    echo json_encode([
        'mensagem' => 'Upload realizado com sucesso',
        'url' => $url,
        'nome' => $nomeArquivo
    ]);
} else {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao fazer upload']);
}
?>
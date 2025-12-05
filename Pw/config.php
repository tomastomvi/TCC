<?php
// config.php
$host = 'localhost';
$dbname = 'sistema_orçamento';
$usuario = 'root';
$senha = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $senha);
    die("Erro de conexão: " . $e->getMessage());
}

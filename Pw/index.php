<?php
// index.php
require_once 'config.php';

$url = $_GET['url'] ?? 'orcamento/listar';
$parts = explode('/', $url);

$controller = $parts[0] ?? 'orcamento';
$action = $parts[1] ?? 'listar';
$id = $parts[2] ?? null;

// Carregar controller
$controllerClass = ucfirst($controller) . 'Controller';
require_once "controller/$controllerClass.php";

$controllerInstance = new $controllerClass();

// Chamar método
if($id) {
    $controllerInstance->$action($id);
} else {
    $controllerInstance->$action();
}
?>
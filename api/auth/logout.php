<?php
// Como usamos JWT stateless, o logout é feito no cliente (descartando o token)
echo json_encode(['mensagem' => 'Logout efetuado (descarte o token no cliente)']);
?>
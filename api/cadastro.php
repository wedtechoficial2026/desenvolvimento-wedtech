<?php
// POST api/cadastro.php - Cria uma conta { nome, email, senha } e já abre a sessão do painel
require_once __DIR__ . '/_api.php';
exigir_metodo('POST');

$corpo = ler_corpo();
$res = register_user(
    (string)($corpo['nome'] ?? ''),
    (string)($corpo['email'] ?? ''),
    (string)($corpo['senha'] ?? '')
);

if (!$res['success']) {
    responder(['ok' => false, 'erro' => $res['message']], $res['code'] ?? 422);
}

responder([
    'ok'       => true,
    'usuario'  => usuario_publico($res['user']),
    'redirect' => 'painel.php',
], 201);

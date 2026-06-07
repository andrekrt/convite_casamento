<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/n8n.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/flash.php';

if ($n8nWebhookUrl === '') {
    setFlash(
        'error',
        'Webhook não configurado',
        'A URL do webhook do n8n não foi configurada no servidor.'
    );

    header('Location: painel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    exit('Convite inválido.');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM convites
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id,
]);

$convite = $stmt->fetch();

if (!$convite) {
    exit('Convite não encontrado.');
}

if (empty($convite['telefone'])) {
    setFlash(
        'error',
        'Telefone não cadastrado',
        'Este convite não possui telefone cadastrado. Edite o convite e informe um WhatsApp.'
    );

    header('Location: painel.php');
    exit;
}

$linkConvite = $conviteBaseUrl . urlencode($convite['token']);

$telefoneLimpo = preg_replace('/\D/', '', $convite['telefone']);

/*
    Se o telefone estiver no formato brasileiro sem DDI,
    adicionamos 55 automaticamente.
*/
if (strlen($telefoneLimpo) === 10 || strlen($telefoneLimpo) === 11) {
    $telefoneLimpo = '55' . $telefoneLimpo;
}

if (strlen($telefoneLimpo) < 12) {
    setFlash(
        'error',
        'Telefone inválido',
        'Verifique o número cadastrado antes de enviar o convite.'
    );

    header('Location: painel.php');
    exit;
}

$mensagem = "Olá, {$convite['nome_convite']}! 💍\n\n"
    . "Com muita alegria, André & Monica convidam vocês para celebrar esse momento tão especial.\n\n"
    . "Acesse o convite pelo link abaixo e confirme sua presença:\n"
    . $linkConvite . "\n\n"
    . "Pedimos, por gentileza, que a confirmação seja feita até o dia 05/07/2026, "
    . "pois precisamos organizar com carinho todos os detalhes da recepção, como buffet, lugares e estrutura para os convidados.\n\n"
    . "Caso não haja confirmação até essa data, entenderemos que não será possível comparecer.\n\n"
    . "Será uma grande alegria ter vocês conosco nesse dia tão especial!";
$payload = [
    'convite_id' => (int) $convite['id'],
    'nome_convite' => $convite['nome_convite'],
    'telefone' => $telefoneLimpo,
    'telefone_original' => $convite['telefone'],
    'link_convite' => $linkConvite,
    'adultos_permitidos' => (int) $convite['adultos_permitidos'],
    'criancas_permitidas' => (int) $convite['criancas_permitidas'],
    'mensagem' => $mensagem,
];

$ch = curl_init($n8nWebhookUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 20,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($response === false || $httpCode < 200 || $httpCode >= 300) {
    setFlash(
        'error',
        'Erro no envio',
        'Não foi possível enviar o convite para o n8n. Verifique o webhook e tente novamente.'
    );

    header('Location: painel.php');
    exit;
}

$stmtEnvio = $pdo->prepare("
    UPDATE convites
    SET
        enviado_em = NOW(),
        total_envios = total_envios + 1,
        atualizado_em = NOW()
    WHERE id = :id
");

$stmtEnvio->execute([
    ':id' => $convite['id'],
]);

setFlash(
    'success',
    'Convite enviado!',
    'O convite foi enviado para o webhook com sucesso.'
);

header('Location: painel.php');
exit;

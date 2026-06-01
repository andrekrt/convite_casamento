<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$token = trim($_POST['token'] ?? '');

if ($token === '') {
    http_response_code(400);
    exit('Token do convite não informado.');
}

$stmtConvite = $pdo->prepare("
    SELECT *
    FROM convites
    WHERE token = :token
    LIMIT 1
");

$stmtConvite->execute([
    ':token' => $token,
]);

$convite = $stmtConvite->fetch();

if (!$convite) {
    http_response_code(404);
    exit('Convite não encontrado.');
}

$adultosConfirmados = (int) ($_POST['adultos_confirmados'] ?? 0);
$criancasConfirmadas = (int) ($_POST['criancas_confirmadas'] ?? 0);
$mensagem = trim($_POST['mensagem'] ?? '');

$adultosPermitidos = (int) $convite['adultos_permitidos'];
$criancasPermitidas = (int) $convite['criancas_permitidas'];

if ($adultosConfirmados < 0 || $adultosConfirmados > $adultosPermitidos) {
    http_response_code(400);
    exit('Quantidade de adultos inválida.');
}

if ($criancasConfirmadas < 0 || $criancasConfirmadas > $criancasPermitidas) {
    http_response_code(400);
    exit('Quantidade de crianças inválida.');
}

$totalPermitido = $adultosPermitidos + $criancasPermitidas;
$totalConfirmado = $adultosConfirmados + $criancasConfirmadas;

if ($totalConfirmado === 0) {
    $status = 'recusado';
} elseif ($totalConfirmado === $totalPermitido) {
    $status = 'confirmado';
} else {
    $status = 'parcial';
}

$stmtUpdate = $pdo->prepare("
    UPDATE convites
    SET
        adultos_confirmados = :adultos_confirmados,
        criancas_confirmadas = :criancas_confirmadas,
        status = :status,
        mensagem = :mensagem,
        respondido_em = NOW(),
        atualizado_em = NOW()
    WHERE id = :id
");

$stmtUpdate->execute([
    ':adultos_confirmados' => $adultosConfirmados,
    ':criancas_confirmadas' => $criancasConfirmadas,
    ':status' => $status,
    ':mensagem' => $mensagem,
    ':id' => $convite['id'],
]);

header('Location: obrigado.php?t=' . urlencode($token));
exit;
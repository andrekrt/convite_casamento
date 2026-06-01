<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    exit('Convite inválido.');
}

$stmt = $pdo->prepare("
    SELECT id
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

$stmtReset = $pdo->prepare("
    UPDATE convites
    SET
        adultos_confirmados = NULL,
        criancas_confirmadas = NULL,
        mensagem = NULL,
        status = 'pendente',
        respondido_em = NULL,
        atualizado_em = NOW()
    WHERE id = :id
");

$stmtReset->execute([
    ':id' => $id,
]);

setFlash(
    'success',
    'Resposta resetada!',
    'A confirmação foi removida e o convite voltou para pendente.'
);

header('Location: painel.php');
exit;
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

$stmtDelete = $pdo->prepare("
    DELETE FROM convites
    WHERE id = :id
");

$stmtDelete->execute([
    ':id' => $id,
]);

setFlash(
    'success',
    'Convite excluído!',
    'O convite foi removido com sucesso.'
);

header('Location: painel.php');
exit;
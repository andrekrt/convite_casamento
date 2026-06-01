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
$nomeConvite = trim($_POST['nome_convite'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$adultosPermitidos = (int) ($_POST['adultos_permitidos'] ?? 0);
$criancasPermitidas = (int) ($_POST['criancas_permitidas'] ?? 0);
$observacaoAdmin = trim($_POST['observacao_admin'] ?? '');

if ($id <= 0) {
    exit('Convite inválido.');
}

if ($nomeConvite === '') {
    exit('O nome do convite é obrigatório.');
}

if ($adultosPermitidos < 0 || $adultosPermitidos > 20) {
    exit('Quantidade de adultos inválida.');
}

if ($criancasPermitidas < 0 || $criancasPermitidas > 20) {
    exit('Quantidade de crianças inválida.');
}

if (($adultosPermitidos + $criancasPermitidas) < 1) {
    exit('O convite precisa permitir pelo menos uma pessoa.');
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

/*
    Regra importante:
    Se o convite já foi respondido, não podemos deixar os confirmados
    maiores que os novos limites permitidos.
*/
$adultosConfirmados = $convite['adultos_confirmados'];
$criancasConfirmadas = $convite['criancas_confirmadas'];

if ($adultosConfirmados !== null && (int) $adultosConfirmados > $adultosPermitidos) {
    exit('Não é possível reduzir adultos permitidos abaixo da quantidade já confirmada.');
}

if ($criancasConfirmadas !== null && (int) $criancasConfirmadas > $criancasPermitidas) {
    exit('Não é possível reduzir crianças permitidas abaixo da quantidade já confirmada.');
}

$stmtUpdate = $pdo->prepare("
    UPDATE convites
    SET
        nome_convite = :nome_convite,
        telefone = :telefone,
        adultos_permitidos = :adultos_permitidos,
        criancas_permitidas = :criancas_permitidas,
        observacao_admin = :observacao_admin,
        atualizado_em = NOW()
    WHERE id = :id
");

$stmtUpdate->execute([
    ':nome_convite' => $nomeConvite,
    ':telefone' => $telefone,
    ':adultos_permitidos' => $adultosPermitidos,
    ':criancas_permitidas' => $criancasPermitidas,
    ':observacao_admin' => $observacaoAdmin,
    ':id' => $id,
]);

setFlash(
    'success',
    'Convite atualizado!',
    'As alterações foram salvas com sucesso.'
);

header('Location: painel.php');
exit;
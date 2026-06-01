<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: convite-criar.php');
    exit;
}

$nomeConvite = trim($_POST['nome_convite'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$adultosPermitidos = (int) ($_POST['adultos_permitidos'] ?? 0);
$criancasPermitidas = (int) ($_POST['criancas_permitidas'] ?? 0);
$observacaoAdmin = trim($_POST['observacao_admin'] ?? '');

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

$token = bin2hex(random_bytes(16));

$stmt = $pdo->prepare("
    INSERT INTO convites
    (
        nome_convite,
        telefone,
        token,
        adultos_permitidos,
        criancas_permitidas,
        observacao_admin
    )
    VALUES
    (
        :nome_convite,
        :telefone,
        :token,
        :adultos_permitidos,
        :criancas_permitidas,
        :observacao_admin
    )
");

$stmt->execute([
    ':nome_convite' => $nomeConvite,
    ':telefone' => $telefone,
    ':token' => $token,
    ':adultos_permitidos' => $adultosPermitidos,
    ':criancas_permitidas' => $criancasPermitidas,
    ':observacao_admin' => $observacaoAdmin,
]);

setFlash(
    'success',
    'Convite criado!',
    'O novo convite foi cadastrado com sucesso.'
);

header('Location: painel.php');
exit;
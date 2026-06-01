<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$tempoMaximoInativo = 60 * 60 * 2;

if (isset($_SESSION['ultimo_acesso'])) {
    $tempoInativo = time() - (int) $_SESSION['ultimo_acesso'];

    if ($tempoInativo > $tempoMaximoInativo) {
        $_SESSION = [];
        session_destroy();

        header('Location: login.php?expirado=1');
        exit;
    }
}

$_SESSION['ultimo_acesso'] = time();
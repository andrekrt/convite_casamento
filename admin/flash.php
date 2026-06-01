<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function setFlash(string $tipo, string $titulo, string $mensagem): void
{
    $_SESSION['flash'] = [
        'tipo' => $tipo,
        'titulo' => $titulo,
        'mensagem' => $mensagem,
    ];
}

function getFlash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);

    return $flash;
}
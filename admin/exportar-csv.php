<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query("
    SELECT
        nome_convite,
        telefone,
        adultos_permitidos,
        criancas_permitidas,
        adultos_confirmados,
        criancas_confirmadas,
        status,
        enviado_em,
        total_envios,
        visualizado_em,
        respondido_em,
        mensagem,
        observacao_admin,
        criado_em
    FROM convites
    ORDER BY nome_convite ASC
");

$convites = $stmt->fetchAll();

$filename = 'convites-andre-monica-' . date('Y-m-d-H-i-s') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

/*
    BOM UTF-8 para o Excel reconhecer acentos corretamente.
*/
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Nome do convite',
    'Telefone',
    'Adultos permitidos',
    'Crianças permitidas',
    'Adultos confirmados',
    'Crianças confirmadas',
    'Total confirmado',
    'Status',
    'Enviado em',
    'Total de envios',
    'Visualizado em',
    'Respondido em',
    'Mensagem',
    'Observação interna',
    'Criado em',
], ';');

foreach ($convites as $convite) {
    $adultosConfirmados = $convite['adultos_confirmados'] !== null
        ? (int) $convite['adultos_confirmados']
        : 0;

    $criancasConfirmadas = $convite['criancas_confirmadas'] !== null
        ? (int) $convite['criancas_confirmadas']
        : 0;

    $totalConfirmado = $adultosConfirmados + $criancasConfirmadas;

    fputcsv($output, [
        $convite['nome_convite'],
        $convite['telefone'],
        (int) $convite['adultos_permitidos'],
        (int) $convite['criancas_permitidas'],
        $convite['adultos_confirmados'],
        $convite['criancas_confirmadas'],
        $convite['respondido_em'] ? $totalConfirmado : '',
        $convite['status'],
        formatarDataCsv($convite['enviado_em']),
        (int) $convite['total_envios'],
        formatarDataCsv($convite['visualizado_em']),
        formatarDataCsv($convite['respondido_em']),
        $convite['mensagem'],
        $convite['observacao_admin'],
        formatarDataCsv($convite['criado_em']),
    ], ';');
}

fclose($output);
exit;

function formatarDataCsv(?string $data): string
{
    if (empty($data)) {
        return '';
    }

    return date('d/m/Y H:i', strtotime($data));
}
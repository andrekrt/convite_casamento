<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$token = trim($_GET['t'] ?? '');

$convite = null;

if ($token !== '') {
    $stmt = $pdo->prepare("
        SELECT *
        FROM convites
        WHERE token = :token
        LIMIT 1
    ");

    $stmt->execute([
        ':token' => $token,
    ]);

    $convite = $stmt->fetch();
}

$nomeConvite = $convite['nome_convite'] ?? 'convidado especial';
$adultos = (int) ($convite['adultos_confirmados'] ?? 0);
$criancas = (int) ($convite['criancas_confirmadas'] ?? 0);
$total = $adultos + $criancas;
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <title>Confirmação recebida</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: #fff8f3;
            color: #3d2523;
            font-family: Georgia, 'Times New Roman', serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }

        .box {
            max-width: 560px;
            background: #ffffff;
            border-radius: 28px;
            padding: 48px 32px;
            box-shadow: 0 20px 50px rgba(61, 37, 35, 0.12);
            border: 1px solid #f3e3d3;
        }

        .ornament {
            color: #c9a45c;
            font-size: 36px;
            margin-bottom: 16px;
        }

        h1 {
            font-size: 42px;
            margin: 0 0 16px;
            color: #7b2d35;
            font-weight: normal;
        }

        p {
            font-size: 18px;
            line-height: 1.7;
        }

        .summary {
            background: #fff8f3;
            border: 1px solid #f3e3d3;
            border-radius: 18px;
            padding: 18px;
            margin-top: 22px;
        }

        a {
            display: inline-block;
            margin-top: 24px;
            background: #7b2d35;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 999px;
        }
    </style>
</head>

<body>

    <div class="box">
        <div class="ornament">❦</div>

        <h1>Confirmação recebida!</h1>

        <p>
            Obrigado, <strong><?= htmlspecialchars($nomeConvite) ?></strong>.
            Sua resposta foi registrada com sucesso.
        </p>

        <?php if ($convite): ?>
            <div class="summary">
                <p>
                    Adultos confirmados:
                    <strong><?= $adultos ?></strong>
                </p>

                <p>
                    Crianças confirmadas:
                    <strong><?= $criancas ?></strong>
                </p>

                <p>
                    Total:
                    <strong><?= $total ?></strong>
                </p>
            </div>

            <a href="convite.php?t=<?= urlencode($token) ?>">Voltar ao convite</a>
        <?php else: ?>
            <a href="index.php">Voltar ao convite</a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const totalConfirmado = <?= (int) $total ?>;

        if (totalConfirmado === 0) {
            Swal.fire({
                title: 'Resposta registrada!',
                text: 'Sentiremos sua falta, mas agradecemos por responder ao nosso convite.',
                icon: 'info',
                confirmButtonText: 'Ver convite',
                confirmButtonColor: '#7b2d35'
            });
        } else {
            Swal.fire({
                title: 'Presença confirmada!',
                text: 'Sua confirmação foi registrada com sucesso. Estamos felizes em celebrar esse momento com você.',
                icon: 'success',
                confirmButtonText: 'Ver convite',
                confirmButtonColor: '#7b2d35'
            });
        }
    </script>
</body>

</html>
<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$token = trim($_GET['t'] ?? '');

if ($token === '') {
    http_response_code(404);
    exit('Convite não encontrado. Verifique se o link foi copiado corretamente.');
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
    exit('Convite não encontrado. Verifique se o link foi copiado corretamente.');
}

if ($convite['visualizado_em'] === null) {
    $stmtView = $pdo->prepare("
        UPDATE convites
        SET visualizado_em = NOW()
        WHERE id = :id
    ");

    $stmtView->execute([
        ':id' => $convite['id'],
    ]);
}

$stmt = $pdo->query("
    SELECT id, titulo, subtitulo, descricao, valor, link_pagamento
    FROM presentes
    WHERE ativo = 1
    ORDER BY valor ASC
");

$presentes = $stmt->fetchAll();

$mapsIgreja = 'https://maps.google.com';
$mapsRecepcao = 'https://maps.google.com';

$adultosPermitidos = (int) $convite['adultos_permitidos'];
$criancasPermitidas = (int) $convite['criancas_permitidas'];

$adultosConfirmados = $convite['adultos_confirmados'];
$criancasConfirmadas = $convite['criancas_confirmadas'];

$jaRespondeu = $convite['respondido_em'] !== null;
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <title>Andre & Monica | Convite de Casamento</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Convite de casamento de Andre e Monica. Confirme sua presença e celebre conosco esse momento especial.">

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <header class="hero">
        <div class="hero-overlay">
            <p class="pre-title">Com alegria, convidamos você para o nosso casamento</p>

            <h1>Andre <span>&</span> Monica</h1>

            <p class="date">01 de Agosto de 2026</p>

            <div id="contador" class="contador">
                Carregando contagem...
            </div>

            <div class="hero-buttons">
                <a href="#confirmar" class="btn btn-primary">Confirmar presença</a>
                <a href="#presentes" class="btn btn-outline">Lista de presentes</a>
            </div>
        </div>
    </header>

    <main>

        <section class="section intro">
            <div class="ornament">❦</div>

            <h2>Vamos nos casar!</h2>

            <p>
                Com a bênção de Deus e o amor de nossas famílias, convidamos você
                para celebrar conosco o início da nossa nova vida.
            </p>

            <p>
                Será uma alegria imensa ter sua presença neste dia tão especial,
                compartilhando conosco cada momento dessa união.
            </p>

            <p class="guest-name">
                Este convite é especial para:
                <strong><?= htmlspecialchars($convite['nome_convite']) ?></strong>
            </p>

            <strong>Com carinho, Andre & Monica</strong>
        </section>

        <section class="section event-info">
            <h2>O grande dia</h2>

            <div class="info-grid">
                <article class="info-card">
                    <span>01</span>
                    <h3>Celebração</h3>
                    <p>01 de Agosto de 2026, às 16h</p>
                    <p>Igreja de Sant'Ana e São Joaquim</p>

                    <a href="<?= htmlspecialchars($mapsIgreja) ?>" target="_blank" class="small-link">
                        Ver trajeto
                    </a>
                </article>

                <article class="info-card">
                    <span>02</span>
                    <h3>Recepção</h3>
                    <p>Após a celebração</p>
                    <p>Prime House</p>

                    <a href="<?= htmlspecialchars($mapsRecepcao) ?>" target="_blank" class="small-link">
                        Ver trajeto
                    </a>
                </article>
            </div>
        </section>

        <section id="confirmar" class="section confirm-section">
            <h2>Confirme sua presença</h2>

            <p>
                Para nos ajudar na organização, confirme abaixo a quantidade de adultos
                e crianças que irão comparecer.
            </p>

            <div class="invite-limit-box">
                <h3><?= htmlspecialchars($convite['nome_convite']) ?></h3>

                <p>
                    Este convite contempla:
                    <strong><?= $adultosPermitidos ?> adulto(s)</strong>

                    <?php if ($criancasPermitidas > 0): ?>
                        e <strong><?= $criancasPermitidas ?> criança(s)</strong>
                    <?php endif; ?>
                </p>

                <?php if ($jaRespondeu): ?>
                    <p class="already-answered">
                        Você já respondeu este convite.
                        Caso necessário, é possível atualizar a confirmação abaixo.
                    </p>
                <?php endif; ?>
            </div>

            <form action="confirmar.php" method="POST" class="form-rsvp" id="formConfirmacao">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <label>
                    Adultos que irão comparecer
                    <select name="adultos_confirmados" required>
                        <?php for ($i = 0; $i <= $adultosPermitidos; $i++): ?>
                            <option value="<?= $i ?>"
                                <?= $adultosConfirmados !== null && (int) $adultosConfirmados === $i ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </label>

                <label>
                    Crianças que irão comparecer
                    <select name="criancas_confirmadas" required>
                        <?php for ($i = 0; $i <= $criancasPermitidas; $i++): ?>
                            <option value="<?= $i ?>"
                                <?= $criancasConfirmadas !== null && (int) $criancasConfirmadas === $i ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </label>

                <label>
                    Mensagem para os noivos
                    <textarea name="mensagem" maxlength="500" placeholder="Deixe uma mensagem especial"><?= htmlspecialchars($convite['mensagem'] ?? '') ?></textarea>
                </label>

                <button type="submit">Enviar confirmação</button>
            </form>
        </section>

        <section id="presentes" class="section gift-section">
            <h2>Presentes</h2>

            <p>
                Sua presença é o nosso maior presente. Mas, se desejar nos presentear,
                preparamos algumas opções com muito carinho.
            </p>

            <div class="gift-grid">
                <?php foreach ($presentes as $presente): ?>
                    <article class="gift-card">
                        <div class="gift-badge">
                            <?= htmlspecialchars($presente['titulo']) ?>
                        </div>

                        <h3><?= htmlspecialchars($presente['subtitulo']) ?></h3>

                        <p><?= htmlspecialchars($presente['descricao']) ?></p>

                        <strong>
                            R$ <?= number_format((float) $presente['valor'], 2, ',', '.') ?>
                        </strong>

                        <?php if (!empty($presente['link_pagamento']) && $presente['link_pagamento'] !== '#'): ?>
                            <a href="<?= htmlspecialchars($presente['link_pagamento']) ?>" target="_blank" class="btn btn-primary">
                                Presentear
                            </a>
                        <?php else: ?>
                            <button class="btn btn-disabled" type="button" disabled>
                                Link em breve
                            </button>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section gallery">
            <h2>Retratos do nosso amor</h2>

            <div class="photo-grid">
                <img src="assets/img/casal-1.jpg" alt="Andre e Monica">
                <img src="assets/img/casal-2.jpg" alt="Andre e Monica">
                <img src="assets/img/casal-3.jpg" alt="Andre e Monica">
            </div>
        </section>

        <section class="section closing">
            <div class="ornament">❦</div>

            <h2>Esperamos você</h2>

            <p>
                Para viver conosco esse dia tão sonhado.
                Sua presença tornará esse momento ainda mais especial.
            </p>

            <strong>Andre & Monica</strong>
        </section>

    </main>

    <footer>
        <p>Andre & Monica — 01.08.2026</p>
    </footer>

    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const formConfirmacao = document.getElementById('formConfirmacao');

        if (formConfirmacao) {
            formConfirmacao.addEventListener('submit', function(event) {
                event.preventDefault();

                const adultos = document.querySelector('[name="adultos_confirmados"]').value;
                const criancas = document.querySelector('[name="criancas_confirmadas"]').value;
                const total = Number(adultos) + Number(criancas);

                let texto = '';

                if (total === 0) {
                    texto = 'Você está confirmando que não poderá comparecer ao casamento.';
                } else {
                    texto = `Você está confirmando ${adultos} adulto(s) e ${criancas} criança(s).`;
                }

                Swal.fire({
                    title: 'Confirmar resposta?',
                    text: texto,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, confirmar',
                    cancelButtonText: 'Voltar',
                    confirmButtonColor: '#7b2d35',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formConfirmacao.submit();
                    }
                });
            });
        }
    </script>
</body>

</html>
<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Convite inválido.');
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <title>Editar convite | Painel Administrativo</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f7f2ed;
            color: #3d2523;
            font-family: Arial, sans-serif;
        }

        header {
            background: #7b2d35;
            color: #fff;
            padding: 24px 32px;
        }

        header h1 {
            margin: 0;
            font-size: 26px;
        }

        main {
            padding: 32px;
            max-width: 760px;
            margin: 0 auto;
        }

        .box {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.06);
        }

        label {
            display: block;
            margin-bottom: 18px;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            margin-top: 8px;
            padding: 13px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            font-family: Arial, sans-serif;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        button,
        .btn {
            display: inline-block;
            background: #7b2d35;
            color: #fff;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            padding: 12px 18px;
            cursor: pointer;
            font-size: 15px;
        }

        .btn-secondary {
            background: #c9a45c;
            color: #3d2523;
        }

        .info {
            background: #fff8f3;
            border: 1px solid #f3e3d3;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 22px;
            font-size: 14px;
        }

        small {
            display: block;
            margin-top: 6px;
            color: #777;
            font-weight: normal;
        }

        @media (max-width: 640px) {
            main {
                padding: 20px;
            }

            .grid {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Editar convite</h1>
</header>

<main>
    <div class="box">
        <div class="info">
            <strong>Status atual:</strong>
            <?= htmlspecialchars($convite['status']) ?>
            <br>

            <strong>Token:</strong>
            <?= htmlspecialchars($convite['token']) ?>
        </div>

        <form action="convite-atualizar.php" method="POST">
            <input type="hidden" name="id" value="<?= (int) $convite['id'] ?>">

            <label>
                Nome do convite
                <input 
                    type="text" 
                    name="nome_convite" 
                    required 
                    maxlength="150"
                    value="<?= htmlspecialchars($convite['nome_convite']) ?>"
                >

                <small>Esse nome aparecerá no convite individual.</small>
            </label>

            <label>
                Telefone / WhatsApp
                <input 
                    type="text" 
                    name="telefone" 
                    id="telefone"
                    maxlength="15"
                    placeholder="(99) 99999-9999"
                    value="<?= htmlspecialchars($convite['telefone'] ?? '') ?>"
                >
            </label>

            <div class="grid">
                <label>
                    Adultos permitidos
                    <input 
                        type="number" 
                        name="adultos_permitidos" 
                        min="0" 
                        max="20" 
                        value="<?= (int) $convite['adultos_permitidos'] ?>" 
                        required
                    >
                </label>

                <label>
                    Crianças permitidas
                    <input 
                        type="number" 
                        name="criancas_permitidas" 
                        min="0" 
                        max="20" 
                        value="<?= (int) $convite['criancas_permitidas'] ?>" 
                        required
                    >
                </label>
            </div>

            <label>
                Observação interna
                <textarea 
                    name="observacao_admin" 
                    maxlength="1000"
                ><?= htmlspecialchars($convite['observacao_admin'] ?? '') ?></textarea>
            </label>

            <div class="actions">
                <button type="submit">Salvar alterações</button>
                <a href="painel.php" class="btn btn-secondary">Voltar ao painel</a>
            </div>
        </form>
    </div>
</main>

<script>
function aplicarMascaraTelefone(campo) {
    let valor = campo.value.replace(/\D/g, '');

    if (valor.length > 11) {
        valor = valor.slice(0, 11);
    }

    if (valor.length <= 10) {
        valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2');
        valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2');
        valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
    }

    campo.value = valor;
}

const telefoneInput = document.getElementById('telefone');

if (telefoneInput) {
    telefoneInput.addEventListener('input', function () {
        aplicarMascaraTelefone(this);
    });
}
</script>

</body>
</html>
<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: painel.php');
    exit;
}

$erro = '';
if (($_GET['expirado'] ?? '') === '1') {
    $erro = 'Sua sessão expirou. Faça login novamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } else {
        $stmt = $pdo->prepare("
            SELECT *
            FROM administradores
            WHERE email = :email
            AND ativo = 1
            LIMIT 1
        ");

        $stmt->execute([
            ':email' => $email,
        ]);

        $admin = $stmt->fetch();

        if ($admin && password_verify($senha, $admin['senha'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            $_SESSION['admin_email'] = $admin['email'];

            header('Location: painel.php');
            exit;
        }

        $erro = 'E-mail ou senha inválidos.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <title>Login | Painel Administrativo</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f7f2ed;
            color: #3d2523;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin: 0 0 8px;
            color: #7b2d35;
            font-size: 28px;
            text-align: center;
        }

        p {
            margin: 0 0 26px;
            text-align: center;
            color: #666;
        }

        label {
            display: block;
            margin-bottom: 18px;
            font-weight: bold;
        }

        input {
            width: 100%;
            margin-top: 8px;
            padding: 13px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            width: 100%;
            background: #7b2d35;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 13px 18px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #5f2028;
        }

        .error {
            background: #f4cccc;
            color: #8a1f1f;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 18px;
            text-align: center;
            font-size: 14px;
        }

        .footer {
            margin-top: 22px;
            text-align: center;
            font-size: 13px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h1>Andre & Monica</h1>
        <p>Painel administrativo</p>

        <?php if ($erro !== ''): ?>
            <div class="error">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>
                E-mail
                <input type="email" name="email" required autofocus>
            </label>

            <label>
                Senha
                <input type="password" name="senha" required>
            </label>

            <button type="submit">Entrar</button>
        </form>

        <div class="footer">
            Acesso restrito
        </div>
    </div>

</body>

</html>
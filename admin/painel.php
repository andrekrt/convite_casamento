<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/flash.php';

$stmtResumo = $pdo->query("
    SELECT
        COUNT(*) AS total_convites,

        SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) AS total_pendentes,
        SUM(CASE WHEN status = 'confirmado' THEN 1 ELSE 0 END) AS total_confirmados,
        SUM(CASE WHEN status = 'parcial' THEN 1 ELSE 0 END) AS total_parciais,
        SUM(CASE WHEN status = 'recusado' THEN 1 ELSE 0 END) AS total_recusados,

        COALESCE(SUM(adultos_permitidos), 0) AS adultos_cadastrados,
        COALESCE(SUM(criancas_permitidas), 0) AS criancas_cadastradas,

        COALESCE(SUM(adultos_confirmados), 0) AS adultos_confirmados,
        COALESCE(SUM(criancas_confirmadas), 0) AS criancas_confirmadas
    FROM convites
");

$resumo = $stmtResumo->fetch();

$statusFiltro = $_GET['status'] ?? '';
$busca = trim($_GET['busca'] ?? '');
$page = (int) ($_GET['page'] ?? 1);

if ($page < 1) {
    $page = 1;
}

$porPagina = 10;
$offset = ($page - 1) * $porPagina;

$where = [];
$params = [];

if (in_array($statusFiltro, ['pendente', 'confirmado', 'parcial', 'recusado'], true)) {
    $where[] = 'status = :status';
    $params[':status'] = $statusFiltro;
}

if ($busca !== '') {
    $where[] = '(nome_convite LIKE :busca OR telefone LIKE :busca)';
    $params[':busca'] = '%' . $busca . '%';
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$stmtTotal = $pdo->prepare("
    SELECT COUNT(*)
    FROM convites
    {$whereSql}
");

$stmtTotal->execute($params);

$totalRegistros = (int) $stmtTotal->fetchColumn();

$totalPaginas = (int) ceil($totalRegistros / $porPagina);

$stmtConvites = $pdo->prepare("
    SELECT *
    FROM convites
    {$whereSql}
    ORDER BY criado_em DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $stmtConvites->bindValue($key, $value);
}

$stmtConvites->bindValue(':limit', $porPagina, PDO::PARAM_INT);
$stmtConvites->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmtConvites->execute();

$convites = $stmtConvites->fetchAll();

$flash = getFlash();

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <title>Painel Administrativo | Convites</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/painel.css">
</head>

<body>

    <header>
        <div class="header-content">
            <div>
                <h1>Painel Administrativo — Andre & Monica</h1>
                <p>Olá, <?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Administrador') ?></p>
            </div>

            <a href="logout.php" class="logout-btn btn-text-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sair</span>
            </a>
        </div>
    </header>

    <main>
        <div class="top-actions">
            <a href="convite-criar.php" class="btn btn-text-icon">
                <i class="fa-solid fa-plus"></i>
                <span>Novo convite</span>
            </a>

            <a href="exportar-csv.php" class="btn btn-secondary btn-text-icon">
                <i class="fa-solid fa-file-csv"></i>
                <span>Exportar CSV</span>
            </a>
        </div>

        <form method="GET" class="filters">
            <div>
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="pendente" <?= $statusFiltro === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="confirmado" <?= $statusFiltro === 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                    <option value="parcial" <?= $statusFiltro === 'parcial' ? 'selected' : '' ?>>Parcial</option>
                    <option value="recusado" <?= $statusFiltro === 'recusado' ? 'selected' : '' ?>>Recusado</option>
                </select>
            </div>

            <div>
                <label>Buscar</label>
                <input
                    type="text"
                    name="busca"
                    value="<?= htmlspecialchars($busca) ?>"
                    placeholder="Nome ou telefone">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn">Filtrar</button>
                <a href="painel.php" class="btn btn-secondary">Limpar</a>
            </div>
        </form>

        <section class="cards">
            <div class="card">
                <span><i class="fa-solid fa-envelope"></i> Total de convites</span>
                <strong><?= (int) ($resumo['total_convites'] ?? 0) ?></strong>
            </div>

            <div class="card">
                <span><i class="fa-solid fa-user-group"></i> Adultos cadastrados</span>
                <strong><?= (int) ($resumo['adultos_cadastrados'] ?? 0) ?></strong>
            </div>

            <div class="card">
                <span><i class="fa-solid fa-child"></i> Crianças cadastradas</span>
                <strong><?= (int) ($resumo['criancas_cadastradas'] ?? 0) ?></strong>
            </div>

            <div class="card">
                <span><i class="fa-solid fa-people-group"></i> Total geral cadastrado</span>
                <strong>
                    <?= (int) ($resumo['adultos_cadastrados'] ?? 0) + (int) ($resumo['criancas_cadastradas'] ?? 0) ?>
                </strong>
            </div>

            <div class="card">
                <span><i class="fa-solid fa-clock"></i> Pendentes</span>
                <strong><?= (int) ($resumo['total_pendentes'] ?? 0) ?></strong>
            </div>

            <div class="card">
                <span><i class="fa-solid fa-circle-check"></i> Confirmados</span>
                <strong><?= (int) ($resumo['total_confirmados'] ?? 0) ?></strong>
            </div>

            <div class="card">
                <span><i class="fa-solid fa-circle-half-stroke"></i> Parciais</span>
                <strong><?= (int) ($resumo['total_parciais'] ?? 0) ?></strong>
            </div>

            <div class="card">
                <span><i class="fa-solid fa-circle-xmark"></i> Recusados</span>
                <strong><?= (int) ($resumo['total_recusados'] ?? 0) ?></strong>
            </div>

            <div class="card">
                <span><i class="fa-solid fa-user-tie"></i> Adultos confirmados</span>
                <strong><?= (int) ($resumo['adultos_confirmados'] ?? 0) ?></strong>
            </div>

            <div class="card">
                <span><i class="fa-solid fa-child-reaching"></i> Crianças confirmadas</span>
                <strong><?= (int) ($resumo['criancas_confirmadas'] ?? 0) ?></strong>
            </div>

            <div class="card">
                <span><i class="fa-solid fa-users"></i> Total geral confirmado</span>
                <strong>
                    <?= (int) ($resumo['adultos_confirmados'] ?? 0) + (int) ($resumo['criancas_confirmadas'] ?? 0) ?>
                </strong>
            </div>
        </section>

        <div class="result-info">
            Exibindo <?= count($convites) ?> de <?= $totalRegistros ?> convite(s)
            <?php if ($totalPaginas > 1): ?>
                — Página <?= $page ?> de <?= $totalPaginas ?>
            <?php endif; ?>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Convite</th>
                        <th>Telefone</th>
                        <th>Permitidos</th>
                        <th>Confirmados</th>
                        <th>Status</th>
                        <th>Visualizado</th>
                        <th>Respondido</th>
                        <th>Envio</th>
                        <th>Observação</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($convites)): ?>
                        <tr>
                            <td colspan="10">Nenhum convite cadastrado ainda.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($convites as $convite): ?>
                        <?php
                        $link = $conviteBaseUrl . urlencode($convite['token']);
                        $status = $convite['status'];
                        ?>

                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($convite['nome_convite']) ?></strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($convite['telefone'] ?? '-') ?>
                            </td>

                            <td>
                                <?= (int) $convite['adultos_permitidos'] ?> adulto(s)<br>
                                <?= (int) $convite['criancas_permitidas'] ?> criança(s)
                            </td>

                            <td>
                                <?php if ($convite['respondido_em']): ?>
                                    <?= (int) $convite['adultos_confirmados'] ?> adulto(s)<br>
                                    <?= (int) $convite['criancas_confirmadas'] ?> criança(s)
                                <?php else: ?>
                                    <span class="muted">Ainda não respondeu</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="status status-<?= htmlspecialchars($status) ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>

                            <td>
                                <?= $convite['visualizado_em'] ? date('d/m/Y H:i', strtotime($convite['visualizado_em'])) : '-' ?>
                            </td>

                            <td>
                                <?= $convite['respondido_em'] ? date('d/m/Y H:i', strtotime($convite['respondido_em'])) : '-' ?>
                            </td>

                            <td>
                                <?php if (!empty($convite['enviado_em'])): ?>
                                    <?= date('d/m/Y H:i', strtotime($convite['enviado_em'])) ?>
                                    <br>
                                    <span class="muted">
                                        <?= (int) $convite['total_envios'] ?> envio(s)
                                    </span>
                                <?php else: ?>
                                    <span class="muted">Ainda não enviado</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= nl2br(htmlspecialchars($convite['observacao_admin'] ?? '-')) ?>
                            </td>

                            <td>
                                <div class="actions-table">
                                    <button
                                        type="button"
                                        class="btn btn-copy btn-icon"
                                        data-link="<?= htmlspecialchars($link, ENT_QUOTES) ?>"
                                        onclick="copiarLinkAcao(this)"
                                        title="Copiar link individual"
                                        aria-label="Copiar link individual">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>

                                    <a
                                        href="convite-editar.php?id=<?= (int) $convite['id'] ?>"
                                        class="btn btn-secondary btn-icon"
                                        title="Editar convite"
                                        aria-label="Editar convite">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form
                                        action="convite-enviar.php"
                                        method="POST"
                                        class="form-enviar"
                                        data-nome="<?= htmlspecialchars($convite['nome_convite'], ENT_QUOTES) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $convite['id'] ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-send btn-icon"
                                            title="Enviar convite"
                                            aria-label="Enviar convite">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </button>
                                    </form>

                                    <?php if (!empty($convite['respondido_em'])): ?>
                                        <form
                                            action="convite-resetar.php"
                                            method="POST"
                                            class="form-resetar"
                                            data-nome="<?= htmlspecialchars($convite['nome_convite'], ENT_QUOTES) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $convite['id'] ?>">

                                            <button
                                                type="submit"
                                                class="btn btn-warning btn-icon"
                                                title="Resetar resposta"
                                                aria-label="Resetar resposta">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form
                                        action="convite-excluir.php"
                                        method="POST"
                                        class="form-excluir"
                                        data-nome="<?= htmlspecialchars($convite['nome_convite'], ENT_QUOTES) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $convite['id'] ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-icon"
                                            title="Excluir convite"
                                            aria-label="Excluir convite">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <?php
                $queryBase = [
                    'status' => $statusFiltro,
                    'busca' => $busca,
                ];
                ?>

                <?php if ($page > 1): ?>
                    <?php
                    $queryAnterior = http_build_query(array_merge($queryBase, [
                        'page' => $page - 1,
                    ]));
                    ?>

                    <a href="painel.php?<?= $queryAnterior ?>" class="page-link">
                        Anterior
                    </a>
                <?php endif; ?>

                <?php
                $inicio = max(1, $page - 2);
                $fim = min($totalPaginas, $page + 2);
                ?>

                <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                    <?php
                    $queryPagina = http_build_query(array_merge($queryBase, [
                        'page' => $i,
                    ]));
                    ?>

                    <a
                        href="painel.php?<?= $queryPagina ?>"
                        class="page-link <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPaginas): ?>
                    <?php
                    $queryProxima = http_build_query(array_merge($queryBase, [
                        'page' => $page + 1,
                    ]));
                    ?>

                    <a href="painel.php?<?= $queryProxima ?>" class="page-link">
                        Próxima
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const flashMessage = <?= json_encode($flash, JSON_UNESCAPED_UNICODE) ?>;
    </script>

    <script src="../assets/js/painel.js"></script>

</body>

</html>
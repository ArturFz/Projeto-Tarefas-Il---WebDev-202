<?php

declare(strict_types=1);

require_once __DIR__ . '/class/rb.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = file_exists(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : [];

$host = $config['host'] ?? (getenv('DB_HOST') ?: '127.0.0.1');
$port = $config['port'] ?? (getenv('DB_PORT') ?: '3306');
$name = $config['name'] ?? (getenv('DB_NAME') ?: 'gestao_tarefas');
$user = $config['user'] ?? (getenv('DB_USER') ?: 'root');
$pass = $config['pass'] ?? (getenv('DB_PASS') ?: '');

R::setup(
    "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
    $user,
    $pass
);

// Tabela de contas.
R::exec('CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB');

// Mantém a tabela original e acrescenta o vínculo com o usuário.
// A coluna fica NULL para não apagar nem atribuir tarefas antigas a uma conta indevidamente.
R::exec(
    'CREATE TABLE IF NOT EXISTS tarefas (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(180) NOT NULL,
        descricao TEXT NULL,
        prazo DATE NULL,
        concluida TINYINT(1) NOT NULL DEFAULT 0,
        criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tarefas_status (concluida),
        INDEX idx_tarefas_criacao (criada_em)
    ) ENGINE=InnoDB'
);

$colunaUsuario = R::getAll("SHOW COLUMNS FROM tarefas LIKE 'usuario_id'");
if (!$colunaUsuario) {
    R::exec('ALTER TABLE tarefas ADD usuario_id INT UNSIGNED NULL AFTER id');
}
$indiceUsuario = R::getAll("SHOW INDEX FROM tarefas WHERE Key_name = 'idx_tarefas_usuario'");
if (!$indiceUsuario) {
    R::exec('ALTER TABLE tarefas ADD INDEX idx_tarefas_usuario (usuario_id)');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function usuarioAtual(): ?array
{
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['usuario_id'],
        'nome' => (string) ($_SESSION['usuario_nome'] ?? 'Usuário'),
    ];
}

function exigirLogin(): array
{
    $usuario = usuarioAtual();
    if (!$usuario) {
        header('Location: login.php');
        exit;
    }
    return $usuario;
}

function paginaInicio(string $titulo): void
{
    $usuario = usuarioAtual();
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($titulo) ?> | Foco em lista</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        <header class="site-header">
            <a class="wordmark" href="index.php">
                <span class="wordmark-box">FL</span>
                <span>Foco em lista</span>
            </a>
            <?php if ($usuario): ?>
                <div class="account-area">
                    <span class="account-name">Olá, <?= e($usuario['nome']) ?></span>
                    <a class="logout-link" href="logout.php">Sair</a>
                </div>
            <?php else: ?>
                <span class="header-note">ORGANIZAÇÃO PESSOAL</span>
            <?php endif; ?>
        </header>
        <main class="site-main">
    <?php
}

function paginaFim(): void
{
    ?>
        </main>
        <footer class="site-footer">
            <span>Foco em lista</span>
            <span>Organização simples, ação objetiva.</span>
        </footer>
    </body>
    </html>
    <?php
}

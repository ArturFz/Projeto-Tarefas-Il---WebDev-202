<?php
require_once __DIR__ . '/conexao.php';

if (usuarioAtual()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
        $erro = 'Informe um e-mail válido e sua senha.';
    } else {
        $conta = R::findOne('usuarios', 'email = ?', [$email]);
        if ($conta && password_verify($senha, $conta->senha)) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $conta->id;
            $_SESSION['usuario_nome'] = (string) $conta->nome;
            header('Location: index.php');
            exit;
        }
        $erro = 'E-mail ou senha incorretos.';
    }
}

paginaInicio('Entrar');
?>
<section class="auth-layout">
    <div class="auth-intro">
        <span class="eyebrow">SEU ESPAÇO DE ORGANIZAÇÃO</span>
        <h1>Um passo de cada vez.</h1>
        <p>Entre na sua conta para continuar de onde parou e manter suas tarefas organizadas em um só lugar.</p>
        <div class="auth-mark"><span>FL</span><small>FOCO EM LISTA</small></div>
    </div>
    <div class="auth-card">
        <span class="eyebrow">BEM-VINDO DE VOLTA</span>
        <h2>Acessar conta</h2>
        <p class="auth-description">Informe seus dados para abrir sua lista pessoal.</p>

        <?php if ($erro): ?><div class="feedback error"><?= e($erro) ?></div><?php endif; ?>

        <form method="post" class="auth-form" autocomplete="on">
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" maxlength="190" value="<?= e($email) ?>" required autocomplete="email" placeholder="voce@email.com">
            </div>
            <div class="field">
                <label for="senha">Senha</label>
                <input id="senha" name="senha" type="password" required autocomplete="current-password" placeholder="Sua senha">
            </div>
            <button class="submit-button" type="submit">Entrar na minha lista</button>
        </form>
        <p class="auth-switch">Ainda não tem conta? <a href="cadastro.php">Criar cadastro</a></p>
    </div>
</section>
<?php paginaFim(); ?>

<?php
require_once __DIR__ . '/conexao.php';

if (usuarioAtual()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $confirmacao = $_POST['confirmacao'] ?? '';

    if (mb_strlen($nome) < 2) {
        $erro = 'Digite seu nome (pelo menos 2 caracteres).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um endereço de e-mail válido.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha precisa ter pelo menos 8 caracteres.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'As senhas não coincidem.';
    } elseif (R::findOne('usuarios', 'email = ?', [$email])) {
        $erro = 'Já existe uma conta cadastrada com esse e-mail.';
    } else {
        $conta = R::dispense('usuarios');
        $conta->nome = $nome;
        $conta->email = $email;
        $conta->senha = password_hash($senha, PASSWORD_DEFAULT);
        R::store($conta);

        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $conta->id;
        $_SESSION['usuario_nome'] = $nome;
        header('Location: index.php');
        exit;
    }
}

paginaInicio('Criar conta');
?>
<section class="auth-layout auth-register">
    <div class="auth-intro">
        <span class="eyebrow">COMECE COM CLAREZA</span>
        <h1>Organize suas ideias. Faça acontecer.</h1>
        <p>Crie seu espaço pessoal e acompanhe suas tarefas no seu ritmo. Sua lista fica separada das listas de outras pessoas.</p>
        <div class="auth-mark"><span>FL</span><small>FOCO EM LISTA</small></div>
    </div>
    <div class="auth-card">
        <span class="eyebrow">NOVO POR AQUI?</span>
        <h2>Criar minha conta</h2>
        <p class="auth-description">Preencha os campos para começar a usar o Foco em lista.</p>

        <?php if ($erro): ?><div class="feedback error"><?= e($erro) ?></div><?php endif; ?>

        <form method="post" class="auth-form" autocomplete="on">
            <div class="field">
                <label for="nome">Nome</label>
                <input id="nome" name="nome" maxlength="120" value="<?= e($nome) ?>" required autocomplete="name" placeholder="Como podemos chamar você?">
            </div>
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" maxlength="190" value="<?= e($email) ?>" required autocomplete="email" placeholder="voce@email.com">
            </div>
            <div class="field">
                <label for="senha">Senha</label>
                <input id="senha" name="senha" type="password" minlength="8" required autocomplete="new-password" placeholder="Mínimo de 8 caracteres">
            </div>
            <div class="field">
                <label for="confirmacao">Confirmar senha</label>
                <input id="confirmacao" name="confirmacao" type="password" minlength="8" required autocomplete="new-password" placeholder="Digite a senha novamente">
            </div>
            <button class="submit-button" type="submit">Criar conta</button>
        </form>
        <p class="auth-switch">Já tem uma conta? <a href="login.php">Entrar</a></p>
    </div>
</section>
<?php paginaFim(); ?>

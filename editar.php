<?php
declare(strict_types=1);

require_once __DIR__ . '/conexao.php';
$usuario = exigirLogin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$tarefa = R::findOne('tarefas', 'id = ? AND usuario_id = ?', [$id, $usuario['id']]);

if (!$tarefa) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $prazo = trim($_POST['prazo'] ?? '');

    if (mb_strlen($titulo) < 3) {
        $erro = 'Digite um título com pelo menos 3 caracteres.';
    } elseif ($prazo !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $prazo)) {
        $erro = 'Informe uma data válida.';
    } else {
        $tarefa->titulo = $titulo;
        $tarefa->descricao = $descricao !== '' ? $descricao : null;
        $tarefa->prazo = $prazo !== '' ? $prazo : null;

        // Não altera o campo concluida: o status atual é preservado.
        R::store($tarefa);
        header('Location: index.php?editada=1');
        exit;
    }
}

paginaInicio('Editar tarefa');
?>
<section class="confirmation-page">
    <a class="back-link" href="index.php">← Voltar para a lista</a>

    <div class="confirmation-card">
        <span class="eyebrow">ATUALIZAR OBJETIVO</span>
        <h1>Editar tarefa</h1>
        <p>Altere os dados abaixo e salve suas mudanças.</p>

        <?php if ($erro): ?>
            <div class="feedback error"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="task-form">
            <input type="hidden" name="id" value="<?= (int) $tarefa->id ?>">

            <div class="field edit-field">
                <label for="titulo">Título</label>
                <input id="titulo" name="titulo" maxlength="180" minlength="3" required
                       value="<?= e($tarefa->titulo) ?>">
            </div>

            <div class="field edit-field">
                <label for="descricao">Descrição <span>opcional</span></label>
                <textarea id="descricao" name="descricao"><?= e($tarefa->descricao) ?></textarea>
            </div>

            <div class="field edit-field">
                <label for="prazo">Prazo <span>opcional</span></label>
                <input id="prazo" name="prazo" type="date"
                       value="<?= e($tarefa->prazo ? (string) $tarefa->prazo : '') ?>">
            </div>

            <div class="confirmation-actions">
                <a class="secondary-button" href="index.php">Cancelar</a>
                <button class="submit-button" type="submit">Salvar alterações</button>
            </div>
        </form>
    </div>
</section>
<?php paginaFim(); ?>

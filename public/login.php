<?php
require __DIR__ . '/../app/bootstrap.php';
if (current_user()) { redirect('index.php'); }
$pageTitle = 'Entrar';
$email = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $email = mb_substr(mb_strtolower(input($_POST, 'email')), 0, 190);
    $password = input($_POST, 'senha');
    $statement = db()->prepare('SELECT *, (bloqueado_ate IS NOT NULL AND bloqueado_ate > NOW()) AS bloqueado FROM usuarios WHERE email = :email');
    $statement->execute(['email' => $email]);
    $account = $statement->fetch();
    $verified = password_verify($password, $account['senha_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
    if ($account && !$account['bloqueado'] && $verified) {
        db()->prepare('UPDATE usuarios SET tentativas_falhas=0, bloqueado_ate=NULL WHERE id=:id')->execute(['id' => $account['id']]);
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $account['id'], 'nome' => $account['nome']];
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        redirect('index.php');
    }
    if ($account && !$account['bloqueado']) {
        // A quinta falha bloqueia a conta por 15 minutos. Após esse período a contagem recomeça.
        db()->prepare('UPDATE usuarios SET tentativas_falhas = IF(bloqueado_ate IS NOT NULL AND bloqueado_ate <= NOW(), 1, tentativas_falhas + 1), bloqueado_ate = IF(tentativas_falhas >= 5, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NULL) WHERE id=:id')->execute(['id' => $account['id']]);
    }
    $error = 'Não foi possível entrar. Verifique e-mail e senha. Após 5 tentativas incorretas, aguarde 15 minutos.';
    http_response_code(422);
}
require __DIR__ . '/../app/views/head.php';
?>
<div class="login-layout"><section class="login-story" aria-label="Editora Fonteles"><a class="brand" href="login.php"><span class="brand-icon"><?= icon('book') ?></span><span><span class="brand-small">EDITORA</span><strong>Fonteles<span class="brand-dot">.</span></strong></span></a><div class="login-story-content"><p class="eyebrow">ORGANIZAÇÃO QUE INSPIRA</p><h1>Grandes histórias.<br>Um acervo<br>bem cuidado.</h1><p>Um espaço para organizar seus títulos<br>e dar lugar aos próximos capítulos.</p><div class="editorial-books" aria-hidden="true"><div class="drawn-book b1"><span>HISTÓRIAS</span></div><div class="drawn-book b2"><span>CONHECIMENTO</span></div><div class="drawn-book b3"><span>NOVOS CAPÍTULOS</span></div><div class="book-line"></div></div></div><span class="story-foot">ACERVO EDITORIAL · <?= date('Y') ?></span></section>
<main class="login-main" id="conteudo"><div class="login-card"><span class="login-lock"><?= icon('lock') ?></span><p class="eyebrow">ÁREA ADMINISTRATIVA</p><h2>Bem-vindo de volta.</h2><p>Entre para cuidar do seu acervo.</p>
<?php if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form action="login.php" method="post" class="login-form"><?= csrf_field() ?><div class="field"><label for="email">E-mail</label><input type="email" name="email" id="email" value="<?= e($email) ?>" placeholder="seu@email.com" maxlength="190" autocomplete="username" required></div><div class="field"><label for="senha">Senha</label><div class="password-field"><input type="password" name="senha" id="senha" maxlength="128" autocomplete="current-password" required><button type="button" class="password-toggle" id="password-toggle" aria-controls="senha" aria-pressed="false">Mostrar</button></div></div><button class="button primary login-submit">Entrar no acervo <span aria-hidden="true">→</span></button></form><p class="login-help"><?= icon('lock') ?>Acesso exclusivo à administração da editora.</p></div><footer>© <?= date('Y') ?> Editora Fonteles</footer></main></div>
</body></html>

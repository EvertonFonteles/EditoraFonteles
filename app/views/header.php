<?php require __DIR__ . '/head.php'; ?>
<div class="app-layout">
    <aside class="sidebar" aria-label="Navegação principal">
        <a class="brand" href="index.php" aria-label="Editora Fonteles, início"><span class="brand-icon"><?= icon('book') ?></span><span><span class="brand-small">EDITORA</span><strong>Fonteles<span class="brand-dot">.</span></strong></span></a>
        <div class="nav-label">ÁREA ADMINISTRATIVA</div>
        <nav><a class="nav-link <?= ($activePage ?? '') === 'acervo' ? 'active' : '' ?>" href="index.php" <?= ($activePage ?? '') === 'acervo' ? 'aria-current="page"' : '' ?>><?= icon('grid') ?>Visão do acervo</a><a class="nav-link <?= ($activePage ?? '') === 'novo' ? 'active' : '' ?>" href="livro.php" <?= ($activePage ?? '') === 'novo' ? 'aria-current="page"' : '' ?>><?= icon('plus') ?>Cadastrar livro</a></nav>
        <div class="sidebar-note"><?= icon('book') ?><p>Cada livro, uma possibilidade.</p><span>Seu acervo bem cuidado,<br>seu próximo capítulo mais simples.</span></div>
        <div class="account"><span class="avatar"><?= e(mb_strtoupper(mb_substr($user['nome'], 0, 1))) ?></span><div><strong><?= e($user['nome']) ?></strong><small>Administrador</small></div><form action="sair.php" method="post"><?= csrf_field() ?><button class="icon-button" aria-label="Sair da conta" title="Sair da conta"><?= icon('logout') ?></button></form></div>
    </aside>
    <div class="workspace">
        <header class="topbar"><span>GESTÃO DO ACERVO</span><span class="topbar-right"><span class="status-dot"></span>Ambiente administrativo</span></header>
        <main id="conteudo" class="main-content">
        <?php if (isset($_SESSION['flash'])): $notification = $_SESSION['flash']; unset($_SESSION['flash']); ?>
            <div class="notice <?= e($notification['type']) ?>" role="status"><?= icon('check') ?><span><?= e($notification['message']) ?></span></div>
        <?php endif; ?>

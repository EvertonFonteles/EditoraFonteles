<?php
declare(strict_types=1);

// Copie para local.php somente se sua instalação usar dados diferentes.
// Nunca publique local.php: ele está no .gitignore.
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'fonteles_acervo',
    'username' => 'root',
    'password' => '', // Padrão local do XAMPP. Em hospedagem, use usuário e senha próprios.
];

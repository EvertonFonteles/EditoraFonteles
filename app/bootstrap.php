<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('Cache-Control: no-store');

session_name('fonteles_session');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
    'path' => '/',
]);
ini_set('session.use_strict_mode', '1');
session_start();

require __DIR__ . '/database.php';
require __DIR__ . '/functions.php';
require __DIR__ . '/books.php';

set_exception_handler(function (Throwable $exception): void {
    error_log((string) $exception);
    http_response_code(500);
    if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Não foi possível acessar o acervo. Tente novamente.']);
        return;
    }
    $pageTitle = 'Serviço indisponível';
    require __DIR__ . '/views/head.php';
    echo '<main class="error-page"><p class="eyebrow">EDITORA FONTELES</p><h1>Vamos tentar novamente?</h1><p>Não foi possível acessar o sistema. Verifique se o MySQL está iniciado e se a instalação foi concluída conforme o README.</p><a class="button primary" href="index.php">Tentar novamente</a></main></body></html>';
});

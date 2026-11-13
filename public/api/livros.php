<?php
require __DIR__ . '/../../app/bootstrap.php';
require_auth(true);
$catalog = catalog(input($_GET, 'q'), positive_id($_GET['pagina'] ?? 1));
ob_start();
require __DIR__ . '/../../app/views/catalog.php';
$html = ob_get_clean();
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['html' => $html, 'total' => $catalog['total'], 'page' => $catalog['page']], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

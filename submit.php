<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

$nome      = trim($_POST['nome']      ?? '');
$nota      = intval($_POST['nota']    ?? -1);
$comentario= trim($_POST['comentario']?? '');

if ($nota < 0 || $nota > 10) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nota inválida.']);
    exit;
}

try {
    $t = table();
    $stmt = db()->prepare("
        INSERT INTO `$t` (nome, nota, comentario)
        VALUES (:nome, :nota, :comentario)
    ");
    $stmt->execute([
        ':nome'      => mb_substr($nome, 0, 100),
        ':nota'      => $nota,
        ':comentario'=> mb_substr($comentario, 0, 2000),
    ]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erro ao salvar: ' . $e->getMessage()]);
}

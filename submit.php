<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/NotificationService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

$nome       = trim($_POST['nome']       ?? '');
$telefone   = trim($_POST['telefone']   ?? '');
$nota       = intval($_POST['nota']     ?? -1);
$comentario = trim($_POST['comentario'] ?? '');

if ($nota < 1 || $nota > 10) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nota inválida.']);
    exit;
}

try {
    $t = table();
    $stmt = db()->prepare("
        INSERT INTO `$t` (nome, telefone, nota, comentario)
        VALUES (:nome, :telefone, :nota, :comentario)
    ");
    $stmt->execute([
        ':nome'       => mb_substr($nome, 0, 100),
        ':telefone'   => mb_substr($telefone, 0, 20),
        ':nota'       => $nota,
        ':comentario' => mb_substr($comentario, 0, 2000),
    ]);

    $id      = db()->lastInsertId();
    $review  = db()->query("SELECT * FROM `$t` WHERE id = $id")->fetch();

    if ($review) {
        $config = [
            'brevo_enabled'      => filter_var($_ENV['BREVO_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'brevo_api_key'      => $_ENV['BREVO_API_KEY']       ?? '',
            'brevo_from_email'   => $_ENV['BREVO_FROM_EMAIL']    ?? '',
            'brevo_from_name'    => $_ENV['BREVO_FROM_NAME']     ?? 'Amazônia 360',
            'notification_email' => $_ENV['NOTIFICATION_EMAIL']  ?? '',
        ];
        (new NotificationService($config))->notifyNewReview($review);
    }

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erro ao salvar: ' . $e->getMessage()]);
}

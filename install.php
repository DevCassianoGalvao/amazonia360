<?php
require_once __DIR__ . '/config.php';

$step    = $_POST['step']    ?? 'check';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? 'check';

    if ($step === 'check') {
        try {
            db();
            $message = 'Conexão com o banco de dados estabelecida com sucesso!';
            $success = true;
        } catch (PDOException $e) {
            $message = 'Erro de conexão: ' . htmlspecialchars($e->getMessage());
        }
    }

    if ($step === 'install') {
        try {
            $t = table();
            db()->exec("
                CREATE TABLE IF NOT EXISTS `$t` (
                    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    nome        VARCHAR(100)     NOT NULL DEFAULT '',
                    telefone    VARCHAR(20)      NOT NULL DEFAULT '',
                    nota        TINYINT UNSIGNED NOT NULL,
                    comentario  TEXT             NOT NULL DEFAULT '',
                    criado_em   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            db()->exec("
                ALTER TABLE `$t`
                ADD COLUMN IF NOT EXISTS telefone VARCHAR(20) NOT NULL DEFAULT '' AFTER nome;
            ");
            $message = "Tabela `$t` criada/atualizada. Instalação concluída!";
            $success = true;
        } catch (PDOException $e) {
            $message = 'Erro ao criar tabela: ' . htmlspecialchars($e->getMessage());
        }
    }
}

$envExists = file_exists(__DIR__ . '/.env');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Instalador — Amazônia 360</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #0a0a0a;
      color: #f5f5f0;
      font-family: 'Space Grotesk', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 16px;
    }
    .card {
      background: #111;
      border: 1px solid #222;
      border-radius: 20px;
      padding: 48px 52px;
      width: 100%;
      max-width: 560px;
    }
    .badge {
      display: inline-block;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      background: rgba(45,122,79,.2);
      color: #3fa066;
      border: 1px solid #2d7a4f;
      border-radius: 6px;
      padding: 3px 10px;
      margin-bottom: 20px;
    }
    h1 { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
    .sub { font-size: 14px; color: #6a6a6a; margin-bottom: 36px; line-height: 1.6; }
    .steps { counter-reset: step; list-style: none; margin-bottom: 36px; }
    .steps li {
      counter-increment: step;
      display: flex;
      gap: 16px;
      align-items: flex-start;
      margin-bottom: 16px;
      font-size: 14px;
      color: #ccc;
      line-height: 1.5;
    }
    .steps li::before {
      content: counter(step);
      min-width: 26px;
      height: 26px;
      border-radius: 50%;
      background: #1e1e1e;
      border: 1px solid #333;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 700;
      color: #3fa066;
      flex-shrink: 0;
    }
    code {
      background: #1a1a1a;
      border: 1px solid #2a2a2a;
      border-radius: 6px;
      padding: 2px 7px;
      font-family: monospace;
      font-size: 13px;
      color: #c8a84b;
    }
    .alert {
      border-radius: 10px;
      padding: 14px 18px;
      margin-bottom: 24px;
      font-size: 14px;
      font-weight: 500;
      line-height: 1.5;
    }
    .alert.ok  { background: rgba(45,122,79,.15);  border: 1px solid #2d7a4f; color: #5ecf8a; }
    .alert.err { background: rgba(180,40,40,.15);  border: 1px solid #7a2d2d; color: #f08080; }
    .alert.warn{ background: rgba(200,168,75,.1);  border: 1px solid #6b5a24; color: #c8a84b; }
    .btns { display: flex; gap: 12px; flex-wrap: wrap; }
    button {
      flex: 1;
      padding: 14px 20px;
      border: none;
      border-radius: 10px;
      font-family: 'Space Grotesk', sans-serif;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: background .18s, transform .12s;
    }
    button:active { transform: scale(.98); }
    .btn-primary { background: #2d7a4f; color: #fff; }
    .btn-primary:hover { background: #3fa066; }
    .btn-secondary { background: #1e1e1e; color: #ccc; border: 1px solid #2e2e2e; }
    .btn-secondary:hover { background: #252525; }
    .go-link {
      display: block;
      text-align: center;
      margin-top: 28px;
      font-size: 14px;
      color: #3fa066;
      text-decoration: none;
      font-weight: 500;
    }
    .go-link:hover { text-decoration: underline; }
    .env-status {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: <?= $envExists ? '#5ecf8a' : '#f08080' ?>;
      margin-bottom: 28px;
    }
    .dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
  </style>
</head>
<body>
<div class="card">
  <span class="badge">Instalador</span>
  <h1>Amazônia 360 — NPS</h1>
  <p class="sub">Configure a conexão com o banco de dados e crie a tabela necessária para o sistema funcionar.</p>

  <div class="env-status">
    <span class="dot"></span>
    <?= $envExists ? 'Arquivo <code>.env</code> encontrado' : 'Arquivo <code>.env</code> não encontrado — crie a partir do <code>.env.example</code>' ?>
  </div>

  <?php if (!$envExists): ?>
  <div class="alert warn">
    Renomeie o arquivo <code>.env.example</code> para <code>.env</code> e preencha as credenciais do banco antes de continuar.
  </div>
  <?php endif; ?>

  <ul class="steps">
    <li>Faça upload de todos os arquivos via Gerenciador de Arquivos do cPanel.</li>
    <li>Renomeie <code>.env.example</code> → <code>.env</code> e preencha as credenciais MySQL.</li>
    <li>Clique em <strong>Testar Conexão</strong> para verificar se o banco responde.</li>
    <li>Clique em <strong>Criar Tabela</strong> para criar a tabela <code><?= htmlspecialchars(table()) ?></code>.</li>
    <li>Acesse <code>index.php</code> — o sistema estará pronto.</li>
  </ul>

  <?php if ($message): ?>
  <div class="alert <?= $success ? 'ok' : 'err' ?>"><?= $message ?></div>
  <?php endif; ?>

  <form method="POST" class="btns">
    <button type="submit" name="step" value="check" class="btn-secondary" <?= !$envExists ? 'disabled' : '' ?>>
      Testar Conexão
    </button>
    <button type="submit" name="step" value="install" class="btn-primary" <?= !$envExists ? 'disabled' : '' ?>>
      Criar Tabela
    </button>
  </form>

  <?php if ($success && $step === 'install'): ?>
  <a href="index.php" class="go-link">→ Ir para a página principal</a>
  <?php endif; ?>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/config.php';

// Fetch last 20 reviews
$reviews = [];
$dbError = false;

try {
    $t = table();
    $stmt = db()->query("
        SELECT nome, nota, comentario, criado_em
        FROM `$t`
        ORDER BY criado_em DESC
        LIMIT 20
    ");
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $dbError = true;
}

function score_class(int $n): string {
    if ($n >= 9) return 'score-promoter';
    if ($n >= 7) return 'score-passive';
    return 'score-detractor';
}

function format_date(string $dt): string {
    $d = new DateTime($dt);
    return $d->format('d/m/Y \à\s H\hi');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Amazônia 360 — Avaliação</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>

<div class="container">

  <!-- Logo -->
  <header class="site-header">
    <img src="assets/logo amazonia360.png" alt="Amazônia 360" />
  </header>

  <!-- Reviews -->
  <p class="section-title">Últimas avaliações</p>

  <?php if ($dbError): ?>
    <div class="no-reviews">
      Não foi possível carregar as avaliações. Verifique as configurações do banco de dados.
    </div>
  <?php elseif (empty($reviews)): ?>
    <div class="no-reviews">
      Nenhuma avaliação ainda. Seja o primeiro a compartilhar sua experiência!
    </div>
  <?php else: ?>
    <div class="reviews-list">
      <?php foreach ($reviews as $r): ?>
      <div class="review-card">
        <div class="review-score <?= score_class((int)$r['nota']) ?>">
          <?= (int)$r['nota'] ?>
        </div>
        <div class="review-name">
          <?= $r['nome'] !== '' ? htmlspecialchars($r['nome']) : 'Anônimo' ?>
        </div>
        <div class="review-date">
          <?= format_date($r['criado_em']) ?>
        </div>
        <?php if ($r['comentario'] !== ''): ?>
        <div class="review-comment">
          "<?= nl2br(htmlspecialchars($r['comentario'])) ?>"
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Form -->
  <p class="section-title">Deixe sua avaliação</p>

  <div class="form-card">
    <h1 class="form-title">Como foi sua experiência na&nbsp;Amazônia&nbsp;360?</h1>
    <p class="form-sub">Sua avaliação nos ajuda a melhorar. Leva menos de 1 minuto.</p>

    <form id="npsForm" novalidate>

      <p class="nps-label">Qual a probabilidade de você nos recomendar?</p>
      <div class="nps-grid" id="npsGrid"></div>
      <div class="nps-hints">
        <span>Não recomendaria</span>
        <span>Recomendaria com certeza</span>
      </div>

      <div class="field">
        <label for="nomeInput">Seu nome <span class="label-optional">(NÃO É OBRIGATÓRIO)</span></label>
        <input type="text" id="nomeInput" name="nome" placeholder="Como podemos te chamar?" maxlength="100" />
      </div>

      <div class="field">
        <label for="comentarioInput">Comentário livre</label>
        <textarea id="comentarioInput" name="comentario" placeholder="Conte o que achou, o que mais gostou ou o que podemos melhorar…"></textarea>
      </div>

      <button type="submit" class="submit-btn" id="submitBtn" disabled>
        Enviar avaliação
      </button>

      <div class="form-feedback" id="formFeedback"></div>
    </form>
  </div>

</div>

<script src="assets/script.js"></script>
</body>
</html>

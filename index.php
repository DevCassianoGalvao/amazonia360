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
    <form id="npsForm" novalidate>

      <!-- NPS Atendimento -->
      <p class="nps-question">Em uma escala de 1 a 10, como você avalia o atendimento que recebeu hoje?</p>
      <div class="nps-grid" id="npsGrid"></div>
      <div class="nps-hints">
        <span>Muito insatisfeito</span>
        <span>Muito satisfeito</span>
      </div>

      <!-- NPS Retorno -->
      <p class="nps-question">O quanto você voltaria a comprar na loja?</p>
      <div class="nps-grid" id="npsGridRetorno"></div>
      <div class="nps-hints">
        <span>Nunca voltaria</span>
        <span>Com certeza voltaria</span>
      </div>

      <!-- Encontrou o produto -->
      <div class="field">
        <label>Encontrou o produto que procurava?</label>
        <div class="toggle-row">
          <button type="button" class="toggle-btn toggle-sim" id="btnSim">Sim</button>
          <button type="button" class="toggle-btn toggle-nao" id="btnNao">Não</button>
        </div>
        <div class="field-slide" id="produtoField">
          <input type="text" id="produtoInput" name="produto_nao_encontrado" placeholder="Qual produto?" maxlength="200" />
        </div>
      </div>

      <!-- Sugestão / Crítica -->
      <div class="field">
        <label for="comentarioInput">Sugestão / Crítica</label>
        <textarea id="comentarioInput" name="comentario" placeholder="Tem alguma sugestão ou crítica para melhorarmos?"></textarea>
      </div>

      <!-- Identificação -->
      <div class="field">
        <p class="field-hint">Esta pesquisa é anônima. Porém, se você teve algum problema e deseja que nossa diretoria entre em contato para resolver, deixe seu nome e telefone.</p>
        <div class="field-row">
          <div class="field-col">
            <label for="nomeInput">Nome</label>
            <input type="text" id="nomeInput" name="nome" placeholder="Seu nome" maxlength="100" />
          </div>
          <div class="field-col">
            <label for="telefoneInput">Telefone</label>
            <input type="tel" id="telefoneInput" name="telefone" placeholder="(00) 00000-0000" maxlength="15" />
          </div>
        </div>
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

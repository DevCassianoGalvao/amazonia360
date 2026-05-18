document.addEventListener('DOMContentLoaded', () => {
  const grid      = document.getElementById('npsGrid');
  const submitBtn = document.getElementById('submitBtn');
  const feedback  = document.getElementById('formFeedback');
  const form      = document.getElementById('npsForm');

  let selectedScore = null;

  // Build 0–10 buttons
  for (let i = 0; i <= 10; i++) {
    const btn = document.createElement('button');
    btn.type      = 'button';
    btn.className = 'nps-btn';
    btn.textContent = i;
    btn.setAttribute('aria-label', `Nota ${i}`);
    btn.addEventListener('click', () => {
      grid.querySelectorAll('.nps-btn').forEach(b => b.classList.remove('selected'));
      btn.classList.add('selected');
      selectedScore = i;
      submitBtn.disabled = false;
    });
    grid.appendChild(btn);
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    feedback.className = 'form-feedback';

    const nome       = document.getElementById('nomeInput').value.trim();
    const comentario = document.getElementById('comentarioInput').value.trim();

    if (selectedScore === null) return;

    submitBtn.disabled = true;
    submitBtn.textContent = 'Enviando…';

    const body = new FormData();
    body.append('nota',       selectedScore);
    body.append('nome',       nome);
    body.append('comentario', comentario);

    try {
      const res  = await fetch('submit.php', { method: 'POST', body });
      const data = await res.json();

      if (data.ok) {
        feedback.textContent = 'Obrigado! Sua avaliação foi registrada com sucesso.';
        feedback.classList.add('ok');
        form.reset();
        grid.querySelectorAll('.nps-btn').forEach(b => b.classList.remove('selected'));
        selectedScore = null;
        submitBtn.disabled = true;

        // Reload reviews section after short delay
        setTimeout(() => location.reload(), 1800);
      } else {
        throw new Error(data.error || 'Erro desconhecido.');
      }
    } catch (err) {
      feedback.textContent = 'Não foi possível enviar. Tente novamente.';
      feedback.classList.add('err');
      submitBtn.disabled = false;
    }

    submitBtn.textContent = 'Enviar avaliação';
  });
});

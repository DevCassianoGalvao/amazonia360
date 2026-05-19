document.addEventListener('DOMContentLoaded', () => {
  const submitBtn  = document.getElementById('submitBtn');
  const feedback   = document.getElementById('formFeedback');
  const form       = document.getElementById('npsForm');

  let scoreAtendimento = null;
  let scoreRetorno     = null;
  let encontrouProduto = null;

  function checkSubmit() {
    submitBtn.disabled = (scoreAtendimento === null || scoreRetorno === null);
  }

  function buildNpsGrid(containerId, onSelect) {
    const grid = document.getElementById(containerId);
    for (let i = 1; i <= 10; i++) {
      const btn = document.createElement('button');
      btn.type        = 'button';
      btn.className   = 'nps-btn';
      btn.textContent = i;
      btn.setAttribute('aria-label', `Nota ${i}`);
      btn.addEventListener('click', () => {
        grid.querySelectorAll('.nps-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        onSelect(i);
        checkSubmit();
      });
      grid.appendChild(btn);
    }
  }

  buildNpsGrid('npsGrid',        (v) => { scoreAtendimento = v; });
  buildNpsGrid('npsGridRetorno', (v) => { scoreRetorno     = v; });

  // Phone mask
  const telefoneInput = document.getElementById('telefoneInput');
  if (telefoneInput) {
    telefoneInput.addEventListener('input', () => {
      let v = telefoneInput.value.replace(/\D/g, '').slice(0, 11);
      if      (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
      else if (v.length > 6)  v = v.replace(/^(\d{2})(\d{4})(\d*)$/,   '($1) $2-$3');
      else if (v.length > 2)  v = v.replace(/^(\d{2})(\d*)$/,           '($1) $2');
      else if (v.length > 0)  v = v.replace(/^(\d*)$/,                  '($1');
      telefoneInput.value = v;
    });
  }

  // Sim / Não
  const btnSim      = document.getElementById('btnSim');
  const btnNao      = document.getElementById('btnNao');
  const produtoField= document.getElementById('produtoField');

  btnSim.addEventListener('click', () => {
    encontrouProduto = 'sim';
    btnSim.classList.add('selected');
    btnNao.classList.remove('selected');
    produtoField.classList.remove('open');
  });

  btnNao.addEventListener('click', () => {
    encontrouProduto = 'nao';
    btnNao.classList.add('selected');
    btnSim.classList.remove('selected');
    produtoField.classList.add('open');
    document.getElementById('produtoInput').focus();
  });

  // Submit
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    feedback.className = 'form-feedback';

    if (scoreAtendimento === null || scoreRetorno === null) return;

    const nome                = document.getElementById('nomeInput').value.trim();
    const telefone            = document.getElementById('telefoneInput').value.trim();
    const comentario          = document.getElementById('comentarioInput').value.trim();
    const produtoNaoEncontrado= document.getElementById('produtoInput').value.trim();

    submitBtn.disabled    = true;
    submitBtn.textContent = 'Enviando…';

    const body = new FormData();
    body.append('nota',                  scoreAtendimento);
    body.append('nota_retorno',          scoreRetorno);
    body.append('encontrou_produto',     encontrouProduto ?? '');
    body.append('produto_nao_encontrado',produtoNaoEncontrado);
    body.append('nome',                  nome);
    body.append('telefone',              telefone);
    body.append('comentario',            comentario);

    try {
      const res  = await fetch('submit.php', { method: 'POST', body });
      const data = await res.json();

      if (data.ok) {
        feedback.textContent = 'Obrigado! Sua avaliação foi registrada com sucesso.';
        feedback.classList.add('ok');
        form.reset();
        document.querySelectorAll('.nps-btn').forEach(b => b.classList.remove('selected'));
        btnSim.classList.remove('selected');
        btnNao.classList.remove('selected');
        produtoField.classList.remove('open');
        scoreAtendimento = null;
        scoreRetorno     = null;
        encontrouProduto = null;
        submitBtn.disabled = true;
        setTimeout(() => location.reload(), 1800);
      } else {
        throw new Error(data.error || 'Erro desconhecido.');
      }
    } catch {
      feedback.textContent = 'Não foi possível enviar. Tente novamente.';
      feedback.classList.add('err');
      submitBtn.disabled = false;
    }

    submitBtn.textContent = 'Enviar avaliação';
  });
});

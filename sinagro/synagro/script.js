function selecionarPerfil(valor, el) {
  document.querySelectorAll('.perfil-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('perfil-hidden').value = valor;
}
 
document.addEventListener('DOMContentLoaded', () => {
  const atual = document.getElementById('perfil-hidden').value || 'operador';
  const cards = document.querySelectorAll('.perfil-card');
  const temSelecionado = [...cards].some(c => c.classList.contains('selected'));
  if (!temSelecionado) {
    const perfis = ['proprietario','gerente','operador','visualizador','admin'];
    const idx = perfis.indexOf(atual);
    if (idx >= 0 && cards[idx]) cards[idx].classList.add('selected');
  }
});
 
function avaliarSenha(v) {
  const fill = document.getElementById('forcaFill');
  const txt  = document.getElementById('forcaTexto');
  let pts = 0;
  if (v.length >= 8)         pts++;
  if (/[A-Z]/.test(v))       pts++;
  if (/[0-9]/.test(v))       pts++;
  if (/[^A-Za-z0-9]/.test(v))pts++;
 
  const cfg = [
    { w: '0%',   c: '#E8E0CC', t: 'Digite sua senha' },
    { w: '25%',  c: '#E53935', t: '🔴 Muito fraca' },
    { w: '50%',  c: '#C8973A', t: '🟡 Fraca — adicione maiúsculas e números' },
    { w: '75%',  c: '#2C5F2D', t: '🟢 Boa — adicione símbolos para fortalecer' },
    { w: '100%', c: '#1A3C2A', t: '✅ Senha forte!' },
  ];
 
  const n = v.length === 0 ? 0 : pts;
  fill.style.width      = cfg[n].w;
  fill.style.background = cfg[n].c;
  txt.textContent       = cfg[n].t;
  txt.style.color       = cfg[n].c;
 
  verificarConfirmacao();
}
 
function verificarConfirmacao() {
  const s1  = document.getElementById('senha').value;
  const s2  = document.getElementById('confirmar_senha').value;
  const el  = document.getElementById('confirmaTxt');
  const inp = document.getElementById('confirmar_senha');
  if (!s2) { el.textContent = ''; inp.classList.remove('invalido'); return; }
  if (s1 === s2) {
    el.textContent  = '✓ Senhas coincidem';
    el.style.color  = '#2C5F2D';
    inp.classList.remove('invalido');
  } else {
    el.textContent  = '✗ Senhas não coincidem';
    el.style.color  = '#A32D2D';
    inp.classList.add('invalido');
  }
}
 
document.getElementById('telefone').addEventListener('input', function () {
  let v = this.value.replace(/\D/g, '').slice(0, 11);
  if (v.length <= 10) {
    v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
  } else {
    v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
  }
  this.value = v;
});
<?php
require_once 'config/conexao.php';
require_once 'includes/auth.php';
if (usuarioLogado()) { header('Location: pages/dashboard.php'); exit; }

$erro = $sucesso = '';
$f = ['nome'=>'','email'=>'','telefone'=>'','perfil'=>'operador'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f['nome']     = limpar($_POST['nome']     ?? '');
    $f['email']    = limpar($_POST['email']    ?? '');
    $f['telefone'] = limpar($_POST['telefone'] ?? '');
    $f['perfil']   = limpar($_POST['perfil']   ?? 'operador');
    $senha         = $_POST['senha']           ?? '';
    $conf          = $_POST['confirmar_senha'] ?? '';

    if (empty($f['nome'])||empty($f['email'])||empty($senha))              $erro='Preencha todos os campos obrigatórios.';
    elseif (mb_strlen($f['nome'])<3)                                        $erro='Nome deve ter ao menos 3 caracteres.';
    elseif (!filter_var($f['email'],FILTER_VALIDATE_EMAIL))                 $erro='Formato de e-mail inválido.';
    elseif (strlen($senha)<8)                                               $erro='Senha deve ter ao menos 8 caracteres.';
    elseif (!preg_match('/[A-Z]/',$senha))                                  $erro='Senha deve ter ao menos uma letra maiúscula.';
    elseif (!preg_match('/[0-9]/',$senha))                                  $erro='Senha deve ter ao menos um número.';
    elseif ($senha!==$conf)                                                 $erro='As senhas não coincidem.';
    elseif (!in_array($f['perfil'],PERFIS_VALIDOS))                         $erro='Perfil inválido.';
    else {
        $pdo = conectar();
        $chk = $pdo->prepare("SELECT id FROM usuarios WHERE email=:e LIMIT 1");
        $chk->execute([':e'=>$f['email']]);
        if ($chk->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost'=>12]);
            $ins = $pdo->prepare("INSERT INTO usuarios(nome,email,senha_hash,perfil,telefone,ativo,email_verificado)VALUES(:n,:e,:h,:p,:t,1,0)");
            $ins->execute([':n'=>$f['nome'],':e'=>$f['email'],':h'=>$hash,':p'=>$f['perfil'],':t'=>$f['telefone']?:null]);
            $id = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO logs_sistema(usuario_id,acao,tabela_afetada,registro_id,descricao,ip_address)VALUES(:u,'criar','usuarios',:r,:d,:ip)")->execute([':u'=>$id,':r'=>$id,':d'=>"Cadastro: {$f['email']} — {$f['perfil']}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            $sucesso = 'Conta criada! Você já pode fazer login.';
            $f = ['nome'=>'','email'=>'','telefone'=>'','perfil'=>'operador'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SynAgro — Criar Conta</title>
<link rel="stylesheet" href="assets/css/synagro.css">
</head>
<body>
<div class="auth-page">
<div class="auth-card" style="width:1000px">

  <div class="auth-left">
    <span class="al-icon">🌿</span>
    <h1>SYNAGRO</h1>
    <div class="al-line"></div>
    <p>Crie sua conta e comece a gerenciar sua propriedade rural com tecnologia.</p>
    <ul>
      <li>Preencha seus dados pessoais</li>
      <li>Escolha seu perfil de acesso</li>
      <li>Crie uma senha segura</li>
      <li>Acesse o sistema imediatamente</li>
    </ul>
  </div>

  <div class="auth-right" style="padding:36px 40px">
    <h2>Criar nova conta</h2>
    <p class="auth-sub">Preencha os dados abaixo para acessar o SynAgro</p>

    <?php if ($erro):    ?><div class="alert alert-error">⚠ <?= limpar($erro) ?></div><?php endif; ?>
    <?php if ($sucesso): ?>
      <div class="alert alert-success">
        ✓ <?= limpar($sucesso) ?> <a href="login.php" style="color:var(--green);font-weight:600">Fazer login →</a>
      </div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px">
        <div class="form-group">
          <label class="form-label">Nome completo <span class="req">*</span></label>
          <input class="form-control" type="text" name="nome" value="<?= limpar($f['nome']) ?>" placeholder="João da Silva" required>
        </div>
        <div class="form-group">
          <label class="form-label">E-mail <span class="req">*</span></label>
          <input class="form-control" type="email" name="email" value="<?= limpar($f['email']) ?>" placeholder="seu@email.com.br" required>
        </div>
      </div>

      <div class="form-group" style="max-width:50%;padding-right:8px">
        <label class="form-label">Telefone / WhatsApp</label>
        <input class="form-control" type="tel" id="tel" name="telefone" value="<?= limpar($f['telefone']) ?>" placeholder="(11) 99999-9999">
      </div>

      <div class="form-group">
        <label class="form-label">Perfil de acesso <span class="req">*</span></label>
        <input type="hidden" id="perfil-val" name="perfil" value="<?= limpar($f['perfil']) ?>">
        <div class="perfil-grid">
          <?php
          $perfisOpts = [
            'proprietario'=>['🏡','Proprietário','Dono da fazenda'],
            'gerente'     =>['📋','Gerente','Gerencia operações'],
            'operador'    =>['🚜','Operador','Registra atividades'],
            'visualizador'=>['👁️','Visualizador','Apenas leitura'],
            'admin'       =>['⚙️','Admin','Acesso total'],
          ];
          foreach ($perfisOpts as $pv => [$pi,$pn,$pd]):
            $sel = $f['perfil']===$pv ? 'selected' : '';
          ?>
          <div class="perfil-card <?= $sel ?>" onclick="selP('<?= $pv ?>',this)">
            <span class="pi"><?= $pi ?></span>
            <div class="pn"><?= $pn ?></div>
            <div class="pd"><?= $pd ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px">
        <div class="form-group">
          <label class="form-label">Senha <span class="req">*</span></label>
          <input class="form-control" type="password" id="pwd" name="senha" placeholder="Mín. 8 caracteres" oninput="chkPwd(this.value)">
          <div class="strength-bar"><div class="strength-fill" id="sf" style="width:0"></div></div>
          <div class="strength-hint" id="sh">Digite sua senha</div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirmar senha <span class="req">*</span></label>
          <input class="form-control" type="password" id="pwd2" name="confirmar_senha" placeholder="Repita a senha" oninput="chkConf()">
          <div class="strength-hint" id="ch"></div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">Criar minha conta →</button>
    </form>

    <div class="auth-link">Já tem conta? <a href="login.php">Fazer login</a></div>
  </div>

</div>
</div>
<script>
function selP(v,el){document.querySelectorAll('.perfil-card').forEach(c=>c.classList.remove('selected'));el.classList.add('selected');document.getElementById('perfil-val').value=v;}
function chkPwd(v){
  const sf=document.getElementById('sf'),sh=document.getElementById('sh');
  let p=0;
  if(v.length>=8)p++;if(/[A-Z]/.test(v))p++;if(/[0-9]/.test(v))p++;if(/[^A-Za-z0-9]/.test(v))p++;
  const c=[['0%','transparent','Digite sua senha'],['20%','#F87171','Muito fraca'],['50%','#FBBF24','Fraca'],['80%','#22C55E','Boa'],['100%','#4ADE80','Forte ✓']];
  const n=v.length?p:0;
  sf.style.width=c[n][0];sf.style.background=c[n][1];sh.textContent=c[n][2];sh.style.color=c[n][1];
  chkConf();
}
function chkConf(){
  const v1=document.getElementById('pwd').value,v2=document.getElementById('pwd2').value,el=document.getElementById('ch');
  if(!v2){el.textContent='';return;}
  if(v1===v2){el.textContent='✓ Senhas coincidem';el.style.color='#4ADE80';}
  else{el.textContent='✗ Senhas não coincidem';el.style.color='#F87171';}
}
document.getElementById('tel').addEventListener('input',function(){
  let v=this.value.replace(/\D/g,'').slice(0,11);
  if(v.length<=10)v=v.replace(/(\d{2})(\d{4})(\d{0,4})/,'($1) $2-$3');
  else v=v.replace(/(\d{2})(\d{5})(\d{0,4})/,'($1) $2-$3');
  this.value=v;
});
</script>
</body>
</html>

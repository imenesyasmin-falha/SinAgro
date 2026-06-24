<?php
require_once 'config/conexao.php';
require_once 'includes/auth.php';

if (usuarioLogado()) { header('Location: pages/dashboard.php'); exit; }

$erro = $sucesso = $email = '';
if (isset($_GET['sessao']) && $_GET['sessao'] === 'expirada') $erro = 'Sua sessão expirou. Faça login novamente.';
if (isset($_GET['logout']) && $_GET['logout'] === '1') $sucesso = 'Você saiu com segurança. Até logo!';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpar($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha e-mail e senha.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Formato de e-mail inválido.';
    } else {
        $pdo  = conectar();
        $stmt = $pdo->prepare("SELECT id,nome,email,senha_hash,perfil,tentativas_login,bloqueado_ate,ativo FROM usuarios WHERE email=:e LIMIT 1");
        $stmt->execute([':e' => $email]);
        $u = $stmt->fetch();

        if (!$u || !$u['ativo']) {
            $erro = 'E-mail ou senha incorretos.';
        } elseif ($u['bloqueado_ate'] && strtotime($u['bloqueado_ate']) > time()) {
            $min  = ceil((strtotime($u['bloqueado_ate']) - time()) / 60);
            $erro = "Conta bloqueada. Tente em {$min} min.";
        } elseif (!password_verify($senha, $u['senha_hash'])) {
            $nt = $u['tentativas_login'] + 1;
            $bl = $nt >= 5 ? date('Y-m-d H:i:s', strtotime('+30 minutes')) : null;
            $pdo->prepare("UPDATE usuarios SET tentativas_login=:t,bloqueado_ate=:b WHERE id=:id")->execute([':t'=>$nt,':b'=>$bl,':id'=>$u['id']]);
            $pdo->prepare("INSERT INTO logs_sistema(usuario_id,acao,tabela_afetada,registro_id,descricao,ip_address)VALUES(:u,'login_falhou','usuarios',:r,:d,:ip)")->execute([':u'=>$u['id'],':r'=>$u['id'],':d'=>"Tentativa {$nt} falhou",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            $rest = max(0,5-$nt);
            $erro = $rest > 0 ? "Senha incorreta. {$rest} tentativa(s) restante(s)." : 'Conta bloqueada por 30 minutos.';
        } else {
            $pdo->prepare("UPDATE usuarios SET tentativas_login=0,bloqueado_ate=NULL WHERE id=:id")->execute([':id'=>$u['id']]);
            $pdo->prepare("INSERT INTO logs_sistema(usuario_id,acao,tabela_afetada,registro_id,descricao,ip_address)VALUES(:u,'login','usuarios',:r,:d,:ip)")->execute([':u'=>$u['id'],':r'=>$u['id'],':d'=>"Login OK — {$u['perfil']}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            session_regenerate_id(true);
            $_SESSION['usuario_id']    = $u['id'];
            $_SESSION['usuario_nome']  = $u['nome'];
            $_SESSION['usuario_email'] = $u['email'];
            $_SESSION['perfil']        = $u['perfil'];
            $_SESSION['login_em']      = time();
            header('Location: pages/dashboard.php'); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SynAgro — Login</title>
<link rel="stylesheet" href="assets/css/synagro.css">
</head>
<body>
<div class="auth-page">
<div class="auth-card">

  <!-- Esquerdo -->
  <div class="auth-left">
    <span class="al-icon">🌿</span>
    <h1>SYNAGRO</h1>
    <div class="al-line"></div>
    <p>Tecnologia e gestão inteligente do carbono no campo.</p>
    <ul>
      <li>Gestão de culturas e rebanho</li>
      <li>Controle de estoque e equipamentos</li>
      <li>Financeiro e relatórios</li>
      <li>Ciclo sustentável do carbono</li>
    </ul>
  </div>

  <!-- Direito -->
  <div class="auth-right">
    <h2>Bem-vindo de volta</h2>
    <p class="auth-sub">Acesse o painel da sua propriedade rural</p>

    <?php if ($erro): ?>
      <div class="alert alert-error">⚠ <?= limpar($erro) ?></div>
    <?php endif; ?>
    <?php if ($sucesso): ?>
      <div class="alert alert-success">✓ <?= limpar($sucesso) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <div class="form-group">
        <label class="form-label" for="email">E-mail <span class="req">*</span></label>
        <input class="form-control" type="email" id="email" name="email"
               value="<?= limpar($email) ?>" placeholder="seu@email.com.br" required autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label" for="senha">Senha <span class="req">*</span></label>
        <input class="form-control" type="password" id="senha" name="senha"
               placeholder="••••••••" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary btn-full">Entrar no sistema →</button>
    </form>

    <div class="auth-link">
      Não tem conta? <a href="register.php">Criar conta grátis</a>
    </div>

    <div class="dev-hint">
      <strong>Usuários de teste:</strong><br>
      admin@synagro.com · produtor@synagro.com · operador@synagro.com<br>
      <em>Senha: SynAgro@2025</em>
    </div>
  </div>

</div>
</div>
</body>
</html>

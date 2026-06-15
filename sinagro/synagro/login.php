<?php
require_once 'config/conexao.php';
require_once 'includes/auth.php';
 
if (usuarioLogado()) {
    header('Location: pages/dashboard.php');
    exit;
}
 
$erro    = '';
$sucesso = '';
$email   = '';
 
if (isset($_GET['sessao']) && $_GET['sessao'] === 'expirada') {
    $erro = 'Sua sessão expirou. Faça login novamente.';
}
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $sucesso = 'Você saiu com segurança. Até logo!';
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $email  = limpar($_POST['email']  ?? '');
    $senha  = $_POST['senha'] ?? '';       
 
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha o e-mail e a senha para continuar.';
 
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Formato de e-mail inválido.';
 
    } else {
        $pdo = conectar();
 
        $stmt = $pdo->prepare("
            SELECT id, nome, email, senha_hash, perfil,
                   tentativas_login, bloqueado_ate, ativo
            FROM usuarios
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();
 
        if (!$usuario || !$usuario['ativo']) {
            $erro = 'E-mail ou senha incorretos.';
 
        } elseif ($usuario['bloqueado_ate'] && strtotime($usuario['bloqueado_ate']) > time()) {
            $minutos = ceil((strtotime($usuario['bloqueado_ate']) - time()) / 60);
            $erro = "Conta bloqueada por excesso de tentativas. Tente novamente em {$minutos} minuto(s).";

        } elseif (!password_verify($senha, $usuario['senha_hash'])) {
 
            $novasTentativas = $usuario['tentativas_login'] + 1;
            $bloquear        = null;
 
            if ($novasTentativas >= 5) {
                $bloquear = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            }
 
            $upd = $pdo->prepare("
                UPDATE usuarios
                SET tentativas_login = :t,
                    bloqueado_ate    = :b
                WHERE id = :id
            ");
            $upd->execute([
                ':t'  => $novasTentativas,
                ':b'  => $bloquear,
                ':id' => $usuario['id'],
            ]);
 
            $log = $pdo->prepare("
                INSERT INTO logs_sistema
                    (usuario_id, acao, tabela_afetada, registro_id, descricao, ip_address)
                VALUES (:uid, 'login_falhou', 'usuarios', :rid, :desc, :ip)
            ");
            $log->execute([
                ':uid'  => $usuario['id'],
                ':rid'  => $usuario['id'],
                ':desc' => "Tentativa {$novasTentativas} de login com senha incorreta",
                ':ip'   => $_SERVER['REMOTE_ADDR'] ?? 'desconhecido',
            ]);
 
            $restantes = max(0, 5 - $novasTentativas);
            $erro = $restantes > 0
                ? "Senha incorreta. Você tem {$restantes} tentativa(s) antes do bloqueio."
                : 'Conta bloqueada por 30 minutos devido a múltiplas tentativas incorretas.';
 
        } else {
 
            $pdo->prepare("
                UPDATE usuarios
                SET tentativas_login = 0,
                    bloqueado_ate    = NULL
                WHERE id = :id
            ")->execute([':id' => $usuario['id']]);
 
            $log = $pdo->prepare("
                INSERT INTO logs_sistema
                    (usuario_id, acao, tabela_afetada, registro_id, descricao, ip_address)
                VALUES (:uid, 'login', 'usuarios', :rid, :desc, :ip)
            ");
            $log->execute([
                ':uid'  => $usuario['id'],
                ':rid'  => $usuario['id'],
                ':desc' => "Login realizado com sucesso — perfil: {$usuario['perfil']}",
                ':ip'   => $_SERVER['REMOTE_ADDR'] ?? 'desconhecido',
            ]);
            session_regenerate_id(true);
 
            $_SESSION['usuario_id']    = $usuario['id'];
            $_SESSION['usuario_nome']  = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['perfil']        = $usuario['perfil'];
            $_SESSION['login_em']      = time();
 
            header('Location: pages/dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SynAgro System — Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
 
<div class="login-wrapper">
 
  <div class="login-left">
    <div class="logo-icon">🌿</div>
    <h1>SYNAGRO</h1>
    <div class="divider-line"></div>
    <p>Tecnologia, sustentabilidade e gestão inteligente do carbono no campo.</p>
    <ul class="pillar-list">
      <li>Produção Agrícola</li>
      <li>Eficiência Energética</li>
      <li>Gestão do Carbono</li>
      <li>Controle Financeiro</li>
      <li>Estoque e Equipamentos</li>
    </ul>
  </div>
 
  <div class="login-right">
    <h2>Bem-vindo de volta</h2>
    <p class="subtitle">Faça login para acessar o SynAgro System</p>
 
    <?php if ($erro): ?>
      <div class="alert alert-erro">⚠ <?= limpar($erro) ?></div>
    <?php endif; ?>
 
    <?php if ($sucesso): ?>
      <div class="alert alert-sucesso">✓ <?= limpar($sucesso) ?></div>
    <?php endif; ?>
 
    <form method="POST" action="login.php" novalidate>
 
      <div class="form-group">
        <label for="email">E-mail</label>
        <input
          type="email"
          id="email"
          name="email"
          value="<?= limpar($email) ?>"
          placeholder="seu@email.com.br"
          required
          autocomplete="email"
        >
      </div>
 
      <div class="form-group">
        <label for="senha">Senha</label>
        <input
          type="password"
          id="senha"
          name="senha"
          placeholder="••••••••"
          required
          autocomplete="current-password"
        >
      </div>
 
      <button type="submit" class="btn-login">Entrar no Sistema</button>
 
    </form>
 
    <p style="text-align:center;margin-top:18px;font-size:13px;color:#5A5A5A">
      Não tem uma conta?
      <a href="register.php" style="color:#2C5F2D;font-weight:700;text-decoration:none">Criar conta grátis</a>
    </p>
 
    <div class="hint">
      <strong>Usuários de teste (AV2):</strong><br>
      admin@synagro.com / <em>senha: SynAgro@2025</em> → Administrador<br>
      produtor@synagro.com / <em>senha: SynAgro@2025</em> → Proprietário<br>
      operador@synagro.com / <em>senha: SynAgro@2025</em> → Operador
    </div>
 
  </div>
</div>
 
</body>
</html>

<?php
require_once 'config/conexao.php';
require_once 'includes/auth.php';
 
if (usuarioLogado()) {
    header('Location: pages/dashboard.php');
    exit;
}
 
$erro    = '';
$sucesso = '';
 
$campos = [
    'nome'     => '',
    'email'    => '',
    'telefone' => '',
    'perfil'   => 'operador',
];
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $campos['nome']     = limpar($_POST['nome']     ?? '');
    $campos['email']    = limpar($_POST['email']    ?? '');
    $campos['telefone'] = limpar($_POST['telefone'] ?? '');
    $campos['perfil']   = limpar($_POST['perfil']   ?? 'operador');
    $senha              = $_POST['senha']            ?? '';
    $confirmar          = $_POST['confirmar_senha']  ?? '';
 
    if (empty($campos['nome']) || empty($campos['email']) || empty($senha)) {
        $erro = 'Preencha todos os campos obrigatórios.';
 
    } elseif (mb_strlen($campos['nome']) < 3) {
        $erro = 'O nome deve ter pelo menos 3 caracteres.';
 
    } elseif (!filter_var($campos['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = 'Formato de e-mail inválido.';
 
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha deve ter pelo menos 8 caracteres.';
 
    } elseif (!preg_match('/[A-Z]/', $senha)) {
        $erro = 'A senha deve conter pelo menos uma letra maiúscula.';
 
    } elseif (!preg_match('/[0-9]/', $senha)) {
        $erro = 'A senha deve conter pelo menos um número.';
 
    } elseif ($senha !== $confirmar) {
        $erro = 'As senhas não coincidem. Verifique e tente novamente.';
 
    } elseif (!in_array($campos['perfil'], PERFIS_VALIDOS)) {
        $erro = 'Perfil de acesso inválido.';
 
    } else {
        $pdo = conectar();
 
        $chk = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $chk->execute([':email' => $campos['email']]);
 
        if ($chk->fetch()) {
            $erro = 'Este e-mail já está cadastrado. Tente fazer login ou use outro e-mail.';
 
        } else {
            $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
 
            $ins = $pdo->prepare("
                INSERT INTO usuarios
                    (nome, email, senha_hash, perfil, telefone, ativo, email_verificado)
                VALUES
                    (:nome, :email, :hash, :perfil, :tel, 1, 0)
            ");
            $ins->execute([
                ':nome'   => $campos['nome'],
                ':email'  => $campos['email'],
                ':hash'   => $hash,
                ':perfil' => $campos['perfil'],
                ':tel'    => $campos['telefone'] ?: null,
            ]);
 
            $novoId = $pdo->lastInsertId();
 
            $log = $pdo->prepare("
                INSERT INTO logs_sistema
                    (usuario_id, acao, tabela_afetada, registro_id, descricao, ip_address)
                VALUES
                    (:uid, 'criar', 'usuarios', :rid, :desc, :ip)
            ");
            $log->execute([
                ':uid'  => $novoId,
                ':rid'  => $novoId,
                ':desc' => "Novo usuário cadastrado: {$campos['email']} — perfil: {$campos['perfil']}",
                ':ip'   => $_SERVER['REMOTE_ADDR'] ?? 'desconhecido',
            ]);
 
            $sucesso = 'Cadastro realizado com sucesso! Você já pode fazer login.';
            $campos  = ['nome' => '', 'email' => '', 'telefone' => '', 'perfil' => 'operador'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SinAgro Sistema — Criar Conta</title>
  <link rel="stylesheet" href="assets/css/sinagro.css">
</head>
<body>

<div class="auth-page">
  <div class="auth-card" style="width:1000px">

    <div class="auth-left">
      <span class="al-icon">🌿</span>
      <h1>SINAGRO</h1>
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
      <p class="auth-sub">Preencha os dados abaixo para acessar o Sistema SinAgro</p>

      <?php if ($erro): ?>
        <div class="alert alert-error">⚠ <?= limpar($erro) ?></div>
      <?php endif; ?>

      <?php if ($sucesso): ?>
        <div class="alert alert-success">
          ✓ <?= limpar($sucesso) ?>
          <br><a href="login.php" style="color:var(--green);font-weight:700">→ Clique aqui para fazer login</a>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php" id="formCadastro" novalidate>

        <div class="grid-2">
          <div class="form-group">
            <label class="form-label" for="nome">
              Nome completo <span style="color:var(--red)">*</span>
            </label>
            <input
              class="form-control"
              type="text" id="nome" name="nome"
              value="<?= limpar($campos['nome']) ?>"
              placeholder="Ex: João da Silva"
              required autocomplete="name"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="email">
              E-mail <span style="color:var(--red)">*</span>
            </label>
            <input
              class="form-control"
              type="email" id="email" name="email"
              value="<?= limpar($campos['email']) ?>"
              placeholder="seu@email.com.br"
              required autocomplete="email"
            >
          </div>
        </div>

        <div class="form-group" style="max-width:50%;padding-right:10px">
          <label class="form-label" for="telefone">Telefone / WhatsApp</label>
          <input
            class="form-control"
            type="tel" id="telefone" name="telefone"
            value="<?= limpar($campos['telefone']) ?>"
            placeholder="(11) 99999-9999"
            autocomplete="tel"
          >
        </div>

        <div class="form-group">
          <label class="form-label">
            Perfil de acesso <span style="color:var(--red)">*</span>
          </label>
          <input type="hidden" id="perfil-hidden" name="perfil" value="<?= limpar($campos['perfil']) ?>">

          <div class="perfil-grid" id="perfilGrid">

            <div class="perfil-card <?= $campos['perfil']==='proprietario' ? 'selected' : '' ?>"
                 onclick="selecionarPerfil('proprietario', this)">
              <span class="pi">🏡</span>
              <div class="pn">Proprietário</div>
              <div class="pd">Dono da fazenda</div>
            </div>

            <div class="perfil-card <?= $campos['perfil']==='gerente' ? 'selected' : '' ?>"
                 onclick="selecionarPerfil('gerente', this)">
              <span class="pi">📋</span>
              <div class="pn">Gerente</div>
              <div class="pd">Gerencia operações</div>
            </div>

            <div class="perfil-card <?= $campos['perfil']==='operador' ? 'selected' : '' ?>"
                 onclick="selecionarPerfil('operador', this)">
              <span class="pi">🚜</span>
              <div class="pn">Operador</div>
              <div class="pd">Registra atividades</div>
            </div>

            <div class="perfil-card <?= $campos['perfil']==='visualizador' ? 'selected' : '' ?>"
                 onclick="selecionarPerfil('visualizador', this)">
              <span class="pi">👁️</span>
              <div class="pn">Visualizador</div>
              <div class="pd">Apenas leitura</div>
            </div>

            <div class="perfil-card <?= $campos['perfil']==='admin' ? 'selected' : '' ?>"
                 onclick="selecionarPerfil('admin', this)">
              <span class="pi">⚙️</span>
              <div class="pn">Admin</div>
              <div class="pd">Acesso total</div>
            </div>

          </div>
        </div>

        <div class="grid-2">
          <div class="form-group">
            <label class="form-label" for="senha">
              Senha <span style="color:var(--red)">*</span>
            </label>
            <input
              class="form-control"
              type="password" id="senha" name="senha"
              placeholder="Mínimo 8 caracteres"
              required autocomplete="new-password"
              oninput="avaliarSenha(this.value)"
            >
            <div class="strength-bar">
              <div class="strength-fill" id="forcaFill"></div>
            </div>
            <div class="strength-hint" id="forcaTexto">Digite sua senha</div>
          </div>

          <div class="form-group">
            <label class="form-label" for="confirmar_senha">
              Confirmar senha <span style="color:var(--red)">*</span>
            </label>
            <input
              class="form-control"
              type="password" id="confirmar_senha" name="confirmar_senha"
              placeholder="Repita a senha"
              required autocomplete="new-password"
              oninput="verificarConfirmacao()"
            >
            <div class="strength-hint" id="confirmaTxt"></div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full">
          Criar minha conta →
        </button>

      </form>

      <div class="auth-link">
        Já tem uma conta? <a href="login.php">Fazer login</a>
      </div>

    </div>
  </div>
</div>

<script src="script.js"></script>
</body>
</html>

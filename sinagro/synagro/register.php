<?php
// =============================================================================
//  SynAgro System — Cadastro de Usuário
//  Arquivo : register.php
// =============================================================================
 
require_once 'config/conexao.php';
require_once 'includes/auth.php';
 
// Se já está logado, vai pro dashboard
if (usuarioLogado()) {
    header('Location: pages/dashboard.php');
    exit;
}
 
$erro    = '';
$sucesso = '';
 
// Preserva os campos preenchidos em caso de erro
$campos = [
    'nome'     => '',
    'email'    => '',
    'telefone' => '',
    'perfil'   => 'operador',
];
 
// -----------------------------------------------------------------------------
// Processa o formulário (POST)
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $campos['nome']     = limpar($_POST['nome']     ?? '');
    $campos['email']    = limpar($_POST['email']    ?? '');
    $campos['telefone'] = limpar($_POST['telefone'] ?? '');
    $campos['perfil']   = limpar($_POST['perfil']   ?? 'operador');
    $senha              = $_POST['senha']            ?? '';
    $confirmar          = $_POST['confirmar_senha']  ?? '';
 
    // ── Validações ────────────────────────────────────────────────────────
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
 
        // Verifica se o e-mail já está cadastrado
        $chk = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $chk->execute([':email' => $campos['email']]);
 
        if ($chk->fetch()) {
            $erro = 'Este e-mail já está cadastrado. Tente fazer login ou use outro e-mail.';
 
        } else {
            // Gera hash seguro da senha (bcrypt, cost 12)
            $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
 
            // Insere o novo usuário
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
 
            // Registra criação nos logs
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
 
            // Limpa os campos após sucesso
            $campos = ['nome' => '', 'email' => '', 'telefone' => '', 'perfil' => 'operador'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SynAgro System — Criar Conta</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 
    body {
      font-family: Arial, sans-serif;
      background: #F4F1E8;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
    }
 
    .wrapper {
      display: flex;
      width: 960px;
      max-width: 100%;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 8px 40px rgba(26,60,42,.18);
    }
 
    /* ── Painel esquerdo ─────────────────────────────────── */
    .left {
      background: #1A3C2A;
      flex: 0 0 300px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px 32px;
      gap: 16px;
    }
 
    .left .icon   { font-size: 60px; }
    .left h1      { color: #7CB87A; font-size: 26px; font-weight: 900; letter-spacing: 2px; text-align: center; }
    .left p       { color: #D0CEC5; font-size: 12px; text-align: center; line-height: 1.6; font-style: italic; }
    .left .line   { width: 50px; height: 3px; background: #7CB87A; border-radius: 2px; }
 
    .step-list    { list-style: none; width: 100%; display: flex; flex-direction: column; gap: 10px; }
    .step-list li {
      display: flex; align-items: flex-start; gap: 10px;
      color: #A5D6A7; font-size: 12px; line-height: 1.5;
    }
    .step-num {
      background: #2C5F2D; color: #7CB87A; font-weight: 700; font-size: 11px;
      width: 22px; height: 22px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
 
    /* ── Painel direito (formulário) ─────────────────────── */
    .right {
      background: #fff;
      flex: 1;
      padding: 44px 44px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
 
    .right h2    { font-size: 22px; color: #1A3C2A; font-weight: 700; margin-bottom: 4px; }
    .right .sub  { color: #5A5A5A; font-size: 13px; margin-bottom: 28px; }
 
    /* Grid 2 colunas */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; }
 
    .form-group { margin-bottom: 18px; }
 
    label {
      display: block; font-size: 11px; font-weight: 700; color: #2C5F2D;
      margin-bottom: 6px; text-transform: uppercase; letter-spacing: .5px;
    }
 
    label .obr { color: #A32D2D; margin-left: 2px; }
 
    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="password"],
    select {
      width: 100%; padding: 11px 14px;
      border: 1.5px solid #D0CEC5; border-radius: 8px;
      font-size: 14px; color: #141414; background: #FAFAF8;
      transition: border-color .2s; outline: none;
      font-family: Arial, sans-serif;
    }
 
    input:focus, select:focus { border-color: #2C5F2D; background: #fff; }
    input.invalido            { border-color: #A32D2D; background: #FFFBFB; }
 
    /* Força de senha */
    .senha-forca-bar {
      height: 4px; border-radius: 2px; margin-top: 6px;
      background: #E8E0CC; overflow: hidden;
    }
    .senha-forca-fill {
      height: 100%; border-radius: 2px;
      width: 0; transition: width .3s, background .3s;
    }
    .senha-hint { font-size: 11px; color: #5A5A5A; margin-top: 4px; }
 
    /* Perfil cards */
    .perfil-grid {
      display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
      margin-top: 2px;
    }
 
    .perfil-card {
      border: 1.5px solid #D0CEC5; border-radius: 8px; padding: 10px 8px;
      text-align: center; cursor: pointer; transition: all .15s;
      background: #FAFAF8;
    }
 
    .perfil-card:hover    { border-color: #2C5F2D; background: #F0FAF0; }
    .perfil-card.selected { border-color: #1A3C2A; background: #EAF3DE; }
    .perfil-card .p-icon  { font-size: 22px; display: block; margin-bottom: 4px; }
    .perfil-card .p-name  { font-size: 11px; font-weight: 700; color: #1A3C2A; }
    .perfil-card .p-desc  { font-size: 10px; color: #5A5A5A; margin-top: 2px; line-height: 1.3; }
 
    /* Input hidden para perfil */
    #perfil-hidden { display: none; }
 
    /* Alerts */
    .alert {
      padding: 12px 16px; border-radius: 8px; font-size: 13px;
      margin-bottom: 20px; border-left: 4px solid;
    }
    .alert-erro    { background: #FCEBEB; color: #A32D2D; border-color: #A32D2D; }
    .alert-sucesso { background: #EAF3DE; color: #1A3C2A; border-color: #2C5F2D; }
 
    /* Botão */
    .btn {
      width: 100%; padding: 13px; border: none; border-radius: 8px;
      font-size: 15px; font-weight: 700; cursor: pointer; letter-spacing: .5px;
      transition: background .2s; margin-top: 6px; font-family: Arial, sans-serif;
    }
    .btn-verde   { background: #1A3C2A; color: #fff; }
    .btn-verde:hover { background: #2C5F2D; }
 
    /* Link login */
    .link-login {
      text-align: center; margin-top: 18px; font-size: 13px; color: #5A5A5A;
    }
    .link-login a { color: #2C5F2D; font-weight: 700; text-decoration: none; }
    .link-login a:hover { text-decoration: underline; }
 
    @media (max-width: 680px) {
      .left        { display: none; }
      .right       { padding: 32px 20px; }
      .grid-2      { grid-template-columns: 1fr; }
      .perfil-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>
 
<div class="wrapper">
 
  <!-- ── Painel esquerdo ───────────────────────────────────────────── -->
  <div class="left">
    <div class="icon">🌿</div>
    <h1>SYNAGRO</h1>
    <div class="line"></div>
    <p>Crie sua conta e comece a gerenciar sua propriedade rural com tecnologia.</p>
    <ul class="step-list">
      <li><span class="step-num">1</span>Preencha seus dados pessoais</li>
      <li><span class="step-num">2</span>Escolha seu perfil de acesso</li>
      <li><span class="step-num">3</span>Crie uma senha segura</li>
      <li><span class="step-num">4</span>Acesse o sistema imediatamente</li>
    </ul>
  </div>
 
  <!-- ── Formulário ────────────────────────────────────────────────── -->
  <div class="right">
    <h2>Criar nova conta</h2>
    <p class="sub">Preencha os dados abaixo para acessar o SynAgro System</p>
 
    <?php if ($erro): ?>
      <div class="alert alert-erro">⚠ <?= limpar($erro) ?></div>
    <?php endif; ?>
 
    <?php if ($sucesso): ?>
      <div class="alert alert-sucesso">
        ✓ <?= limpar($sucesso) ?>
        <br><a href="login.php" style="color:#1A3C2A;font-weight:700">→ Clique aqui para fazer login</a>
      </div>
    <?php endif; ?>
 
    <form method="POST" action="register.php" id="formCadastro" novalidate>
 
      <!-- Dados pessoais -->
      <div class="grid-2">
        <div class="form-group">
          <label for="nome">Nome completo <span class="obr">*</span></label>
          <input
            type="text" id="nome" name="nome"
            value="<?= limpar($campos['nome']) ?>"
            placeholder="Ex: João da Silva"
            required autocomplete="name"
          >
        </div>
 
        <div class="form-group">
          <label for="email">E-mail <span class="obr">*</span></label>
          <input
            type="email" id="email" name="email"
            value="<?= limpar($campos['email']) ?>"
            placeholder="seu@email.com.br"
            required autocomplete="email"
          >
        </div>
      </div>
 
      <div class="form-group" style="max-width:50%;padding-right:10px">
        <label for="telefone">Telefone / WhatsApp</label>
        <input
          type="tel" id="telefone" name="telefone"
          value="<?= limpar($campos['telefone']) ?>"
          placeholder="(11) 99999-9999"
          autocomplete="tel"
        >
      </div>
 
      <!-- Perfil de acesso -->
      <div class="form-group">
        <label>Perfil de acesso <span class="obr">*</span></label>
        <input type="hidden" id="perfil-hidden" name="perfil" value="<?= limpar($campos['perfil']) ?>">
        <div class="perfil-grid" id="perfilGrid">
 
          <div class="perfil-card <?= $campos['perfil']==='proprietario'?'selected':'' ?>"
               onclick="selecionarPerfil('proprietario', this)">
            <span class="p-icon">🏡</span>
            <div class="p-name">Proprietário</div>
            <div class="p-desc">Dono da fazenda</div>
          </div>
 
          <div class="perfil-card <?= $campos['perfil']==='gerente'?'selected':'' ?>"
               onclick="selecionarPerfil('gerente', this)">
            <span class="p-icon">📋</span>
            <div class="p-name">Gerente</div>
            <div class="p-desc">Gerencia operações</div>
          </div>
 
          <div class="perfil-card <?= $campos['perfil']==='operador'?'selected':'' ?>"
               onclick="selecionarPerfil('operador', this)">
            <span class="p-icon">🚜</span>
            <div class="p-name">Operador</div>
            <div class="p-desc">Registra atividades</div>
          </div>
 
          <div class="perfil-card <?= $campos['perfil']==='visualizador'?'selected':'' ?>"
               onclick="selecionarPerfil('visualizador', this)">
            <span class="p-icon">👁️</span>
            <div class="p-name">Visualizador</div>
            <div class="p-desc">Apenas leitura</div>
          </div>
 
          <div class="perfil-card <?= $campos['perfil']==='admin'?'selected':'' ?>"
               onclick="selecionarPerfil('admin', this)">
            <span class="p-icon">⚙️</span>
            <div class="p-name">Admin</div>
            <div class="p-desc">Acesso total</div>
          </div>
 
        </div>
      </div>
 
      <!-- Senhas -->
      <div class="grid-2">
        <div class="form-group">
          <label for="senha">Senha <span class="obr">*</span></label>
          <input
            type="password" id="senha" name="senha"
            placeholder="Mínimo 8 caracteres"
            required autocomplete="new-password"
            oninput="avaliarSenha(this.value)"
          >
          <div class="senha-forca-bar">
            <div class="senha-forca-fill" id="forcaFill"></div>
          </div>
          <div class="senha-hint" id="forcaTexto">Digite sua senha</div>
        </div>
 
        <div class="form-group">
          <label for="confirmar_senha">Confirmar senha <span class="obr">*</span></label>
          <input
            type="password" id="confirmar_senha" name="confirmar_senha"
            placeholder="Repita a senha"
            required autocomplete="new-password"
            oninput="verificarConfirmacao()"
          >
          <div class="senha-hint" id="confirmaTxt"></div>
        </div>
      </div>
 
      <button type="submit" class="btn btn-verde">Criar minha conta</button>
 
    </form>
 
    <div class="link-login">
      Já tem uma conta? <a href="login.php">Fazer login</a>
    </div>
 
  </div>
</div>
<script src="script.js"><script>
</body>
</html>
 




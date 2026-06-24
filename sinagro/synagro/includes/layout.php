<?php
// =============================================================================
//  SynAgro — Componente: Sidebar + Topbar
//  includes/layout.php
//  Uso: include __DIR__ . '/../includes/layout.php';
//       Defina $pageTitle e $pageActive antes de incluir
// =============================================================================

$usuario  = usuarioAtual();
$pageTitle  = $pageTitle  ?? 'Dashboard';
$pageActive = $pageActive ?? 'dashboard';

// Iniciais do usuário para avatar
$partes  = explode(' ', $usuario['nome']);
$iniciais = strtoupper(($partes[0][0] ?? '') . ($partes[1][0] ?? ''));

// Módulos e ícones
$todosModulos = [
    'dashboard'    => ['icon' => '⊞',  'label' => 'Dashboard',    'section' => 'menu'],
    'propriedades' => ['icon' => '🏡', 'label' => 'Propriedades', 'section' => 'menu'],
    'culturas'     => ['icon' => '🌾', 'label' => 'Culturas',     'section' => 'menu'],
    'ciclos'       => ['icon' => '🔄', 'label' => 'Ciclos',       'section' => 'menu'],
    'animais'      => ['icon' => '🐄', 'label' => 'Rebanho',      'section' => 'producao'],
    'estoque'      => ['icon' => '📦', 'label' => 'Estoque',      'section' => 'producao'],
    'equipamentos' => ['icon' => '🚜', 'label' => 'Equipamentos', 'section' => 'producao'],
    'manutencoes'  => ['icon' => '🔧', 'label' => 'Manutenções',  'section' => 'producao'],
    'financeiro'   => ['icon' => '💰', 'label' => 'Financeiro',   'section' => 'gestao'],
    'relatorios'   => ['icon' => '📊', 'label' => 'Relatórios',   'section' => 'gestao'],
    'usuarios'     => ['icon' => '👥', 'label' => 'Usuários',     'section' => 'admin'],
    'logs'         => ['icon' => '📋', 'label' => 'Logs',         'section' => 'admin'],
];

$modulosPermitidos = MODULOS_PERFIL[$usuario['perfil']] ?? ['dashboard'];
$sections = [
    'menu'     => 'Menu',
    'producao' => 'Produção',
    'gestao'   => 'Gestão',
    'admin'    => 'Admin',
];

// Agrupa módulos permitidos por seção
$agrupados = [];
foreach ($todosModulos as $key => $mod) {
    if (in_array($key, $modulosPermitidos)) {
        $agrupados[$mod['section']][$key] = $mod;
    }
}

// Base path relativo (pages/ ou raiz)
$isPage = str_contains($_SERVER['SCRIPT_FILENAME'], '/pages/');
$base   = $isPage ? '../' : '';
?>

<!-- ── SIDEBAR ──────────────────────────────────────────────────────────── -->
<nav class="sidebar" id="sidebar">

  <div class="sidebar-brand">
    <span class="brand-icon">🌿</span>
    <div class="brand-text">
      <strong>SYNAGRO</strong>
      <span>System</span>
    </div>
  </div>

  <div class="sidebar-nav">
    <?php foreach ($sections as $secKey => $secLabel): ?>
      <?php if (empty($agrupados[$secKey])) continue; ?>
      <div class="nav-section-label"><?= $secLabel ?></div>
      <?php foreach ($agrupados[$secKey] as $key => $mod): ?>
        <?php
          $ativo    = ($pageActive === $key) ? 'active' : '';
          $href     = ($key === 'dashboard') ? $base . 'pages/dashboard.php' : $base . "pages/{$key}.php";
          $badgeNum = '';
          // Badge de alertas
          if (isset($alertas[$key])) $badgeNum = $alertas[$key];
        ?>
        <a href="<?= $href ?>" class="nav-item <?= $ativo ?>" data-tip="<?= $mod['label'] ?>">
          <span class="nav-icon"><?= $mod['icon'] ?></span>
          <span class="nav-label"><?= $mod['label'] ?></span>
          <?php if ($badgeNum): ?>
            <span class="nav-badge"><?= $badgeNum ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>

  <div class="sidebar-footer">
    <div class="sidebar-avatar"><?= $iniciais ?></div>
    <div class="sidebar-user-info">
      <div class="u-name"><?= limpar($usuario['nome']) ?></div>
      <div class="u-role"><?= $usuario['label'] ?></div>
    </div>
  </div>

</nav>

<!-- ── TOPBAR ────────────────────────────────────────────────────────────── -->
<header class="topbar">
  <div class="topbar-left">
    <div>
      <div class="page-title"><?= $pageTitle ?></div>
      <div class="page-breadcrumb">SynAgro / <?= $pageTitle ?></div>
    </div>
  </div>

  <div class="topbar-right">
    <!-- Notificações -->
    <button class="topbar-icon-btn" title="Notificações">
      🔔
      <span class="notif-dot"></span>
    </button>

    <!-- Perfil chip -->
    <div class="profile-chip">
      <div class="chip-avatar"><?= $iniciais ?></div>
      <span class="chip-name"><?= explode(' ', limpar($usuario['nome']))[0] ?></span>
      <span class="badge badge-green" style="margin-left:4px"><?= $usuario['label'] ?></span>
    </div>

    <!-- Logout -->
    <a href="<?= $base ?>logout.php" class="btn btn-ghost" style="padding:7px 12px;font-size:12px">
      Sair →
    </a>
  </div>
</header>

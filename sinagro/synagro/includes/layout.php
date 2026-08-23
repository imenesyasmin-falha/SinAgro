<?php
$usuario    = usuarioAtual();
$pageTitle  = $pageTitle  ?? 'Dashboard';
$pageActive = $pageActive ?? 'dashboard';

$partes   = explode(' ', $usuario['nome']);
$iniciais = strtoupper(($partes[0][0] ?? '') . ($partes[1][0] ?? ''));

$todosModulos = [
    'dashboard'    => ['icon' => '⊞',  'label' => 'Dashboard',    'section' => 'menu'],
    'propriedades' => ['icon' => '🏡', 'label' => 'Propriedades', 'section' => 'menu'],
    'culturas'     => ['icon' => '🌾', 'label' => 'Culturas',     'section' => 'menu'],
    'ciclos'       => ['icon' => '🔄', 'label' => 'Ciclos',       'section' => 'menu'],
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

$agrupados = [];
foreach ($todosModulos as $key => $mod) {
    if (in_array($key, $modulosPermitidos)) {
        $agrupados[$mod['section']][$key] = $mod;
    }
}

$scriptNorm = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
$isPage     = str_contains($scriptNorm, '/pages/');
$base       = $isPage ? '../' : '';
?>

<nav class="sidebar" id="sidebar">

  <div class="sidebar-brand">
    <span class="brand-icon">🌿</span>
    <div class="brand-text">
      <strong>SINAGRO</strong>
      <span>Sistema</span>
    </div>
  </div>

  <div class="sidebar-nav">
    <?php foreach ($sections as $secKey => $secLabel): ?>
      <?php if (empty($agrupados[$secKey])) continue; ?>

      <div class="nav-section-label"><?= $secLabel ?></div>

      <?php foreach ($agrupados[$secKey] as $key => $mod): ?>
        <?php
          $ativo    = ($pageActive === $key) ? 'active' : '';
          $href     = ($key === 'dashboard')
                        ? $base . 'pages/dashboard.php'
                        : $base . "pages/{$key}.php";
          $badgeNum = $alertas[$key] ?? '';
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

<header class="topbar">
  <div class="topbar-left">
    <div>
      <div class="page-title"><?= $pageTitle ?></div>
      <div class="page-breadcrumb">SinAgro / <?= $pageTitle ?></div>
    </div>
  </div>

  <div class="topbar-right">

    <button class="topbar-icon-btn" title="Notificações">
      🔔
      <span class="notif-dot"></span>
    </button>

    <div class="profile-chip">
      <div class="chip-avatar"><?= $iniciais ?></div>
      <span class="chip-name"><?= explode(' ', limpar($usuario['nome']))[0] ?></span>
      <span class="badge badge-green" style="margin-left:4px;background:<?= $usuario['cor'] ?>22;color:<?= $usuario['cor'] ?>">
        <?= $usuario['label'] ?>
      </span>
    </div>

    <a href="<?= $base ?>logout.php" class="btn btn-ghost" style="padding:7px 12px;font-size:12px">
      Sair →
    </a>

  </div>
</header>

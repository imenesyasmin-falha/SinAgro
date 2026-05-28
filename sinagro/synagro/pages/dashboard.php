<?php
// =============================================================================
//  SynAgro System — Dashboard Principal
//  Arquivo : pages/dashboard.php
// =============================================================================

require_once '../config/conexao.php';
require_once '../includes/auth.php';

// Exige login — redireciona para login.php se não autenticado
exigirLogin('../');

$usuario = usuarioAtual();
$pdo     = conectar();
$aviso   = '';

if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado') {
    $aviso = 'Você não tem permissão para acessar essa área.';
}

// -----------------------------------------------------------------------------
// Carrega dados do dashboard conforme o perfil
// -----------------------------------------------------------------------------
$dados = [
    'total_propriedades'  => 0,
    'total_culturas'      => 0,
    'total_equipamentos'  => 0,
    'total_animais'       => 0,
    'estoque_critico'     => 0,
    'manutencoes_abertas' => 0,
    'receitas_mes'        => 0,
    'despesas_mes'        => 0,
    'logs_recentes'       => [],
    'propriedades_lista'  => [],
];

try {

    // Propriedades do usuário (ou todas para admin)
    if ($usuario['perfil'] === 'admin') {
        $dados['total_propriedades'] = $pdo
            ->query("SELECT COUNT(*) FROM propriedades WHERE deleted_at IS NULL")
            ->fetchColumn();
        $dados['propriedades_lista'] = $pdo
            ->query("SELECT p.nome, u.nome AS dono, p.municipio, p.estado, p.area_total_ha
                     FROM propriedades p JOIN usuarios u ON u.id = p.usuario_id
                     WHERE p.deleted_at IS NULL ORDER BY p.criado_em DESC LIMIT 5")
            ->fetchAll();
    } else {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM propriedades WHERE usuario_id = :uid AND deleted_at IS NULL"
        );
        $stmt->execute([':uid' => $usuario['id']]);
        $dados['total_propriedades'] = $stmt->fetchColumn();

        $stmt2 = $pdo->prepare(
            "SELECT nome, municipio, estado, area_total_ha FROM propriedades
             WHERE usuario_id = :uid AND deleted_at IS NULL ORDER BY criado_em DESC LIMIT 5"
        );
        $stmt2->execute([':uid' => $usuario['id']]);
        $dados['propriedades_lista'] = $stmt2->fetchAll();
    }

    // Culturas ativas
    $dados['total_culturas'] = $pdo
        ->query("SELECT COUNT(*) FROM culturas WHERE status = 'em_andamento' AND deleted_at IS NULL")
        ->fetchColumn();

    // Equipamentos operacionais
    $dados['total_equipamentos'] = $pdo
        ->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'operacional' AND deleted_at IS NULL")
        ->fetchColumn();

    // Animais ativos
    $dados['total_animais'] = $pdo
        ->query("SELECT COUNT(*) FROM animais WHERE status = 'ativo' AND deleted_at IS NULL")
        ->fetchColumn();

    // Itens em estoque crítico (view)
    $dados['estoque_critico'] = $pdo
        ->query("SELECT COUNT(*) FROM vw_estoque_critico")
        ->fetchColumn();

    // Manutenções abertas (view)
    $dados['manutencoes_abertas'] = $pdo
        ->query("SELECT COUNT(*) FROM vw_equipamentos_em_manutencao")
        ->fetchColumn();

    // Financeiro do mês corrente
    $mes = date('m');
    $ano = date('Y');
    $fin = $pdo->prepare("
        SELECT
            SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) AS receitas,
            SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) AS despesas
        FROM movimentacoes_financeiras
        WHERE MONTH(data_movimentacao) = :mes
          AND YEAR(data_movimentacao)  = :ano
          AND deleted_at IS NULL
    ");
    $fin->execute([':mes' => $mes, ':ano' => $ano]);
    $fin_row = $fin->fetch();
    $dados['receitas_mes']  = $fin_row['receitas']  ?? 0;
    $dados['despesas_mes']  = $fin_row['despesas']  ?? 0;

    // Logs recentes (só admin)
    if ($usuario['perfil'] === 'admin') {
        $dados['logs_recentes'] = $pdo
            ->query("
                SELECT l.acao, l.tabela_afetada, l.descricao,
                       l.criado_em, u.nome AS usuario_nome
                FROM logs_sistema l
                LEFT JOIN usuarios u ON u.id = l.usuario_id
                ORDER BY l.criado_em DESC
                LIMIT 8
            ")
            ->fetchAll();
    }

} catch (PDOException $e) {
    error_log("[SynAgro Dashboard] " . $e->getMessage());
}

// Formata valor monetário
function moeda(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}

// Ícone de módulo
function iconeModulo(string $m): string {
    $icons = [
        'dashboard'   => '📊', 'usuarios'     => '👥',
        'propriedades'=> '🏡', 'culturas'     => '🌾',
        'animais'     => '🐄', 'estoque'      => '📦',
        'equipamentos'=> '🚜', 'financeiro'   => '💰',
        'logs'        => '📋',
    ];
    return $icons[$m] ?? '🔹';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — SynAgro System</title>
  <link rel="stylesheet" href="style.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: Arial, sans-serif;
      background: #F4F1E8;
      color: #141414;
      min-height: 100vh;
    }

    /* ── Topbar ──────────────────────────────────────────── */
    .topbar {
      background: #1A3C2A;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      height: 60px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 8px rgba(0,0,0,.18);
    }

    .topbar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .topbar-brand span:first-child { font-size: 26px; }

    .topbar-brand strong {
      color: #7CB87A;
      font-size: 18px;
      letter-spacing: 1px;
      font-weight: 900;
    }

    .topbar-brand em {
      color: #D0CEC5;
      font-size: 12px;
      font-style: normal;
    }

    .topbar-user {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .topbar-user .user-info {
      text-align: right;
    }

    .topbar-user .user-nome {
      color: #fff;
      font-size: 13px;
      font-weight: 700;
    }

    .topbar-user .user-email {
      color: #A5D6A7;
      font-size: 11px;
    }

    .badge-perfil {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      color: #fff;
      white-space: nowrap;
    }

    .btn-logout {
      background: rgba(255,255,255,.12);
      color: #D0CEC5;
      border: 1px solid rgba(255,255,255,.2);
      padding: 6px 14px;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
      text-decoration: none;
      transition: background .2s;
    }

    .btn-logout:hover { background: rgba(255,255,255,.22); color: #fff; }

    /* ── Sidebar ─────────────────────────────────────────── */
    .layout {
      display: flex;
      min-height: calc(100vh - 60px);
    }

    .sidebar {
      width: 220px;
      background: #fff;
      border-right: 1px solid #D0CEC5;
      padding: 20px 0;
      flex-shrink: 0;
    }

    .sidebar-section {
      padding: 8px 20px 4px;
      font-size: 10px;
      font-weight: 700;
      color: #A0A098;
      text-transform: uppercase;
      letter-spacing: .8px;
    }

    .sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 20px;
      font-size: 13px;
      color: #141414;
      text-decoration: none;
      border-left: 3px solid transparent;
      transition: all .15s;
    }

    .sidebar a:hover {
      background: #EAF3DE;
      border-left-color: #2C5F2D;
      color: #1A3C2A;
    }

    .sidebar a.ativo {
      background: #EAF3DE;
      border-left-color: #1A3C2A;
      font-weight: 700;
      color: #1A3C2A;
    }

    .sidebar a .icon { font-size: 16px; }

    /* ── Conteúdo principal ──────────────────────────────── */
    .main {
      flex: 1;
      padding: 28px 32px;
      overflow-y: auto;
    }

    .page-header {
      margin-bottom: 24px;
    }

    .page-header h1 {
      font-size: 22px;
      color: #1A3C2A;
      font-weight: 700;
    }

    .page-header p {
      color: #5A5A5A;
      font-size: 13px;
      margin-top: 4px;
    }

    /* ── Alert ───────────────────────────────────────────── */
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 13px;
      margin-bottom: 20px;
      border-left: 4px solid;
    }

    .alert-aviso {
      background: #FAEEDA;
      color: #854F0B;
      border-color: #C8973A;
    }

    /* ── Cards de indicadores ────────────────────────────── */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 16px;
      margin-bottom: 28px;
    }

    .card {
      background: #fff;
      border-radius: 12px;
      padding: 20px 18px;
      border: 1px solid #E8E0CC;
      box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }

    .card-icon { font-size: 28px; margin-bottom: 10px; }

    .card-num {
      font-size: 28px;
      font-weight: 700;
      color: #1A3C2A;
      line-height: 1;
    }

    .card-label {
      font-size: 12px;
      color: #5A5A5A;
      margin-top: 4px;
    }

    .card.alerta { border-color: #C8973A; background: #FFFDF7; }
    .card.alerta .card-num { color: #C8973A; }

    .card.positivo { border-color: #7CB87A; background: #F7FBF7; }
    .card.positivo .card-num { color: #2C5F2D; }

    /* ── Seção de tabela ─────────────────────────────────── */
    .secao {
      background: #fff;
      border-radius: 12px;
      border: 1px solid #E8E0CC;
      margin-bottom: 24px;
      overflow: hidden;
    }

    .secao-header {
      padding: 16px 20px;
      border-bottom: 1px solid #E8E0CC;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .secao-header h2 {
      font-size: 15px;
      font-weight: 700;
      color: #1A3C2A;
    }

    .secao-header span {
      font-size: 11px;
      color: #5A5A5A;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    th {
      background: #F4F1E8;
      color: #1A3C2A;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .4px;
      padding: 10px 16px;
      text-align: left;
    }

    td {
      padding: 10px 16px;
      border-top: 1px solid #F0EDE4;
      color: #141414;
    }

    tr:hover td { background: #FAFAF6; }

    .tag {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }

    .tag-verde  { background: #EAF3DE; color: #2C5F2D; }
    .tag-laranja{ background: #FAEEDA; color: #C8973A; }
    .tag-azul   { background: #E6F1FB; color: #0C447C; }
    .tag-cinza  { background: #F4F1E8; color: #5A5A5A; }

    /* ── Grid 2 colunas ──────────────────────────────────── */
    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    @media (max-width: 900px) {
      .grid-2 { grid-template-columns: 1fr; }
      .sidebar { display: none; }
    }
  </style>
</head>
<body>

<!-- ── Topbar ──────────────────────────────────────────────────────────── -->
<header class="topbar">
  <div class="topbar-brand">
    <span>🌿</span>
    <div>
      <strong>SYNAGRO</strong><br>
      <em>System</em>
    </div>
  </div>

  <div class="topbar-user">
    <div class="user-info">
      <div class="user-nome"><?= limpar($usuario['nome']) ?></div>
      <div class="user-email"><?= limpar($usuario['email']) ?></div>
    </div>
    <span class="badge-perfil" style="background:<?= $usuario['cor'] ?>">
      <?= $usuario['label'] ?>
    </span>
    <a href="../logout.php" class="btn-logout">Sair</a>
  </div>
</header>

<div class="layout">

  <!-- ── Sidebar ───────────────────────────────────────────────────────── -->
  <nav class="sidebar">
    <div class="sidebar-section">Menu Principal</div>

    <?php foreach (MODULOS_PERFIL[$usuario['perfil']] as $modulo): ?>
      <?php
        $labels = [
          'dashboard'    => 'Dashboard',
          'usuarios'     => 'Usuários',
          'propriedades' => 'Propriedades',
          'culturas'     => 'Culturas',
          'animais'      => 'Rebanho',
          'estoque'      => 'Estoque',
          'equipamentos' => 'Equipamentos',
          'financeiro'   => 'Financeiro',
          'logs'         => 'Logs do Sistema',
        ];
        $ativo = $modulo === 'dashboard' ? 'ativo' : '';
      ?>
      <a href="#<?= $modulo ?>" class="<?= $ativo ?>">
        <span class="icon"><?= iconeModulo($modulo) ?></span>
        <?= $labels[$modulo] ?? ucfirst($modulo) ?>
      </a>
    <?php endforeach; ?>

  </nav>

  <!-- ── Conteúdo ──────────────────────────────────────────────────────── -->
  <main class="main">

    <?php if ($aviso): ?>
      <div class="alert alert-aviso">⚠ <?= limpar($aviso) ?></div>
    <?php endif; ?>

    <div class="page-header">
      <h1>Olá, <?= explode(' ', limpar($usuario['nome']))[0] ?>! 👋</h1>
      <p>
        <?= date('l, d \d\e F \d\e Y') ?> &nbsp;·&nbsp;
        Perfil: <strong><?= $usuario['label'] ?></strong>
      </p>
    </div>

    <!-- ── Cards de indicadores ──────────────────────────────────────── -->
    <div class="cards-grid">

      <div class="card">
        <div class="card-icon">🏡</div>
        <div class="card-num"><?= (int)$dados['total_propriedades'] ?></div>
        <div class="card-label">Propriedade(s)</div>
      </div>

      <div class="card positivo">
        <div class="card-icon">🌾</div>
        <div class="card-num"><?= (int)$dados['total_culturas'] ?></div>
        <div class="card-label">Culturas Ativas</div>
      </div>

      <div class="card">
        <div class="card-icon">🐄</div>
        <div class="card-num"><?= (int)$dados['total_animais'] ?></div>
        <div class="card-label">Animais</div>
      </div>

      <div class="card">
        <div class="card-icon">🚜</div>
        <div class="card-num"><?= (int)$dados['total_equipamentos'] ?></div>
        <div class="card-label">Equipamentos</div>
      </div>

      <?php if (temAcesso('estoque')): ?>
      <div class="card <?= $dados['estoque_critico'] > 0 ? 'alerta' : '' ?>">
        <div class="card-icon">📦</div>
        <div class="card-num"><?= (int)$dados['estoque_critico'] ?></div>
        <div class="card-label">Itens em Estoque Crítico</div>
      </div>
      <?php endif; ?>

      <?php if (temAcesso('equipamentos')): ?>
      <div class="card <?= $dados['manutencoes_abertas'] > 0 ? 'alerta' : '' ?>">
        <div class="card-icon">🔧</div>
        <div class="card-num"><?= (int)$dados['manutencoes_abertas'] ?></div>
        <div class="card-label">Manutenções Abertas</div>
      </div>
      <?php endif; ?>

      <?php if (temAcesso('financeiro')): ?>
      <div class="card positivo">
        <div class="card-icon">📈</div>
        <div class="card-num" style="font-size:18px"><?= moeda((float)$dados['receitas_mes']) ?></div>
        <div class="card-label">Receitas do Mês</div>
      </div>

      <div class="card <?= $dados['despesas_mes'] > $dados['receitas_mes'] ? 'alerta' : '' ?>">
        <div class="card-icon">📉</div>
        <div class="card-num" style="font-size:18px"><?= moeda((float)$dados['despesas_mes']) ?></div>
        <div class="card-label">Despesas do Mês</div>
      </div>
      <?php endif; ?>

    </div><!-- /cards-grid -->

    <!-- ── Tabelas por perfil ─────────────────────────────────────────── -->
    <div class="grid-2">

      <!-- Propriedades -->
      <?php if (!empty($dados['propriedades_lista'])): ?>
      <div class="secao">
        <div class="secao-header">
          <h2>🏡 Propriedades Recentes</h2>
          <span>Últimas cadastradas</span>
        </div>
        <table>
          <thead>
            <tr>
              <th>Fazenda</th>
              <th>Localização</th>
              <?php if ($usuario['perfil'] === 'admin'): ?>
                <th>Proprietário</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dados['propriedades_lista'] as $prop): ?>
            <tr>
              <td><?= limpar($prop['nome']) ?></td>
              <td>
                <?= limpar($prop['municipio']) ?>/<?= limpar($prop['estado']) ?>
                <?php if ($prop['area_total_ha']): ?>
                  <br><small style="color:#5A5A5A"><?= number_format($prop['area_total_ha'],1,',','.') ?> ha</small>
                <?php endif; ?>
              </td>
              <?php if ($usuario['perfil'] === 'admin'): ?>
                <td><?= limpar($prop['dono']) ?></td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- Logs recentes (só admin) -->
      <?php if ($usuario['perfil'] === 'admin' && !empty($dados['logs_recentes'])): ?>
      <div class="secao">
        <div class="secao-header">
          <h2>📋 Logs Recentes</h2>
          <span>Últimas ações</span>
        </div>
        <table>
          <thead>
            <tr><th>Ação</th><th>Usuário</th><th>Data/Hora</th></tr>
          </thead>
          <tbody>
            <?php foreach ($dados['logs_recentes'] as $log): ?>
            <?php
              $tagClass = match($log['acao']) {
                'login'          => 'tag-verde',
                'login_falhou'   => 'tag-laranja',
                'conta_bloqueada'=> 'tag-laranja',
                'criar'          => 'tag-azul',
                'excluir'        => 'tag-laranja',
                default          => 'tag-cinza',
              };
            ?>
            <tr>
              <td><span class="tag <?= $tagClass ?>"><?= limpar($log['acao']) ?></span></td>
              <td><?= limpar($log['usuario_nome'] ?? '—') ?></td>
              <td style="font-size:11px;color:#5A5A5A">
                <?= date('d/m H:i', strtotime($log['criado_em'])) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

    </div><!-- /grid-2 -->

    <!-- Aviso de acesso restrito para perfis sem módulos avançados -->
    <?php if ($usuario['perfil'] === 'visualizador'): ?>
    <div class="secao">
      <div style="padding:32px;text-align:center;color:#5A5A5A">
        <div style="font-size:40px;margin-bottom:12px">🔒</div>
        <strong style="color:#1A3C2A;font-size:16px">Acesso de Visualização</strong>
        <p style="margin-top:8px;font-size:13px">
          Seu perfil permite apenas visualizar os dashboards e relatórios.<br>
          Solicite ao administrador caso precise de mais permissões.
        </p>
      </div>
    </div>
    <?php endif; ?>

  </main><!-- /main -->
</div><!-- /layout -->

</body>
</html>

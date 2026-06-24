<?php
require_once '../config/conexao.php';
require_once '../includes/auth.php';
exigirLogin('../');

$pageTitle  = 'Dashboard';
$pageActive = 'dashboard';
$usuario    = usuarioAtual();
$pdo        = conectar();

// Dados
$stats = ['propriedades'=>0,'culturas'=>0,'animais'=>0,'equipamentos'=>0,'estoque_critico'=>0,'manutencoes'=>0,'receitas'=>0,'despesas'=>0];
try {
    $stats['propriedades']   = $pdo->query("SELECT COUNT(*) FROM propriedades WHERE deleted_at IS NULL")->fetchColumn();
    $stats['culturas']       = $pdo->query("SELECT COUNT(*) FROM culturas WHERE status='em_andamento' AND deleted_at IS NULL")->fetchColumn();
    $stats['animais']        = $pdo->query("SELECT COUNT(*) FROM animais WHERE status='ativo' AND deleted_at IS NULL")->fetchColumn();
    $stats['equipamentos']   = $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE status='operacional' AND deleted_at IS NULL")->fetchColumn();
    $stats['estoque_critico']= $pdo->query("SELECT COUNT(*) FROM vw_estoque_critico")->fetchColumn();
    $stats['manutencoes']    = $pdo->query("SELECT COUNT(*) FROM vw_equipamentos_em_manutencao")->fetchColumn();
    $fin = $pdo->prepare("SELECT SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END) r, SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END) d FROM movimentacoes_financeiras WHERE MONTH(data_movimentacao)=:m AND YEAR(data_movimentacao)=:a AND deleted_at IS NULL");
    $fin->execute([':m'=>date('m'),':a'=>date('Y')]);
    $fr = $fin->fetch();
    $stats['receitas'] = $fr['r'] ?? 0;
    $stats['despesas'] = $fr['d'] ?? 0;

    $propriedades = $pdo->query("SELECT p.nome,p.municipio,p.estado,p.area_total_ha,u.nome dono FROM propriedades p JOIN usuarios u ON u.id=p.usuario_id WHERE p.deleted_at IS NULL ORDER BY p.criado_em DESC LIMIT 5")->fetchAll();
    $logs = $pdo->query("SELECT l.acao,l.descricao,l.criado_em,u.nome uname FROM logs_sistema l LEFT JOIN usuarios u ON u.id=l.usuario_id ORDER BY l.criado_em DESC LIMIT 6")->fetchAll();
} catch(PDOException $e){ error_log($e->getMessage()); $propriedades = $logs = []; }

function moeda($v){ return 'R$ '.number_format($v,2,',','.'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — SynAgro</title>
<link rel="stylesheet" href="../assets/css/synagro.css">
</head>
<body>

<?php include '../includes/layout.php'; ?>

<div class="main-content">
<div class="page-body">

  <!-- Boas vindas -->
  <div class="section-header fade-up" style="margin-bottom:24px">
    <div>
      <div class="section-title">
        Olá, <?= explode(' ',limpar($usuario['nome']))[0] ?>! 👋
      </div>
      <div class="section-sub"><?= date('l, d \d\e F \d\e Y') ?> · <?= $usuario['label'] ?></div>
    </div>
    <span class="badge badge-green"><?= $usuario['label'] ?></span>
  </div>

  <!-- Stat cards -->
  <div class="grid grid-4" style="margin-bottom:24px">

    <div class="stat-card fade-up" style="--accent:#4ADE80">
      <span class="stat-icon">🏡</span>
      <div class="stat-value"><?= (int)$stats['propriedades'] ?></div>
      <div class="stat-label">Propriedades</div>
      <div class="stat-delta delta-up">↑ Ativas</div>
    </div>

    <div class="stat-card fade-up" style="--accent:#22C55E">
      <span class="stat-icon">🌾</span>
      <div class="stat-value"><?= (int)$stats['culturas'] ?></div>
      <div class="stat-label">Culturas em andamento</div>
      <div class="stat-delta delta-up">↑ Em campo</div>
    </div>

    <div class="stat-card fade-up" style="--accent:#60A5FA">
      <span class="stat-icon">🐄</span>
      <div class="stat-value"><?= (int)$stats['animais'] ?></div>
      <div class="stat-label">Animais ativos</div>
      <div class="stat-delta delta-up">↑ No rebanho</div>
    </div>

    <div class="stat-card fade-up" style="--accent:#FBBF24">
      <span class="stat-icon">🚜</span>
      <div class="stat-value"><?= (int)$stats['equipamentos'] ?></div>
      <div class="stat-label">Equipamentos operacionais</div>
      <div class="stat-delta <?= $stats['manutencoes']>0?'delta-down':'delta-up' ?>">
        <?= $stats['manutencoes'] ?> em manutenção
      </div>
    </div>

    <?php if(temAcesso('estoque')): ?>
    <div class="stat-card fade-up" style="--accent:<?= $stats['estoque_critico']>0?'#F87171':'#4ADE80' ?>">
      <span class="stat-icon">📦</span>
      <div class="stat-value" style="color:<?= $stats['estoque_critico']>0?'var(--red)':'var(--text-1)' ?>"><?= (int)$stats['estoque_critico'] ?></div>
      <div class="stat-label">Itens em estoque crítico</div>
      <div class="stat-delta <?= $stats['estoque_critico']>0?'delta-down':'delta-up' ?>">
        <?= $stats['estoque_critico']>0 ? '⚠ Atenção necessária' : '✓ Estoque OK' ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if(temAcesso('financeiro')): ?>
    <div class="stat-card fade-up" style="--accent:#4ADE80">
      <span class="stat-icon">📈</span>
      <div class="stat-value" style="font-size:20px"><?= moeda($stats['receitas']) ?></div>
      <div class="stat-label">Receitas do mês</div>
      <div class="stat-delta delta-up">↑ <?= date('F') ?></div>
    </div>

    <div class="stat-card fade-up" style="--accent:<?= $stats['despesas']>$stats['receitas']?'#F87171':'#FBBF24' ?>">
      <span class="stat-icon">📉</span>
      <div class="stat-value" style="font-size:20px"><?= moeda($stats['despesas']) ?></div>
      <div class="stat-label">Despesas do mês</div>
      <?php $saldo = $stats['receitas'] - $stats['despesas']; ?>
      <div class="stat-delta <?= $saldo>=0?'delta-up':'delta-down' ?>">
        Saldo: <?= moeda(abs($saldo)) ?>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <!-- Tabelas -->
  <div class="grid grid-2">

    <!-- Propriedades recentes -->
    <div class="card fade-up">
      <div class="card-header">
        <div>
          <div class="card-title">🏡 Propriedades Recentes</div>
          <div class="card-sub">Últimas cadastradas</div>
        </div>
        <?php if(temAcesso('propriedades')): ?>
          <a href="propriedades.php" class="btn btn-ghost" style="padding:6px 12px;font-size:12px">Ver todas →</a>
        <?php endif; ?>
      </div>
      <?php if(empty($propriedades)): ?>
        <div class="empty-state">
          <span class="es-icon">🏡</span>
          <div class="es-title">Nenhuma propriedade</div>
          <div class="es-sub">Cadastre sua primeira fazenda</div>
        </div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="syn-table">
          <thead><tr><th>Fazenda</th><th>Localização</th><?php if($usuario['perfil']==='admin'): ?><th>Proprietário</th><?php endif; ?></tr></thead>
          <tbody>
            <?php foreach($propriedades as $p): ?>
            <tr>
              <td style="font-weight:600"><?= limpar($p['nome']) ?></td>
              <td>
                <div><?= limpar($p['municipio']) ?>/<?= limpar($p['estado']) ?></div>
                <?php if($p['area_total_ha']): ?><div style="font-size:11px;color:var(--text-3)"><?= number_format($p['area_total_ha'],1,',','.') ?> ha</div><?php endif; ?>
              </td>
              <?php if($usuario['perfil']==='admin'): ?><td style="color:var(--text-2)"><?= limpar($p['dono']) ?></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

    <!-- Logs (admin) ou boas-vindas (outros) -->
    <?php if($usuario['perfil']==='admin' && !empty($logs)): ?>
    <div class="card fade-up">
      <div class="card-header">
        <div>
          <div class="card-title">📋 Atividade Recente</div>
          <div class="card-sub">Últimas ações no sistema</div>
        </div>
        <a href="logs.php" class="btn btn-ghost" style="padding:6px 12px;font-size:12px">Ver logs →</a>
      </div>
      <div class="table-wrap">
        <table class="syn-table">
          <thead><tr><th>Ação</th><th>Usuário</th><th>Quando</th></tr></thead>
          <tbody>
            <?php foreach($logs as $l): ?>
            <?php
              $bc = match($l['acao']){
                'login'=>'badge-green','login_falhou'=>'badge-gold',
                'criar'=>'badge-blue','excluir'=>'badge-red',default=>'badge-gray'
              };
            ?>
            <tr>
              <td><span class="badge <?= $bc ?>"><?= limpar($l['acao']) ?></span></td>
              <td style="color:var(--text-2)"><?= limpar($l['uname']??'Sistema') ?></td>
              <td style="font-size:11px;color:var(--text-3)"><?= date('d/m H:i',strtotime($l['criado_em'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>
    <div class="card fade-up">
      <div style="padding:24px 0;text-align:center">
        <div style="font-size:48px;margin-bottom:16px">🌿</div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;color:var(--text-1);margin-bottom:8px">
          Bem-vindo ao SynAgro!
        </div>
        <div style="font-size:13px;color:var(--text-3);line-height:1.7;max-width:280px;margin:0 auto">
          Use o menu lateral para navegar entre os módulos da sua propriedade rural.
        </div>
        <div style="margin-top:20px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
          <a href="propriedades.php" class="btn btn-primary" style="font-size:12px;padding:8px 14px">🏡 Propriedades</a>
          <a href="culturas.php"     class="btn btn-ghost"   style="font-size:12px;padding:8px 14px">🌾 Culturas</a>
          <a href="estoque.php"      class="btn btn-ghost"   style="font-size:12px;padding:8px 14px">📦 Estoque</a>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>

</div>
</div>

</body>
</html>

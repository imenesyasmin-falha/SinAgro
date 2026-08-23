<?php
require_once '../config/conexao.php';
require_once '../includes/auth.php';
 
exigirLogin('../');
 
$pageTitle  = 'Dashboard';
$pageActive = 'dashboard';
 
$usuario = usuarioAtual();
$pdo     = conectar();
 
$aviso = '';
if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado') {
    $aviso = 'Você não tem permissão para acessar essa área.';
}
 
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
 
    $dados['total_culturas'] = $pdo
        ->query("SELECT COUNT(*) FROM culturas WHERE status = 'em_andamento' AND deleted_at IS NULL")
        ->fetchColumn();
 
    $dados['total_equipamentos'] = $pdo
        ->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'operacional' AND deleted_at IS NULL")
        ->fetchColumn();
 
    $dados['total_animais'] = $pdo
        ->query("SELECT COUNT(*) FROM animais WHERE status = 'ativo' AND deleted_at IS NULL")
        ->fetchColumn();
 
    $dados['estoque_critico']     = $pdo->query("SELECT COUNT(*) FROM vw_estoque_critico")->fetchColumn();
    $dados['manutencoes_abertas'] = $pdo->query("SELECT COUNT(*) FROM vw_equipamentos_em_manutencao")->fetchColumn();
 
    $fin = $pdo->prepare("
        SELECT
            SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) AS receitas,
            SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) AS despesas
        FROM movimentacoes_financeiras
        WHERE MONTH(data_movimentacao) = :mes
          AND YEAR(data_movimentacao)  = :ano
          AND deleted_at IS NULL
    ");
    $fin->execute([':mes' => date('m'), ':ano' => date('Y')]);
    $fin_row = $fin->fetch();
    $dados['receitas_mes'] = $fin_row['receitas'] ?? 0;
    $dados['despesas_mes'] = $fin_row['despesas'] ?? 0;
 
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
    error_log("[SinAgro Dashboard] " . $e->getMessage());
}
 
function moeda(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — SinAgro Sistema</title>
  <link rel="stylesheet" href="../assets/css/sinagro.css">
</head>
<body>
 
<?php include '../includes/layout.php'; ?>
 
<div class="main-content">
<div class="page-body">
 
  <?php if ($aviso): ?>
    <div class="alert alert-warn">⚠ <?= limpar($aviso) ?></div>
  <?php endif; ?>
 
  <div class="section-header fade-up" style="margin-bottom:24px">
    <div>
      <div class="section-title">
        Olá, <?= explode(' ', limpar($usuario['nome']))[0] ?>! 👋
      </div>
      <div class="section-sub">
        <?= date('d/m/Y') ?> &nbsp;·&nbsp; Perfil: <?= $usuario['label'] ?>
      </div>
    </div>
    <span class="badge badge-green"><?= $usuario['label'] ?></span>
  </div>
 
  <div class="grid grid-4" style="margin-bottom:24px">
 
    <div class="stat-card fade-up" style="--accent:#4ADE80">
      <span class="stat-icon">🏡</span>
      <div class="stat-value"><?= (int)$dados['total_propriedades'] ?></div>
      <div class="stat-label">Propriedades</div>
    </div>
 
    <div class="stat-card fade-up" style="--accent:#22C55E">
      <span class="stat-icon">🌾</span>
      <div class="stat-value"><?= (int)$dados['total_culturas'] ?></div>
      <div class="stat-label">Culturas em andamento</div>
    </div>
 
    <div class="stat-card fade-up" style="--accent:#60A5FA">
      <span class="stat-icon">🐄</span>
      <div class="stat-value"><?= (int)$dados['total_animais'] ?></div>
      <div class="stat-label">Animais ativos</div>
    </div>
 
    <div class="stat-card fade-up" style="--accent:#FBBF24">
      <span class="stat-icon">🚜</span>
      <div class="stat-value"><?= (int)$dados['total_equipamentos'] ?></div>
      <div class="stat-label">Equipamentos operacionais</div>
      <div class="stat-delta <?= $dados['manutencoes_abertas'] > 0 ? 'delta-down' : 'delta-up' ?>">
        <?= $dados['manutencoes_abertas'] ?> em manutenção
      </div>
    </div>
 
    <?php if (temAcesso('estoque')): ?>
    <div class="stat-card fade-up"
         style="--accent:<?= $dados['estoque_critico'] > 0 ? '#F87171' : '#4ADE80' ?>">
      <span class="stat-icon">📦</span>
      <div class="stat-value" style="color:<?= $dados['estoque_critico'] > 0 ? 'var(--red)' : 'var(--text-1)' ?>">
        <?= (int)$dados['estoque_critico'] ?>
      </div>
      <div class="stat-label">Estoque crítico</div>
      <div class="stat-delta <?= $dados['estoque_critico'] > 0 ? 'delta-down' : 'delta-up' ?>">
        <?= $dados['estoque_critico'] > 0 ? '⚠ Atenção' : '✓ OK' ?>
      </div>
    </div>
    <?php endif; ?>
 
    <?php if (temAcesso('equipamentos')): ?>
    <div class="stat-card fade-up"
         style="--accent:<?= $dados['manutencoes_abertas'] > 0 ? '#FBBF24' : '#4ADE80' ?>">
      <span class="stat-icon">🔧</span>
      <div class="stat-value"><?= (int)$dados['manutencoes_abertas'] ?></div>
      <div class="stat-label">Manutenções abertas</div>
    </div>
    <?php endif; ?>
 
    <?php if (temAcesso('financeiro')): ?>
    <div class="stat-card fade-up" style="--accent:#4ADE80">
      <span class="stat-icon">📈</span>
      <div class="stat-value" style="font-size:18px"><?= moeda((float)$dados['receitas_mes']) ?></div>
      <div class="stat-label">Receitas do mês</div>
      <div class="stat-delta delta-up">↑ <?= date('m/Y') ?></div>
    </div>
 
    <div class="stat-card fade-up"
         style="--accent:<?= $dados['despesas_mes'] > $dados['receitas_mes'] ? '#F87171' : '#FBBF24' ?>">
      <span class="stat-icon">📉</span>
      <div class="stat-value" style="font-size:18px"><?= moeda((float)$dados['despesas_mes']) ?></div>
      <div class="stat-label">Despesas do mês</div>
      <?php $saldo = $dados['receitas_mes'] - $dados['despesas_mes']; ?>
      <div class="stat-delta <?= $saldo >= 0 ? 'delta-up' : 'delta-down' ?>">
        Saldo: <?= moeda(abs((float)$saldo)) ?>
      </div>
    </div>
    <?php endif; ?>
 
  </div>
 
  <div class="grid grid-2">
 
    <?php if (!empty($dados['propriedades_lista'])): ?>
    <div class="card fade-up">
      <div class="card-header">
        <div>
          <div class="card-title">🏡 Propriedades Recentes</div>
          <div class="card-sub">Últimas cadastradas</div>
        </div>
        <?php if (temAcesso('propriedades')): ?>
          <a href="propriedades.php" class="btn btn-ghost" style="padding:6px 12px;font-size:12px">
            Ver todas →
          </a>
        <?php endif; ?>
      </div>
      <div class="table-wrap">
        <table class="syn-table">
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
              <td style="font-weight:600"><?= limpar($prop['nome']) ?></td>
              <td>
                <?= limpar($prop['municipio']) ?>/<?= limpar($prop['estado']) ?>
                <?php if ($prop['area_total_ha']): ?>
                  <br><span style="font-size:11px;color:var(--text-3)">
                    <?= number_format($prop['area_total_ha'], 1, ',', '.') ?> ha
                  </span>
                <?php endif; ?>
              </td>
              <?php if ($usuario['perfil'] === 'admin'): ?>
                <td style="color:var(--text-2)"><?= limpar($prop['dono']) ?></td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
 
    <?php if ($usuario['perfil'] === 'admin' && !empty($dados['logs_recentes'])): ?>
    <div class="card fade-up">
      <div class="card-header">
        <div>
          <div class="card-title">📋 Atividade Recente</div>
          <div class="card-sub">Últimas ações no sistema</div>
        </div>
        <a href="logs.php" class="btn btn-ghost" style="padding:6px 12px;font-size:12px">
          Ver logs →
        </a>
      </div>
      <div class="table-wrap">
        <table class="syn-table">
          <thead>
            <tr><th>Ação</th><th>Usuário</th><th>Data/Hora</th></tr>
          </thead>
          <tbody>
            <?php foreach ($dados['logs_recentes'] as $log): ?>
            <?php
              $badgeClass = match($log['acao']) {
                'login'           => 'badge-green',
                'login_falhou'    => 'badge-gold',
                'conta_bloqueada' => 'badge-red',
                'criar'           => 'badge-blue',
                'excluir'         => 'badge-red',
                default           => 'badge-gray',
              };
            ?>
            <tr>
              <td><span class="badge <?= $badgeClass ?>"><?= limpar($log['acao']) ?></span></td>
              <td style="color:var(--text-2)"><?= limpar($log['usuario_nome'] ?? '—') ?></td>
              <td style="font-size:11px;color:var(--text-3)">
                <?= date('d/m H:i', strtotime($log['criado_em'])) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
 
  </div>
 
  <?php if ($usuario['perfil'] === 'visualizador'): ?>
  <div class="card fade-up" style="margin-top:16px">
    <div class="empty-state">
      <span class="es-icon">🔒</span>
      <div class="es-title">Acesso de Visualização</div>
      <div class="es-sub">
        Seu perfil permite apenas visualizar dashboards e relatórios.<br>
        Solicite ao administrador caso precise de mais permissões.
      </div>
    </div>
  </div>
  <?php endif; ?>
 
</div>
</div>
 
</body>
</html>

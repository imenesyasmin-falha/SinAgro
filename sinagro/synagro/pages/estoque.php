<?php
// Gerador de páginas internas — inclua e customize $pageTitle, $pageActive, $tableData
require_once '../config/conexao.php';
require_once '../includes/auth.php';
exigirLogin('../');

/* ── Configuração por página ────────────────────────────────────────────── */
$slug = basename($_SERVER['SCRIPT_FILENAME'], '.php'); // ex: "estoque"

$config = [
  'propriedades' => [
    'title'   => 'Propriedades',
    'icon'    => '🏡',
    'sub'     => 'Fazendas e propriedades rurais cadastradas',
    'cols'    => ['Nome','Estado','Município','Área (ha)','Dono','Status'],
    'sql'     => "SELECT p.nome,p.estado,p.municipio,
                         IFNULL(p.area_total_ha,'—') area,
                         u.nome dono,
                         IF(p.ativa,'Ativa','Inativa') status
                  FROM propriedades p JOIN usuarios u ON u.id=p.usuario_id
                  WHERE p.deleted_at IS NULL ORDER BY p.criado_em DESC",
    'modulo'  => 'propriedades',
    'badge'   => ['Ativa'=>'badge-green','Inativa'=>'badge-gray'],
    'bIdx'    => 5,
  ],
  'culturas' => [
    'title'   => 'Culturas',
    'icon'    => '🌾',
    'sub'     => 'Culturas plantadas por área',
    'cols'    => ['Espécie','Área','Variedade','Plantio','Status'],
    'sql'     => "SELECT e.nome_comum especie, a.nome area, IFNULL(c.variedade,'—'),
                         IFNULL(DATE_FORMAT(c.data_plantio,'%d/%m/%Y'),'—'),c.status
                  FROM culturas c
                  JOIN especies e ON e.id=c.especie_id
                  JOIN areas_propriedades a ON a.id=c.area_id
                  WHERE c.deleted_at IS NULL ORDER BY c.criado_em DESC",
    'modulo'  => 'culturas',
    'badge'   => ['em_andamento'=>'badge-green','planejada'=>'badge-blue','colhida'=>'badge-gray','perdida'=>'badge-red','cancelada'=>'badge-red'],
    'bIdx'    => 4,
  ],
  'ciclos' => [
    'title'   => 'Ciclos de Plantio',
    'icon'    => '🔄',
    'sub'     => 'Etapas de cada cultura em campo',
    'cols'    => ['Etapa','Cultura','Início','Fim','Concluída'],
    'sql'     => "SELECT cp.etapa, e.nome_comum,
                         DATE_FORMAT(cp.data_inicio,'%d/%m/%Y'),
                         IFNULL(DATE_FORMAT(cp.data_fim,'%d/%m/%Y'),'—'),
                         IF(cp.concluida,'Sim','Não') conc
                  FROM ciclos_plantio cp
                  JOIN culturas c ON c.id=cp.cultura_id
                  JOIN especies e ON e.id=c.especie_id
                  ORDER BY cp.data_inicio DESC",
    'modulo'  => 'ciclos',
    'badge'   => ['Sim'=>'badge-green','Não'=>'badge-gold'],
    'bIdx'    => 4,
  ],
  'animais' => [
    'title'   => 'Rebanho',
    'icon'    => '🐄',
    'sub'     => 'Animais cadastrados nas propriedades',
    'cols'    => ['ID','Espécie','Raça','Sexo','Peso (kg)','Status'],
    'sql'     => "SELECT a.identificacao,e.nome_comum,IFNULL(a.raca,'—'),
                         CASE a.sexo WHEN 'M' THEN 'Macho' WHEN 'F' THEN 'Fêmea' ELSE '—' END,
                         IFNULL(a.peso_kg,'—'),a.status
                  FROM animais a JOIN especies e ON e.id=a.especie_id
                  WHERE a.deleted_at IS NULL ORDER BY a.criado_em DESC",
    'modulo'  => 'animais',
    'badge'   => ['ativo'=>'badge-green','vendido'=>'badge-gray','morto'=>'badge-red','abatido'=>'badge-red','transferido'=>'badge-blue'],
    'bIdx'    => 5,
  ],
  'estoque' => [
    'title'   => 'Estoque',
    'icon'    => '📦',
    'sub'     => 'Insumos, combustível, sementes e materiais',
    'cols'    => ['Item','Categoria','Qtd. Atual','Mínimo','Unid.','Status'],
    'sql'     => "SELECT e.nome,e.categoria,e.quantidade_atual,
                         IFNULL(e.quantidade_minima,'—'),e.unidade_medida,
                         CASE WHEN e.quantidade_minima IS NOT NULL AND e.quantidade_atual < e.quantidade_minima THEN 'Crítico' ELSE 'OK' END st
                  FROM estoque e WHERE e.ativo=1 AND e.deleted_at IS NULL ORDER BY e.nome",
    'modulo'  => 'estoque',
    'badge'   => ['Crítico'=>'badge-red','OK'=>'badge-green'],
    'bIdx'    => 5,
  ],
  'equipamentos' => [
    'title'   => 'Equipamentos',
    'icon'    => '🚜',
    'sub'     => 'Máquinas, veículos e implementos',
    'cols'    => ['Nome','Tipo','Fabricante','Ano','Combustível','Status'],
    'sql'     => "SELECT nome,tipo,IFNULL(fabricante,'—'),IFNULL(ano_fabricacao,'—'),combustivel,status
                  FROM equipamentos WHERE deleted_at IS NULL ORDER BY nome",
    'modulo'  => 'equipamentos',
    'badge'   => ['operacional'=>'badge-green','em_manutencao'=>'badge-gold','parado'=>'badge-red','vendido'=>'badge-gray','sucateado'=>'badge-gray'],
    'bIdx'    => 5,
  ],
  'manutencoes' => [
    'title'   => 'Manutenções',
    'icon'    => '🔧',
    'sub'     => 'Histórico de manutenções dos equipamentos',
    'cols'    => ['Equipamento','Tipo','Descrição','Abertura','Custo Total','Status'],
    'sql'     => "SELECT eq.nome,m.tipo,m.descricao,DATE_FORMAT(m.data_abertura,'%d/%m/%Y'),
                         CONCAT('R$ ',FORMAT(COALESCE(m.custo_total,0),2,'pt_BR')),m.status
                  FROM manutencoes m JOIN equipamentos eq ON eq.id=m.equipamento_id
                  ORDER BY m.data_abertura DESC",
    'modulo'  => 'manutencoes',
    'badge'   => ['aberta'=>'badge-blue','em_andamento'=>'badge-gold','concluida'=>'badge-green','cancelada'=>'badge-gray'],
    'bIdx'    => 5,
  ],
  'financeiro' => [
    'title'   => 'Financeiro',
    'icon'    => '💰',
    'sub'     => 'Movimentações financeiras da propriedade',
    'cols'    => ['Descrição','Tipo','Valor','Data','Pago'],
    'sql'     => "SELECT mf.descricao,mf.tipo,CONCAT('R$ ',FORMAT(mf.valor,2,'pt_BR')),
                         DATE_FORMAT(mf.data_movimentacao,'%d/%m/%Y'),IF(mf.pago,'Sim','Não')
                  FROM movimentacoes_financeiras mf
                  WHERE mf.deleted_at IS NULL ORDER BY mf.data_movimentacao DESC LIMIT 50",
    'modulo'  => 'financeiro',
    'badge'   => ['receita'=>'badge-green','despesa'=>'badge-red','transferencia'=>'badge-blue','Sim'=>'badge-green','Não'=>'badge-gold'],
    'bIdx'    => [1,4],
  ],
  'relatorios' => [
    'title'   => 'Relatórios',
    'icon'    => '📊',
    'sub'     => 'Resumos financeiros e de produção',
    'cols'    => ['Propriedade','Proprietário','Ano','Mês','Receitas','Despesas','Saldo'],
    'sql'     => "SELECT propriedade,proprietario,ano,mes,
                         CONCAT('R$ ',FORMAT(total_receitas,2,'pt_BR')),
                         CONCAT('R$ ',FORMAT(total_despesas,2,'pt_BR')),
                         CONCAT('R$ ',FORMAT(saldo,2,'pt_BR'))
                  FROM vw_resumo_financeiro ORDER BY ano DESC,mes DESC",
    'modulo'  => 'relatorios',
    'badge'   => [],
    'bIdx'    => -1,
  ],
  'usuarios' => [
    'title'   => 'Usuários',
    'icon'    => '👥',
    'sub'     => 'Contas cadastradas no sistema',
    'cols'    => ['Nome','E-mail','Perfil','Telefone','Verificado','Ativo'],
    'sql'     => "SELECT nome,email,perfil,IFNULL(telefone,'—'),
                         IF(email_verificado,'Sim','Não'),IF(ativo,'Sim','Não')
                  FROM usuarios WHERE deleted_at IS NULL ORDER BY criado_em DESC",
    'modulo'  => 'usuarios',
    'badge'   => ['admin'=>'badge-red','proprietario'=>'badge-green','gerente'=>'badge-gold','operador'=>'badge-blue','visualizador'=>'badge-gray','Sim'=>'badge-green','Não'=>'badge-gold'],
    'bIdx'    => [2,4,5],
  ],
  'logs' => [
    'title'   => 'Logs do Sistema',
    'icon'    => '📋',
    'sub'     => 'Auditoria completa de todas as ações',
    'cols'    => ['Ação','Usuário','Tabela','Descrição','IP','Data/Hora'],
    'sql'     => "SELECT l.acao,IFNULL(u.nome,'Sistema'),IFNULL(l.tabela_afetada,'—'),
                         IFNULL(l.descricao,'—'),IFNULL(l.ip_address,'—'),
                         DATE_FORMAT(l.criado_em,'%d/%m/%Y %H:%i')
                  FROM logs_sistema l LEFT JOIN usuarios u ON u.id=l.usuario_id
                  ORDER BY l.criado_em DESC LIMIT 100",
    'modulo'  => 'logs',
    'badge'   => ['login'=>'badge-green','login_falhou'=>'badge-gold','conta_bloqueada'=>'badge-red','criar'=>'badge-blue','excluir'=>'badge-red','erro'=>'badge-red'],
    'bIdx'    => 0,
  ],
];

if (!isset($config[$slug])) { header('Location: dashboard.php'); exit; }

$cfg        = $config[$slug];
$pageTitle  = $cfg['title'];
$pageActive = $slug;
$usuario    = usuarioAtual();

// Controle de acesso
if (!temAcesso($cfg['modulo'])) {
    header('Location: dashboard.php?erro=acesso_negado'); exit;
}

// Busca dados
$rows = [];
try {
    $pdo  = conectar();
    $rows = $pdo->query($cfg['sql'])->fetchAll(PDO::FETCH_NUM);
} catch(PDOException $e){ error_log($e->getMessage()); }

function badgify(string $val, array $map): string {
    foreach ($map as $k => $cls) {
        if (strtolower($val) === strtolower($k)) {
            return "<span class=\"badge {$cls}\">{$val}</span>";
        }
    }
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $cfg['title'] ?> — SynAgro</title>
<link rel="stylesheet" href="../assets/css/synagro.css">
</head>
<body>

<?php include '../includes/layout.php'; ?>

<div class="main-content">
<div class="page-body">

  <div class="section-header fade-up" style="margin-bottom:24px">
    <div>
      <div class="section-title"><?= $cfg['icon'] ?> <?= $cfg['title'] ?></div>
      <div class="section-sub"><?= $cfg['sub'] ?></div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <span class="badge badge-gray"><?= count($rows) ?> registro(s)</span>
      <button class="btn btn-primary" style="font-size:12px;padding:8px 14px">+ Novo</button>
    </div>
  </div>

  <div class="card fade-up">

    <!-- Busca rápida -->
    <div style="margin-bottom:16px;display:flex;gap:10px;align-items:center">
      <input class="form-control" type="text" id="busca" placeholder="Buscar..."
             style="max-width:280px" oninput="filtrar(this.value)">
      <span style="font-size:12px;color:var(--text-3)" id="contagem"><?= count($rows) ?> itens</span>
    </div>

    <?php if(empty($rows)): ?>
      <div class="empty-state">
        <span class="es-icon"><?= $cfg['icon'] ?></span>
        <div class="es-title">Nenhum registro encontrado</div>
        <div class="es-sub">Clique em "+ Novo" para cadastrar o primeiro item.</div>
      </div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="syn-table" id="mainTable">
        <thead>
          <tr>
            <?php foreach($cfg['cols'] as $c): ?><th><?= $c ?></th><?php endforeach; ?>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $r): ?>
          <tr>
            <?php foreach($r as $ci => $cell): ?>
            <td>
              <?php
                $bIdxArr = is_array($cfg['bIdx']) ? $cfg['bIdx'] : [$cfg['bIdx']];
                if (in_array($ci, $bIdxArr)) {
                    echo badgify((string)$cell, $cfg['badge']);
                } else {
                    echo '<span>'.htmlspecialchars($cell,ENT_QUOTES,'UTF-8').'</span>';
                }
              ?>
            </td>
            <?php endforeach; ?>
            <td>
              <div style="display:flex;gap:6px">
                <button class="btn btn-ghost" style="padding:4px 10px;font-size:11px">✏ Editar</button>
                <button class="btn btn-danger" style="padding:4px 10px;font-size:11px">🗑</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

  </div>

</div>
</div>

<script>
function filtrar(v){
  const t=document.getElementById('mainTable');
  if(!t)return;
  const rows=t.querySelectorAll('tbody tr');
  let vis=0;
  rows.forEach(r=>{
    const show=r.textContent.toLowerCase().includes(v.toLowerCase());
    r.style.display=show?'':'none';
    if(show)vis++;
  });
  document.getElementById('contagem').textContent=vis+' itens';
}
</script>

</body>
</html>

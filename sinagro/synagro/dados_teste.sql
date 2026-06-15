USE synagro_db;

INSERT INTO usuarios
    (nome, email, senha_hash, perfil, telefone, ativo, email_verificado)
VALUES
(
    'Admin SynAgro',
    'admin@synagro.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    '(19) 98939-9593',
    1, 1
),
(
    'João da Silva',
    'produtor@synagro.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'proprietario',
    '(11) 99123-4567',
    1, 1
),
(
    'Maria Oliveira',
    'gerente@synagro.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'gerente',
    '(11) 98765-4321',
    1, 1
),
(
    'Carlos Souza',
    'operador@synagro.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'operador',
    NULL,
    1, 1
),
(
    'Ana Ferreira',
    'visita@synagro.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'visualizador',
    NULL,
    1, 1
);
-- -----------------------------------------------------------------------------
INSERT INTO propriedades
    (usuario_id, nome, estado, municipio, area_total_ha, descricao, ativa)
VALUES
(2, 'Fazenda Boa Esperança', 'SP', 'Campinas',      320.50, 'Fazenda de soja e milho com biodigestor', 1),
(2, 'Sítio São João',        'MG', 'Uberlândia',    180.00, 'Produção de leite e pastagem regenerativa', 1),
(3, 'Chácara Verde',         'PR', 'Londrina',       85.75, 'Horticultura e fruticultura orgânica', 1);
-- -----------------------------------------------------------------------------
INSERT INTO areas_propriedades
    (propriedade_id, nome, tipo, area_ha, solo_tipo, irrigada)
VALUES
(1, 'Talhão 1 — Soja',     'talhao', 120.00, 'Latossolo Vermelho',    0),
(1, 'Talhão 2 — Milho',    'talhao',  90.00, 'Latossolo Vermelho',    1),
(1, 'Reserva Legal',       'reserva_legal', 64.00, NULL,               0),
(2, 'Piquete A',           'piquete',  60.00, 'Argissolo Vermelho',   1),
(2, 'Piquete B',           'piquete',  80.00, 'Argissolo Vermelho',   0),
(3, 'Canteiro Principal',  'gleba',    40.00, 'Solo Arenoso',         1);
-- -----------------------------------------------------------------------------
INSERT INTO culturas
    (area_id, especie_id, usuario_id, variedade, data_plantio, status)
VALUES
(1, 1, 2, 'Soja Intacta RR2', '2025-10-15', 'em_andamento'),
(2, 2, 2, 'Milho 2B710PW',    '2025-11-01', 'em_andamento'),
(4, 6, 3, 'Braquiária BRS',   '2025-09-01', 'em_andamento');
-- -----------------------------------------------------------------------------
INSERT INTO animais
    (propriedade_id, especie_id, identificacao, raca, sexo, peso_kg, status)
VALUES
(2, 8, 'BOV-001', 'Girolando', 'F', 520.0, 'ativo'),
(2, 8, 'BOV-002', 'Girolando', 'F', 495.0, 'ativo'),
(2, 8, 'BOV-003', 'Nelore',    'M', 620.0, 'ativo');
-- -----------------------------------------------------------------------------
INSERT INTO equipamentos
    (propriedade_id, nome, tipo, fabricante, modelo, ano_fabricacao,
     combustivel, status)
VALUES
(1, 'Trator John Deere', 'trator', 'John Deere', '6110J', 2021, 'diesel', 'operacional'),
(1, 'Plantadeira', 'plantadeira', 'Jumil', 'Exacta Plus', 2020, 'nao_aplica', 'operacional'),
(1, 'Biodigestor',  'biodigestor', NULL, NULL, NULL, 'nao_aplica', 'operacional'),
(2, 'Trator Massey', 'trator', 'Massey Ferguson', 'MF 4710', 2019, 'diesel', 'em_manutencao');
-- -----------------------------------------------------------------------------
INSERT INTO estoque
    (propriedade_id, nome, categoria, unidade_medida,
     quantidade_atual, quantidade_minima, custo_unitario, localizacao)
VALUES
(1, 'Semente de Soja',   'semente',      'sc',  80,  20, 145.00, 'Galpão 1'),
(1, 'Semente de Milho',  'semente',      'sc',  45,  10, 112.00, 'Galpão 1'),
(1, 'Diesel S-10',       'combustivel',  'L',  800, 200,   5.89, 'Tanque Principal'),
(1, 'Ureia',             'fertilizante', 'sc',  12,  15,  98.00, 'Depósito'), 
(2, 'Ração Bovino',      'racao',        'kg', 500, 100,   1.85, 'Silo 1');
-- -----------------------------------------------------------------------------
INSERT INTO movimentacoes_estoque
    (estoque_id, usuario_id, tipo, quantidade, motivo, data_movimentacao)
VALUES
(1, 2, 'entrada', 80, 'Compra NF-1042', '2025-09-10'),
(3, 2, 'entrada', 800, 'Abastecimento tanque', '2025-10-01'),
(4, 2, 'saida',   3, 'Aplicação Talhão 1', '2025-10-20');
-- -----------------------------------------------------------------------------
INSERT INTO movimentacoes_financeiras
    (propriedade_id, categoria_id, usuario_id, tipo, descricao,
     valor, data_movimentacao, pago)
VALUES
(1, 2, 2, 'receita', 'Venda Soja Safra 24/25', 48000.00, CURDATE(), 1),
(1, 7, 2, 'despesa', 'Diesel Outubro',          2850.00, CURDATE(), 1),
(1, 9, 2, 'despesa', 'Sementes Soja',           11600.00, CURDATE(), 1),
(2, 3, 2, 'receita', 'Venda Leite Outubro',      3200.00, CURDATE(), 1);
-- -----------------------------------------------------------------------------
INSERT INTO manutencoes
    (equipamento_id, usuario_id, tipo, descricao,
     data_abertura, custo_pecas, custo_mao_obra, status)
VALUES
(4, 3, 'corretiva', 'Troca de filtro de óleo e revisão geral',
 CURDATE(), 320.00, 250.00, 'em_andamento');
-- -----------------------------------------------------------------------------
INSERT INTO logs_sistema
    (usuario_id, acao, tabela_afetada, registro_id, descricao, ip_address)
VALUES
(1, 'criar', 'usuarios', 2, 'Usuário João da Silva cadastrado', '127.0.0.1'),
(1, 'criar', 'propriedades', 1, 'Fazenda Boa Esperança cadastrada', '127.0.0.1'),
(2, 'login', 'usuarios', 2, 'Login realizado com sucesso — perfil: proprietario', '127.0.0.1');

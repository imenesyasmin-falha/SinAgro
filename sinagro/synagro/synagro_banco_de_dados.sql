SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';
 
DROP DATABASE IF EXISTS synagro_db;
CREATE DATABASE synagro_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
 
USE synagro_db;
-- =============================================================================
CREATE TABLE usuarios (
    id               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nome             VARCHAR(120)     NOT NULL,
    email            VARCHAR(180)     NOT NULL,
    senha_hash       VARCHAR(255)     NOT NULL         COMMENT 'bcrypt hash — NUNCA armazenar senha plana',
    perfil           ENUM(
                         'admin',
                         'proprietario',
                         'gerente',
                         'operador',
                         'visualizador'
                     )                NOT NULL DEFAULT 'operador',
    telefone         VARCHAR(20)          NULL,
    foto_url         VARCHAR(500)         NULL,
    token_refresh    VARCHAR(512)         NULL         COMMENT 'JWT refresh token',
    tentativas_login TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_ate    DATETIME             NULL         COMMENT 'NULL = conta desbloqueada',
    ativo            TINYINT(1)       NOT NULL DEFAULT 1,
    email_verificado TINYINT(1)       NOT NULL DEFAULT 0,
    criado_em        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at       DATETIME             NULL         COMMENT 'soft-delete',
 
    CONSTRAINT pk_usuarios PRIMARY KEY (id),
    CONSTRAINT uq_usuarios_email UNIQUE (email),
    CONSTRAINT chk_usuarios_tentativas CHECK (tentativas_login <= 10)
) ENGINE=InnoDB COMMENT='Usuários do sistema SynAgro';
 
CREATE INDEX idx_usuarios_email   ON usuarios (email);
CREATE INDEX idx_usuarios_perfil  ON usuarios (perfil);
CREATE INDEX idx_usuarios_ativo   ON usuarios (ativo);
-- =============================================================================
CREATE TABLE propriedades (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    usuario_id      INT UNSIGNED   NOT NULL,
    nome            VARCHAR(150)   NOT NULL,
    cad_itr         VARCHAR(30)        NULL COMMENT 'Cadastro Rural ITR / NIRF',
    car_numero      VARCHAR(80)        NULL COMMENT 'Cadastro Ambiental Rural',
    estado          CHAR(2)        NOT NULL COMMENT 'UF — ex: SP, MG, MT',
    municipio       VARCHAR(100)   NOT NULL,
    cep             CHAR(8)            NULL,
    latitude        DECIMAL(10,7)      NULL,
    longitude       DECIMAL(10,7)      NULL,
    area_total_ha   DECIMAL(12,4)      NULL CHECK (area_total_ha > 0),
    descricao       TEXT               NULL,
    ativa           TINYINT(1)     NOT NULL DEFAULT 1,
    criado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME           NULL,
 
    CONSTRAINT pk_propriedades  PRIMARY KEY (id),
    CONSTRAINT fk_prop_usuario  FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Propriedades rurais cadastradas no SynAgro';
 
CREATE INDEX idx_prop_usuario ON propriedades (usuario_id);
CREATE INDEX idx_prop_estado  ON propriedades (estado);
 
-- =============================================================================
CREATE TABLE areas_propriedades (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    propriedade_id  INT UNSIGNED   NOT NULL,
    nome            VARCHAR(100)   NOT NULL,
    tipo            ENUM(
                        'talhao',
                        'piquete',
                        'gleba',
                        'reserva_legal',
                        'app',
                        'outro'
                    )              NOT NULL DEFAULT 'talhao',
    area_ha         DECIMAL(12,4)  NOT NULL CHECK (area_ha > 0),
    solo_tipo       VARCHAR(80)        NULL COMMENT 'Ex: Latossolo Vermelho',
    irrigada        TINYINT(1)     NOT NULL DEFAULT 0,
    observacoes     TEXT               NULL,
    ativa           TINYINT(1)     NOT NULL DEFAULT 1,
    criado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME           NULL,
 
    CONSTRAINT pk_areas          PRIMARY KEY (id),
    CONSTRAINT fk_areas_prop     FOREIGN KEY (propriedade_id)
        REFERENCES propriedades (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Talhões e subdivisões das propriedades';
 
CREATE INDEX idx_areas_prop ON areas_propriedades (propriedade_id);
CREATE INDEX idx_areas_tipo ON areas_propriedades (tipo);
 
-- =============================================================================
CREATE TABLE especies (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    nome_comum      VARCHAR(120)   NOT NULL,
    nome_cientifico VARCHAR(180)       NULL,
    categoria       ENUM(
                        'grao',
                        'hortalica',
                        'fruta',
                        'fibra',
                        'forrageira',
                        'florestal',
                        'bovino',
                        'suino',
                        'avicola',
                        'equino',
                        'caprino',
                        'piscicultura',
                        'outro'
                    )              NOT NULL,
    ciclo_dias_min  SMALLINT UNSIGNED  NULL COMMENT 'Ciclo mínimo em dias',
    ciclo_dias_max  SMALLINT UNSIGNED  NULL COMMENT 'Ciclo máximo em dias',
    observacoes     TEXT               NULL,
    criado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 
    CONSTRAINT pk_especies          PRIMARY KEY (id),
    CONSTRAINT uq_especies_nome     UNIQUE (nome_comum),
    CONSTRAINT chk_ciclo_consistente CHECK (
        ciclo_dias_min IS NULL OR ciclo_dias_max IS NULL OR
        ciclo_dias_min <= ciclo_dias_max
    )
) ENGINE=InnoDB COMMENT='Catálogo de espécies vegetais e animais';
 
CREATE INDEX idx_especies_categoria ON especies (categoria);
 
-- =============================================================================
CREATE TABLE culturas (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    area_id         INT UNSIGNED   NOT NULL,
    especie_id      INT UNSIGNED   NOT NULL,
    usuario_id      INT UNSIGNED   NOT NULL  COMMENT 'Responsável pelo registro',
    variedade       VARCHAR(100)       NULL,
    data_plantio    DATE               NULL,
    data_colheita   DATE               NULL,
    producao_kg     DECIMAL(12,3)      NULL CHECK (producao_kg >= 0),
    status          ENUM(
                        'planejada',
                        'em_andamento',
                        'colhida',
                        'perdida',
                        'cancelada'
                    )              NOT NULL DEFAULT 'planejada',
    observacoes     TEXT               NULL,
    criado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME           NULL,
 
    CONSTRAINT pk_culturas       PRIMARY KEY (id),
    CONSTRAINT fk_cult_area      FOREIGN KEY (area_id)
        REFERENCES areas_propriedades (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_cult_especie   FOREIGN KEY (especie_id)
        REFERENCES especies (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_cult_usuario   FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_cult_datas    CHECK (
        data_plantio IS NULL OR data_colheita IS NULL OR
        data_colheita >= data_plantio
    )
) ENGINE=InnoDB COMMENT='Culturas plantadas por área';
 
CREATE INDEX idx_cult_area    ON culturas (area_id);
CREATE INDEX idx_cult_especie ON culturas (especie_id);
CREATE INDEX idx_cult_status  ON culturas (status);
 
-- =============================================================================
CREATE TABLE ciclos_plantio (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    cultura_id      INT UNSIGNED   NOT NULL,
    etapa           VARCHAR(80)    NOT NULL COMMENT 'Ex: Germinação, Adubação, Colheita',
    data_inicio     DATE           NOT NULL,
    data_fim        DATE               NULL,
    insumos_usados  TEXT               NULL COMMENT 'Descrição livre dos insumos',
    custo_estimado  DECIMAL(12,2)      NULL CHECK (custo_estimado >= 0),
    custo_real      DECIMAL(12,2)      NULL CHECK (custo_real >= 0),
    concluida       TINYINT(1)     NOT NULL DEFAULT 0,
    observacoes     TEXT               NULL,
    criado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 
    CONSTRAINT pk_ciclos         PRIMARY KEY (id),
    CONSTRAINT fk_ciclos_cultura FOREIGN KEY (cultura_id)
        REFERENCES culturas (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_ciclos_datas  CHECK (
        data_fim IS NULL OR data_fim >= data_inicio
    )
) ENGINE=InnoDB COMMENT='Etapas e fases de cada ciclo de plantio';
 
CREATE INDEX idx_ciclos_cultura   ON ciclos_plantio (cultura_id);
CREATE INDEX idx_ciclos_concluida ON ciclos_plantio (concluida);
 
-- =============================================================================
CREATE TABLE animais (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    propriedade_id  INT UNSIGNED   NOT NULL,
    especie_id      INT UNSIGNED   NOT NULL,
    identificacao   VARCHAR(60)    NOT NULL COMMENT 'Brinco, chip, nome etc.',
    raca            VARCHAR(80)        NULL,
    sexo            ENUM('M','F','I')  NOT NULL DEFAULT 'I' COMMENT 'M=Macho F=Fêmea I=Indefinido',
    data_nascimento DATE               NULL,
    peso_kg         DECIMAL(8,2)       NULL CHECK (peso_kg > 0),
    status          ENUM(
                        'ativo',
                        'vendido',
                        'abatido',
                        'morto',
                        'transferido'
                    )              NOT NULL DEFAULT 'ativo',
    observacoes     TEXT               NULL,
    criado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME           NULL,
 
    CONSTRAINT pk_animais        PRIMARY KEY (id),
    CONSTRAINT fk_anim_prop      FOREIGN KEY (propriedade_id)
        REFERENCES propriedades (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_anim_especie   FOREIGN KEY (especie_id)
        REFERENCES especies (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Rebanho e plantel das propriedades';
 
CREATE INDEX idx_anim_prop    ON animais (propriedade_id);
CREATE INDEX idx_anim_status  ON animais (status);
CREATE INDEX idx_anim_especie ON animais (especie_id);
 
-- =============================================================================
CREATE TABLE categorias_financeiras (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    parent_id   INT UNSIGNED       NULL COMMENT 'NULL = categoria raiz',
    nome        VARCHAR(100)   NOT NULL,
    tipo        ENUM('receita','despesa','transferencia') NOT NULL,
    icone       VARCHAR(50)        NULL COMMENT 'Nome do ícone (Feather, Material)',
    cor_hex     CHAR(7)            NULL COMMENT 'Ex: #4CAF50',
    ativa       TINYINT(1)     NOT NULL DEFAULT 1,
    criado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 
    CONSTRAINT pk_cat_fin        PRIMARY KEY (id),
    CONSTRAINT fk_cat_parent     FOREIGN KEY (parent_id)
        REFERENCES categorias_financeiras (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT uq_cat_nome_tipo  UNIQUE (nome, tipo)
) ENGINE=InnoDB COMMENT='Categorias de receitas e despesas';
 
CREATE INDEX idx_cat_tipo ON categorias_financeiras (tipo);
 
-- =============================================================================
CREATE TABLE movimentacoes_financeiras (
    id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    propriedade_id  INT UNSIGNED     NOT NULL,
    categoria_id    INT UNSIGNED     NOT NULL,
    usuario_id      INT UNSIGNED     NOT NULL  COMMENT 'Quem registrou',
    tipo            ENUM('receita','despesa','transferencia') NOT NULL,
    descricao       VARCHAR(255)     NOT NULL,
    valor           DECIMAL(14,2)    NOT NULL CHECK (valor > 0),
    data_movimentacao DATE           NOT NULL,
    comprovante_url VARCHAR(500)         NULL COMMENT 'URL do anexo (nota fiscal etc.)',
    pago            TINYINT(1)       NOT NULL DEFAULT 0,
    data_pagamento  DATE                 NULL,
    observacoes     TEXT                 NULL,
    criado_em       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME             NULL,
 
    CONSTRAINT pk_mov_fin        PRIMARY KEY (id),
    CONSTRAINT fk_movfin_prop    FOREIGN KEY (propriedade_id)
        REFERENCES propriedades (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_movfin_cat     FOREIGN KEY (categoria_id)
        REFERENCES categorias_financeiras (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_movfin_user    FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Movimentações financeiras da propriedade';
 
CREATE INDEX idx_movfin_prop  ON movimentacoes_financeiras (propriedade_id);
CREATE INDEX idx_movfin_data  ON movimentacoes_financeiras (data_movimentacao);
CREATE INDEX idx_movfin_tipo  ON movimentacoes_financeiras (tipo);
CREATE INDEX idx_movfin_pago  ON movimentacoes_financeiras (pago);
 
-- =============================================================================
CREATE TABLE estoque (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    propriedade_id  INT UNSIGNED   NOT NULL,
    nome            VARCHAR(150)   NOT NULL,
    categoria       ENUM(
                        'semente',
                        'fertilizante',
                        'agrotoxico',
                        'combustivel',
                        'racao',
                        'medicamento',
                        'equipamento_peca',
                        'embalagem',
                        'outro'
                    )              NOT NULL,
    unidade_medida  VARCHAR(20)    NOT NULL COMMENT 'Ex: kg, L, sc, un',
    quantidade_atual DECIMAL(14,4) NOT NULL DEFAULT 0 CHECK (quantidade_atual >= 0),
    quantidade_minima DECIMAL(14,4)    NULL CHECK (quantidade_minima >= 0)
                                        COMMENT 'Alerta de estoque mínimo',
    custo_unitario  DECIMAL(12,4)      NULL CHECK (custo_unitario >= 0),
    localizacao     VARCHAR(100)       NULL COMMENT 'Depósito, galpão, silo etc.',
    ativo           TINYINT(1)     NOT NULL DEFAULT 1,
    criado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME           NULL,
 
    CONSTRAINT pk_estoque       PRIMARY KEY (id),
    CONSTRAINT fk_est_prop      FOREIGN KEY (propriedade_id)
        REFERENCES propriedades (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Itens em estoque da propriedade';
 
CREATE INDEX idx_est_prop      ON estoque (propriedade_id);
CREATE INDEX idx_est_categoria ON estoque (categoria);
 
-- =============================================================================
CREATE TABLE movimentacoes_estoque (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    estoque_id      INT UNSIGNED   NOT NULL,
    usuario_id      INT UNSIGNED   NOT NULL,
    tipo            ENUM('entrada','saida','ajuste') NOT NULL,
    quantidade      DECIMAL(14,4)  NOT NULL CHECK (quantidade > 0),
    custo_unitario  DECIMAL(12,4)      NULL CHECK (custo_unitario >= 0),
    motivo          VARCHAR(200)       NULL COMMENT 'Ex: Compra NF-001, Aplicação Talhão 3',
    data_movimentacao DATE          NOT NULL,
    documento_ref   VARCHAR(100)       NULL COMMENT 'Nota fiscal, ordem de serviço etc.',
    criado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 
    CONSTRAINT pk_movest        PRIMARY KEY (id),
    CONSTRAINT fk_movest_est    FOREIGN KEY (estoque_id)
        REFERENCES estoque (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_movest_user   FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Movimentações de entrada e saída de estoque';
 
CREATE INDEX idx_movest_est  ON movimentacoes_estoque (estoque_id);
CREATE INDEX idx_movest_data ON movimentacoes_estoque (data_movimentacao);
CREATE INDEX idx_movest_tipo ON movimentacoes_estoque (tipo);
 
-- =============================================================================
CREATE TABLE equipamentos (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    propriedade_id  INT UNSIGNED   NOT NULL,
    nome            VARCHAR(150)   NOT NULL,
    tipo            ENUM(
                        'trator',
                        'colheitadeira',
                        'plantadeira',
                        'pulverizador',
                        'veiculo',
                        'bomba',
                        'gerador',
                        'biodigestor',
                        'implemento',
                        'outro'
                    )              NOT NULL,
    fabricante      VARCHAR(80)        NULL,
    modelo          VARCHAR(80)        NULL,
    ano_fabricacao  YEAR               NULL,
    numero_serie    VARCHAR(80)        NULL,
    placa           VARCHAR(10)        NULL,
    horimetro_atual DECIMAL(10,1)      NULL CHECK (horimetro_atual >= 0)
                                        COMMENT 'Horas de uso acumuladas',
    km_atual        DECIMAL(10,1)      NULL CHECK (km_atual >= 0),
    combustivel     ENUM(
                        'diesel',
                        'gasolina',
                        'etanol',
                        'eletrico',
                        'flex',
                        'nao_aplica'
                    )              NOT NULL DEFAULT 'nao_aplica',
    status          ENUM(
                        'operacional',
                        'em_manutencao',
                        'parado',
                        'vendido',
                        'sucateado'
                    )              NOT NULL DEFAULT 'operacional',
    foto_url        VARCHAR(500)       NULL,
    observacoes     TEXT               NULL,
    criado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME           NULL,
 
    CONSTRAINT pk_equip         PRIMARY KEY (id),
    CONSTRAINT fk_equip_prop    FOREIGN KEY (propriedade_id)
        REFERENCES propriedades (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Equipamentos e máquinas da propriedade';
 
CREATE INDEX idx_equip_prop   ON equipamentos (propriedade_id);
CREATE INDEX idx_equip_tipo   ON equipamentos (tipo);
CREATE INDEX idx_equip_status ON equipamentos (status);
 
-- =============================================================================
CREATE TABLE manutencoes (
    id                  INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    equipamento_id      INT UNSIGNED   NOT NULL,
    usuario_id          INT UNSIGNED   NOT NULL  COMMENT 'Responsável pelo registro',
    tipo                ENUM(
                            'preventiva',
                            'corretiva',
                            'revisao',
                            'troca_oleo',
                            'troca_filtro',
                            'calibragem',
                            'outro'
                        )              NOT NULL,
    descricao           VARCHAR(255)   NOT NULL,
    data_abertura       DATE           NOT NULL,
    data_conclusao      DATE               NULL,
    custo_pecas         DECIMAL(12,2)      NULL CHECK (custo_pecas >= 0),
    custo_mao_obra      DECIMAL(12,2)      NULL CHECK (custo_mao_obra >= 0),
    custo_total         DECIMAL(12,2)
        AS (COALESCE(custo_pecas, 0) + COALESCE(custo_mao_obra, 0)) STORED
        COMMENT 'Calculado automaticamente',
    prestador           VARCHAR(120)       NULL COMMENT 'Oficina ou técnico externo',
    proxima_revisao_km  DECIMAL(10,1)      NULL,
    proxima_revisao_hr  DECIMAL(10,1)      NULL,
    status              ENUM(
                            'aberta',
                            'em_andamento',
                            'concluida',
                            'cancelada'
                        )              NOT NULL DEFAULT 'aberta',
    observacoes         TEXT               NULL,
    criado_em           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 
    CONSTRAINT pk_manut          PRIMARY KEY (id),
    CONSTRAINT fk_manut_equip    FOREIGN KEY (equipamento_id)
        REFERENCES equipamentos (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_manut_user     FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_manut_datas   CHECK (
        data_conclusao IS NULL OR data_conclusao >= data_abertura
    )
) ENGINE=InnoDB COMMENT='Histórico de manutenções dos equipamentos';
 
CREATE INDEX idx_manut_equip  ON manutencoes (equipamento_id);
CREATE INDEX idx_manut_status ON manutencoes (status);
CREATE INDEX idx_manut_data   ON manutencoes (data_abertura);
 
-- =============================================================================
CREATE TABLE logs_sistema (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    usuario_id      INT UNSIGNED         NULL COMMENT 'NULL = ação do sistema/anônimo',
    acao            ENUM(
                        'login',
                        'logout',
                        'login_falhou',
                        'conta_bloqueada',
                        'senha_alterada',
                        'criar',
                        'editar',
                        'excluir',
                        'restaurar',
                        'visualizar',
                        'exportar',
                        'importar',
                        'erro'
                    )                NOT NULL,
    tabela_afetada  VARCHAR(80)          NULL COMMENT 'Ex: usuarios, culturas, estoque',
    registro_id     INT UNSIGNED         NULL COMMENT 'ID do registro afetado',
    descricao       VARCHAR(500)         NULL,
    dados_anteriores JSON                NULL COMMENT 'Snapshot ANTES da alteração',
    dados_novos     JSON                NULL COMMENT 'Snapshot APÓS a alteração',
    ip_address      VARCHAR(45)          NULL COMMENT 'IPv4 ou IPv6',
    user_agent      VARCHAR(300)         NULL,
    criado_em       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
 
    CONSTRAINT pk_logs PRIMARY KEY (id),
    CONSTRAINT fk_logs_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Auditoria completa — append-only, nunca alterar ou excluir';
 
CREATE INDEX idx_logs_usuario ON logs_sistema (usuario_id);
CREATE INDEX idx_logs_acao    ON logs_sistema (acao);
CREATE INDEX idx_logs_tabela  ON logs_sistema (tabela_afetada);
CREATE INDEX idx_logs_data    ON logs_sistema (criado_em);
 
-- =============================================================================
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
DELIMITER $$
 
CREATE TRIGGER trg_movest_after_insert
AFTER INSERT ON movimentacoes_estoque
FOR EACH ROW
BEGIN
    IF NEW.tipo = 'entrada' THEN
        UPDATE estoque
        SET quantidade_atual = quantidade_atual + NEW.quantidade
        WHERE id = NEW.estoque_id;
    ELSEIF NEW.tipo = 'saida' THEN
        UPDATE estoque
        SET quantidade_atual = quantidade_atual - NEW.quantidade
        WHERE id = NEW.estoque_id;
    ELSEIF NEW.tipo = 'ajuste' THEN
        UPDATE estoque
        SET quantidade_atual = NEW.quantidade
        WHERE id = NEW.estoque_id;
    END IF;
END$$
 
-- =============================================================================
DELIMITER $$

CREATE TRIGGER trg_bloquear_conta
BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.tentativas_login >= 5 AND OLD.tentativas_login < 5 THEN
        SET NEW.bloqueado_ate = DATE_ADD(NOW(), INTERVAL 30 MINUTE);
    END IF;
END$$

DELIMITER ;
 
-- =============================================================================
DELIMITER $$

CREATE TRIGGER trg_log_usuario_delete
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL THEN
        INSERT INTO logs_sistema (
            usuario_id, acao, tabela_afetada, registro_id, descricao
        ) VALUES (
            NEW.id,
            'excluir',
            'usuarios',
            NEW.id,
            CONCAT('Soft-delete do usuário: ', NEW.email)
        );
    END IF;
END$$

DELIMITER ;

-- =============================================================================
CREATE OR REPLACE VIEW vw_resumo_financeiro AS
SELECT
    p.id                                    AS propriedade_id,
    p.nome                                  AS propriedade,
    u.nome                                  AS proprietario,
    YEAR(mf.data_movimentacao)              AS ano,
    MONTH(mf.data_movimentacao)             AS mes,
    SUM(CASE WHEN mf.tipo = 'receita'  THEN mf.valor ELSE 0 END) AS total_receitas,
    SUM(CASE WHEN mf.tipo = 'despesa'  THEN mf.valor ELSE 0 END) AS total_despesas,
    SUM(CASE WHEN mf.tipo = 'receita'  THEN mf.valor ELSE 0 END) -
    SUM(CASE WHEN mf.tipo = 'despesa'  THEN mf.valor ELSE 0 END) AS saldo
FROM movimentacoes_financeiras mf
JOIN propriedades p ON p.id = mf.propriedade_id
JOIN usuarios     u ON u.id = p.usuario_id
WHERE mf.deleted_at IS NULL
GROUP BY p.id, p.nome, u.nome, YEAR(mf.data_movimentacao), MONTH(mf.data_movimentacao);
 
-- =============================================================================
CREATE OR REPLACE VIEW vw_estoque_critico AS
SELECT
    e.id,
    p.nome                  AS propriedade,
    e.nome                  AS item,
    e.categoria,
    e.quantidade_atual,
    e.quantidade_minima,
    e.unidade_medida,
    (e.quantidade_minima - e.quantidade_atual) AS deficit
FROM estoque e
JOIN propriedades p ON p.id = e.propriedade_id
WHERE e.ativo = 1
  AND e.deleted_at IS NULL
  AND e.quantidade_minima IS NOT NULL
  AND e.quantidade_atual < e.quantidade_minima;

-- =============================================================================
CREATE OR REPLACE VIEW vw_equipamentos_em_manutencao AS
SELECT
    eq.id                   AS equipamento_id,
    p.nome                  AS propriedade,
    eq.nome                 AS equipamento,
    eq.tipo,
    m.tipo                  AS tipo_manutencao,
    m.descricao,
    m.data_abertura,
    m.custo_total,
    u.nome                  AS responsavel
FROM manutencoes m
JOIN equipamentos eq ON eq.id = m.equipamento_id
JOIN propriedades  p ON p.id  = eq.propriedade_id
JOIN usuarios      u ON u.id  = m.usuario_id
WHERE m.status IN ('aberta', 'em_andamento');
 
-- =============================================================================
INSERT INTO categorias_financeiras (parent_id, nome, tipo, icone, cor_hex) VALUES
-- RECEITAS
(NULL, 'Receitas',                  'receita',  'trending-up',     '#4CAF50'),
(1,    'Venda de Grãos',            'receita',  'package',         '#66BB6A'),
(1,    'Venda de Animais',          'receita',  'activity',        '#81C784'),
(1,    'Créditos de Carbono',       'receita',  'leaf',            '#A5D6A7'),
(1,    'Serviços Prestados',        'receita',  'tool',            '#C8E6C9'),
-- DESPESAS
(NULL, 'Despesas',                  'despesa',  'trending-down',   '#F44336'),
(6,    'Combustível',               'despesa',  'droplet',         '#EF9A9A'),
(6,    'Manutenção de Máquinas',    'despesa',  'settings',        '#EF9A9A'),
(6,    'Insumos e Sementes',        'despesa',  'feather',         '#FFCDD2'),
(6,    'Mão de Obra',               'despesa',  'users',           '#FFCDD2'),
(6,    'Energia Elétrica',          'despesa',  'zap',             '#FFEBEE'),
(6,    'Ração e Medicamentos',      'despesa',  'heart',           '#FFEBEE');
 
-- =============================================================================
INSERT INTO especies (nome_comum, nome_cientifico, categoria, ciclo_dias_min, ciclo_dias_max) VALUES
('Soja',         'Glycine max',             'grao',       90,  150),
('Milho',        'Zea mays',                'grao',       90,  120),
('Feijão',       'Phaseolus vulgaris',      'grao',       60,   90),
('Cana-de-açúcar','Saccharum officinarum',  'fibra',     365, 1800),
('Café',         'Coffea arabica',          'fruta',      730, 1095),
('Pastagem',     'Urochloa brizantha',      'forrageira',  30,   60),
('Bovino Corte', 'Bos taurus',              'bovino',    NULL, NULL),
('Bovino Leite', 'Bos taurus',              'bovino',    NULL, NULL),
('Frango de Corte','Gallus gallus',         'avicola',     35,   45),
('Suíno',        'Sus scrofa domesticus',   'suino',      150,  180);

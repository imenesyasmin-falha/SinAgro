<?php
// =============================================================================
//  SynAgro System — Arquivo de Conexão com o Banco de Dados
//  Arquivo : config/conexao.php
//  Servidor: MySQL via XAMPP/WAMP (localhost)
// =============================================================================

define('DB_HOST',    'localhost');
define('DB_USUARIO', 'root');
define('DB_SENHA',   '');           // padrão XAMPP: vazio | WAMP: vazio ou 'root'
define('DB_NOME',    'synagro_db');
define('DB_PORTA',   '3306');
define('DB_CHARSET', 'utf8mb4');

// -----------------------------------------------------------------------------
// Cria e retorna a conexão PDO
// PDO é mais seguro que mysqli: usa prepared statements nativos
// -----------------------------------------------------------------------------
function conectar(): PDO {
    static $pdo = null;          // guarda a conexão para não abrir duas vezes

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST
             . ";port="      . DB_PORTA
             . ";dbname="    . DB_NOME
             . ";charset="   . DB_CHARSET;

        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // lança exceção em erros
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // retorna arrays associativos
            PDO::ATTR_EMULATE_PREPARES   => false,                     // prepared statements reais
        ];

        try {
            $pdo = new PDO($dsn, DB_USUARIO, DB_SENHA, $opcoes);
        } catch (PDOException $e) {
            // Em produção: logar o erro — nunca expor mensagem ao usuário
            error_log("[SynAgro] Falha na conexão: " . $e->getMessage());
            die(json_encode([
                'erro' => 'Não foi possível conectar ao banco de dados. Verifique se o MySQL está ativo no XAMPP.'
            ]));
        }
    }

    return $pdo;
}

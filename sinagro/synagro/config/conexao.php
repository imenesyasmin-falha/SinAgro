<?php
define('DB_HOST',    'localhost');
define('DB_USUARIO', 'root');
define('DB_SENHA',   '');           
define('DB_NOME',    'synagro_db');
define('DB_PORTA',   '3306');
define('DB_CHARSET', 'utf8mb4');
-
function conectar(): PDO {
    static $pdo = null;          

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST
             . ";port="      . DB_PORTA
             . ";dbname="    . DB_NOME
             . ";charset="   . DB_CHARSET;

        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        
            PDO::ATTR_EMULATE_PREPARES   => false,                     
        ];

        try {
            $pdo = new PDO($dsn, DB_USUARIO, DB_SENHA, $opcoes);
        } catch (PDOException $e) {
            error_log("[SynAgro] Falha na conexão: " . $e->getMessage());
            die(json_encode([
                'erro' => 'Não foi possível conectar ao banco de dados. Verifique se o MySQL está ativo no XAMPP.'
            ]));
        }
    }

    return $pdo;
}

<?php
// =============================================================================
//  SynAgro System — Logout
//  Arquivo : logout.php
// =============================================================================

require_once 'config/conexao.php';
require_once 'includes/auth.php';

// Registra logout nos logs antes de encerrar sessão
if (usuarioLogado()) {
    try {
        $pdo  = conectar();
        $stmt = $pdo->prepare("
            INSERT INTO logs_sistema
                (usuario_id, acao, tabela_afetada, registro_id, descricao, ip_address)
            VALUES (:uid, 'logout', 'usuarios', :rid, 'Logout realizado', :ip)
        ");
        $stmt->execute([
            ':uid' => $_SESSION['usuario_id'],
            ':rid' => $_SESSION['usuario_id'],
            ':ip'  => $_SERVER['REMOTE_ADDR'] ?? 'desconhecido',
        ]);
    } catch (PDOException $e) {
        error_log("[SynAgro Logout] " . $e->getMessage());
    }
}

encerrarSessao();
header('Location: login.php?logout=1');
exit;

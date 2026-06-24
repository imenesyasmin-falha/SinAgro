<?php
require_once 'config/conexao.php';
require_once 'includes/auth.php';
if (usuarioLogado()) {
    try {
        $pdo = conectar();
        $pdo->prepare("INSERT INTO logs_sistema(usuario_id,acao,tabela_afetada,registro_id,descricao,ip_address)VALUES(:u,'logout','usuarios',:r,'Logout',':ip')")->execute([':u'=>$_SESSION['usuario_id'],':r'=>$_SESSION['usuario_id'],':ip'=>$_SERVER['REMOTE_ADDR']??'']);
    } catch(Exception $e){}
}
encerrarSessao();
header('Location: login.php?logout=1'); 
exit;

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>false,'httponly'=>true,'samesite'=>'Strict']);
    session_start();
}

const PERFIS_VALIDOS = ['admin','proprietario','gerente','operador','visualizador'];

const LABELS_PERFIL = [
    'admin'=>'Administrador','proprietario'=>'Proprietário',
    'gerente'=>'Gerente','operador'=>'Operador','visualizador'=>'Visualizador',
];

const CORES_PERFIL = [
    'admin'=>'#4ADE80','proprietario'=>'#4ADE80',
    'gerente'=>'#FBBF24','operador'=>'#60A5FA','visualizador'=>'#A3B8A8',
];

const MODULOS_PERFIL = [
    'admin'        => ['dashboard','propriedades','culturas','ciclos','animais','estoque','equipamentos','manutencoes','financeiro','relatorios','usuarios','logs'],
    'proprietario' => ['dashboard','propriedades','culturas','ciclos','animais','estoque','equipamentos','manutencoes','financeiro','relatorios'],
    'gerente'      => ['dashboard','culturas','ciclos','animais','estoque','equipamentos','manutencoes','financeiro'],
    'operador'     => ['dashboard','culturas','ciclos','estoque','equipamentos','manutencoes'],
    'visualizador' => ['dashboard','relatorios'],
];

function usuarioLogado(): bool {
    return isset($_SESSION['usuario_id'], $_SESSION['perfil'])
        && in_array($_SESSION['perfil'], PERFIS_VALIDOS);
}

function exigirLogin(string $base = ''): void {
    if (!usuarioLogado()) { header('Location: '.$base.'login.php?sessao=expirada'); exit; }
}

function exigirPerfil(array $perfis, string $base = ''): void {
    exigirLogin($base);
    if (!in_array($_SESSION['perfil'], $perfis)) {
        header('Location: '.$base.'pages/dashboard.php?erro=acesso_negado'); exit;
    }
}

function temAcesso(string $m): bool {
    if (!usuarioLogado()) return false;
    return in_array($m, MODULOS_PERFIL[$_SESSION['perfil']] ?? []);
}

function usuarioAtual(): array {
    if (!usuarioLogado()) return [];
    return [
        'id'     => $_SESSION['usuario_id'],
        'nome'   => $_SESSION['usuario_nome']  ?? 'Usuário',
        'email'  => $_SESSION['usuario_email'] ?? '',
        'perfil' => $_SESSION['perfil'],
        'label'  => LABELS_PERFIL[$_SESSION['perfil']] ?? $_SESSION['perfil'],
        'cor'    => CORES_PERFIL[$_SESSION['perfil']]  ?? '#A3B8A8',
    ];
}

function encerrarSessao(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function limpar(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

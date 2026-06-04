<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,                 
        'path'     => '/',
        'secure'   => false,             
        'httponly' => true,               
        'samesite' => 'Strict',           
    ]);
    session_start();
}

const PERFIS_VALIDOS = ['admin', 'proprietario', 'gerente', 'operador', 'visualizador'];

const ROTAS_PERFIL = [
    'admin'        => 'pages/dashboard.php',
    'proprietario' => 'pages/dashboard.php',
    'gerente'      => 'pages/dashboard.php',
    'operador'     => 'pages/dashboard.php',
    'visualizador' => 'pages/dashboard.php',
];

const LABELS_PERFIL = [
    'admin'        => 'Administrador',
    'proprietario' => 'Proprietário',
    'gerente'      => 'Gerente',
    'operador'     => 'Operador',
    'visualizador' => 'Visualizador',
];

const CORES_PERFIL = [
    'admin'        => '#1A3C2A',
    'proprietario' => '#2C5F2D',
    'gerente'      => '#C8973A',
    'operador'     => '#0C447C',
    'visualizador' => '#5A5A5A',
];

const MODULOS_PERFIL = [
    'admin' => [
        'dashboard', 'usuarios', 'propriedades', 'culturas',
        'animais', 'estoque', 'equipamentos', 'financeiro', 'logs'
    ],
    'proprietario' => [
        'dashboard', 'propriedades', 'culturas', 'animais',
        'estoque', 'equipamentos', 'financeiro'
    ],
    'gerente' => [
        'dashboard', 'culturas', 'animais',
        'estoque', 'equipamentos', 'financeiro'
    ],
    'operador' => [
        'dashboard', 'culturas', 'estoque', 'equipamentos'
    ],
    'visualizador' => [
        'dashboard'
    ],
];

function usuarioLogado(): bool {
    return isset($_SESSION['usuario_id'])
        && isset($_SESSION['perfil'])
        && in_array($_SESSION['perfil'], PERFIS_VALIDOS);
}

function exigirLogin(string $base = ''): void {
    if (!usuarioLogado()) {
        header('Location: ' . $base . 'login.php?sessao=expirada');
        exit;
    }
}

function exigirPerfil(array $perfisPermitidos, string $base = ''): void {
    exigirLogin($base);
    if (!in_array($_SESSION['perfil'], $perfisPermitidos)) {
        header('Location: ' . $base . 'pages/dashboard.php?erro=acesso_negado');
        exit;
    }
}

function temAcesso(string $modulo): bool {
    if (!usuarioLogado()) return false;
    $perfil = $_SESSION['perfil'];
    return in_array($modulo, MODULOS_PERFIL[$perfil] ?? []);
}

function usuarioAtual(): array {
    if (!usuarioLogado()) return [];
    return [
        'id'     => $_SESSION['usuario_id'],
        'nome'   => $_SESSION['usuario_nome']  ?? 'Usuário',
        'email'  => $_SESSION['usuario_email'] ?? '',
        'perfil' => $_SESSION['perfil'],
        'label'  => LABELS_PERFIL[$_SESSION['perfil']] ?? $_SESSION['perfil'],
        'cor'    => CORES_PERFIL[$_SESSION['perfil']]  ?? '#5A5A5A',
    ];
}

function encerrarSessao(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function limpar(string $valor): string {
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

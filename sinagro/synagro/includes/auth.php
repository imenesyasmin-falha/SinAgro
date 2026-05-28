<?php
// =============================================================================
//  SynAgro System — Controle de Sessão e Acesso por Perfil
//  Arquivo : includes/auth.php
// =============================================================================

// Inicia sessão segura se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,                  // cookie de sessão (some ao fechar o browser)
        'path'     => '/',
        'secure'   => false,              // true em produção com HTTPS
        'httponly' => true,               // impede acesso via JavaScript (proteção XSS)
        'samesite' => 'Strict',           // proteção CSRF
    ]);
    session_start();
}

// -----------------------------------------------------------------------------
// Perfis válidos do sistema e suas rotas padrão após login
// -----------------------------------------------------------------------------
const PERFIS_VALIDOS = ['admin', 'proprietario', 'gerente', 'operador', 'visualizador'];

const ROTAS_PERFIL = [
    'admin'        => 'pages/dashboard.php',
    'proprietario' => 'pages/dashboard.php',
    'gerente'      => 'pages/dashboard.php',
    'operador'     => 'pages/dashboard.php',
    'visualizador' => 'pages/dashboard.php',
];

// Rótulos amigáveis para exibir na tela
const LABELS_PERFIL = [
    'admin'        => 'Administrador',
    'proprietario' => 'Proprietário',
    'gerente'      => 'Gerente',
    'operador'     => 'Operador',
    'visualizador' => 'Visualizador',
];

// Cores (badges) de cada perfil
const CORES_PERFIL = [
    'admin'        => '#1A3C2A',
    'proprietario' => '#2C5F2D',
    'gerente'      => '#C8973A',
    'operador'     => '#0C447C',
    'visualizador' => '#5A5A5A',
];

// Módulos que cada perfil pode acessar
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

// -----------------------------------------------------------------------------
// Verifica se há um usuário autenticado na sessão
// -----------------------------------------------------------------------------
function usuarioLogado(): bool {
    return isset($_SESSION['usuario_id'])
        && isset($_SESSION['perfil'])
        && in_array($_SESSION['perfil'], PERFIS_VALIDOS);
}

// -----------------------------------------------------------------------------
// Exige login — redireciona para login.php se não estiver autenticado
// Uso: chamar no topo de qualquer página protegida
// -----------------------------------------------------------------------------
function exigirLogin(string $base = ''): void {
    if (!usuarioLogado()) {
        header('Location: ' . $base . 'login.php?sessao=expirada');
        exit;
    }
}

// -----------------------------------------------------------------------------
// Exige um perfil específico para acessar a página
// Uso: exigirPerfil(['admin', 'proprietario'])
// -----------------------------------------------------------------------------
function exigirPerfil(array $perfisPermitidos, string $base = ''): void {
    exigirLogin($base);
    if (!in_array($_SESSION['perfil'], $perfisPermitidos)) {
        header('Location: ' . $base . 'pages/dashboard.php?erro=acesso_negado');
        exit;
    }
}

// -----------------------------------------------------------------------------
// Verifica se o usuário logado tem acesso a um módulo
// -----------------------------------------------------------------------------
function temAcesso(string $modulo): bool {
    if (!usuarioLogado()) return false;
    $perfil = $_SESSION['perfil'];
    return in_array($modulo, MODULOS_PERFIL[$perfil] ?? []);
}

// -----------------------------------------------------------------------------
// Retorna os dados do usuário logado da sessão
// -----------------------------------------------------------------------------
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

// -----------------------------------------------------------------------------
// Encerra a sessão com segurança (logout)
// -----------------------------------------------------------------------------
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

// -----------------------------------------------------------------------------
// Sanitiza entrada do usuário (prevenção XSS)
// -----------------------------------------------------------------------------
function limpar(string $valor): string {
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

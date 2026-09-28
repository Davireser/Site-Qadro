<?php
/**
 * Configuração central do painel administrativo.
 * Copie este arquivo para includes/config.php e preencha as credenciais reais.
 * Ajuste as credenciais de banco conforme o seu ambiente (XAMPP/Laragon/produção).
 */

// --- Ambiente ---
// Defina como false em produção para nunca vazar detalhes de erro na tela.
define('APP_DEBUG', false);
define('DB_HOST', 'localhost');
define('DB_NAME', 'nome_do_banco');
define('DB_USER', 'usuario_do_banco');
define('DB_PASS', 'senha_do_banco');
define('DB_CHARSET', 'utf8mb4');

// --- Sessão ---
define('SESSION_INACTIVITY_LIMIT', 30 * 60); // expira a sessão após 30 min sem uso

// --- Login / força bruta ---
define('LOGIN_MAX_ATTEMPTS', 3);
define('LOGIN_LOCKOUT_SECONDS', 5 * 60);

// --- Upload de imagens ---
// Fica FORA da pasta pública do site: nunca é acessado por URL direta,
// somente através de includes/image.php, que valida o pedido no servidor.
define('UPLOAD_BASE_DIR', __DIR__ . '/../storage/uploads');
define('UPLOAD_MAX_BYTES', 20 * 1024 * 1024); // 20MB (o php.ini do XAMPP já permite até 40MB por arquivo)
define('UPLOAD_ALLOWED_TYPES', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
]);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

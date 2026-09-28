<?php
/**
 * Cria (ou redefine a senha de) um usuário administrador.
 * Uso via linha de comando:
 *   php database/create_admin.php <usuario> <senha>
 */

require_once __DIR__ . '/../includes/db_connect.php';

if (PHP_SAPI !== 'cli') {
    exit('Este script só pode ser executado via linha de comando (CLI).');
}

if ($argc !== 3) {
    fwrite(STDERR, "Uso: php database/create_admin.php <usuario> <senha>\n");
    exit(1);
}

[, $username, $password] = $argv;

if (strlen($password) < 8) {
    fwrite(STDERR, "A senha deve ter pelo menos 8 caracteres.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = db()->prepare(
    'INSERT INTO admin_users (username, password_hash) VALUES (:u, :h)
     ON DUPLICATE KEY UPDATE password_hash = :h2, failed_attempts = 0, locked_until = NULL'
);
$stmt->execute(['u' => $username, 'h' => $hash, 'h2' => $hash]);

echo "Usuário admin '{$username}' criado/atualizado com sucesso.\n";

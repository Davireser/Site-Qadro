<?php
/**
 * Conexão PDO única (singleton) com o banco de dados.
 */

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die('Erro de conexão com o banco de dados: ' . $e->getMessage());
            }
            error_log('DB connection error: ' . $e->getMessage());
            die('Erro interno. Tente novamente mais tarde.');
        }
    }

    return $pdo;
}

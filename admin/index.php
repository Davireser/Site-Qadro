<?php
require_once __DIR__ . '/../includes/functions.php';

session_bootstrap();

// Já autenticado: vai direto pro painel.
if (!empty($_SESSION['admin_id'])) {
    header('Location: home.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Tente novamente.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Informe usuário e senha.';
        } else {
            $result = login_attempt($username, $password);
            if ($result === 'ok') {
                header('Location: home.php');
                exit;
            }
            if ($result === 'locked') {
                $error = 'Muitas tentativas falhas. Conta bloqueada por alguns minutos, tente novamente mais tarde.';
            } else {
                $error = 'Usuário ou senha inválidos.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Painel Admin Qadro</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
  <div class="admin-login-wrap">
    <div class="admin-login-card">
      <img src="../assets/logos/PNG/Horizontal Preto.png" alt="Qadro" class="admin-login-logo">
      <p class="admin-login-title">Painel Administrativo</p>

      <?php if ($error): ?>
        <div class="admin-alert admin-alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="index.php" novalidate>
        <?= csrf_field() ?>
        <div class="admin-field">
          <label class="admin-label" for="username">Usuário</label>
          <input class="admin-input" type="text" id="username" name="username" autocomplete="username" required autofocus>
        </div>
        <div class="admin-field">
          <label class="admin-label" for="password">Senha</label>
          <input class="admin-input" type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="admin-btn admin-btn-block">Entrar</button>
      </form>
    </div>
  </div>
</body>
</html>

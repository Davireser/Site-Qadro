<?php
/**
 * Funções de autenticação, segurança (CSRF, upload) e utilitários
 * compartilhados pelo painel administrativo.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

/**
 * Inicia a sessão com cookies seguros e aplica expiração por inatividade.
 * Deve ser chamada no topo de toda página admin, antes de qualquer output.
 */
function session_bootstrap(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Expiração por inatividade.
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_INACTIVITY_LIMIT) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Exige um admin autenticado; caso contrário, redireciona para o login.
 */
function require_login(): void
{
    session_bootstrap();
    if (empty($_SESSION['admin_id'])) {
        header('Location: index.php');
        exit;
    }
}

function current_admin_username(): string
{
    return $_SESSION['admin_username'] ?? '';
}

/**
 * Versão de um arquivo estático (CSS/JS), usada na query string do <link>/<script>.
 * Muda sozinha a cada alteração do arquivo, então o navegador nunca serve
 * uma versão antiga do cache depois de um deploy.
 */
function asset_version(string $relativePath): string
{
    $full = __DIR__ . '/../' . ltrim($relativePath, '/');
    return is_file($full) ? (string) filemtime($full) : '1';
}

/** Escapa texto para saída segura em HTML (proteção contra XSS). */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// ------------------------------------------------------------
// CSRF
// ------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool
{
    return !empty($_SESSION['csrf_token']) && !empty($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

/** Interrompe a execução com 400 caso o token CSRF do POST seja inválido. */
function require_valid_csrf(): void
{
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        exit('Requisição inválida (token CSRF ausente ou expirado). Volte e tente novamente.');
    }
}

// ------------------------------------------------------------
// Mensagens flash (feedback entre redirecionamentos)
// ------------------------------------------------------------

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

// ------------------------------------------------------------
// Autenticação / força bruta
// ------------------------------------------------------------

/**
 * Tenta autenticar um usuário admin.
 * Retorna 'ok', 'invalid' ou 'locked'.
 */
function login_attempt(string $username, string $password): string
{
    $stmt = db()->prepare('SELECT id, username, password_hash, failed_attempts, locked_until FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Hash "dummy" (formato bcrypt válido) para manter tempo de resposta
    // constante quando o usuário não existe, dificultando enumeração.
    $dummyHash = '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOPQRSTUVWXYZ01234';

    if (!$user) {
        password_verify($password, $dummyHash);
        return 'invalid';
    }

    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
        return 'locked';
    }

    if (!password_verify($password, $user['password_hash'])) {
        $attempts = (int) $user['failed_attempts'] + 1;
        if ($attempts >= LOGIN_MAX_ATTEMPTS) {
            $lockedUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_SECONDS);
            $upd = db()->prepare('UPDATE admin_users SET failed_attempts = ?, locked_until = ? WHERE id = ?');
            $upd->execute([$attempts, $lockedUntil, $user['id']]);
            return 'locked';
        }
        $upd = db()->prepare('UPDATE admin_users SET failed_attempts = ? WHERE id = ?');
        $upd->execute([$attempts, $user['id']]);
        return 'invalid';
    }

    // Sucesso: zera tentativas e regenera o ID de sessão (evita session fixation).
    $upd = db()->prepare('UPDATE admin_users SET failed_attempts = 0, locked_until = NULL WHERE id = ?');
    $upd->execute([$user['id']]);

    session_regenerate_id(true);
    $_SESSION['admin_id']       = (int) $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['csrf_token']     = bin2hex(random_bytes(32));
    $_SESSION['last_activity']  = time();

    return 'ok';
}

// ------------------------------------------------------------
// Portfólio
// ------------------------------------------------------------

/** Máximo de métricas exibidas ao lado da descrição de um projeto. */
const PROJECT_MAX_METRICS = 4;

/** Quebra o campo de tags ("Residencial, Retrofit") na lista usada no filtro do portfólio. */
function project_tags(?string $category): array
{
    return array_values(array_filter(array_map('trim', explode(',', $category ?? ''))));
}

/** Converte um texto em parágrafos (linhas em branco separam os blocos). */
function text_paragraphs(?string $raw): array
{
    if ($raw === null || trim($raw) === '') {
        return [];
    }
    $blocks = preg_split("/\n\s*\n/", trim(str_replace("\r\n", "\n", $raw)));
    return array_values(array_filter(array_map('trim', $blocks), fn ($block) => $block !== ''));
}

// ------------------------------------------------------------
// Blocos do artigo (editor do painel admin)
// ------------------------------------------------------------

/**
 * Tipos de bloco disponíveis e as opções válidas de cada um.
 * O primeiro valor de cada opção é sempre o padrão.
 */
/**
 * Os dois lugares que usam o editor de blocos: o corpo dos artigos do blog e
 * o conteúdo abaixo do carrossel dos projetos. Muda a tabela, a chave
 * estrangeira e a pasta de upload — o formato dos blocos é o mesmo nos dois.
 *
 * Os valores saem sempre deste mapa fixo, nunca do cliente: por isso podem
 * ser interpolados com segurança no SQL das funções abaixo.
 */
function block_scope(string $scope): array
{
    $scopes = [
        'post'    => ['table' => 'post_blocks',    'key' => 'post_id',    'folder' => 'blog_blocks'],
        'project' => ['table' => 'project_blocks', 'key' => 'project_id', 'folder' => 'portfolio_blocks'],
    ];

    if (!isset($scopes[$scope])) {
        throw new InvalidArgumentException('Escopo de blocos inválido.');
    }

    return $scopes[$scope];
}

function post_block_types(): array
{
    return [
        'text' => [
            'label'   => 'Texto',
            'icon'    => '¶',
            'hint'    => 'Parágrafos de texto corrido. Uma linha em branco separa parágrafos.',
            'options' => [
                'width' => ['full' => 'Largura total', 'narrow' => 'Coluna centralizada'],
            ],
        ],
        'image' => [
            'label'   => 'Imagem',
            'icon'    => '▣',
            'hint'    => 'Imagem com legenda opcional.',
            'options' => [
                'width' => ['full' => 'Largura total', 'half' => 'Metade', 'third' => 'Um terço'],
                'align' => ['center' => 'Centro', 'left' => 'Esquerda', 'right' => 'Direita'],
            ],
        ],
        'split' => [
            'label'   => 'Texto + Imagem',
            'icon'    => '◨',
            'hint'    => 'Imagem e texto lado a lado.',
            'options' => [
                'invert' => ['0' => 'Imagem à esquerda / texto à direita', '1' => 'Texto à esquerda / imagem à direita'],
                'ratio'  => ['50-50' => '50 / 50', '60-40' => '60 / 40', '40-60' => '40 / 60'],
            ],
        ],
        'quote' => [
            'label'   => 'Citação em destaque',
            'icon'    => '❝',
            'hint'    => 'Frase curta em fonte grande, centralizada.',
            'options' => [],
        ],
        'spacer' => [
            'label'   => 'Espaçamento',
            'icon'    => '↕',
            'hint'    => 'Respiro vertical entre as seções. Não tem conteúdo.',
            'options' => [
                'size' => ['medium' => 'Médio', 'small' => 'Pequeno', 'large' => 'Grande'],
            ],
        ],
    ];
}

/** Opção padrão (primeiro valor) de cada configuração de um tipo de bloco. */
function post_block_defaults(string $type): array
{
    $types = post_block_types();
    if (!isset($types[$type])) {
        return [];
    }
    $defaults = [];
    foreach ($types[$type]['options'] as $option => $values) {
        $defaults[$option] = array_key_first($values);
    }
    return $defaults;
}

/**
 * Lê o JSON de settings de um bloco e devolve apenas opções válidas,
 * completando com os padrões. Valor inválido nunca chega ao HTML.
 */
function post_block_settings(string $type, ?string $json): array
{
    $types = post_block_types();
    if (!isset($types[$type])) {
        return [];
    }

    $stored = json_decode($json ?? '', true);
    if (!is_array($stored)) {
        $stored = [];
    }

    $settings = [];
    foreach ($types[$type]['options'] as $option => $values) {
        $value = isset($stored[$option]) ? (string) $stored[$option] : '';
        $settings[$option] = isset($values[$value]) ? $value : array_key_first($values);
    }
    return $settings;
}

/** Codifica as settings de um bloco para gravar no banco, validando as opções. */
function post_block_settings_json(string $type, array $raw): string
{
    $types = post_block_types();
    if (!isset($types[$type])) {
        return '{}';
    }

    $settings = [];
    foreach ($types[$type]['options'] as $option => $values) {
        $value = isset($raw[$option]) ? (string) $raw[$option] : '';
        $settings[$option] = isset($values[$value]) ? $value : array_key_first($values);
    }
    // JSON_FORCE_OBJECT para que um bloco sem opções grave {} e não [].
    return json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
}

/**
 * Remove imagens de bloco que sobraram no disco sem estar em nenhum artigo.
 *
 * O editor envia a imagem assim que ela é escolhida (para a prévia poder
 * mostrá-la antes de salvar); se o usuário fechar a tela sem salvar, o arquivo
 * fica órfão. Só apaga arquivos com mais de um dia, para nunca remover o que
 * está sendo usado numa edição em andamento.
 */
function post_blocks_cleanup_orphan_images(int $olderThanSeconds = 86400, string $scope = 'post'): void
{
    ['table' => $table, 'folder' => $folder] = block_scope($scope);

    $dir = UPLOAD_BASE_DIR . '/' . $folder;
    if (!is_dir($dir)) {
        return;
    }

    $inUse = db()->query("SELECT image FROM `$table` WHERE image IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    $inUse = array_flip($inUse);

    foreach (glob($dir . '/*') as $path) {
        $name = basename($path);
        if ($name === '.gitkeep' || !is_file($path)) {
            continue;
        }
        if (isset($inUse[$name])) {
            continue;
        }
        if (time() - filemtime($path) > $olderThanSeconds) {
            @unlink($path);
        }
    }
}

/** Carrega os blocos de um artigo (ou projeto), já na ordem de exibição. */
function post_blocks(int $ownerId, string $scope = 'post'): array
{
    ['table' => $table, 'key' => $key] = block_scope($scope);

    $stmt = db()->prepare("SELECT * FROM `$table` WHERE `$key` = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$ownerId]);
    return $stmt->fetchAll();
}

/**
 * Normaliza os blocos vindos do formulário do editor.
 * Devolve ['blocks' => [...], 'usedImages' => [...]]; blocos vazios são
 * descartados (texto sem conteúdo, imagem sem arquivo etc.).
 */
function post_blocks_from_request(array $rawBlocks): array
{
    $types = post_block_types();
    $blocks = [];
    $usedImages = [];

    foreach ($rawBlocks as $raw) {
        $type = (string) ($raw['type'] ?? '');
        if (!isset($types[$type])) {
            continue;
        }

        $content = trim((string) ($raw['content'] ?? ''));
        $image   = trim((string) ($raw['image'] ?? ''));
        $caption = trim((string) ($raw['caption'] ?? ''));
        $image   = $image !== '' ? basename($image) : '';

        // Descarta blocos sem nada para mostrar.
        if (($type === 'text' || $type === 'quote') && $content === '') {
            continue;
        }
        if ($type === 'image' && $image === '') {
            continue;
        }
        if ($type === 'split' && $content === '' && $image === '') {
            continue;
        }

        if ($image !== '') {
            $usedImages[] = $image;
        }

        $blocks[] = [
            'type'     => $type,
            'content'  => in_array($type, ['text', 'quote', 'split'], true) ? $content : null,
            'image'    => in_array($type, ['image', 'split'], true) && $image !== '' ? $image : null,
            'caption'  => $type === 'image' && $caption !== '' ? mb_substr($caption, 0, 255) : null,
            'settings' => post_block_settings_json($type, is_array($raw['settings'] ?? null) ? $raw['settings'] : []),
        ];
    }

    return ['blocks' => $blocks, 'usedImages' => $usedImages];
}

/** Regrava os blocos de um artigo (ou projeto) e apaga do disco as imagens que saíram. */
function post_blocks_save(int $ownerId, array $blocks, array $usedImages, string $scope = 'post'): void
{
    ['table' => $table, 'key' => $key, 'folder' => $folder] = block_scope($scope);

    $previous = db()->prepare("SELECT image FROM `$table` WHERE `$key` = ? AND image IS NOT NULL");
    $previous->execute([$ownerId]);
    $previousImages = $previous->fetchAll(PDO::FETCH_COLUMN);

    $del = db()->prepare("DELETE FROM `$table` WHERE `$key` = ?");
    $del->execute([$ownerId]);

    $ins = db()->prepare("INSERT INTO `$table` (`$key`, block_type, sort_order, content, image, caption, settings) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($blocks as $i => $block) {
        $ins->execute([$ownerId, $block['type'], $i, $block['content'], $block['image'], $block['caption'], $block['settings']]);
    }

    foreach ($previousImages as $old) {
        if (!in_array($old, $usedImages, true)) {
            delete_image_file($folder, $old);
        }
    }
}

// ------------------------------------------------------------
// Slugs
// ------------------------------------------------------------

function slugify(string $text): string
{
    $map = [
        'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
        'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
        'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','ñ'=>'n',
        'Á'=>'a','À'=>'a','Ã'=>'a','Â'=>'a','Ä'=>'a',
        'É'=>'e','È'=>'e','Ê'=>'e','Ë'=>'e',
        'Í'=>'i','Ì'=>'i','Î'=>'i','Ï'=>'i',
        'Ó'=>'o','Ò'=>'o','Õ'=>'o','Ô'=>'o','Ö'=>'o',
        'Ú'=>'u','Ù'=>'u','Û'=>'u','Ü'=>'u',
        'Ç'=>'c','Ñ'=>'n',
    ];
    $text = strtr($text, $map);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item';
}

/** Gera um slug único para a tabela/coluna informadas, ignorando $excludeId se fornecido. */
function unique_slug(string $table, string $baseText, ?int $excludeId = null): string
{
    $base = slugify($baseText);
    $slug = $base;
    $i = 2;

    while (true) {
        if ($excludeId !== null) {
            $stmt = db()->prepare("SELECT id FROM `$table` WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = db()->prepare("SELECT id FROM `$table` WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
    }
}

// ------------------------------------------------------------
// Upload de imagens (armazenadas fora da pasta pública)
// ------------------------------------------------------------

/**
 * Valida e salva um upload de imagem para 'blog' ou 'portfolio'.
 * Retorna o nome do arquivo salvo (a ser gravado no banco).
 * Lança RuntimeException com mensagem amigável em caso de erro.
 */
function handle_image_upload(array $file, string $type): string
{
    if (!in_array($type, ['blog', 'blog_blocks', 'portfolio', 'portfolio_gallery', 'portfolio_blocks'], true)) {
        throw new RuntimeException('Tipo de upload inválido.');
    }

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('Nenhuma imagem enviada.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha no upload da imagem (código ' . $file['error'] . ').');
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('Imagem maior que o limite permitido (' . (UPLOAD_MAX_BYTES / 1024 / 1024) . 'MB).');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Upload inválido.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!isset(UPLOAD_ALLOWED_TYPES[$mime])) {
        throw new RuntimeException('Formato de imagem não permitido. Envie apenas JPG ou PNG.');
    }

    $ext = UPLOAD_ALLOWED_TYPES[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;

    $destDir = UPLOAD_BASE_DIR . '/' . $type;
    if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        throw new RuntimeException('Não foi possível preparar o diretório de upload.');
    }

    $dest = $destDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Não foi possível salvar a imagem no servidor.');
    }

    return $filename;
}

/**
 * Recebe o $_FILES de um campo múltiplo (name="campo[]") e devolve os
 * arquivos enviados no formato de um upload simples, ignorando os slots vazios.
 */
function normalize_file_array(?array $files): array
{
    if (!$files || !isset($files['error']) || !is_array($files['error'])) {
        return [];
    }

    $normalized = [];
    foreach ($files['error'] as $i => $error) {
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $normalized[] = [
            'name'     => $files['name'][$i],
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $error,
            'size'     => $files['size'][$i],
        ];
    }

    return $normalized;
}

/** Remove um arquivo de imagem previamente enviado (usado em exclusão/substituição). */
function delete_image_file(string $type, ?string $filename): void
{
    if (!$filename) {
        return;
    }
    $path = UPLOAD_BASE_DIR . '/' . $type . '/' . basename($filename);
    if (is_file($path)) {
        @unlink($path);
    }
}

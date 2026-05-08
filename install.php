<?php
/**
 * Instalador web para Kontbox — Sistema de Gestión Operativa y Contractual
 * 
 * Suba este archivo junto con los zips a la raíz del sitio en cPanel,
 * luego acceda via web: https://sudominio.com/install.php
 * 
 * Requisitos: PHP 8.1+, ext-zip, ext-mbstring, ext-pdo_mysql, ext-bcmath
 * 
 * IMPORTANTE: Elimine este archivo después de la instalación.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];
$success = [];
$output = [];

// ── 1. VERIFICAR ENTORNO ──
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    $errors[] = 'Se requiere PHP 8.1+ (versión actual: ' . PHP_VERSION . ')';
}
foreach (['zip', 'mbstring', 'pdo', 'bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'json', 'openssl', 'session', 'tokenizer', 'xml', 'pdo_mysql'] as $ext) {
    if (!extension_loaded($ext)) { $errors[] = "Extensión faltante: $ext"; }
}

$dir = __DIR__;
$productionZip = "$dir/kontbox-system-production.zip";
$vendorZip = "$dir/kontbox-system-vendor.zip";
$hasProd = file_exists($productionZip);
$hasVendor = file_exists($vendorZip);

if (!$hasProd) $errors[] = 'No se encontró kontbox-system-production.zip';
if (!$hasVendor) $errors[] = 'No se encontró kontbox-system-vendor.zip';

$step = $_POST['step'] ?? 'start';

// ── 2. PROCESAR ──
if ($step === 'go' && !$errors) {
    set_time_limit(300);

    // 2a. Extraer zips
    $zip = new ZipArchive();
    foreach ([$productionZip, $vendorZip] as $z) {
        if ($zip->open($z) === true) {
            $zip->extractTo($dir);
            $zip->close();
            $success[] = basename($z) . ' extraído.';
            @unlink($z);
        } else {
            $errors[] = "Error al abrir " . basename($z);
        }
    }
    if (!file_exists("$dir/artisan")) { $errors[] = 'artisan no encontrado después de extraer.'; }

    // 2b. Crear .env limpio
    if (!$errors) {
        $appUrl = $_POST['app_url'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);
        $dbHost = $_POST['db_host'] ?? '127.0.0.1';
        $dbPort = $_POST['db_port'] ?? '3306';
        $dbName = $_POST['db_database'] ?? 'kontbox';
        $dbUser = $_POST['db_username'] ?? '';
        $dbPass = $_POST['db_password'] ?? '';

        if (empty($dbUser)) { $errors[] = 'Usuario de BD requerido.'; }

        if (!$errors) {
            $env = "APP_NAME=Kontbox\n";
            $env .= "APP_ENV=production\n";
            $env .= "APP_KEY=\n";
            $env .= "APP_DEBUG=false\n";
            $env .= "APP_TIMEZONE=UTC\n";
            $env .= "APP_URL=$appUrl\n";
            $env .= "APP_LOCALE=es\n";
            $env .= "APP_FALLBACK_LOCALE=en\n";
            $env .= "APP_MAINTENANCE_DRIVER=file\n";
            $env .= "BCRYPT_ROUNDS=12\n";
            $env .= "LOG_CHANNEL=stack\n";
            $env .= "LOG_LEVEL=error\n";
            $env .= "DB_CONNECTION=mysql\n";
            $env .= "DB_HOST=$dbHost\n";
            $env .= "DB_PORT=$dbPort\n";
            $env .= "DB_DATABASE=$dbName\n";
            $env .= "DB_USERNAME=$dbUser\n";
            $env .= "DB_PASSWORD=$dbPass\n";
            $env .= "SESSION_DRIVER=file\n";
            $env .= "SESSION_LIFETIME=120\n";
            $env .= "SESSION_ENCRYPT=false\n";
            $env .= "BROADCAST_CONNECTION=log\n";
            $env .= "FILESYSTEM_DISK=local\n";
            $env .= "QUEUE_CONNECTION=database\n";
            $env .= "CACHE_STORE=file\n";
            $env .= "MAIL_MAILER=log\n";

            file_put_contents("$dir/.env", $env);
            $success[] = '.env creado con MySQL.';
        }
    }

    // 2c. Ejecutar comandos Artisan
    if (!$errors) {
        $artisan = 'php ' . escapeshellarg("$dir/artisan");

        $cmds = [
            'key:generate --force' => 'APP_KEY generada',
            'cache:clear' => 'Cache general limpiado',
            'config:clear' => 'Config limpiada',
            'route:clear' => 'Rutas limpiadas',
            'view:clear' => 'Vistas limpiadas',
            'migrate --seed --force' => 'Migraciones + seed ejecutados',
            'config:cache' => 'Config cacheada',
            'route:cache' => 'Rutas cacheadas',
            'view:cache' => 'Vistas cacheadas',
        ];

        foreach ($cmds as $cmd => $msg) {
            $result = shell_exec("$artisan $cmd 2>&1");
            $output[] = "<strong>\$ php artisan $cmd</strong><pre>" . htmlspecialchars($result ?? 'sin respuesta') . '</pre>';
            $success[] = $msg;
        }

        // Permisos
        $perms = ['storage', 'bootstrap/cache'];
        foreach ($perms as $p) {
            $path = "$dir/$p";
            if (is_dir($path)) {
                @chmod($path, 0755);
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS));
                foreach ($it as $f) { @chmod($f->getPathname(), $f->isDir() ? 0755 : 0644); }
            }
        }
        $success[] = 'Permisos configurados.';

        @unlink(__FILE__);
        $step = 'done';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Instalador Kontbox</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f3f4f6;color:#1f2937;padding:2rem 1rem}
.container{max-width:640px;margin:0 auto}
.card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.1);padding:2rem;margin-bottom:1.5rem}
h1{font-size:1.5rem;font-weight:800}
h1 span{color:#6366f1}
.subtitle{color:#6b7280;font-size:.875rem;margin-bottom:1.5rem}
label{display:block;font-size:.875rem;font-weight:600;margin-top:1rem;margin-bottom:.375rem;color:#374151}
input,select{width:100%;padding:.75rem 1rem;border:1px solid #d1d5db;border-radius:10px;font-size:.875rem}
input:focus,select:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.15)}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.btn{display:inline-block;padding:.75rem 2rem;font-size:.875rem;font-weight:600;border:none;border-radius:10px;cursor:pointer;margin-top:1.5rem;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;text-decoration:none}
.btn:hover{box-shadow:0 4px 12px rgba(99,102,241,.35)}
.alert{padding:.75rem 1rem;border-radius:10px;font-size:.875rem;margin-bottom:.75rem}
.alert-e{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.alert-s{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.alert-w{background:#fffbeb;border:1px solid #fde68a;color:#92400e}
.output{background:#111827;color:#e5e7eb;border-radius:10px;padding:1rem;font-family:'Courier New',monospace;font-size:.8rem;line-height:1.6;max-height:500px;overflow-y:auto;margin-bottom:1rem}
.output pre{white-space:pre-wrap;word-break:break-all;margin-bottom:.5rem;color:#a5b4fc}
.output strong{color:#fbbf24}
.mb-4{margin-bottom:1rem}
.text-sm{font-size:.875rem;color:#6b7280}
.text-center{text-align:center}
.info{background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px;padding:1rem;font-size:.875rem;color:#4338ca;margin-bottom:1.5rem}
</style>
</head>
<body>
<div class="container">

<div class="card text-center">
    <h1><span>Kontbox</span> — Instalador</h1>
    <p class="subtitle">Sistema de Gestión Operativa y Contractual</p>
</div>

<?php foreach ($errors as $e): ?>
<div class="alert alert-e">✗ <?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>
<?php foreach ($success as $s): ?>
<div class="alert alert-s">✔ <?= htmlspecialchars($s) ?></div>
<?php endforeach; ?>

<?php if ($step === 'done'): ?>
<div class="card">
    <h2>✔ Instalación completada</h2>
    <p class="text-sm mb-4">Kontbox se ha instalado correctamente con MySQL.</p>
    <div class="info">
        <strong>Credenciales por defecto:</strong><br>
        Admin: <strong>admin@kontbox.com</strong> / password<br>
        Vendedor: <strong>vendedor@kontbox.com</strong> / password<br>
        Gerente: <strong>gerente@kontbox.com</strong> / password<br>
        Administrativo: <strong>administrativo@kontbox.com</strong> / password
    </div>
    <?php if ($output): ?>
    <div class="output"><?= implode("\n", $output) ?></div>
    <?php endif; ?>
    <div class="text-center">
        <a href="<?= htmlspecialchars($_POST['app_url'] ?? '.') ?>" class="btn">Ingresar al sistema →</a>
    </div>
</div>

<?php elseif ($errors): ?>
<div class="card text-center">
    <p class="text-sm">Corrija los errores y recargue la página. Si falta algún ZIP, súbalo al mismo directorio que install.php.</p>
</div>

<?php else: ?>
<div class="card">
    <h2>Configurar base de datos</h2>
    <p class="text-sm mb-4">Ingrese los datos de conexión MySQL de su hosting.</p>
    <form method="post">
        <input type="hidden" name="step" value="go">

        <label>URL del sitio</label>
        <input type="url" name="app_url" value="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?>" placeholder="https://tudominio.com">

        <label>Tipo de BD</label>
        <select name="db_connection"><option value="mysql">MySQL / MariaDB</option></select>

        <div class="grid">
            <div><label>Host</label><input type="text" name="db_host" value="127.0.0.1" placeholder="127.0.0.1"></div>
            <div><label>Puerto</label><input type="text" name="db_port" value="3306" placeholder="3306"></div>
        </div>

        <label>Nombre de BD</label>
        <input type="text" name="db_database" value="kontbox" placeholder="kontbox" required>

        <label>Usuario</label>
        <input type="text" name="db_username" value="" placeholder="usuario_mysql" required>

        <label>Contraseña</label>
        <input type="password" name="db_password" value="" placeholder="Contraseña del usuario">

        <button type="submit" class="btn">Instalar Kontbox →</button>
    </form>
</div>
<?php endif; ?>

<div class="text-center text-sm" style="color:#9ca3af;margin-top:2rem">Kontbox — Elimine install.php después de la instalación</div>
</div>
</body>
</html>

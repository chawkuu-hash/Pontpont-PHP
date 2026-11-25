<?php
/**
 * Pont Pont Handbag Shop - 1-Click Bluehost Database Installer
 * (ပုံ့ပုံ့ အိတ်ဆိုင် - Database အလိုအလျောက် သွင်းပေးသည့် စနစ်)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$configFile = __DIR__ . '/config/db.php';
$sqlFile = __DIR__ . '/database/pontpont_db.sql';
$lockFile = __DIR__ . '/database/install.lock';

// Security check: Lock installer if already installed
if (file_exists($lockFile)) {
    die("<!DOCTYPE html>
    <html lang='my'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Installer Locked - Pont Pont Handbag Shop</title>
        <script src='https://cdn.tailwindcss.com'></script>
    </head>
    <body class='bg-stone-50 min-h-screen flex items-center justify-center p-4'>
        <div class='bg-white max-w-md w-full rounded-2xl shadow-xl border border-stone-200 p-6 text-center'>
            <div class='w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4'>
                <svg class='w-8 h-8' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'></path></svg>
            </div>
            <h2 class='text-xl font-bold text-stone-900 mb-2'>Installer is Locked 🔒</h2>
            <p class='text-stone-600 text-sm mb-6 leading-relaxed'>စနစ်ကို တပ်ဆင်ပြီးဖြစ်သဖြင့် လုံခြုံရေးအရ ပိတ်ထားပါသည်။ ပြန်လည် install လုပ်လိုပါက <code>database/install.lock</code> ဖိုင်အား ဖျက်ပေးပါ။</p>
            <a href='index.php' class='inline-block w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition'>🛍️ စတိုးဆိုင်သို့ သွားမည်</a>
        </div>
    </body>
    </html>");
}

$step = 'form';
$error = '';
$success = '';

// Pre-fill from current config or defaults
$dbHost = defined('DB_HOST') ? DB_HOST : 'localhost';
$dbPort = defined('DB_PORT') ? DB_PORT : '3306';
$dbName = defined('DB_NAME') ? DB_NAME : '';
$dbUser = defined('DB_USER') ? DB_USER : '';
$dbPass = defined('DB_PASS') ? DB_PASS : '';

if (file_exists($configFile)) {
    $content = file_get_contents($configFile);
    if (preg_match("/define\('DB_HOST',\s*getenv\('DB_HOST'\)\s*\?:\s*'([^']+)'\);/", $content, $m)) $dbHost = $m[1];
    if (preg_match("/define\('DB_NAME',\s*getenv\('DB_NAME'\)\s*\?:\s*'([^']+)'\);/", $content, $m)) $dbName = $m[1];
    if (preg_match("/define\('DB_USER',\s*getenv\('DB_USER'\)\s*\?:\s*'([^']+)'\);/", $content, $m)) $dbUser = $m[1];
    if (preg_match("/define\('DB_PASS',\s*getenv\('DB_PASS'\)\s*!==\s*false\s*\?\s*getenv\('DB_PASS'\)\s*:\s*'([^']*)'\);/", $content, $m)) $dbPass = $m[1];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $inHost = trim($_POST['db_host'] ?? 'localhost');
    $inPort = trim($_POST['db_port'] ?? '3306');
    $inName = trim($_POST['db_name'] ?? '');
    $inUser = trim($_POST['db_user'] ?? '');
    $inPass = trim($_POST['db_pass'] ?? '');

    if (empty($inName) || empty($inUser)) {
        $error = 'Database Name နှင့် Username အား ဖြည့်သွင်းပေးရန် လိုအပ်ပါသည်။';
    } elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $inName)) {
        $error = 'Database Name တွင် အင်္ဂလိပ်စာလုံး၊ နံပါတ်နှင့် underscore (_) သာ အသုံးပြုနိုင်ပါသည်။';
    } else {
        try {
            $dsn = "mysql:host={$inHost};port={$inPort};charset=utf8mb4";
            $pdo = new PDO($dsn, $inUser, $inPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);

            // Ensure database exists or create it safely
            $cleanDbName = str_replace('`', '``', $inName);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$cleanDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$cleanDbName}`");

            if ($action === 'install') {
                if (!file_exists($sqlFile)) {
                    throw new Exception("database/pontpont_db.sql ဖိုင် မတွေ့ရှိပါ။");
                }

                $sqlContent = file_get_contents($sqlFile);
                // Execute SQL script
                $pdo->exec($sqlContent);

                // Generate random secret key for HMAC
                $generatedSecret = bin2hex(random_bytes(32));

                $expHost   = var_export($inHost, true);
                $expPort   = var_export($inPort, true);
                $expName   = var_export($inName, true);
                $expUser   = var_export($inUser, true);
                $expPass   = var_export($inPass, true);
                $expSecret = var_export($generatedSecret, true);

                // Update config/db.php safely without PHP string interpolation vulnerabilities
                $newConfig = "<?php
/**
 * Pont Pont Handbag Shop (ပုံ့ပုံ့ အိတ်ဆိုင် - မန္တလေး)
 * Bluehost MySQL Database Configuration
 */

if (defined('PONTPONT_DB_CONFIG_LOADED')) {
    return;
}
define('PONTPONT_DB_CONFIG_LOADED', true);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 604800);
    ini_set('session.gc_maxlifetime', 604800);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);

    \$isHttps = (!empty(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] !== 'off')
               || (isset(\$_SERVER['SERVER_PORT']) && (int)\$_SERVER['SERVER_PORT'] === 443)
               || (!empty(\$_SERVER['HTTP_X_FORWARDED_PROTO']) && \$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if (\$isHttps) {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.cookie_samesite', 'Lax');

    session_start();
}

define('DB_HOST', getenv('DB_HOST') ?: {$expHost});
define('DB_PORT', getenv('DB_PORT') ?: {$expPort});
define('DB_NAME', getenv('DB_NAME') ?: {$expName});
define('DB_USER', getenv('DB_USER') ?: {$expUser});
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : {$expPass});
define('DB_CHARSET', 'utf8mb4');

define('APP_SECRET_KEY', getenv('APP_SECRET_KEY') ?: {$expSecret});

function ensureSchemaUpdated(\$pdo) {
    static \$checked = false;
    if (\$checked) return;
    \$checked = true;
    try {
        \$colStmt = \$pdo->query(\"SHOW COLUMNS FROM `orders` LIKE 'paymentSlip'\");
        if (!\$colStmt->fetch()) {
            \$pdo->exec(\"ALTER TABLE `orders` ADD COLUMN `paymentSlip` VARCHAR(255) DEFAULT NULL AFTER `transactionRef`\");
        }
        \$colStmt2 = \$pdo->query(\"SHOW COLUMNS FROM `orders` LIKE 'couponCode'\");
        if (!\$colStmt2->fetch()) {
            \$pdo->exec(\"ALTER TABLE `orders` ADD COLUMN `couponCode` VARCHAR(50) DEFAULT NULL AFTER `paymentSlip`\");
        }
        \$pdo->exec(\"CREATE TABLE IF NOT EXISTS `coupons` (
            `id` VARCHAR(50) NOT NULL PRIMARY KEY,
            `code` VARCHAR(50) NOT NULL UNIQUE,
            `discountType` VARCHAR(20) NOT NULL DEFAULT 'percent',
            `discountValue` INT NOT NULL,
            `minOrderAmount` INT NOT NULL DEFAULT 0,
            `usageLimit` INT NOT NULL DEFAULT 0,
            `usedCount` INT NOT NULL DEFAULT 0,
            `expiresAt` VARCHAR(50) DEFAULT NULL,
            `isActive` TINYINT(1) NOT NULL DEFAULT 1,
            `createdAt` VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\");
        \$cnt = \$pdo->query(\"SELECT COUNT(*) as c FROM coupons\")->fetch();
        if (\$cnt && (int)\$cnt['c'] === 0) {
            \$pdo->exec(\"INSERT INTO `coupons` (`id`, `code`, `discountType`, `discountValue`, `minOrderAmount`, `usageLimit`, `usedCount`, `expiresAt`, `isActive`, `createdAt`) VALUES
            ('cpn-01', 'PONTPONT10', 'percent', 10, 0, 0, 0, NULL, 1, '2026-11-23T00:00:00Z'),
            ('cpn-02', 'PONTWELCOME', 'fixed', 5000, 30000, 100, 0, NULL, 1, '2026-11-23T00:00:00Z'),
            ('cpn-03', 'VIPLUXURY', 'percent', 15, 100000, 50, 0, NULL, 1, '2026-11-23T00:00:00Z')\");
        }
        \$pdo->exec(\"INSERT IGNORE INTO `settings` (`key`, `value`) VALUES 
            ('telegram_bot_token', ''),
            ('telegram_chat_id', ''),
            ('telegram_enabled', '0'),
            ('app_version', '1.2.0'),
            ('app_min_version', '1.0.0'),
            ('app_download_url', 'https://pontpont.com/download')\");
    } catch (\\Throwable \$e) {}
}

function getDb() {
    static \$pdo = null;
    if (\$pdo !== null) return \$pdo;

    \$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    \$options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ];

    try {
        \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options);
        ensureSchemaUpdated(\$pdo);
        return \$pdo;
    } catch (PDOException \$e) {
        error_log('Database connection error: ' . \$e->getMessage());
        \$isApi = (isset(\$_SERVER['REQUEST_URI']) && strpos(\$_SERVER['REQUEST_URI'], '/api/') !== false)
                 || (isset(\$_SERVER['HTTP_ACCEPT']) && strpos(\$_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        if (\$isApi) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Database connection failed. Please check server logs.']);
            exit;
        }
        die(\"<div style='font-family:sans-serif;padding:20px;color:#c00;'><h2>Database Connection Error</h2><p>Please check your database configuration in config/db.php.</p></div>\");
    }
}
";
                file_put_contents($configFile, $newConfig);

                // Create installation lock file
                file_put_contents($lockFile, date('c'));

                $step = 'success';
                $success = "Database Setup အောင်မြင်စွာ ပြီးမြောက်ပါပြီ။ စတိုးဆိုင်ကို အသုံးပြုနိုင်ပါပြီ။";
            } else {
                $success = "Database Connection အောင်မြင်ပါသည်။ 'Install / Run Database Setup' ခလုတ်ကို နှိပ်ပြီး ဆက်လက်လုပ်ဆောင်နိုင်ပါသည်။";
            }
        } catch (Exception $e) {
            $error = 'ချိတ်ဆက်မှု မအောင်မြင်ပါ: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pont Pont Handbag Shop - Bluehost Database Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Padauk:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Padauk', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-amber-50/50 via-stone-100 to-amber-100/30 min-h-screen py-10 px-4">
    <div class="max-w-xl mx-auto">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-600 text-white shadow-lg shadow-amber-600/30 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-stone-900">ပုံ့ပုံ့ အိတ်ဆိုင် (Pont Pont Shop)</h1>
            <p class="text-stone-600 text-sm mt-1">Bluehost Web Hosting - 1-Click Database Installer</p>
        </div>

        <?php if ($step === 'success'): ?>
            <!-- Success Card -->
            <div class="bg-white rounded-3xl shadow-xl border border-emerald-100 p-8 text-center">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-stone-900 mb-2">Setup ပြီးမြောက်ပါပြီ! 🎉</h2>
                <p class="text-stone-600 text-sm mb-6 leading-relaxed">
                    Database tables များနှင့် မူလကုန်ပစ္စည်း (၈) မျိုး၊ ဆိုင်ခွဲအချက်အလက်များနှင့် Admin အကောင့်ကို အောင်မြင်စွာ ဖန်တီးထည့်သွင်းပြီးပါပြီ။
                </p>

                <div class="bg-stone-50 border border-stone-200 rounded-2xl p-4 text-left text-xs text-stone-700 mb-6 space-y-2">
                    <p class="font-bold text-stone-900 text-sm border-b pb-2">🔑 Default Admin Account အချက်အလက်များ -</p>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Email:</span>
                        <code class="font-bold text-stone-900 bg-white px-2 py-0.5 rounded border">admin@pontpont.com</code>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Password:</span>
                        <code class="font-bold text-stone-900 bg-white px-2 py-0.5 rounded border">admin123</code>
                    </div>
                    <p class="text-[11px] text-amber-700 pt-1">
                        * လုံခြုံရေးအတွက် စတိုးဆိုင်သို့ ဝင်ရောက်ပြီးပါက Admin စကားဝှက်ကို ပြောင်းလဲပေးပါ။
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <a href="index.php" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-4 rounded-xl text-sm transition shadow-md shadow-amber-600/20 text-center">
                        🛍️ စတိုးဆိုင် ကြည့်မည်
                    </a>
                    <a href="admin.php" class="bg-stone-900 hover:bg-black text-white font-bold py-3 px-4 rounded-xl text-sm transition text-center">
                        ⚙️ Admin Dashboard
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Setup Form -->
            <div class="bg-white rounded-3xl shadow-xl border border-stone-200/80 p-6 md:p-8">
                <div class="border-b border-stone-100 pb-4 mb-6">
                    <h2 class="text-lg font-bold text-stone-900">Database ချိတ်ဆက်မှု အချက်အလက်များ</h2>
                    <p class="text-xs text-stone-500 mt-0.5">Bluehost cPanel > MySQL Databases တွင် ဖန်တီးထားသော အချက်အလက်များကို ဖြည့်ပါ။</p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-5 bg-red-50 border border-red-200 rounded-2xl p-4 flex gap-3 text-red-800 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex gap-3 text-emerald-800 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <div><?= htmlspecialchars($success) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-stone-700 mb-1">Database Host</label>
                            <input type="text" name="db_host" value="<?= htmlspecialchars($dbHost) ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <span class="text-[11px] text-stone-400">Bluehost အတွက် 'localhost' ထားပါ</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Port</label>
                            <input type="text" name="db_port" value="<?= htmlspecialchars($dbPort) ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Database Name *</label>
                        <input type="text" name="db_name" value="<?= htmlspecialchars($dbName) ?>" placeholder="ဥပမာ: mybluehost_pontpont" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <span class="text-[11px] text-stone-400">Bluehost cPanel တွင် သတ်မှတ်ထားသော Database အမည်အပြည့်အစုံ</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Database User *</label>
                        <input type="text" name="db_user" value="<?= htmlspecialchars($dbUser) ?>" placeholder="ဥပမာ: mybluehost_admin" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Database Password</label>
                        <input type="password" name="db_pass" value="<?= htmlspecialchars($dbPass) ?>" placeholder="MySQL User Password" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row gap-3">
                        <button type="submit" name="action" value="test" class="flex-1 px-4 py-3 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-sm transition">
                            🔍 ချိတ်ဆက်မှု စစ်ဆေးမည် (Test)
                        </button>
                        <button type="submit" name="action" value="install" class="flex-1 px-4 py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-sm transition shadow-lg shadow-amber-600/30 flex items-center justify-center gap-2">
                            <span>🚀 Setup စတင်သွင်းမည်</span>
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-6 border-t border-stone-100 text-center">
                    <p class="text-xs text-stone-500">
                        phpMyAdmin ဖြင့် တိုက်ရိုက် သွင်းလိုပါက <code>database/pontpont_db.sql</code> ဖိုင်ကို Import ပြုလုပ်နိုင်ပါသည်။
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

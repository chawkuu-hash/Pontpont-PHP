<?php
/**
 * Pont Pont Handbag Shop - 1-Click Bluehost Database Installer
 * (ပုံ့ပုံ့ အိတ်ဆိုင် - Database အလိုအလျောက် သွင်းပေးသည့် စနစ်)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$configFile = __DIR__ . '/config/db.php';
$sqlFile = __DIR__ . '/database/pontpont_db.sql';

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
    } else {
        try {
            $dsn = "mysql:host={$inHost};port={$inPort};charset=utf8mb4";
            $pdo = new PDO($dsn, $inUser, $inPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);

            // Ensure database exists or create it
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$inName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$inName}`");

            if ($action === 'install') {
                if (!file_exists($sqlFile)) {
                    throw new Exception("database/pontpont_db.sql ဖိုင် မတွေ့ရှိပါ။");
                }

                $sqlContent = file_get_contents($sqlFile);
                // Execute SQL script
                $pdo->exec($sqlContent);

                // Update config/db.php
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
    session_start();
}

define('DB_HOST', getenv('DB_HOST') ?: '{$inHost}');
define('DB_PORT', getenv('DB_PORT') ?: '{$inPort}');
define('DB_NAME', getenv('DB_NAME') ?: '{$inName}');
define('DB_USER', getenv('DB_USER') ?: '{$inUser}');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '{$inPass}');
define('DB_CHARSET', 'utf8mb4');

define('APP_SECRET_KEY', getenv('APP_SECRET_KEY') ?: 'pontpont-luxury-handbag-shop-secret-key-2026');

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
        return \$pdo;
    } catch (PDOException \$e) {
        \$isApi = (isset(\$_SERVER['REQUEST_URI']) && strpos(\$_SERVER['REQUEST_URI'], '/api/') !== false)
                 || (isset(\$_SERVER['HTTP_ACCEPT']) && strpos(\$_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        if (\$isApi) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Database connection failed: ' . \$e->getMessage()]);
            exit;
        }
        die(\"<div style='font-family:sans-serif;padding:20px;color:#c00;'><h2>Database Connection Error</h2><p>\" . htmlspecialchars(\$e->getMessage()) . \"</p></div>\");
    }
}
";
                file_put_contents($configFile, $newConfig);
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
                    <a href="index.php?admin=1" class="bg-stone-900 hover:bg-black text-white font-bold py-3 px-4 rounded-xl text-sm transition text-center">
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

<?php
/**
 * Pont Pont Handbag Shop (ပုံ့ပုံ့ အိတ်ဆိုင် - မန္တလေး)
 * Bluehost MySQL Database Configuration
 */

// Prevent multiple includes
if (defined('PONTPONT_DB_CONFIG_LOADED')) {
    return;
}
define('PONTPONT_DB_CONFIG_LOADED', true);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    // 7 days session lifetime
    ini_set('session.cookie_lifetime', 604800);
    ini_set('session.gc_maxlifetime', 604800);
    session_start();
}

// ============================================================
// BLUEHOST DATABASE SETTINGS (Bluehost cPanel Database အချက်အလက်များ)
// ============================================================
// Bluehost cPanel > MySQL Databases တွင် ဖန်တီးထားသော အချက်အလက်များကို ဖြည့်ပါ။
// Bluehost shared hosting တွင် Host သည် ပုံမှန်အားဖြင့် 'localhost' ဖြစ်ပါသည်။
// Database Name နှင့် User တွင် cPanel prefix (ဥပမာ- 'myusername_pontpont') ပါဝင်တတ်ပါသည်။

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'pontpont_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Secret key for HMAC token signing (can be customized)
define('APP_SECRET_KEY', getenv('APP_SECRET_KEY') ?: 'pontpont-luxury-handbag-shop-secret-key-2026');

/**
 * Get the PDO Database connection
 * @return PDO
 */
function getDb() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // If calling from API, return JSON error
        $isApi = (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false)
                 || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => 'Database connection failed: ' . $e->getMessage(),
                'hint' => 'Please configure config/db.php with your Bluehost MySQL database credentials.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Friendly HTML error page for browser
        die("<!DOCTYPE html>
        <html lang='my'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Database Connection Error - Pont Pont Shop</title>
            <script src='https://cdn.tailwindcss.com'></script>
        </head>
        <body class='bg-stone-50 min-h-screen flex items-center justify-center p-4'>
            <div class='bg-white max-w-lg w-full rounded-2xl shadow-xl border border-red-200 p-6 md:p-8'>
                <div class='flex items-center gap-3 text-red-600 mb-4'>
                    <svg class='w-8 h-8' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'></path></svg>
                    <h2 class='text-xl font-bold'>Database ချိတ်ဆက်မှု မအောင်မြင်ပါ</h2>
                </div>
                <p class='text-stone-600 text-sm mb-4 leading-relaxed'>
                    MySQL Database သို့ ချိတ်ဆက်၍ မရပါ။ Bluehost cPanel တွင် Database ဖန်တီးပြီး <code>config/db.php</code> ဖိုင်တွင် အချက်အလက်များ မှန်ကန်စွာ ဖြည့်သွင်းပေးပါ။
                </p>
                <div class='bg-stone-100 p-3 rounded-lg text-xs font-mono text-red-700 overflow-x-auto mb-5'>
                    " . htmlspecialchars($e->getMessage()) . "
                </div>
                <div class='bg-amber-50 border border-amber-200 p-4 rounded-xl text-xs text-amber-900 mb-6 space-y-1.5'>
                    <p class='font-bold'>📌 Bluehost cPanel တွင် လုပ်ဆောင်ရန် အဆင့်များ -</p>
                    <p>၁။ Bluehost cPanel > <strong>MySQL Databases</strong> သို့သွားပါ။</p>
                    <p>၂။ Database အသစ်တစ်ခုနှင့် User တစ်ခု ဖန်တီးပြီး <strong>ALL PRIVILEGES</strong> ပေးပါ။</p>
                    <p>၃။ <code>config/db.php</code> ထဲရှိ DB_NAME, DB_USER, DB_PASS များကို ပြင်ဆင်ပါ။</p>
                    <p>၄။ <strong>phpMyAdmin</strong> မှ <code>database/pontpont_db.sql</code> ကို Import လုပ်ပါ (သို့မဟုတ် အောက်ပါ Install ခလုတ်ကို နှိပ်ပါ)။</p>
                </div>
                <div class='flex gap-3'>
                    <a href='install.php' class='flex-1 text-center bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition'>
                        🚀 Web Installer သို့ သွားမည်
                    </a>
                    <button onclick='window.location.reload()' class='px-4 py-2.5 bg-stone-200 hover:bg-stone-300 text-stone-800 font-semibold rounded-xl text-sm transition'>
                        ပြန်လည်စစ်ဆေးမည်
                    </button>
                </div>
            </div>
        </body>
        </html>");
    }
}

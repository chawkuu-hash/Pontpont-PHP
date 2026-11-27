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
    // 7 days session lifetime with secure cookie settings
    ini_set('session.cookie_lifetime', 604800);
    ini_set('session.gc_maxlifetime', 604800);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if ($isHttps) {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.cookie_samesite', 'Lax');

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
define('DB_NAME', getenv('DB_NAME') ?: 'fgkijemy_pontpont');
define('DB_USER', getenv('DB_USER') ?: 'fgkijemy_WPufm');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : ')Hm?{qxauzp3ces=');
define('DB_CHARSET', 'utf8mb4');

// Secret key for HMAC token signing (can be customized)
define('APP_SECRET_KEY', getenv('APP_SECRET_KEY') ?: '6b2cd5e9e97a11a0915621128fdd2a45721c9935f715eceab354ade9905d973d');

/**
 * Automatically update database schema with new tables and columns if missing
 */
function ensureSchemaUpdated($pdo) {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        // 1. Ensure paymentSlip and couponCode exist in orders table
        $colStmt = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'paymentSlip'");
        if (!$colStmt->fetch()) {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `paymentSlip` VARCHAR(255) DEFAULT NULL AFTER `transactionRef`");
        }
        $colStmt2 = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'couponCode'");
        if (!$colStmt2->fetch()) {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `couponCode` VARCHAR(50) DEFAULT NULL AFTER `paymentSlip`");
        }

        // 1b. Ensure avatar column exists in users table
        $colAvatar = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'avatar'");
        if (!$colAvatar->fetch()) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) DEFAULT NULL AFTER `address`");
        }

        // 2. Ensure coupons table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS `coupons` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Seed default coupons if none exist
        $cntCoupons = $pdo->query("SELECT COUNT(*) as c FROM coupons")->fetch();
        if ($cntCoupons && (int)$cntCoupons['c'] === 0) {
            $pdo->exec("INSERT INTO `coupons` (`id`, `code`, `discountType`, `discountValue`, `minOrderAmount`, `usageLimit`, `usedCount`, `expiresAt`, `isActive`, `createdAt`) VALUES
            ('cpn-01', 'PONTPONT10', 'percent', 10, 0, 0, 0, NULL, 1, '2026-11-23T00:00:00Z'),
            ('cpn-02', 'PONTWELCOME', 'fixed', 5000, 30000, 100, 0, NULL, 1, '2026-11-23T00:00:00Z'),
            ('cpn-03', 'VIPLUXURY', 'percent', 15, 100000, 50, 0, NULL, 1, '2026-11-23T00:00:00Z')");
        }

        // 3. Ensure chatbot_config table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS `chatbot_config` (
          `id` INT NOT NULL PRIMARY KEY DEFAULT 1,
          `provider` VARCHAR(50) NOT NULL DEFAULT 'mock',
          `apiUrl` TEXT DEFAULT NULL,
          `region` VARCHAR(50) DEFAULT 'us-east-1',
          `apiKey` TEXT DEFAULT NULL,
          `model` VARCHAR(100) NOT NULL DEFAULT 'gemini-flash-lite-latest',
          `systemPrompt` TEXT NOT NULL,
          `botName` VARCHAR(100) NOT NULL DEFAULT 'ပုံ့ပုံ့ AI အကူအညီ',
          `welcomeMessageMm` TEXT NOT NULL,
          `welcomeMessageEn` TEXT NOT NULL,
          `enabled` TINYINT(1) NOT NULL DEFAULT 1,
          `updatedAt` VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $colRegion = $pdo->query("SHOW COLUMNS FROM `chatbot_config` LIKE 'region'");
        if (!$colRegion->fetch()) {
            $pdo->exec("ALTER TABLE `chatbot_config` ADD COLUMN `region` VARCHAR(50) DEFAULT 'us-east-1' AFTER `apiUrl`");
        }

        $cntBot = $pdo->query("SELECT COUNT(*) as c FROM chatbot_config")->fetch();
        if ($cntBot && (int)$cntBot['c'] === 0) {
            $pdo->exec("INSERT INTO `chatbot_config` (`id`, `provider`, `apiUrl`, `region`, `apiKey`, `model`, `systemPrompt`, `botName`, `welcomeMessageMm`, `welcomeMessageEn`, `enabled`, `updatedAt`) VALUES
            (1, 'mock', '', 'us-east-1', '', 'gemini-flash-lite-latest', 'You are \"Pont Pont AI\" (ပုံ့ပုံ့ AI အကူအညီ), the polite, friendly customer assistant for \"Pont Pont Handbag Shop\" (ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး) based in Mandalay, Myanmar.', 'ပုံ့ပုံ့ AI အကူအညီ', 'မင်္ဂလာပါရှင် 👋 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်။ ဘာများ ကူညီပေးရမလဲရှင်။', 'Hello! Welcome to Pont Pont Handbag Shop Mandalay.', 1, '2026-11-23T00:00:00Z')");
        }

        // 4. Ensure telegram and app settings exist
        $pdo->exec("INSERT IGNORE INTO `settings` (`key`, `value`) VALUES 
            ('telegram_bot_token', ''),
            ('telegram_chat_id', ''),
            ('telegram_enabled', '0'),
            ('app_version', '1.2.0'),
            ('app_min_version', '1.0.0'),
            ('app_download_url', 'https://pontpont.com/download')");

        // 5. Ensure payment_methods table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS `payment_methods` (
          `id` VARCHAR(50) NOT NULL PRIMARY KEY,
          `code` VARCHAR(50) NOT NULL,
          `name` VARCHAR(100) NOT NULL,
          `nameMm` VARCHAR(100) NOT NULL,
          `type` VARCHAR(30) NOT NULL DEFAULT 'transfer',
          `accountName` VARCHAR(100) DEFAULT NULL,
          `accountNumber` VARCHAR(100) DEFAULT NULL,
          `instructions` TEXT DEFAULT NULL,
          `qrCodeUrl` VARCHAR(255) DEFAULT NULL,
          `requiresSlip` TINYINT(1) NOT NULL DEFAULT 1,
          `isActive` TINYINT(1) NOT NULL DEFAULT 1,
          `sortOrder` INT NOT NULL DEFAULT 0,
          `createdAt` VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $cntPay = $pdo->query("SELECT COUNT(*) as c FROM payment_methods")->fetch();
        if ($cntPay && (int)$cntPay['c'] === 0) {
            $pdo->exec("INSERT INTO `payment_methods` (`id`, `code`, `name`, `nameMm`, `type`, `accountName`, `accountNumber`, `instructions`, `qrCodeUrl`, `requiresSlip`, `isActive`, `sortOrder`, `createdAt`) VALUES
            ('pm-kpay', 'KPay', 'KBZPay', 'KBZPay (ငွေလွှဲ)', 'transfer', 'Ma Pont Pont (မပုံ့)', '09963000502', 'KBZPay ဖြင့် ငွေလွှဲပြီးပါက ပြေစာ Screenshot တင်ပေးပါရန်', NULL, 1, 1, 1, '2026-11-23T00:00:00Z'),
            ('pm-wave', 'WavePay', 'WavePay', 'WavePay (ငွေလွှဲ)', 'transfer', 'Ma Pont Pont (မပုံ့)', '09963000502', 'WavePay ဖြင့် ငွေလွှဲပြီးပါက ပြေစာ Screenshot တင်ပေးပါရန်', NULL, 1, 1, 2, '2026-11-23T00:00:00Z'),
            ('pm-cod', 'COD', 'Cash on Delivery', 'အိမ်အရောက်ငွေချေ (COD)', 'cod', '', '', 'မန္တလေးမြို့တွင်း ပစ္စည်းရောက်မှ ငွေချေစနစ် ဖြစ်ပါသည်', NULL, 0, 1, 3, '2026-11-23T00:00:00Z'),
            ('pm-aya', 'AYAPay', 'AYA Pay / Banking', 'AYA Pay / ဘဏ်ငွေလွှဲ', 'banking', 'Daw Pont Pont', '09963000502', 'AYA Pay သို့မဟုတ် AYA Bank ဖြင့် လွှဲပေးနိုင်ပါသည်', NULL, 1, 1, 4, '2026-11-23T00:00:00Z')");
        }
    } catch (\Throwable $e) {
        // Silently skip if database or tables do not exist yet (e.g. before install)
    }
}

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
        ensureSchemaUpdated($pdo);
        return $pdo;
    } catch (PDOException $e) {
        // Log detailed error for system administrator
        error_log("Database connection error: " . $e->getMessage());

        // If calling from API, return generic error (do not leak DB credentials/path)
        $isApi = (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false)
                 || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => 'Database connection failed. Please check server configuration.',
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

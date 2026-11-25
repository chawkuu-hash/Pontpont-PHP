<?php
/**
 * Pont Pont Handbag Shop - Core REST API
 * Compatible with Next.js API, Flutter Mobile App, and Bluehost Web Hosting
 */

// Enable error reporting for debugging if needed, but output clean JSON
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);

// Setup CORS and JSON headers
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!empty($origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Cookie');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$SESSION_COOKIE_NAME = 'pontpont_session';

// Helper: Determine if current connection is HTTPS
function isHttpsRequest() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

// Supported cities with delivery fees
$CITIES = [
    ['name' => 'Mandalay (မန္တလေး - မြို့တွင်း)', 'fee' => 2500],
    ['name' => 'Pyin Oo Lwin (ပြင်ဦးလွင်)', 'fee' => 3500],
    ['name' => 'Sagaing / Monywa (စစ်ကိုင်း / မုံရွာ)', 'fee' => 3500],
    ['name' => 'Yangon (ရန်ကုန်)', 'fee' => 4000],
    ['name' => 'Naypyidaw (နေပြည်တော်)', 'fee' => 3500],
    ['name' => 'Taunggyi (တောင်ကြီး)', 'fee' => 4000],
    ['name' => 'Magway / Pakokku (မကွေး / ပခုက္ကူ)', 'fee' => 4000],
    ['name' => 'Other Cities / ကားဂိတ်တင် (အခြားမြို့များ)', 'fee' => 4500],
];

// Helper: Sign session token
function createSessionToken($userId) {
    $payload = $userId . '.' . time();
    $sig = hash_hmac('sha256', $payload, APP_SECRET_KEY);
    return $payload . '.' . $sig;
}

// Helper: Verify session token (with expiration check)
function verifySessionToken($token) {
    if (!$token || !is_string($token)) return null;
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    list($userId, $timestamp, $sig) = $parts;

    // Enforce token validity: 7 days (604800 seconds)
    $time = (int)$timestamp;
    $now = time();
    if ($now - $time > 604800 || $time > $now + 300) {
        return null;
    }

    $payload = $userId . '.' . $timestamp;
    $expectedSig = hash_hmac('sha256', $payload, APP_SECRET_KEY);
    if (hash_equals($expectedSig, $sig)) {
        return $userId;
    }
    return null;
}

// Helper: Get authenticated user from session cookie or headers
function getAuthenticatedUser($pdo) {
    global $SESSION_COOKIE_NAME;

    $token = null;
    if (!empty($_COOKIE[$SESSION_COOKIE_NAME])) {
        $token = $_COOKIE[$SESSION_COOKIE_NAME];
    } elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = trim($_SERVER['HTTP_AUTHORIZATION']);
        if (preg_match('/Bearer\s+(\S+)/i', $auth, $matches)) {
            $token = $matches[1];
        }
    } elseif (!empty($_SERVER['HTTP_COOKIE'])) {
        if (preg_match('/' . preg_quote($SESSION_COOKIE_NAME) . '=([^;]+)/', $_SERVER['HTTP_COOKIE'], $matches)) {
            $token = urldecode($matches[1]);
        }
    }

    $userId = verifySessionToken($token);

    if ($userId) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if ($user) {
            return $user;
        }
    }

    return null;
}

// Helper: Sanitize user object (remove password)
function sanitizeUser($user) {
    if (!$user) return null;
    $safe = $user;
    unset($safe['password']);
    return $safe;
}

// Helper: Send Telegram Order Notification Alert
function sendTelegramOrderAlert($pdo, $order) {
    try {
        $stmt = $pdo->prepare("SELECT `key`, `value` FROM settings WHERE `key` IN ('telegram_bot_token', 'telegram_chat_id', 'telegram_enabled')");
        $stmt->execute();
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['key']] = $row['value'];
        }

        if (empty($settings['telegram_enabled']) || $settings['telegram_enabled'] !== '1') {
            return;
        }

        $botToken = trim($settings['telegram_bot_token'] ?? '');
        $chatId = trim($settings['telegram_chat_id'] ?? '');

        if (!$botToken || !$chatId) {
            return;
        }

        $itemsText = "";
        if (!empty($order['items']) && is_array($order['items'])) {
            foreach ($order['items'] as $idx => $it) {
                $num = $idx + 1;
                $name = !empty($it['nameMm']) ? $it['nameMm'] : (!empty($it['nameEn']) ? $it['nameEn'] : 'Handbag');
                $color = !empty($it['selectedColor']) ? " ({$it['selectedColor']})" : "";
                $qty = $it['quantity'] ?? 1;
                $price = number_format($it['price'] ?? 0);
                $itemsText .= "  {$num}. {$name}{$color} x {$qty} = {$price} Ks\n";
            }
        }

        $msg = "🛍️ <b>အော်ဒါအသစ် ရောက်ရှိပါသည်! (New Order)</b>\n\n";
        $msg .= "🆔 <b>Order ID:</b> <code>{$order['id']}</code>\n";
        $msg .= "👤 <b>ဝယ်သူအမည်:</b> " . htmlspecialchars($order['customerName'] ?? '') . "\n";
        $msg .= "📞 <b>ဖုန်းနံပါတ်:</b> " . htmlspecialchars($order['phone'] ?? '') . "\n";
        $msg .= "📍 <b>မြို့နယ်:</b> " . htmlspecialchars($order['city'] ?? '') . "\n";
        $msg .= "🏠 <b>လိပ်စာ:</b> " . htmlspecialchars($order['address'] ?? '') . "\n";
        if (!empty($order['note'])) {
            $msg .= "📝 <b>မှတ်ချက်:</b> " . htmlspecialchars($order['note']) . "\n";
        }
        $msg .= "💳 <b>ငွေချေစနစ်:</b> " . ($order['paymentMethod'] ?? 'COD') . "\n";
        if (!empty($order['transactionRef'])) {
            $msg .= "🔢 <b>Transaction Ref:</b> <code>" . htmlspecialchars($order['transactionRef']) . "</code>\n";
        }
        if (!empty($order['couponCode'])) {
            $msg .= "🏷️ <b>ကူပွန်:</b> <code>" . htmlspecialchars($order['couponCode']) . "</code>\n";
        }
        if (!empty($order['paymentSlip'])) {
            $msg .= "🖼️ <b>ငွေလွှဲပြေစာ:</b> ပါရှိပါသည်\n";
        }
        $msg .= "\n📦 <b>မှာယူထားသော ပစ္စည်းများ:</b>\n{$itemsText}\n";
        $msg .= "💰 <b>စုစုပေါင်း ကျသင့်ငွေ:</b> <b>" . number_format($order['total'] ?? 0) . " Ks</b>\n";
        $msg .= "📅 <b>အချိန်:</b> " . gmdate('Y-m-d H:i:s') . " UTC\n";

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $postData = http_build_query([
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'HTML'
        ]);

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $postData,
                'timeout' => 5
            ]
        ];
        $context = stream_context_create($opts);
        @file_get_contents($url, false, $context);
    } catch (\Throwable $t) {
        error_log('Telegram alert error: ' . $t->getMessage());
    }
}

// Helper: Handle secure image uploads (file or base64 data)
function handleSecureImageUpload($fileOrBase64, $subDir, $prefix = 'img') {
    $uploadDir = __DIR__ . '/../assets/uploads/' . $subDir . '/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

    // Case 1: Standard $_FILES upload
    if (is_array($fileOrBase64) && isset($fileOrBase64['tmp_name'])) {
        if ($fileOrBase64['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload error code: ' . $fileOrBase64['error']];
        }
        if ($fileOrBase64['size'] > 5 * 1024 * 1024) {
            return ['ok' => false, 'error' => 'ပုံအရွယ်အစားသည် 5MB ထက် မကျော်ရပါ'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fileOrBase64['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimes)) {
            return ['ok' => false, 'error' => 'လက်ခံသော ပုံအမျိုးအစားများမှာ JPG, PNG, WEBP သာ ဖြစ်ပါသည်'];
        }

        $ext = strtolower(pathinfo($fileOrBase64['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            $ext = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
        }

        $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (move_uploaded_file($fileOrBase64['tmp_name'], $destPath)) {
            chmod($destPath, 0664);
            return ['ok' => true, 'url' => 'assets/uploads/' . $subDir . '/' . $filename];
        }
        return ['ok' => false, 'error' => 'ဖိုင်သိမ်းဆည်းမှု မအောင်မြင်ပါ'];
    }

    // Case 2: Base64 data string (data:image/png;base64,...)
    if (is_string($fileOrBase64) && !empty($fileOrBase64)) {
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $fileOrBase64, $matches)) {
            $ext = strtolower($matches[1]);
            $base64Data = $matches[2];
        } else {
            $ext = 'jpg';
            $base64Data = $fileOrBase64;
        }

        if ($ext === 'jpeg') $ext = 'jpg';
        if (!in_array($ext, $allowedExts)) {
            $ext = 'jpg';
        }

        $decoded = base64_decode($base64Data);
        if ($decoded === false) {
            return ['ok' => false, 'error' => 'Invalid image data'];
        }

        if (strlen($decoded) > 5 * 1024 * 1024) {
            return ['ok' => false, 'error' => 'ပုံအရွယ်အစားသည် 5MB ထက် မကျော်ရပါ'];
        }

        $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (file_put_contents($destPath, $decoded)) {
            chmod($destPath, 0664);
            return ['ok' => true, 'url' => 'assets/uploads/' . $subDir . '/' . $filename];
        }
        return ['ok' => false, 'error' => 'ဖိုင်သိမ်းဆည်းမှု မအောင်မြင်ပါ'];
    }

    return ['ok' => false, 'error' => 'ပုံဖိုင် မတွေ့ရှိပါ'];
}

// Helper: Make resilient HTTP requests using cURL or stream context
function makeHttpRequest($url, $method = 'GET', $headers = [], $postData = null, $timeout = 20) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $headerList = [];
        foreach ($headers as $k => $v) {
            $headerList[] = is_numeric($k) ? $v : "$k: $v";
        }
        if (!empty($headerList)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerList);
        }

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postData !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($postData) ? json_encode($postData) : $postData);
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body'   => $response !== false ? $response : null
        ];
    } else {
        $formattedHeaders = '';
        foreach ($headers as $k => $v) {
            $formattedHeaders .= (is_numeric($k) ? $v : "$k: $v") . "\r\n";
        }
        $options = [
            'http' => [
                'method' => strtoupper($method),
                'header' => $formattedHeaders,
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];
        if (strtoupper($method) === 'POST' && $postData !== null) {
            $options['http']['content'] = is_array($postData) ? json_encode($postData) : $postData;
        }
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        $httpCode = 200;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $hdr) {
                if (preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#', $hdr, $m)) {
                    $httpCode = (int)$m[1];
                    break;
                }
            }
        }
        return [
            'status' => $httpCode,
            'body'   => $response !== false ? $response : null
        ];
    }
}

// Helper: Intelligent Myanmar Rule-Based Chatbot Engine (Tailored to Pont Pont Handbag Shop)
function generateIntelligentBurmeseReply($message, $pdo = null) {
    $q = mb_strtolower(trim($message), 'UTF-8');

    // 1. Delivery, Bus Gates & Cities
    if (preg_match('/(ပို့|ကားဂိတ်|ဂိတ်|ကားလိုင်း|ပို့ခ|deli|delivery|ဘယ်မြို့|နယ်|ရန်ကုန်|တောင်ကြီး|နေပြည်တော်|မုံရွာ|ပဲခူး|စစ်ကိုင်း)/u', $q)) {
        return "မင်္ဂလာပါရှင့် 🙏 Pont Pont အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး (မန္တလေး) မှ ကြိုဆိုပါတယ်ရှင့်။\n\n🚚 ပို့ဆောင်ရေး ဝန်ဆောင်မှု နှုန်းထားများ:\n• မန္တလေးမြို့တွင်း: ၂,၅၀၀ ကျပ် (အိမ်အရောက်ငွေချေစနစ် Cash on Delivery ရပါသည်)\n• ရန်ကုန်မြို့တွင်း: ၃,၅၀၀ ကျပ်\n• နေပြည်တော်: ၃,၅၀၀ ကျပ်\n• နယ်ဝေးမြို့နယ်ပေါင်းစုံ: ၄,၀၀၀ ကျပ် (ခရီးသည်တင် ကားဂိတ်/ကားလိုင်းများမှတစ်ဆင့် ပို့ဆောင်ပေးပါသည်)\n\n⚡ အမှာစာများကို ၁-၃ ရက်အတွင်း အမြန်ဆုံး ပို့ဆောင်ပေးပါသည်ရှင်။ ဘယ်မြို့ကို ပို့ဆောင်ပေးရမလဲရှင့်။";
    }

    // 2. Branches, Address & Location
    if (preg_match('/(လိပ်စာ|ဆိုင်|ဘယ်မှာ|ပင်မ|address|branch|လမ်း|နေရာ|တည်နေရာ)/u', $q)) {
        return "🏪 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေးမြို့) ဆိုင်ခွဲ (၃) ဆိုင် လိပ်စာများ:\n\n• ပင်မဆိုင်: ၆၅ လမ်း၊ ၁၀၇×၁၀၈ လမ်းကြား၊ မန္တလေးမြို့\n• ဆိုင်ခွဲ (၂): ၇၃ လမ်း၊ ၃၆×၃၇ လမ်းကြား၊ မန္တလေးမြို့\n• ဆိုင်ခွဲ (၃): ၇၈ လမ်း (ရန်ကုန်-မန္တလေးလမ်း)၊ တံခွန်တိုင်ဈေးအနီး\n\n⏰ ဆိုင်ဖွင့်ချိန်: နေ့စဉ် မနက် 8:00 AM မှ ည 8:00 PM အထိ ဆိုင်သို့ လာရောက်လေ့လာ ဝယ်ယူနိုင်ပါသည်ရှင်။";
    }

    // 3. Phone & Viber Contact
    if (preg_match('/(ဖုန်း|ဆက်သွယ်|phone|call|viber|contact)/u', $q)) {
        return "📞 ဆက်သွယ်ရန် ဖုန်းနံပါတ်များ:\n• 09-963000502 (ပင်မဖုန်း / Viber)\n• 09-750072752\n• 09-404448881\n\nနေ့စဉ် မနက် 8:00 မှ ည 8:00 အထိ စုံစမ်းမေးမြန်း အော်ဒါတင်နိုင်ပါတယ်ရှင်။";
    }

    // 4. Payment & Banking
    if (preg_match('/(ငွေ|kpay|wave|pay|ဘဏ်|လွှဲ|banking|acc|account|cod)/u', $q)) {
        return "💰 ငွေပေးချေမှု နည်းလမ်းများ:\n• KBZPay: 09-963000502 (Ma Pont Pont)\n• WavePay: 09-963000502 (Ma Pont Pont)\n• မန္တလေးမြို့တွင်း အိမ်အရောက်ငွေချေ (COD)\n• Mobile Banking (KBZ, CB, AYA)\n\n% ထပ်ဆောင်း ကောက်ခံခြင်း လုံးဝ မရှိပါရှင်။";
    }

    // 5. Wholesale
    if (preg_match('/(လက်ကား|လက္ကား|wholesale|ပြန်ရောင်း|အထည်)/u', $q)) {
        return "📦 လက်ကား မှာယူမှုများ (Wholesale):\nဆိုင်ရှင်များနှင့် ပြန်လည်ရောင်းချသူများအတွက် အထူးလက်ကားဈေးနှုန်း သီးသန့်ရှိပါသည် (အနည်းဆုံး ၅ လုံးမှ ၁၀ လုံးအထက်)။\nလက်ကား ကတ်တလောက်နှင့် ဈေးနှုန်းညှိနှိုင်းရန် ဖုန်း 09-963000502 (Viber) သို့ တိုက်ရိုက် ဆက်သွယ်စုံစမ်းနိုင်ပါသည်ရှင်။";
    }

    // 6. Products & Handbags
    if (preg_match('/(အိတ်|လက်ဆွဲ|ကျောပိုး|ဘေးလွယ်|ပေါင်မုန့်|ဒလက်|ဒီဇိုင်း|bag|price|ဈေး)/u', $q)) {
        return "👜 ပုံ့ပုံ့ အိတ်ဆိုင်တွင် လက်ဆွဲအိတ်၊ ကျောပိုးအိတ်၊ ဘေးလွယ်အိတ်၊ ဖက်ရှင်အိတ်နှင့် ရုံးတက်အိတ် အမျိုးစုံကို အရည်အသွေးမြင့် သားရေ/ပိတ်သားများဖြင့် ရရှိနိုင်ပါသည်ရှင်။\nဝဘ်ဆိုက် ပင်မစာမျက်နှာတွင် ပစ္စည်းပုံများနှင့် ဈေးနှုန်းများကို အသေးစိတ် ကြည့်ရှု၍ တိုက်ရိုက် Order တင်နိုင်ပါသည်ရှင်။";
    }

    // 7. General Services & Greetings
    if (preg_match('/(ဝန်ဆောင်မှု|service|မင်္ဂလာ|hello|hi|မေး|စုံစမ်း)/u', $q)) {
        return "မင်္ဂလာပါရှင့် 🙏 \"ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး\" (မန္တလေး) မှ ကြိုဆိုပါတယ်ရှင့်။\n\nကျွန်မတို့ဆိုင်မှ ပေးဆောင်သော ဝန်ဆောင်မှုများမှာ -\n၁။ ခေတ်မီ အရည်အသွေးမြင့် အိတ်အမျိုးမျိုး လက်လီ/လက်ကား ရောင်းချခြင်း\n၂။ မန္တလေးမြို့တွင်း အိမ်အရောက် ငွေချေ (COD) စနစ်\n၃။ နယ်မြို့များအားလုံးသို့ ကားဂိတ်/ပို့ဆောင်ရေးဖြင့် အမြန်ပို့စနစ်\n၄။ ငွေလွှဲ % ပေးရန်မလိုဘဲ KPay/WavePay ဖြင့် လွယ်ကူစွာ ငွေချေနိုင်ခြင်း\n\nအိတ်ဒီဇိုင်းများ၊ ဈေးနှုန်း၊ ဆိုင်လိပ်စာနှင့် ပို့ခများကို စိတ်ကြိုက် မေးမြန်းနိုင်ပါသည်ရှင်။";
    }

    // Default Fallback
    return "မင်္ဂလာပါရှင့် 🙏 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်ရှင့်။\nဆိုင်လိပ်စာ၊ ဖုန်းနံပါတ်၊ ကားဂိတ်ပို့ဆောင်ခ၊ ငွေပေးချေမှုနှင့် အိတ်ဒီဇိုင်း/ဈေးနှုန်းများကို အသေးစိတ် မေးမြန်းနိုင်ပါသည်ရှင်။ (ဖုန်း - 09-963000502)";
}

// Helper: Build complete scoped database response
function buildScopedResponse($pdo, $sessionUser) {
    // 1. Categories
    $catStmt = $pdo->query("SELECT id, labelEn, labelMm, sortOrder FROM categories ORDER BY sortOrder ASC");
    $categories = $catStmt->fetchAll();

    // 2. Products
    $prodStmt = $pdo->query("SELECT * FROM products ORDER BY sortOrder ASC");
    $rawProducts = $prodStmt->fetchAll();
    $products = [];
    foreach ($rawProducts as $p) {
        $colors = json_decode($p['colorsJson'] ?: '[]', true);
        if (!is_array($colors)) $colors = [];

        $products[] = [
            'id'             => (string)$p['id'],
            'nameEn'         => (string)$p['nameEn'],
            'nameMm'         => (string)$p['nameMm'],
            'category'       => (string)$p['category'],
            'categoryMm'     => (string)$p['categoryMm'],
            'price'          => (int)$p['price'],
            'originalPrice'  => $p['originalPrice'] !== null ? (int)$p['originalPrice'] : null,
            'wholesalePrice' => $p['wholesalePrice'] !== null ? (int)$p['wholesalePrice'] : null,
            'image'          => (string)$p['image'],
            'colors'         => $colors,
            'badge'          => $p['badge'] ?: null,
            'rating'         => (float)$p['rating'],
            'reviewsCount'   => (int)$p['reviewsCount'],
            'stock'          => (int)$p['stock'],
            'material'       => (string)$p['material'],
            'dimensions'     => (string)$p['dimensions'],
            'descriptionEn'  => (string)$p['descriptionEn'],
            'descriptionMm'  => (string)$p['descriptionMm'],
        ];
    }

    // 3. Announcements
    $annStmt = $pdo->query("SELECT * FROM announcements ORDER BY createdAt DESC");
    $announcements = $annStmt->fetchAll();

    // 4. Orders (Scoped based on role)
    $isAdmin = $sessionUser && ($sessionUser['role'] === 'admin');
    if ($isAdmin) {
        $orderStmt = $pdo->query("SELECT * FROM orders ORDER BY createdAt DESC");
        $rawOrders = $orderStmt->fetchAll();
    } elseif ($sessionUser) {
        $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE (userEmail = ? OR phone = ?) ORDER BY createdAt DESC");
        $orderStmt->execute([$sessionUser['email'], $sessionUser['phone']]);
        $rawOrders = $orderStmt->fetchAll();
    } else {
        $rawOrders = [];
    }

    $orders = [];
    foreach ($rawOrders as $o) {
        $items = json_decode($o['itemsJson'] ?: '[]', true);
        if (!is_array($items)) $items = [];

        $orders[] = [
            'id'             => (string)$o['id'],
            'customerName'   => (string)$o['customerName'],
            'phone'          => (string)$o['phone'],
            'city'           => (string)$o['city'],
            'address'        => (string)$o['address'],
            'note'           => $o['note'] ?: null,
            'paymentMethod'  => (string)$o['paymentMethod'],
            'transactionRef' => $o['transactionRef'] ?: null,
            'paymentSlip'    => !empty($o['paymentSlip']) ? (string)$o['paymentSlip'] : null,
            'couponCode'     => !empty($o['couponCode']) ? (string)$o['couponCode'] : null,
            'items'          => $items,
            'subtotal'       => (int)$o['subtotal'],
            'discount'       => (int)$o['discount'],
            'deliveryFee'    => (int)$o['deliveryFee'],
            'total'          => (int)$o['total'],
            'status'         => (string)$o['status'],
            'createdAt'      => (string)$o['createdAt'],
            'userEmail'      => $o['userEmail'] ?: null,
        ];
    }

    // 5. Users count & list (users list only visible to Admin)
    $userCountStmt = $pdo->query("SELECT COUNT(*) AS c FROM users");
    $usersCount = (int)$userCountStmt->fetch()['c'];

    $users = [];
    if ($isAdmin) {
        $usersStmt = $pdo->query("SELECT * FROM users ORDER BY createdAt ASC");
        $rawUsers = $usersStmt->fetchAll();
        foreach ($rawUsers as $u) {
            $users[] = sanitizeUser($u);
        }
    }

    // 6. Coupons
    $coupons = [];
    try {
        if ($isAdmin) {
            $cpnStmt = $pdo->query("SELECT * FROM coupons ORDER BY createdAt DESC");
            $coupons = $cpnStmt->fetchAll();
        } else {
            $cpnStmt = $pdo->query("SELECT id, code, discountType, discountValue, minOrderAmount FROM coupons WHERE isActive = 1 ORDER BY createdAt DESC");
            $coupons = $cpnStmt->fetchAll();
        }
    } catch (\Throwable $e) {}

    // 7. Settings
    $settings = [];
    try {
        $setStmt = $pdo->query("SELECT `key`, `value` FROM settings");
        while ($sRow = $setStmt->fetch()) {
            if (!$isAdmin && in_array($sRow['key'], ['telegram_bot_token', 'telegram_chat_id'])) {
                continue;
            }
            $settings[$sRow['key']] = $sRow['value'];
        }
    } catch (\Throwable $e) {}

    $theme = $settings['theme'] ?? 'luxury-gold';

    // 7. Chatbot Config
    $chatbotConfig = null;
    try {
        $botStmt = $pdo->query("SELECT * FROM chatbot_config WHERE id = 1 LIMIT 1");
        $botRow = $botStmt->fetch();
        if ($botRow) {
            if ($isAdmin) {
                $chatbotConfig = [
                    'provider'         => (string)$botRow['provider'],
                    'apiUrl'           => (string)($botRow['apiUrl'] ?? ''),
                    'apiKey'           => (string)($botRow['apiKey'] ?? ''),
                    'model'            => (string)$botRow['model'],
                    'systemPrompt'     => (string)$botRow['systemPrompt'],
                    'botName'          => (string)$botRow['botName'],
                    'welcomeMessageMm' => (string)$botRow['welcomeMessageMm'],
                    'welcomeMessageEn' => (string)$botRow['welcomeMessageEn'],
                    'enabled'          => (bool)$botRow['enabled'],
                    'hasApiKey'        => !empty($botRow['apiKey']),
                ];
            } else {
                $chatbotConfig = [
                    'provider'         => (string)$botRow['provider'],
                    'model'            => (string)$botRow['model'],
                    'botName'          => (string)$botRow['botName'],
                    'welcomeMessageMm' => (string)$botRow['welcomeMessageMm'],
                    'welcomeMessageEn' => (string)$botRow['welcomeMessageEn'],
                    'enabled'          => (bool)$botRow['enabled'],
                    'hasApiKey'        => !empty($botRow['apiKey']),
                ];
            }
        }
    } catch (Exception $e) {
        // Table may not exist yet
    }

    if (!$chatbotConfig) {
        $chatbotConfig = [
            'provider'         => 'mock',
            'model'            => 'gemini-1.5-flash',
            'botName'          => 'ပုံ့ပုံ့ AI အကူအညီ',
            'welcomeMessageMm' => 'မင်္ဂလာပါရှင် 👋 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်။ ဘာများ ကူညီပေးရမလဲရှင်။',
            'welcomeMessageEn' => 'Hello! Welcome to Pont Pont Handbag Shop Mandalay.',
            'enabled'          => true,
            'hasApiKey'        => false,
        ];
        if ($isAdmin) {
            $chatbotConfig['apiUrl'] = '';
            $chatbotConfig['apiKey'] = '';
            $chatbotConfig['systemPrompt'] = 'You are "Pont Pont AI" (ပုံ့ပုံ့ AI အကူအညီ), the polite, friendly customer assistant for "Pont Pont Handbag Shop" (ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး) based in Mandalay, Myanmar.';
        }
    }

    return [
        'products'      => $products,
        'categories'    => $categories,
        'announcements' => $announcements,
        'orders'        => $orders,
        'users'         => $users,
        'usersCount'    => $usersCount,
        'coupons'       => $coupons,
        'settings'      => $settings,
        'currentUser'   => sanitizeUser($sessionUser),
        'theme'         => $theme,
        'chatbotConfig' => $chatbotConfig,
        'paymentMethods' => (function() use ($pdo, $isAdmin) {
            $methods = [];
            try {
                if ($isAdmin) {
                    $stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY sortOrder ASC, createdAt ASC");
                } else {
                    $stmt = $pdo->query("SELECT * FROM payment_methods WHERE isActive = 1 ORDER BY sortOrder ASC, createdAt ASC");
                }
                while ($pm = $stmt->fetch()) {
                    $methods[] = [
                        'id'            => (string)$pm['id'],
                        'code'          => (string)$pm['code'],
                        'name'          => (string)$pm['name'],
                        'nameMm'        => (string)$pm['nameMm'],
                        'type'          => (string)$pm['type'],
                        'accountName'   => $pm['accountName'] ? (string)$pm['accountName'] : '',
                        'accountNumber' => $pm['accountNumber'] ? (string)$pm['accountNumber'] : '',
                        'instructions'  => $pm['instructions'] ? (string)$pm['instructions'] : '',
                        'qrCodeUrl'     => $pm['qrCodeUrl'] ? (string)$pm['qrCodeUrl'] : null,
                        'requiresSlip'  => (bool)$pm['requiresSlip'],
                        'isActive'      => (bool)$pm['isActive'],
                        'sortOrder'     => (int)$pm['sortOrder'],
                        'createdAt'     => (string)$pm['createdAt'],
                    ];
                }
            } catch (\Throwable $e) {}
            return $methods;
        })(),
    ];
}

$pdo = getDb();

// --------------------------------------------------------------------------
// GET Request: Return full scoped shop data
// --------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sessionUser = getAuthenticatedUser($pdo);
    echo json_encode(buildScopedResponse($pdo, $sessionUser), JSON_UNESCAPED_UNICODE);
    exit;
}

// --------------------------------------------------------------------------
// POST Request: Handle actions
// --------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput) && php_sapi_name() === 'cli') {
        $rawInput = file_get_contents('php://stdin');
    }
    $body = json_decode($rawInput, true);
    if (!is_array($body)) {
        $body = $_POST;
    }

    $action = isset($body['action']) ? trim($body['action']) : '';
    $sessionUser = getAuthenticatedUser($pdo);

    // 1. LOGOUT
    if ($action === 'LOGOUT') {
        setcookie($SESSION_COOKIE_NAME, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'secure'   => isHttpsRequest(),
            'samesite' => 'Lax'
        ]);
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, null)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. REGISTER
    if ($action === 'REGISTER') {
        $name = trim($body['name'] ?? '');
        $email = strtolower(trim($body['email'] ?? ''));
        $password = (string)($body['password'] ?? '');
        $phone = trim($body['phone'] ?? '');
        $city = trim($body['city'] ?? 'Mandalay (မန္တလေး - မြို့တွင်း)');
        $address = trim($body['address'] ?? ($city ?: ''));

        if (!$name || !$email || !$password || !$phone) {
            http_response_code(400);
            echo json_encode(['error' => 'လိုအပ်သော အချက်အလက်များ အားလုံး ဖြည့်စွက်ပေးပါ။'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (mb_strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'စကားဝှက်သည် အနည်းဆုံး (၆) လုံး ရှိရပါမည်။'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Check if email already registered
        $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'ဤအီးမေးလ်ဖြင့် အကောင့်ဖွင့်ထားပြီးသား ဖြစ်ပါသည်။ အကောင့်ဝင်ရန် (Login) ကို နှိပ်ပါ။'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // First registered user is automatically admin
        $countStmt = $pdo->query("SELECT COUNT(*) AS c FROM users");
        $totalUsers = (int)$countStmt->fetch()['c'];
        $role = ($totalUsers === 0) ? 'admin' : 'customer';

        $userId = 'usr-' . round(microtime(true) * 1000);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $createdAt = gmdate('Y-m-d\TH:i:s\Z');

        $ins = $pdo->prepare("INSERT INTO users (id, name, email, password, phone, city, address, role, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([
            $userId,
            mb_substr($name, 0, 80),
            mb_substr($email, 0, 120),
            $hashedPassword,
            mb_substr($phone, 0, 30),
            $city,
            mb_substr($address, 0, 250),
            $role,
            $createdAt
        ]);

        $newUser = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'city' => $city,
            'address' => $address,
            'role' => $role,
            'createdAt' => $createdAt
        ];

        $token = createSessionToken($userId);
        setcookie($SESSION_COOKIE_NAME, $token, [
            'expires'  => time() + (86400 * 7),
            'path'     => '/',
            'httponly' => true,
            'secure'   => isHttpsRequest(),
            'samesite' => 'Lax'
        ]);

        echo json_encode([
            'ok' => true,
            'token' => $token,
            'user' => sanitizeUser($newUser),
            'db' => buildScopedResponse($pdo, $newUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. LOGIN
    if ($action === 'LOGIN') {
        $email = strtolower(trim($body['email'] ?? ''));
        $password = (string)($body['password'] ?? '');

        // Database-backed rate limiting per IP to prevent session-clearing bypass
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $now = time();
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
                ip VARCHAR(45) NOT NULL PRIMARY KEY,
                attempts INT NOT NULL DEFAULT 1,
                lastAttempt INT NOT NULL
            ) ENGINE=InnoDB");

            $rateStmt = $pdo->prepare("SELECT attempts, lastAttempt FROM login_attempts WHERE ip = ?");
            $rateStmt->execute([$clientIp]);
            $rateRow = $rateStmt->fetch();

            if ($rateRow) {
                if (($now - (int)$rateRow['lastAttempt'] < 300) && (int)$rateRow['attempts'] >= 10) {
                    http_response_code(429);
                    echo json_encode(['error' => 'စကားဝှက် မှားယွင်းမှု များနေပါသဖြင့် ၅ မိနစ်ခန့် စောင့်ဆိုင်းပြီးမှ ပြန်လည်ကြိုးစားပါ။'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        } catch (Exception $e) {}

        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = ? LIMIT 1");
        $stmt->execute([$email]);
        $foundUser = $stmt->fetch();

        $valid = false;
        if ($foundUser) {
            $stored = $foundUser['password'];
            if (password_verify($password, $stored)) {
                $valid = true;
            } elseif ($stored === $password) { // plaintext fallback & auto-upgrade
                $valid = true;
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->execute([$newHash, $foundUser['id']]);
            }
        }

        if (!$foundUser || !$valid) {
            try {
                $updRate = $pdo->prepare("INSERT INTO login_attempts (ip, attempts, lastAttempt) VALUES (?, 1, ?)
                    ON DUPLICATE KEY UPDATE attempts = IF(? - lastAttempt > 300, 1, attempts + 1), lastAttempt = ?");
                $updRate->execute([$clientIp, $now, $now, $now]);
            } catch (Exception $e) {}

            http_response_code(401);
            echo json_encode(['error' => 'အီးမေးလ် သို့မဟုတ် စကားဝှက် မှားယွင်းနေပါသည်။ အကောင့်မရှိသေးပါက Sign Up လုပ်ပေးပါ။'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $clrRate = $pdo->prepare("DELETE FROM login_attempts WHERE ip = ?");
            $clrRate->execute([$clientIp]);
        } catch (Exception $e) {}

        $token = createSessionToken($foundUser['id']);
        setcookie($SESSION_COOKIE_NAME, $token, [
            'expires'  => time() + (86400 * 7),
            'path'     => '/',
            'httponly' => true,
            'secure'   => isHttpsRequest(),
            'samesite' => 'Lax'
        ]);

        echo json_encode([
            'ok' => true,
            'token' => $token,
            'user' => sanitizeUser($foundUser),
            'db' => buildScopedResponse($pdo, $foundUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. UPDATE_PROFILE
    if ($action === 'UPDATE_PROFILE') {
        $userId = trim($body['userId'] ?? '');
        if (!$sessionUser || ($sessionUser['id'] !== $userId && $sessionUser['role'] !== 'admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized profile update'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $existing = $stmt->fetch();
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $name = !empty($body['name']) ? mb_substr(trim($body['name']), 0, 80) : $existing['name'];
        $phone = !empty($body['phone']) ? mb_substr(trim($body['phone']), 0, 30) : $existing['phone'];
        $city = !empty($body['city']) ? trim($body['city']) : $existing['city'];
        $address = isset($body['address']) ? mb_substr(trim($body['address']), 0, 250) : $existing['address'];

        $password = $existing['password'];
        if (!empty($body['newPassword']) && mb_strlen(trim($body['newPassword'])) >= 6) {
            $password = password_hash(trim($body['newPassword']), PASSWORD_BCRYPT);
        }

        $upd = $pdo->prepare("UPDATE users SET name = ?, phone = ?, city = ?, address = ?, password = ? WHERE id = ?");
        $upd->execute([$name, $phone, $city, $address, $password, $userId]);

        $stmt->execute([$userId]);
        $updatedUser = $stmt->fetch();

        echo json_encode([
            'ok' => true,
            'user' => sanitizeUser($updatedUser),
            'db' => buildScopedResponse($pdo, $updatedUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 5. CREATE_ORDER
    if ($action === 'CREATE_ORDER') {
        $incoming = $body['order'] ?? null;
        if (!is_array($incoming) || empty($incoming['items']) || !is_array($incoming['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid order details.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Server-side calculation to prevent price tampering
            $calculatedSubtotal = 0;
            $verifiedItems = [];

            foreach ($incoming['items'] as $item) {
                $qty = max(1, min(99, (int)($item['quantity'] ?? 1)));
                $productId = $item['productId'] ?? '';

                $pStmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
                $pStmt->execute([$productId]);
                $product = $pStmt->fetch();

                $unitPrice = $product ? (int)$product['price'] : max(0, (int)($item['price'] ?? 0));
                $calculatedSubtotal += $unitPrice * $qty;

                // Decrement stock if product exists
                if ($product) {
                    $dec = $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");
                    $dec->execute([$qty, $productId]);
                }

                $verifiedItems[] = [
                    'productId'     => $productId,
                    'nameEn'        => $product ? $product['nameEn'] : (string)($item['nameEn'] ?? ''),
                    'nameMm'        => $product ? $product['nameMm'] : (string)($item['nameMm'] ?? ''),
                    'price'         => $unitPrice,
                    'quantity'      => $qty,
                    'selectedColor' => mb_substr((string)($item['selectedColor'] ?? 'Default'), 0, 40),
                    'image'         => $product ? $product['image'] : (string)($item['image'] ?? ''),
                ];
            }

            // Coupon calculation
            $couponCode = !empty($incoming['couponCode']) ? strtoupper(trim($incoming['couponCode'])) : null;
            $discount = 0;
            if ($couponCode) {
                $cpnStmt = $pdo->prepare("SELECT * FROM coupons WHERE UPPER(code) = ? AND isActive = 1 LIMIT 1");
                $cpnStmt->execute([$couponCode]);
                $coupon = $cpnStmt->fetch();
                if ($coupon) {
                    $isExpired = !empty($coupon['expiresAt']) && strtotime($coupon['expiresAt']) < time();
                    $isLimitReached = ($coupon['usageLimit'] > 0) && ($coupon['usedCount'] >= $coupon['usageLimit']);
                    $isAmountMet = $calculatedSubtotal >= (int)$coupon['minOrderAmount'];

                    if (!$isExpired && !$isLimitReached && $isAmountMet) {
                        if ($coupon['discountType'] === 'percent') {
                            $discount = (int)round($calculatedSubtotal * ((int)$coupon['discountValue'] / 100));
                        } else {
                            $discount = min($calculatedSubtotal, (int)$coupon['discountValue']);
                        }
                        // Increment used count
                        $incCpn = $pdo->prepare("UPDATE coupons SET usedCount = usedCount + 1 WHERE id = ?");
                        $incCpn->execute([$coupon['id']]);
                    }
                }
            } elseif (!empty($incoming['discount']) && (int)$incoming['discount'] > 0) {
                // Fallback for promo
                $discount = (int)round($calculatedSubtotal * 0.1);
            }

            // Delivery fee calculation
            $cityName = $incoming['city'] ?? $CITIES[0]['name'];
            $fee = $CITIES[0]['fee'];
            foreach ($CITIES as $c) {
                if ($c['name'] === $cityName) {
                    $fee = $c['fee'];
                    break;
                }
            }

            $deliveryFee = ($calculatedSubtotal - $discount >= 100000) ? 0 : $fee;
            $calculatedTotal = max(0, $calculatedSubtotal - $discount + $deliveryFee);

            $orderId = 'PP-2026-' . rand(1000, 9999);
            $createdAt = gmdate('Y-m-d\TH:i:s\Z');
            $customerName = mb_substr(trim($incoming['customerName'] ?? ''), 0, 80);
            $phone = mb_substr(trim($incoming['phone'] ?? ''), 0, 30);
            $address = mb_substr(trim($incoming['address'] ?? ''), 0, 250);
            $note = !empty($incoming['note']) ? mb_substr(trim($incoming['note']), 0, 200) : null;
            $paymentMethod = in_array($incoming['paymentMethod'] ?? '', ['KPay', 'WavePay', 'COD'])
                ? $incoming['paymentMethod']
                : 'COD';
            $transactionRef = !empty($incoming['transactionRef'])
                ? mb_substr(trim($incoming['transactionRef']), 0, 50)
                : null;
            $paymentSlip = !empty($incoming['paymentSlip'])
                ? mb_substr(trim($incoming['paymentSlip']), 0, 250)
                : null;
            $userEmail = $sessionUser ? $sessionUser['email'] : ($incoming['userEmail'] ?? null);

            $insOrder = $pdo->prepare("INSERT INTO orders (id, customerName, phone, city, address, note, paymentMethod, transactionRef, paymentSlip, couponCode, itemsJson, subtotal, discount, deliveryFee, total, status, createdAt, userEmail) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insOrder->execute([
                $orderId,
                $customerName,
                $phone,
                $cityName,
                $address,
                $note,
                $paymentMethod,
                $transactionRef,
                $paymentSlip,
                $couponCode,
                json_encode($verifiedItems, JSON_UNESCAPED_UNICODE),
                $calculatedSubtotal,
                $discount,
                $deliveryFee,
                $calculatedTotal,
                'Pending',
                $createdAt,
                $userEmail
            ]);

            $pdo->commit();

            $safeOrder = [
                'id'             => $orderId,
                'customerName'   => $customerName,
                'phone'          => $phone,
                'city'           => $cityName,
                'address'        => $address,
                'note'           => $note,
                'paymentMethod'  => $paymentMethod,
                'transactionRef' => $transactionRef,
                'paymentSlip'    => $paymentSlip,
                'couponCode'     => $couponCode,
                'items'          => $verifiedItems,
                'subtotal'       => $calculatedSubtotal,
                'discount'       => $discount,
                'deliveryFee'    => $deliveryFee,
                'total'          => $calculatedTotal,
                'status'         => 'Pending',
                'createdAt'      => $createdAt,
                'userEmail'      => $userEmail,
            ];

            // Send Telegram alert if configured
            sendTelegramOrderAlert($pdo, $safeOrder);

            echo json_encode([
                'ok' => true,
                'order' => $safeOrder,
                'db' => buildScopedResponse($pdo, $sessionUser)
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Order creation error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'အော်ဒါတင်ခြင်း မအောင်မြင်ပါ: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // 5.1 VALIDATE_COUPON (Public / Customer)
    if ($action === 'VALIDATE_COUPON') {
        $code = strtoupper(trim($body['code'] ?? ''));
        $subtotal = max(0, (int)($body['subtotal'] ?? 0));

        if (!$code) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ကျေးဇူးပြု၍ ကူပွန်ကုဒ် ရိုက်ထည့်ပါ'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE UPPER(code) = ? LIMIT 1");
        $stmt->execute([$code]);
        $cpn = $stmt->fetch();

        if (!$cpn || (int)$cpn['isActive'] !== 1) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'ကူပွန်ကုဒ် မမှန်ကန်ပါ သို့မဟုတ် သက်တမ်းကုန်သွားပါပြီ'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!empty($cpn['expiresAt']) && strtotime($cpn['expiresAt']) < time()) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ဤကူပွန်သည် သက်တမ်းကုန်ဆုံးသွားပါပြီ'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($cpn['usageLimit'] > 0 && (int)$cpn['usedCount'] >= (int)$cpn['usageLimit']) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ဤကူပွန်သည် အသုံးပြုခွင့် အရေအတွက် ပြည့်သွားပါပြီ'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($subtotal < (int)$cpn['minOrderAmount']) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ဤကူပွန် အသုံးပြုရန် အနည်းဆုံး ' . number_format($cpn['minOrderAmount']) . ' ကျပ်ဖိုး ဝယ်ယူရပါမည်'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $calculatedDiscount = 0;
        if ($cpn['discountType'] === 'percent') {
            $calculatedDiscount = (int)round($subtotal * ((int)$cpn['discountValue'] / 100));
        } else {
            $calculatedDiscount = min($subtotal, (int)$cpn['discountValue']);
        }

        echo json_encode([
            'ok' => true,
            'coupon' => [
                'code' => $cpn['code'],
                'discountType' => $cpn['discountType'],
                'discountValue' => (int)$cpn['discountValue'],
                'discountAmount' => $calculatedDiscount,
                'minOrderAmount' => (int)$cpn['minOrderAmount'],
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 5.2 UPLOAD_SLIP (Public / Customer for Checkout)
    if ($action === 'UPLOAD_SLIP') {
        $file = $_FILES['slip'] ?? ($body['slip_data'] ?? null);
        $res = handleSecureImageUpload($file, 'slips', 'slip');
        if ($res['ok']) {
            echo json_encode(['ok' => true, 'url' => $res['url']], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $res['error']], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 5.3 CHECK_VERSION (Public / Mobile App)
    if ($action === 'CHECK_VERSION') {
        $stmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` IN ('app_version', 'app_min_version', 'app_download_url')");
        $verSettings = [];
        while ($r = $stmt->fetch()) {
            $verSettings[$r['key']] = $r['value'];
        }
        echo json_encode([
            'ok' => true,
            'latestVersion' => $verSettings['app_version'] ?? '1.2.0',
            'minSupportedVersion' => $verSettings['app_min_version'] ?? '1.0.0',
            'downloadUrl' => $verSettings['app_download_url'] ?? 'https://pontpont.com/download'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 5.1. CHAT_MESSAGE (Customer Chatbot Query)
    if ($action === 'CHAT_MESSAGE') {
        $message = trim($body['message'] ?? '');
        if (!$message) {
            http_response_code(400);
            echo json_encode(['error' => 'Message cannot be empty.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Get bot config from database
        $cfg = null;
        try {
            $cfgStmt = $pdo->query("SELECT * FROM chatbot_config WHERE id = 1 LIMIT 1");
            $cfg = $cfgStmt->fetch();
        } catch (Exception $e) {}

        if (!$cfg || empty($cfg['enabled'])) {
            echo json_encode([
                'ok' => true,
                'reply' => 'Chatbot ဝန်ဆောင်မှု ခေတ္တပိတ်ထားပါသည်ရှင်။'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $provider = $cfg['provider'] ?? 'mock';
        $apiKey = trim($cfg['apiKey'] ?? '');
        $apiUrl = trim($cfg['apiUrl'] ?? '');
        $model = trim($cfg['model'] ?? 'gemini-1.5-flash');
        $systemPrompt = $cfg['systemPrompt'] ?? '';

        // Gemini AI Provider
        if ($provider === 'gemini' && $apiKey) {
            $modelsToTry = array_unique([$model, 'gemini-flash-lite-latest', 'gemini-3.8-flash', 'gemini-flash-latest']);
            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => "System Prompt Instructions:\n" . $systemPrompt . "\n\nCustomer Message: " . $message]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 800
                ]
            ];

            foreach ($modelsToTry as $tryModel) {
                if (empty($tryModel)) continue;
                $geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$tryModel}:generateContent?key={$apiKey}";
                $res = makeHttpRequest($geminiUrl, 'POST', ['Content-Type' => 'application/json'], $payload, 20);
                if ($res['status'] >= 200 && $res['status'] < 300 && $res['body']) {
                    $data = json_decode($res['body'], true);
                    $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($reply) {
                        if ($tryModel !== $model) {
                            try {
                                $upd = $pdo->prepare("UPDATE chatbot_config SET model = ? WHERE id = 1");
                                $upd->execute([$tryModel]);
                            } catch (\Throwable $t) {}
                        }
                        echo json_encode(['ok' => true, 'reply' => $reply, 'model' => $tryModel], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
            }
        }

        // OpenAI or Groq AI Provider
        if (($provider === 'openai' || $provider === 'groq') && $apiKey) {
            $endpoint = ($provider === 'groq')
                ? 'https://api.groq.com/openai/v1/chat/completions'
                : 'https://api.openai.com/v1/chat/completions';

            $defaultModel = ($provider === 'groq') ? 'llama-3.3-70b-versatile' : 'gpt-4o-mini';
            $payload = [
                'model' => $model ?: $defaultModel,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.7,
                'max_tokens' => 800
            ];
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$apiKey}"
            ];
            $res = makeHttpRequest($endpoint, 'POST', $headers, $payload, 20);
            if ($res['status'] >= 200 && $res['status'] < 300 && $res['body']) {
                $data = json_decode($res['body'], true);
                $reply = $data['choices'][0]['message']['content'] ?? null;
                if ($reply) {
                    echo json_encode(['ok' => true, 'reply' => $reply], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        }

        // Custom OpenAI-Compatible Provider (OpenWebUI, Ollama, LM Studio, etc.)
        if ($provider === 'custom' && $apiUrl) {
            $endpoint = (substr($apiUrl, -17) === '/chat/completions')
                ? $apiUrl
                : rtrim($apiUrl, '/') . '/chat/completions';

            $headers = ['Content-Type' => 'application/json'];
            if ($apiKey) {
                $headers['Authorization'] = "Bearer {$apiKey}";
            }

            $payload = [
                'model' => $model ?: 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.7,
                'max_tokens' => 800
            ];
            $res = makeHttpRequest($endpoint, 'POST', $headers, $payload, 25);
            if ($res['status'] >= 200 && $res['status'] < 300 && $res['body']) {
                $data = json_decode($res['body'], true);
                $reply = $data['choices'][0]['message']['content'] ?? null;
                if ($reply) {
                    echo json_encode(['ok' => true, 'reply' => $reply], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        }

        // Intelligent Myanmar Rule-based Knowledge Engine fallback
        $reply = generateIntelligentBurmeseReply($message, $pdo);
        echo json_encode(['ok' => true, 'reply' => $reply, 'fallback' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 5.2. FETCH_AI_MODELS (Auto-discover AI Models from Provider or Custom API)
    if ($action === 'FETCH_AI_MODELS') {
        $provider = trim($body['provider'] ?? '');
        $apiKey = trim($body['apiKey'] ?? '');
        $apiUrl = trim($body['apiUrl'] ?? '');
        $models = [];

        try {
            if ($provider === 'gemini' || (!$provider && strpos($apiKey, 'AIzaSy') === 0)) {
                $url = $apiUrl ?: "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
                if (strpos($url, 'key=') === false) {
                    $url .= (strpos($url, '?') === false ? '?' : '&') . "key={$apiKey}";
                }
                $res = makeHttpRequest($url, 'GET', [], null, 15);
                if ($res['status'] === 200 && $res['body']) {
                    $data = json_decode($res['body'], true);
                    if (isset($data['models']) && is_array($data['models'])) {
                        $rawModels = [];
                        foreach ($data['models'] as $m) {
                            $methods = $m['supportedGenerationMethods'] ?? [];
                            $name = $m['name'] ?? '';
                            if (in_array('generateContent', $methods) || strpos($name, 'gemini') !== false) {
                                $cleanName = str_replace('models/', '', $name);
                                if ($cleanName) $rawModels[] = $cleanName;
                            }
                        }
                        // Sort so proven production free-tier / fast models are at the top
                        $preferredOrder = [
                            'gemini-flash-lite-latest',
                            'gemini-3.8-flash',
                            'gemini-3.5-flash',
                            'gemini-2.5-flash-lite',
                            'gemini-flash-latest',
                            'gemini-pro-latest'
                        ];
                        $top = [];
                        $rest = [];
                        foreach ($rawModels as $rm) {
                            if (in_array($rm, $preferredOrder)) {
                                $top[] = $rm;
                            } else {
                                $rest[] = $rm;
                            }
                        }
                        usort($top, function($a, $b) use ($preferredOrder) {
                            return array_search($a, $preferredOrder) <=> array_search($b, $preferredOrder);
                        });
                        $models = array_merge($top, $rest);
                    }
                }
            } else {
                $url = $apiUrl;
                if (!$url) {
                    $url = ($provider === 'groq') ? 'https://api.groq.com/openai/v1/models' : 'https://api.openai.com/v1/models';
                } else {
                    if (substr($url, -7) !== '/models') {
                        $url = rtrim($url, '/') . '/models';
                    }
                }
                $headers = [];
                if ($apiKey) {
                    $headers['Authorization'] = "Bearer {$apiKey}";
                }
                $res = makeHttpRequest($url, 'GET', $headers, null, 15);
                if ($res['status'] === 200 && $res['body']) {
                    $data = json_decode($res['body'], true);
                    if (isset($data['data']) && is_array($data['data'])) {
                        foreach ($data['data'] as $m) {
                            if (!empty($m['id'])) $models[] = $m['id'];
                        }
                    } elseif (isset($data['models']) && is_array($data['models'])) {
                        foreach ($data['models'] as $m) {
                            if (!empty($m['id'])) $models[] = $m['id'];
                            elseif (!empty($m['name'])) $models[] = $m['name'];
                        }
                    }
                }
            }
        } catch (Exception $e) {}

        echo json_encode(['ok' => true, 'models' => array_values(array_unique($models))], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // --------------------------------------------------------------------------
    // ALL REMAINING ACTIONS REQUIRE ADMIN PRIVILEGES
    // --------------------------------------------------------------------------
    if (!$sessionUser || $sessionUser['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden: Admin privileges required.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 6. UPDATE_ORDER_STATUS
    if ($action === 'UPDATE_ORDER_STATUS') {
        $orderId = trim($body['orderId'] ?? '');
        $status = trim($body['status'] ?? '');
        $allowed = ['Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'];
        if (in_array($status, $allowed)) {
            $upd = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $upd->execute([$status, $orderId]);
        }
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 7. ADD_PRODUCT
    if ($action === 'ADD_PRODUCT') {
        $p = $body['product'] ?? [];
        $id = !empty($p['id']) ? trim($p['id']) : ('pp-bag-' . time());
        $nameEn = trim($p['nameEn'] ?? 'New Pont Pont Handbag');
        $nameMm = trim($p['nameMm'] ?? 'ပုံ့ပုံ့ ဒီဇိုင်းသစ် အိတ်');
        $category = trim($p['category'] ?? 'Tote');
        $categoryMm = trim($p['categoryMm'] ?? 'လက်ဆွဲအိတ်');
        $price = max(0, (int)($p['price'] ?? 0));
        $originalPrice = !empty($p['originalPrice']) ? (int)$p['originalPrice'] : null;
        $wholesalePrice = !empty($p['wholesalePrice']) ? (int)$p['wholesalePrice'] : null;
        $image = !empty($p['image']) ? trim($p['image']) : 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=900&q=80';
        $colorsJson = is_array($p['colors'] ?? null) ? json_encode($p['colors'], JSON_UNESCAPED_UNICODE) : (string)($p['colorsJson'] ?? '[]');
        $badge = !empty($p['badge']) ? trim($p['badge']) : null;
        $rating = !empty($p['rating']) ? (float)$p['rating'] : 5.0;
        $reviewsCount = !empty($p['reviewsCount']) ? (int)$p['reviewsCount'] : 1;
        $stock = max(0, (int)($p['stock'] ?? 0));
        $material = trim($p['material'] ?? 'High-Grade PU Leather');
        $dimensions = trim($p['dimensions'] ?? 'Standard');
        $descriptionEn = trim($p['descriptionEn'] ?? '');
        $descriptionMm = trim($p['descriptionMm'] ?? '');

        $insProd = $pdo->prepare("INSERT INTO products (id, nameEn, nameMm, category, categoryMm, price, originalPrice, wholesalePrice, image, colorsJson, badge, rating, reviewsCount, stock, material, dimensions, descriptionEn, descriptionMm, sortOrder) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $insProd->execute([
            $id, $nameEn, $nameMm, $category, $categoryMm, $price, $originalPrice,
            $wholesalePrice, $image, $colorsJson, $badge, $rating, $reviewsCount,
            $stock, $material, $dimensions, $descriptionEn, $descriptionMm
        ]);

        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 8. UPDATE_PRODUCT
    if ($action === 'UPDATE_PRODUCT') {
        $p = $body['product'] ?? [];
        $id = trim($p['id'] ?? '');
        if ($id) {
            $nameEn = trim($p['nameEn'] ?? '');
            $nameMm = trim($p['nameMm'] ?? '');
            $category = trim($p['category'] ?? '');
            $categoryMm = trim($p['categoryMm'] ?? '');
            $price = max(0, (int)($p['price'] ?? 0));
            $originalPrice = !empty($p['originalPrice']) ? (int)$p['originalPrice'] : null;
            $wholesalePrice = !empty($p['wholesalePrice']) ? (int)$p['wholesalePrice'] : null;
            $image = !empty($p['image']) ? trim($p['image']) : '';
            $colorsJson = is_array($p['colors'] ?? null) ? json_encode($p['colors'], JSON_UNESCAPED_UNICODE) : (string)($p['colorsJson'] ?? '[]');
            $badge = !empty($p['badge']) ? trim($p['badge']) : null;
            $rating = !empty($p['rating']) ? (float)$p['rating'] : 5.0;
            $reviewsCount = !empty($p['reviewsCount']) ? (int)$p['reviewsCount'] : 1;
            $stock = max(0, (int)($p['stock'] ?? 0));
            $material = trim($p['material'] ?? '');
            $dimensions = trim($p['dimensions'] ?? '');
            $descriptionEn = trim($p['descriptionEn'] ?? '');
            $descriptionMm = trim($p['descriptionMm'] ?? '');

            $updProd = $pdo->prepare("UPDATE products SET nameEn = ?, nameMm = ?, category = ?, categoryMm = ?, price = ?, originalPrice = ?, wholesalePrice = ?, image = ?, colorsJson = ?, badge = ?, rating = ?, reviewsCount = ?, stock = ?, material = ?, dimensions = ?, descriptionEn = ?, descriptionMm = ? WHERE id = ?");
            $updProd->execute([
                $nameEn, $nameMm, $category, $categoryMm, $price, $originalPrice,
                $wholesalePrice, $image, $colorsJson, $badge, $rating, $reviewsCount,
                $stock, $material, $dimensions, $descriptionEn, $descriptionMm, $id
            ]);
        }

        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 9. DELETE_PRODUCT
    if ($action === 'DELETE_PRODUCT') {
        $productId = trim($body['productId'] ?? '');
        $del = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $del->execute([$productId]);
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 10. ADD_ANNOUNCEMENT
    if ($action === 'ADD_ANNOUNCEMENT') {
        $a = $body['announcement'] ?? [];
        $id = !empty($a['id']) ? trim($a['id']) : ('ann-' . time());
        $title = trim($a['title'] ?? '');
        $content = trim($a['content'] ?? '');
        $tag = trim($a['tag'] ?? 'Notice');
        $image = !empty($a['image']) ? trim($a['image']) : null;
        $createdAt = gmdate('Y-m-d\TH:i:s\Z');
        $authorName = !empty($a['authorName']) ? trim($a['authorName']) : 'မပုံ့ (Admin)';

        $insAnn = $pdo->prepare("INSERT INTO announcements (id, title, content, tag, image, createdAt, authorName) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insAnn->execute([$id, $title, $content, $tag, $image, $createdAt, $authorName]);

        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 11. DELETE_ANNOUNCEMENT
    if ($action === 'DELETE_ANNOUNCEMENT') {
        $announcementId = trim($body['announcementId'] ?? '');
        $del = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $del->execute([$announcementId]);
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 12. ADD_CATEGORY
    if ($action === 'ADD_CATEGORY') {
        $cat = $body['category'] ?? [];
        $id = trim($cat['id'] ?? '');
        $labelEn = trim($cat['labelEn'] ?? $id);
        $labelMm = trim($cat['labelMm'] ?? $labelEn);

        if (!$id && $labelEn) {
            $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', ucwords($labelEn));
        }

        if ($id && $labelEn) {
            $check = $pdo->prepare("SELECT id FROM categories WHERE LOWER(id) = ? OR LOWER(labelEn) = ? LIMIT 1");
            $check->execute([strtolower($id), strtolower($labelEn)]);
            if (!$check->fetch()) {
                $maxSort = $pdo->query("SELECT MAX(sortOrder) as m FROM categories")->fetch();
                $sort = ($maxSort ? (int)$maxSort['m'] : 0) + 1;
                $ins = $pdo->prepare("INSERT INTO categories (id, labelEn, labelMm, sortOrder) VALUES (?, ?, ?, ?)");
                $ins->execute([$id, $labelEn, $labelMm, $sort]);
            }
        }
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 13. DELETE_CATEGORY
    if ($action === 'DELETE_CATEGORY') {
        $categoryId = trim($body['categoryId'] ?? '');
        if ($categoryId && $categoryId !== 'All') {
            $del = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $del->execute([$categoryId]);
        }
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 14. UPDATE_USER_ROLE
    if ($action === 'UPDATE_USER_ROLE') {
        $targetUserId = trim($body['userId'] ?? '');
        $role = trim($body['role'] ?? '');
        if ($targetUserId === $sessionUser['id']) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot change your own admin role'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (in_array($role, ['admin', 'customer'])) {
            $upd = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $upd->execute([$role, $targetUserId]);
        }
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 15. DELETE_USER
    if ($action === 'DELETE_USER') {
        $targetUserId = trim($body['userId'] ?? '');
        if ($targetUserId === $sessionUser['id']) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete your own admin account'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $del->execute([$targetUserId]);
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 16. UPDATE_THEME
    if ($action === 'UPDATE_THEME') {
        $theme = trim($body['theme'] ?? '');
        if (in_array($theme, ['luxury-gold', 'rose-paris', 'minimal-noir'])) {
            $upd = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES ('theme', ?) ON DUPLICATE KEY UPDATE `value` = ?");
            $upd->execute([$theme, $theme]);
        }
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 17. UPLOAD_IMAGE (Admin Product & Banner Upload)
    if ($action === 'UPLOAD_IMAGE') {
        $file = $_FILES['image'] ?? ($body['image_data'] ?? null);
        $res = handleSecureImageUpload($file, 'products', 'prod');
        if ($res['ok']) {
            echo json_encode(['ok' => true, 'url' => $res['url']], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $res['error']], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 18. ADD_COUPON
    if ($action === 'ADD_COUPON') {
        $cpn = $body['coupon'] ?? [];
        $code = strtoupper(trim($cpn['code'] ?? ''));
        if (!$code) {
            http_response_code(400);
            echo json_encode(['error' => 'Coupon code is required'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $id = !empty($cpn['id']) ? trim($cpn['id']) : ('cpn-' . time());
        $type = in_array($cpn['discountType'] ?? '', ['percent', 'fixed']) ? $cpn['discountType'] : 'percent';
        $val = max(1, (int)($cpn['discountValue'] ?? 10));
        $min = max(0, (int)($cpn['minOrderAmount'] ?? 0));
        $limit = max(0, (int)($cpn['usageLimit'] ?? 0));
        $expires = !empty($cpn['expiresAt']) ? trim($cpn['expiresAt']) : null;
        $active = isset($cpn['isActive']) ? (int)$cpn['isActive'] : 1;
        $now = gmdate('Y-m-d\TH:i:s\Z');

        $ins = $pdo->prepare("INSERT INTO coupons (id, code, discountType, discountValue, minOrderAmount, usageLimit, usedCount, expiresAt, isActive, createdAt) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?) ON DUPLICATE KEY UPDATE discountType=?, discountValue=?, minOrderAmount=?, usageLimit=?, expiresAt=?, isActive=?");
        $ins->execute([$id, $code, $type, $val, $min, $limit, $expires, $active, $now, $type, $val, $min, $limit, $expires, $active]);

        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 19. DELETE_COUPON
    if ($action === 'DELETE_COUPON') {
        $cpnId = trim($body['couponId'] ?? '');
        $del = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
        $del->execute([$cpnId]);
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 20. UPDATE_SETTINGS (Admin General, Contact & Telegram Settings)
    if ($action === 'UPDATE_SETTINGS') {
        $incomingSettings = $body['settings'] ?? [];
        if (is_array($incomingSettings)) {
            $allowedKeys = [
                'shop_name', 'shop_name_mm', 'phone_1', 'phone_2', 'kpay_account',
                'telegram_bot_token', 'telegram_chat_id', 'telegram_enabled',
                'app_version', 'app_min_version', 'app_download_url'
            ];
            $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
            foreach ($incomingSettings as $k => $v) {
                if (in_array($k, $allowedKeys)) {
                    $valStr = (string)$v;
                    $stmt->execute([$k, $valStr, $valStr]);
                }
            }
        }
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 21. UPDATE_CHATBOT_CONFIG (Admin Update AI Chatbot Settings)
    if ($action === 'UPDATE_CHATBOT_CONFIG') {
        $cfg = $body['chatbotConfig'] ?? [];
        $provider = in_array($cfg['provider'] ?? '', ['mock', 'gemini', 'openai', 'groq', 'custom']) ? $cfg['provider'] : 'mock';
        $apiUrl = trim($cfg['apiUrl'] ?? '');
        $apiKey = trim($cfg['apiKey'] ?? '');
        $model = trim($cfg['model'] ?? 'gemini-1.5-flash');
        $systemPrompt = trim($cfg['systemPrompt'] ?? '');
        $botName = trim($cfg['botName'] ?? 'ပုံ့ပုံ့ AI အကူအညီ');
        $welcomeMm = trim($cfg['welcomeMessageMm'] ?? 'မင်္ဂလာပါရှင် 👋 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်။');
        $welcomeEn = trim($cfg['welcomeMessageEn'] ?? 'Hello! Welcome to Pont Pont Handbag Shop Mandalay.');
        $enabled = !empty($cfg['enabled']) ? 1 : 0;
        $updatedAt = gmdate('Y-m-d\TH:i:s\Z');

        // Check if table exists, create if not
        $pdo->exec("CREATE TABLE IF NOT EXISTS `chatbot_config` (
          `id` INT NOT NULL PRIMARY KEY DEFAULT 1,
          `provider` VARCHAR(50) NOT NULL DEFAULT 'mock',
          `apiUrl` TEXT DEFAULT NULL,
          `apiKey` TEXT DEFAULT NULL,
          `model` VARCHAR(100) NOT NULL DEFAULT 'gemini-1.5-flash',
          `systemPrompt` TEXT NOT NULL,
          `botName` VARCHAR(100) NOT NULL DEFAULT 'ပုံ့ပုံ့ AI အကူအညီ',
          `welcomeMessageMm` TEXT NOT NULL,
          `welcomeMessageEn` TEXT NOT NULL,
          `enabled` TINYINT(1) NOT NULL DEFAULT 1,
          `updatedAt` VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // If existing apiKey was not provided (empty string submitted), keep old apiKey
        if (empty($apiKey)) {
            $oldStmt = $pdo->query("SELECT apiKey FROM chatbot_config WHERE id = 1 LIMIT 1");
            $oldRow = $oldStmt->fetch();
            if ($oldRow && !empty($oldRow['apiKey'])) {
                $apiKey = $oldRow['apiKey'];
            }
        }

        $upsert = $pdo->prepare("INSERT INTO chatbot_config (id, provider, apiUrl, apiKey, model, systemPrompt, botName, welcomeMessageMm, welcomeMessageEn, enabled, updatedAt) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE provider=VALUES(provider), apiUrl=VALUES(apiUrl), apiKey=VALUES(apiKey), model=VALUES(model), systemPrompt=VALUES(systemPrompt), botName=VALUES(botName), welcomeMessageMm=VALUES(welcomeMessageMm), welcomeMessageEn=VALUES(welcomeMessageEn), enabled=VALUES(enabled), updatedAt=VALUES(updatedAt)");
        $upsert->execute([
            $provider,
            $apiUrl,
            $apiKey,
            $model,
            $systemPrompt,
            $botName,
            $welcomeMm,
            $welcomeEn,
            $enabled,
            $updatedAt
        ]);

        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 22. SAVE_PAYMENT_METHOD (Admin Create or Update Payment Method)
    if ($action === 'SAVE_PAYMENT_METHOD') {
        $pm = $body['paymentMethod'] ?? [];
        $id = !empty($pm['id']) ? trim($pm['id']) : ('pm-' . time());
        $code = trim($pm['code'] ?? 'KPay');
        $name = trim($pm['name'] ?? $code);
        $nameMm = trim($pm['nameMm'] ?? $name);
        $type = trim($pm['type'] ?? 'transfer');
        $accountName = trim($pm['accountName'] ?? '');
        $accountNumber = trim($pm['accountNumber'] ?? '');
        $instructions = trim($pm['instructions'] ?? '');
        $qrCodeUrl = !empty($pm['qrCodeUrl']) ? trim($pm['qrCodeUrl']) : null;
        $requiresSlip = isset($pm['requiresSlip']) ? ($pm['requiresSlip'] ? 1 : 0) : 1;
        $isActive = isset($pm['isActive']) ? ($pm['isActive'] ? 1 : 0) : 1;
        $sortOrder = isset($pm['sortOrder']) ? (int)$pm['sortOrder'] : 0;
        $createdAt = !empty($pm['createdAt']) ? $pm['createdAt'] : gmdate('Y-m-d\TH:i:s\Z');

        $stmt = $pdo->prepare("INSERT INTO payment_methods (id, code, name, nameMm, type, accountName, accountNumber, instructions, qrCodeUrl, requiresSlip, isActive, sortOrder, createdAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            code=VALUES(code), name=VALUES(name), nameMm=VALUES(nameMm), type=VALUES(type),
            accountName=VALUES(accountName), accountNumber=VALUES(accountNumber),
            instructions=VALUES(instructions), qrCodeUrl=VALUES(qrCodeUrl),
            requiresSlip=VALUES(requiresSlip), isActive=VALUES(isActive), sortOrder=VALUES(sortOrder)");
        $stmt->execute([
            $id, $code, $name, $nameMm, $type, $accountName, $accountNumber,
            $instructions, $qrCodeUrl, $requiresSlip, $isActive, $sortOrder, $createdAt
        ]);

        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 23. DELETE_PAYMENT_METHOD
    if ($action === 'DELETE_PAYMENT_METHOD') {
        $id = trim($body['paymentMethodId'] ?? '');
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
            $stmt->execute([$id]);
        }
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 24. TOGGLE_PAYMENT_METHOD
    if ($action === 'TOGGLE_PAYMENT_METHOD') {
        $id = trim($body['paymentMethodId'] ?? '');
        if ($id) {
            $stmt = $pdo->prepare("UPDATE payment_methods SET isActive = NOT isActive WHERE id = ?");
            $stmt->execute([$id]);
        }
        echo json_encode([
            'ok' => true,
            'db' => buildScopedResponse($pdo, $sessionUser)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 25. TEST_TELEGRAM (Send test message to verify Telegram Bot)
    if ($action === 'TEST_TELEGRAM') {
        $testOrder = [
            'id' => 'TEST-' . rand(100, 999),
            'customerName' => 'စမ်းသပ်အသုံးပြုသူ (Test User)',
            'phone' => '09-963000502',
            'city' => 'Mandalay (မန္တလေး)',
            'address' => '၇၃ လမ်း၊ မန္တလေးမြို့',
            'note' => 'Telegram Bot ချိတ်ဆက်မှု စမ်းသပ်ခြင်း ဖြစ်ပါသည်',
            'paymentMethod' => 'KPay',
            'transactionRef' => 'TEST123456',
            'couponCode' => 'PONTPONT10',
            'items' => [
                ['nameMm' => 'ပုံ့ပုံ့ စိန်ကွက်ဖော် ရွှေကြိုးချိတ် ဘေးလွယ်အိတ်', 'selectedColor' => 'Onyx Black', 'quantity' => 1, 'price' => 29500]
            ],
            'total' => 29500
        ];
        sendTelegramOrderAlert($pdo, $testOrder);
        echo json_encode(['ok' => true, 'message' => 'Telegram စမ်းသပ်အကြောင်းကြားချက် ပေးပို့ပြီးပါပြီ']);
        exit;
    }
    http_response_code(400);
    echo json_encode(['error' => 'Unknown action: ' . $action], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);

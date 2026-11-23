<?php
/**
 * Pont Pont Handbag Shop - Core REST API
 * Compatible with Next.js API, Flutter Mobile App, and Bluehost Web Hosting
 */

// Enable error reporting for debugging if needed, but output clean JSON
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);

// Setup CORS and JSON headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, Cookie');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$SESSION_COOKIE_NAME = 'pontpont_session';

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

// Helper: Verify session token
function verifySessionToken($token) {
    if (!$token || !is_string($token)) return null;
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    list($userId, $timestamp, $sig) = $parts;
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

    // Fallback: X-User-Id header (for internal/testing or direct API client)
    if (!$userId && !empty($_SERVER['HTTP_X_USER_ID'])) {
        $userId = trim($_SERVER['HTTP_X_USER_ID']);
    }

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

// Helper: Build complete scoped database response
function buildScopedResponse($pdo, $sessionUser) {
    // 1. Categories
    $catStmt = $pdo->query("SELECT id, labelEn FROM categories ORDER BY sortOrder ASC");
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

    // 6. Theme
    $themeStmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'theme' LIMIT 1");
    $themeStmt->execute();
    $themeRow = $themeStmt->fetch();
    $theme = $themeRow ? $themeRow['value'] : 'luxury-gold';

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
        'currentUser'   => sanitizeUser($sessionUser),
        'theme'         => $theme,
        'chatbotConfig' => $chatbotConfig,
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

        // Rate limiting check using session
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $rateKey = 'login_rate_' . md5($clientIp);
        $attempts = $_SESSION[$rateKey] ?? ['count' => 0, 'resetAt' => time() + 300];

        if (time() < $attempts['resetAt'] && $attempts['count'] >= 10) {
            http_response_code(429);
            echo json_encode(['error' => 'စကားဝှက် မှားယွင်းမှု များနေပါသဖြင့် ၅ မိနစ်ခန့် စောင့်ဆိုင်းပြီးမှ ပြန်လည်ကြိုးစားပါ။'], JSON_UNESCAPED_UNICODE);
            exit;
        }

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
            $currentCount = (time() < $attempts['resetAt']) ? $attempts['count'] + 1 : 1;
            $_SESSION[$rateKey] = [
                'count' => $currentCount,
                'resetAt' => (time() < $attempts['resetAt']) ? $attempts['resetAt'] : time() + 300
            ];

            http_response_code(401);
            echo json_encode(['error' => 'အီးမေးလ် သို့မဟုတ် စကားဝှက် မှားယွင်းနေပါသည်။ အကောင့်မရှိသေးပါက Sign Up လုပ်ပေးပါ။'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        unset($_SESSION[$rateKey]);

        $token = createSessionToken($foundUser['id']);
        setcookie($SESSION_COOKIE_NAME, $token, [
            'expires'  => time() + (86400 * 7),
            'path'     => '/',
            'httponly' => true,
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

        $discount = (!empty($incoming['discount']) && (int)$incoming['discount'] > 0)
            ? (int)round($calculatedSubtotal * 0.1)
            : 0;

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
        $calculatedTotal = $calculatedSubtotal - $discount + $deliveryFee;

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
            ? mb_substr(trim($incoming['transactionRef']), 0, 20)
            : null;
        $userEmail = $sessionUser ? $sessionUser['email'] : ($incoming['userEmail'] ?? null);

        $insOrder = $pdo->prepare("INSERT INTO orders (id, customerName, phone, city, address, note, paymentMethod, transactionRef, itemsJson, subtotal, discount, deliveryFee, total, status, createdAt, userEmail) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insOrder->execute([
            $orderId,
            $customerName,
            $phone,
            $cityName,
            $address,
            $note,
            $paymentMethod,
            $transactionRef,
            json_encode($verifiedItems, JSON_UNESCAPED_UNICODE),
            $calculatedSubtotal,
            $discount,
            $deliveryFee,
            $calculatedTotal,
            'Pending',
            $createdAt,
            $userEmail
        ]);

        $safeOrder = [
            'id'             => $orderId,
            'customerName'   => $customerName,
            'phone'          => $phone,
            'city'           => $cityName,
            'address'        => $address,
            'note'           => $note,
            'paymentMethod'  => $paymentMethod,
            'transactionRef' => $transactionRef,
            'items'          => $verifiedItems,
            'subtotal'       => $calculatedSubtotal,
            'discount'       => $discount,
            'deliveryFee'    => $deliveryFee,
            'total'          => $calculatedTotal,
            'status'         => 'Pending',
            'createdAt'      => $createdAt,
            'userEmail'      => $userEmail,
        ];

        echo json_encode([
            'ok' => true,
            'order' => $safeOrder,
            'db' => buildScopedResponse($pdo, $sessionUser)
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
            $geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
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
            $res = makeHttpRequest($geminiUrl, 'POST', ['Content-Type' => 'application/json'], $payload, 20);
            if ($res['status'] >= 200 && $res['status'] < 300 && $res['body']) {
                $data = json_decode($res['body'], true);
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($reply) {
                    echo json_encode(['ok' => true, 'reply' => $reply], JSON_UNESCAPED_UNICODE);
                    exit;
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

        // Fallback: Signal frontend to use intelligent Burmese knowledgebase
        echo json_encode(['ok' => true, 'reply' => null], JSON_UNESCAPED_UNICODE);
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
                        foreach ($data['models'] as $m) {
                            $methods = $m['supportedGenerationMethods'] ?? [];
                            $name = $m['name'] ?? '';
                            if (in_array('generateContent', $methods) || strpos($name, 'gemini') !== false) {
                                $cleanName = str_replace('models/', '', $name);
                                if ($cleanName) $models[] = $cleanName;
                            }
                        }
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

        if ($id) {
            $check = $pdo->prepare("SELECT id FROM categories WHERE LOWER(id) = ? OR LOWER(labelEn) = ? LIMIT 1");
            $check->execute([strtolower($id), strtolower($labelEn)]);
            if (!$check->fetch()) {
                $maxSort = $pdo->query("SELECT MAX(sortOrder) as m FROM categories")->fetch();
                $sort = ($maxSort ? (int)$maxSort['m'] : 0) + 1;
                $ins = $pdo->prepare("INSERT INTO categories (id, labelEn, sortOrder) VALUES (?, ?, ?)");
                $ins->execute([$id, $labelEn, $sort]);
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

    // 17. UPDATE_CHATBOT_CONFIG (Admin Update AI Chatbot Settings)
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

    http_response_code(400);
    echo json_encode(['error' => 'Unknown action: ' . $action], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);

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

    return [
        'products'      => $products,
        'categories'    => $categories,
        'announcements' => $announcements,
        'orders'        => $orders,
        'users'         => $users,
        'usersCount'    => $usersCount,
        'currentUser'   => sanitizeUser($sessionUser),
        'theme'         => $theme,
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

    http_response_code(400);
    echo json_encode(['error' => 'Unknown action: ' . $action], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);

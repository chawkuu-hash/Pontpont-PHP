<?php
/**
 * Pont Pont Handbag Shop (ပုံ့ပုံ့ အိတ်ဆိုင် - မန္တလေး)
 * AI Assistant & Facebook Messenger Integration Helper
 */

if (!function_exists('makeHttpRequest')) {
    /**
     * Resilient HTTP requests using cURL or stream context
     */
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
}

/**
 * Get Base URL of Pont Pont Handbag Shop
 */
if (!function_exists('getShopBaseUrl')) {
    function getShopBaseUrl($pdo = null) {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            return $scheme . '://' . $_SERVER['HTTP_HOST'];
        }
        return 'https://pontpontbagshop.com';
    }
}

/**
 * Build compact live MySQL shop context (products, coupons, payment methods) for AI Chatbot
 */
if (!function_exists('buildLiveShopContextForAI')) {
    function buildLiveShopContextForAI($pdo) {
        $lines = ["[LIVE SHOP DATABASE - ALWAYS USE THESE EXACT PRICES, STOCK & DETAILS]"];

        // 1. Live Products
        try {
            $stmt = $pdo->query("SELECT id, nameEn, nameMm, category, categoryMm, price, originalPrice, wholesalePrice, colorsJson, stock, material, dimensions, image FROM products ORDER BY sortOrder ASC");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($products)) {
                $lines[] = "\nProducts (" . count($products) . " items):";
                foreach ($products as $p) {
                    $colorsArr = json_decode($p['colorsJson'] ?: '[]', true);
                    $colorNames = [];
                    if (is_array($colorsArr)) {
                        foreach ($colorsArr as $c) {
                            if (!empty($c['name'])) $colorNames[] = $c['name'];
                        }
                    }
                    $colorsStr = !empty($colorNames) ? implode(', ', $colorNames) : 'Standard';
                    $stockNum = (int)$p['stock'];
                    $stockStr = $stockNum > 0 ? "{$stockNum} available" : "OUT OF STOCK";
                    $origStr = !empty($p['originalPrice']) ? " (Original: " . number_format((int)$p['originalPrice']) . " Ks)" : "";
                    $wsStr = !empty($p['wholesalePrice']) ? " | Wholesale (5+ pcs): " . number_format((int)$p['wholesalePrice']) . " Ks" : "";
                    $lines[] = "- [{$p['id']}] {$p['nameEn']} ({$p['nameMm']}) | Category: {$p['category']} ({$p['categoryMm']}) | Retail: " . number_format((int)$p['price']) . " Ks{$origStr}{$wsStr} | Stock: {$stockStr} | Colors: {$colorsStr} | Material: {$p['material']} | Size: {$p['dimensions']} | Image: {$p['image']}";
                }
            }
        } catch (\Throwable $e) {}

        // 2. Active Coupons
        try {
            $cpnStmt = $pdo->query("SELECT code, discountType, discountValue, minOrderAmount FROM coupons WHERE isActive = 1 ORDER BY createdAt DESC");
            $coupons = $cpnStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($coupons)) {
                $cpnParts = [];
                foreach ($coupons as $c) {
                    $val = ($c['discountType'] === 'percent') ? "{$c['discountValue']}% OFF" : number_format((int)$c['discountValue']) . " Ks OFF";
                    $min = ((int)$c['minOrderAmount'] > 0) ? " (min order " . number_format((int)$c['minOrderAmount']) . " Ks)" : "";
                    $cpnParts[] = "{$c['code']}: {$val}{$min}";
                }
                $lines[] = "\nActive Coupons: " . implode(' | ', $cpnParts);
            }
        } catch (\Throwable $e) {}

        // 3. Active Payment Methods
        try {
            $pmStmt = $pdo->query("SELECT name, nameMm, accountName, accountNumber FROM payment_methods WHERE isActive = 1 ORDER BY sortOrder ASC");
            $pms = $pmStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($pms)) {
                $pmParts = [];
                foreach ($pms as $pm) {
                    $acc = !empty($pm['accountNumber']) ? " ({$pm['accountNumber']}" . (!empty($pm['accountName']) ? " - {$pm['accountName']}" : "") . ")" : "";
                    $pmParts[] = "{$pm['name']}{$acc}";
                }
                $lines[] = "\nPayment Methods: " . implode(' | ', $pmParts);
            }
        } catch (\Throwable $e) {}

        return implode("\n", $lines);
    }
}

/**
 * Intelligent Myanmar Rule-Based Chatbot Engine (Tailored to Pont Pont Handbag Shop)
 */
if (!function_exists('generateIntelligentBurmeseReply')) {
    function generateIntelligentBurmeseReply($message, $pdo = null) {
        $q = mb_strtolower(trim($message), 'UTF-8');

        // 1. Delivery, Bus Gates & Cities
        if (preg_match('/(ပို့|ကားဂိတ်|ဂိတ်|ကားလိုင်း|ပို့ခ|deli|delivery|ဘယ်မြို့|နယ်|ရန်ကုန်|တောင်ကြီး|နေပြည်တော်|မုံရွာ|ပဲခူး|စစ်ကိုင်း)/u', $q)) {
            return "မင်္ဂလာပါရှင့် 🙏 Pont Pont အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး (မန္တလေး) မှ ကြိုဆိုပါတယ်ရှင့်။\n\n🚚 ပို့ဆောင်ရေး ဝန်ဆောင်မှု နှုန်းထားများ:\n• မန္တလေးမြို့တွင်း: ၂,၅၀၀ ကျပ် (အိမ်အရောက်ငွေချေစနစ် Cash on Delivery ရပါသည်)\n• ရန်ကုန်မြို့တွင်း: ၃,၅၀၀ ကျပ်\n• နေပြည်တော်: ၃,၅၀၀ ကျပ်\n• နယ်ဝေးမြို့နယ်ပေါင်းစုံ: ၄,၀၀၀ ကျပ် (ခရီးသည်တင် ကားဂိတ်/ကားလိုင်းများမှတစ်ဆင့် ပို့ဆောင်ပေးပါသည်)\n\n⚡ အမှာစာများကို ၁-၃ ရက်အတွင်း အမြန်ဆုံး ပို့ဆောင်ပေးပါသည်ရှင်။ ဘယ်မြို့ကို ပို့ဆောင်ပေးရမလဲရှင့်။";
        }

        // 2. Branches, Address & Location
        if (preg_match('/(လိပ်စာ|ဆိုင်|ဘယ်မှာ|ပင်မ|address|branch|လမ်း|နေရာ|တည်နေရာ)/u', $q)) {
            return "🏪 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေးမြို့) ဆိုင်ခွဲ (၃) ဆိုင် လိပ်စာများ:\n\n• ပင်မဆိုင်: ၆၅ လမ်း၊ ၁၀၇×၁၀၈ လမ်းကြား၊ မန္တလေးမြို့\n• ဆိုင်ခွဲ (၂): ၇၃ လမ်း၊ ၃၆×၃၇ လမ်းကြား၊ မန္တလေးမြို့\n• ဆိုင်ခွဲ (၃): ၇၈ လမ်း (ရန်ကုန်-မန္တလေးလမ်း)၊ တံခွန်တိုင်ဈေးအနီး\n\n⏰ ဆိုင်ဖွင့်ချိန်: နေ့စဉ် မနက် 8:00 AM မှ ည 7:00 PM အထိ ဆိုင်သို့ လာရောက်လေ့လာ ဝယ်ယူနိုင်ပါသည်ရှင်။";
        }

        // 3. Phone & Viber Contact
        if (preg_match('/(ဖုန်း|ဆက်သွယ်|phone|call|viber|contact)/u', $q)) {
            return "📞 ဆက်သွယ်ရန် ဖုန်းနံပါတ်များ:\n• 09-963000502 (ပင်မဖုန်း / Viber)\n• 09-750072752\n• 09-404448881\n\nနေ့စဉ် မနက် 8:00 မှ ည 7:00 အထိ စုံစမ်းမေးမြန်း အော်ဒါတင်နိုင်ပါတယ်ရှင်။";
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
        if (preg_match('/(အိတ်|လက်ဆွဲ|ကျောပိုး|ဘေးလွယ်|ပေါင်မုန့်|ဒလက်|ဒီဇိုင်း|bag|price|ဈေး|ပုံ|photo|pic)/u', $q)) {
            $text = "👜 ပုံ့ပုံ့ အိတ်ဆိုင်၏ လူကြိုက်အများဆုံး အိတ်ဒီဇိုင်းများနှင့် ဓာတ်ပုံများ ဖြစ်ပါသည်ရှင့်:\n";
            if ($pdo) {
                try {
                    $pStmt = $pdo->query("SELECT id, nameMm, nameEn, price, image FROM products ORDER BY sortOrder ASC LIMIT 3");
                    $prods = $pStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($prods as $p) {
                        $text .= "\n• **{$p['nameMm']}** ({$p['nameEn']}) - " . number_format($p['price']) . " Ks\n![{$p['nameMm']}]({$p['image']})\n";
                    }
                } catch (\Throwable $e) {}
            }
            $text .= "\nအိတ်တစ်လုံးချင်းစီ၏ အသေးစိတ်၊ အရောင်နှင့် လက်ကားဈေးနှုန်းများကို အောက်ဖော်ပြပါ Card များတွင် 'ကြည့်မည်' ကို နှိပ်၍ တိုက်ရိုက် Order တင်နိုင်ပါသည်ရှင်။";
            return $text;
        }

        // 7. General Services & Greetings
        if (preg_match('/(ဝန်ဆောင်မှု|service|မင်္ဂလာ|hello|hi|မေး|စုံစမ်း)/u', $q)) {
            return "မင်္ဂလာပါရှင့် 🙏 \"ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး\" (မန္တလေး) မှ ကြိုဆိုပါတယ်ရှင့်။\n\nကျွန်မတို့ဆိုင်မှ ပေးဆောင်သော ဝန်ဆောင်မှုများမှာ -\n၁။ ခေတ်မီ အရည်အသွေးမြင့် အိတ်အမျိုးမျိုး လက်လီ/လက်ကား ရောင်းချခြင်း\n၂။ မန္တလေးမြို့တွင်း အိမ်အရောက် ငွေချေ (COD) စနစ်\n၃။ နယ်မြို့များအားလုံးသို့ ကားဂိတ်/ပို့ဆောင်ရေးဖြင့် အမြန်ပို့စနစ်\n၄။ ငွေလွှဲ % ပေးရန်မလိုဘဲ KPay/WavePay ဖြင့် လွယ်ကူစွာ ငွေချေနိုင်ခြင်း\n\nအိတ်ဒီဇိုင်းများ၊ ဈေးနှုန်း၊ ဆိုင်လိပ်စာနှင့် ပို့ခများကို စိတ်ကြိုက် မေးမြန်းနိုင်ပါသည်ရှင်။";
        }

        // Default Fallback
        return "မင်္ဂလာပါရှင့် 🙏 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်ရှင့်။\nဆိုင်လိပ်စာ၊ ဖုန်းနံပါတ်၊ ကားဂိတ်ပို့ဆောင်ခ၊ ငွေပေးချေမှုနှင့် အိတ်ဒီဇိုင်း/ဈေးနှုန်းများကို အသေးစိတ် မေးမြန်းနိုင်ပါသည်ရှင်။ (ဖုန်း - 09-963000502)";
    }
}

/**
 * Unified AI Chatbot Query Processor
 * Used by both Web Live Chat (api/shop.php) and Facebook Messenger (api/webhook_facebook.php)
 *
 * @param PDO $pdo
 * @param string $message
 * @param array $history Optional history turn list: [['role' => 'user'|'bot', 'text' => '...']]
 * @return array ['ok' => bool, 'reply' => string, 'provider' => string, 'model' => string, 'products' => array, 'fallback' => bool]
 */
if (!function_exists('processAiChatbotQuery')) {
    function processAiChatbotQuery($pdo, $message, $history = []) {
        $message = trim((string)$message);
        if ($message === '') {
            return ['ok' => false, 'reply' => 'မေးခွန်း မတွေ့ရှိပါ။', 'products' => []];
        }

        // 1. Get bot config from database
        $cfg = null;
        try {
            $cfgStmt = $pdo->query("SELECT * FROM chatbot_config WHERE id = 1 LIMIT 1");
            $cfg = $cfgStmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {}

        if (!$cfg || empty($cfg['enabled'])) {
            return [
                'ok' => false,
                'disabled' => true,
                'reply' => 'Chatbot ဝန်ဆောင်မှု ခေတ္တပိတ်ထားပါသည်ရှင်။',
                'products' => []
            ];
        }

        $provider = $cfg['provider'] ?? 'mock';
        $apiKey = trim($cfg['apiKey'] ?? '');
        $apiUrl = trim($cfg['apiUrl'] ?? '');
        $model = trim($cfg['model'] ?? 'gemini-1.5-flash');
        $systemPrompt = trim($cfg['systemPrompt'] ?? '');
        $liveShopContext = buildLiveShopContextForAI($pdo);

        // 2. Query matching products from MySQL database for bag/picture queries
        $matchingProducts = [];
        if (preg_match('/(အိတ်|ပုံ|photo|pic|image|ဒီဇိုင်း|design|တုတ်|tote|sling|လွယ်အိတ်|ကျောပိုး|backpack|wallet|ပိုက်ဆံအိတ်|clutch|ပစ္စည်း|ကြည့်|ရှာ|ဈေး|price|ရောင်းအား|best|အသစ်)/iu', $message)) {
            try {
                $catWhere = '';
                if (preg_match('/(tote|တုတ်|ရုံးတက်|လက်ဆွဲ)/iu', $message)) {
                    $catWhere = "WHERE category LIKE '%Tote%' OR categoryMm LIKE '%လက်ဆွဲ%'";
                } elseif (preg_match('/(sling|crossbody|ဘေးလွယ်)/iu', $message)) {
                    $catWhere = "WHERE category LIKE '%Crossbody%' OR categoryMm LIKE '%ဘေးလွယ်%'";
                } elseif (preg_match('/(shoulder|ပုခုံး)/iu', $message)) {
                    $catWhere = "WHERE category LIKE '%Shoulder%' OR categoryMm LIKE '%ပုခုံး%'";
                } elseif (preg_match('/(backpack|ကျောပိုး)/iu', $message)) {
                    $catWhere = "WHERE category LIKE '%Backpack%' OR categoryMm LIKE '%ကျောပိုး%'";
                } elseif (preg_match('/(wallet|clutch|ပိုက်ဆံအိတ်|ပွဲတက်)/iu', $message)) {
                    $catWhere = "WHERE category LIKE '%Clutch%' OR category LIKE '%Wallet%' OR categoryMm LIKE '%ပိုက်ဆံအိတ်%'";
                }
                $mStmt = $pdo->query("SELECT id, nameEn, nameMm, category, categoryMm, price, originalPrice, wholesalePrice, image, badge FROM products {$catWhere} ORDER BY sortOrder ASC LIMIT 6");
                $matchingProducts = $mStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Throwable $t) {}
        }

        $imageInstructions = "\n\nPRODUCT IMAGES INSTRUCTION:\nWhen the customer asks to see bag pictures, photos, or designs (e.g. 'အိတ်ပုံ', 'ဒီဇိုင်းတွေ', 'ပုံပြပါ'), you MUST display the matching bag pictures using Markdown syntax: ![Bag Name](image_url)\nThe chat UI will render the images directly to the user.";
        $fullSystemPrompt = ($systemPrompt !== '' ? $systemPrompt . "\n\n" : '') . $liveShopContext . $imageInstructions;

        // Sanitize chat history (last 8 turns)
        $cleanHistory = [];
        if (is_array($history)) {
            $rawHistory = array_slice($history, -8);
            foreach ($rawHistory as $h) {
                if (!is_array($h)) continue;
                $hText = trim((string)($h['text'] ?? ''));
                if ($hText === '') continue;
                $hRole = (($h['role'] ?? '') === 'user') ? 'user' : 'bot';
                $cleanHistory[] = ['role' => $hRole, 'text' => mb_substr($hText, 0, 1500)];
            }
        }

        $lastAiError = null;

        // Gemini AI Provider
        if ($provider === 'gemini' && $apiKey) {
            $modelsToTry = array_unique([$model, 'gemini-flash-lite-latest', 'gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-flash-latest']);
            $geminiContents = [];
            foreach ($cleanHistory as $h) {
                $geminiContents[] = [
                    'role' => $h['role'] === 'user' ? 'user' : 'model',
                    'parts' => [['text' => $h['text']]]
                ];
            }
            $geminiContents[] = [
                'role' => 'user',
                'parts' => [['text' => $message]]
            ];

            $payload = [
                'systemInstruction' => [
                    'parts' => [['text' => $fullSystemPrompt]]
                ],
                'contents' => $geminiContents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 4096
                ]
            ];

            foreach ($modelsToTry as $tryModel) {
                if (empty($tryModel)) continue;
                $geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$tryModel}:generateContent?key={$apiKey}";
                $res = makeHttpRequest($geminiUrl, 'POST', ['Content-Type' => 'application/json'], $payload, 20);
                if ($res['status'] >= 200 && $res['status'] < 300 && $res['body']) {
                    $data = json_decode($res['body'], true);
                    $parts = $data['candidates'][0]['content']['parts'] ?? [];
                    $replyParts = [];
                    foreach ($parts as $part) {
                        if (empty($part['thought']) && isset($part['text'])) {
                            $replyParts[] = $part['text'];
                        }
                    }
                    $reply = !empty($replyParts) ? implode('', $replyParts) : null;
                    if ($reply) {
                        if ($tryModel !== $model) {
                            try {
                                $upd = $pdo->prepare("UPDATE chatbot_config SET model = ? WHERE id = 1");
                                $upd->execute([$tryModel]);
                            } catch (\Throwable $t) {}
                        }
                        return [
                            'ok' => true,
                            'reply' => $reply,
                            'model' => $tryModel,
                            'provider' => 'gemini',
                            'products' => $matchingProducts,
                            'fallback' => false
                        ];
                    }
                } else {
                    $errDec = json_decode($res['body'], true);
                    $errMsg = $errDec['error']['message'] ?? ($res['body'] ?: 'Connection failed');
                    $lastAiError = "Google Gemini API Error (HTTP {$res['status']}): {$errMsg}";
                }
            }
        }

        // Amazon Bedrock Provider (Bearer Token / Converse API)
        if ($provider === 'bedrock' && $apiKey) {
            $bedrockRegion = !empty($cfg['region']) ? $cfg['region'] : 'us-east-1';
            $bedrockModel = !empty($model) && $model !== 'built-in-rules' ? $model : 'amazon.nova-lite-v1:0';
            $endpoint = "https://bedrock-runtime.{$bedrockRegion}.amazonaws.com/model/{$bedrockModel}/converse";

            $bedrockMessages = [];
            foreach ($cleanHistory as $h) {
                $bRole = ($h['role'] === 'user') ? 'user' : 'assistant';
                $bedrockMessages[] = [
                    'role' => $bRole,
                    'content' => [['text' => $h['text']]]
                ];
            }
            $bedrockMessages[] = [
                'role' => 'user',
                'content' => [['text' => $message]]
            ];

            $payload = [
                'system' => [
                    ['text' => $fullSystemPrompt]
                ],
                'messages' => $bedrockMessages,
                'inferenceConfig' => [
                    'maxTokens' => 2048,
                    'temperature' => 0.7
                ]
            ];

            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$apiKey}"
            ];

            $res = makeHttpRequest($endpoint, 'POST', $headers, $payload, 25);
            if ($res['status'] >= 200 && $res['status'] < 300 && $res['body']) {
                $data = json_decode($res['body'], true);
                $reply = $data['output']['message']['content'][0]['text'] ?? null;
                if ($reply) {
                    return [
                        'ok' => true,
                        'reply' => $reply,
                        'model' => $bedrockModel,
                        'provider' => 'bedrock',
                        'products' => $matchingProducts,
                        'fallback' => false
                    ];
                }
            } else {
                $bodyDec = json_decode($res['body'], true);
                $msg = $bodyDec['message'] ?? $bodyDec['Message'] ?? ($res['body'] ?: 'Request failed');
                $lastAiError = "Amazon Bedrock Error (HTTP {$res['status']}): {$msg}";
            }
        }

        // OpenAI or Groq AI Provider
        if (($provider === 'openai' || $provider === 'groq') && $apiKey) {
            $endpoint = ($provider === 'groq')
                ? 'https://api.groq.com/openai/v1/chat/completions'
                : 'https://api.openai.com/v1/chat/completions';

            $defaultModel = ($provider === 'groq') ? 'llama-3.3-70b-versatile' : 'gpt-4o-mini';
            $openAiMessages = [['role' => 'system', 'content' => $fullSystemPrompt]];
            foreach ($cleanHistory as $h) {
                $openAiMessages[] = ['role' => $h['role'] === 'user' ? 'user' : 'assistant', 'content' => $h['text']];
            }
            $openAiMessages[] = ['role' => 'user', 'content' => $message];

            $payload = [
                'model' => $model ?: $defaultModel,
                'messages' => $openAiMessages,
                'temperature' => 0.7,
                'max_tokens' => 2048
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
                    return [
                        'ok' => true,
                        'reply' => $reply,
                        'model' => ($model ?: $defaultModel),
                        'provider' => $provider,
                        'products' => $matchingProducts,
                        'fallback' => false
                    ];
                }
            } else {
                $bodyDec = json_decode($res['body'], true);
                $msg = $bodyDec['error']['message'] ?? ($res['body'] ?: 'Request failed');
                $lastAiError = "{$provider} Error (HTTP {$res['status']}): {$msg}";
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

            $customMessages = [['role' => 'system', 'content' => $fullSystemPrompt]];
            foreach ($cleanHistory as $h) {
                $customMessages[] = ['role' => $h['role'] === 'user' ? 'user' : 'assistant', 'content' => $h['text']];
            }
            $customMessages[] = ['role' => 'user', 'content' => $message];

            $payload = [
                'model' => $model ?: 'gpt-3.5-turbo',
                'messages' => $customMessages,
                'temperature' => 0.7,
                'max_tokens' => 2048
            ];
            $res = makeHttpRequest($endpoint, 'POST', $headers, $payload, 25);
            if ($res['status'] >= 200 && $res['status'] < 300 && $res['body']) {
                $data = json_decode($res['body'], true);
                $reply = $data['choices'][0]['message']['content'] ?? null;
                if ($reply) {
                    return [
                        'ok' => true,
                        'reply' => $reply,
                        'model' => ($model ?: 'custom'),
                        'provider' => 'custom',
                        'products' => $matchingProducts,
                        'fallback' => false
                    ];
                }
            } else {
                $bodyDec = json_decode($res['body'], true);
                $msg = $bodyDec['error']['message'] ?? ($res['body'] ?: 'Request failed');
                $lastAiError = "Custom API Error (HTTP {$res['status']}): {$msg}";
            }
        }

        // Intelligent Myanmar Rule-based Knowledge Engine fallback
        $reply = generateIntelligentBurmeseReply($message, $pdo);
        $result = [
            'ok' => true,
            'reply' => $reply,
            'fallback' => true,
            'provider' => 'mock',
            'model' => 'built-in-rules',
            'products' => $matchingProducts
        ];
        if (!empty($lastAiError)) {
            $result['aiError'] = $lastAiError;
        }
        return $result;
    }
}

/**
 * --------------------------------------------------------------------------
 * META FACEBOOK MESSENGER GRAPH API HELPERS
 * --------------------------------------------------------------------------
 */

/**
 * Send Facebook Messenger Message Payload via Meta Graph API Send API
 *
 * @param string $recipientId User PSID (Page-Scoped ID)
 * @param array $messagePayload ['text' => '...'] or ['attachment' => [...]]
 * @param string $pageAccessToken
 * @return array ['ok' => bool, 'status' => int, 'body' => mixed]
 */
if (!function_exists('sendFacebookMessage')) {
    function sendFacebookMessage($recipientId, $messagePayload, $pageAccessToken) {
        $endpoint = 'https://graph.facebook.com/v19.0/me/messages?access_token=' . urlencode($pageAccessToken);
        $payload = [
            'recipient' => ['id' => (string)$recipientId],
            'message'   => $messagePayload,
            'messaging_type' => 'RESPONSE'
        ];

        $res = makeHttpRequest($endpoint, 'POST', ['Content-Type' => 'application/json'], $payload, 15);
        $isOk = ($res['status'] >= 200 && $res['status'] < 300);
        return [
            'ok'     => $isOk,
            'status' => $res['status'],
            'body'   => json_decode($res['body'] ?? '', true) ?: $res['body']
        ];
    }
}

/**
 * Send Facebook Messenger Simple Text Message
 */
if (!function_exists('sendFacebookTextMessage')) {
    function sendFacebookTextMessage($recipientId, $text, $pageAccessToken) {
        return sendFacebookMessage($recipientId, ['text' => $text], $pageAccessToken);
    }
}

/**
 * Send Facebook Messenger Sender Action (typing_on, typing_off, mark_seen)
 */
if (!function_exists('sendFacebookSenderAction')) {
    function sendFacebookSenderAction($recipientId, $action, $pageAccessToken) {
        $endpoint = 'https://graph.facebook.com/v19.0/me/messages?access_token=' . urlencode($pageAccessToken);
        $payload = [
            'recipient'     => ['id' => (string)$recipientId],
            'sender_action' => $action
        ];
        return makeHttpRequest($endpoint, 'POST', ['Content-Type' => 'application/json'], $payload, 10);
    }
}

/**
 * Build Facebook Messenger Generic Template Carousel with Live Product Images & Shop Link
 *
 * @param array $products
 * @param string $shopBaseUrl
 * @return array|null
 */
if (!function_exists('buildFacebookProductCarousel')) {
    function buildFacebookProductCarousel($products, $shopBaseUrl = 'https://pontpontbagshop.com') {
        if (empty($products) || !is_array($products)) {
            return null;
        }

        $shopBaseUrl = rtrim($shopBaseUrl, '/');
        $elements = [];

        // Meta Generic Template accepts up to 10 elements
        $items = array_slice($products, 0, 10);
        foreach ($items as $p) {
            $name = trim($p['nameMm'] ?: $p['nameEn'] ?: 'ပုံ့ပုံ့ အိတ်');
            // Title max 80 chars
            $title = mb_substr($name, 0, 75);

            // Price & details subtitle max 80 chars
            $priceStr = number_format((int)$p['price']) . ' Ks';
            $catStr = !empty($p['categoryMm']) ? " ({$p['categoryMm']})" : "";
            $wsStr = !empty($p['wholesalePrice']) ? " | လက်ကား: " . number_format((int)$p['wholesalePrice']) . " Ks" : "";
            $subtitle = mb_substr("ဈေးနှုန်း: {$priceStr}{$catStr}{$wsStr}", 0, 75);

            // Format full image URL
            $img = trim((string)($p['image'] ?? ''));
            if ($img !== '') {
                if (!preg_match('#^https?://#i', $img)) {
                    $img = $shopBaseUrl . '/' . ltrim($img, '/');
                }
            } else {
                $img = $shopBaseUrl . '/assets/images/pontpont-logo.jpg';
            }

            // Web URL to view bag
            $prodId = urlencode((string)($p['id'] ?? ''));
            $viewUrl = $shopBaseUrl . '/index.php?product=' . $prodId;

            $element = [
                'title'     => $title,
                'subtitle'  => $subtitle,
                'image_url' => $img,
                'buttons'   => [
                    [
                        'type'  => 'web_url',
                        'url'   => $viewUrl,
                        'title' => '🛍️ အိတ်ကြည့်မည်'
                    ],
                    [
                        'type'  => 'web_url',
                        'url'   => $shopBaseUrl . '/index.php',
                        'title' => '🏪 ဆိုင်သို့ သွားမည်'
                    ]
                ]
            ];

            $elements[] = $element;
        }

        if (empty($elements)) {
            return null;
        }

        return [
            'attachment' => [
                'type'    => 'template',
                'payload' => [
                    'template_type' => 'generic',
                    'elements'      => $elements
                ]
            ]
        ];
    }
}

/**
 * Verify Facebook Page Access Token via Meta Graph API
 *
 * @param string $pageAccessToken
 * @return array ['ok' => bool, 'page' => array|null, 'error' => string|null]
 */
if (!function_exists('verifyFacebookPageAccessToken')) {
    function verifyFacebookPageAccessToken($pageAccessToken) {
        $pageAccessToken = trim($pageAccessToken);
        if (!$pageAccessToken) {
            return ['ok' => false, 'error' => 'Page Access Token မထည့်ရသေးပါ'];
        }

        $endpoint = 'https://graph.facebook.com/v19.0/me?fields=id,name,category,link&access_token=' . urlencode($pageAccessToken);
        $res = makeHttpRequest($endpoint, 'GET', [], null, 15);

        if ($res['status'] === 200 && $res['body']) {
            $data = json_decode($res['body'], true);
            if (!empty($data['id'])) {
                return ['ok' => true, 'page' => $data];
            }
        }

        $errDec = json_decode($res['body'] ?? '', true);
        $errMsg = $errDec['error']['message'] ?? ($res['body'] ?: 'Meta Graph API သို့ ချိတ်ဆက်၍ မရပါ');
        return ['ok' => false, 'error' => "HTTP {$res['status']}: {$errMsg}"];
    }
}

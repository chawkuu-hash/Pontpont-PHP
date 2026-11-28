<?php
/**
 * Pont Pont Handbag Shop (ပုံ့ပုံ့ အိတ်ဆိုင် - မန္တလေး)
 * Meta Facebook Messenger Webhook Endpoint
 * 
 * Handles:
 * 1. Webhook Verification (GET hub.mode, hub.verify_token, hub.challenge)
 * 2. Real-time Incoming Customer Messages (POST messaging events)
 * 3. AI Live Chatbot Auto-Response & MySQL Bag Photos Carousel
 */

// Include database & AI helpers
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/ai_helper.php';

// Initialize PDO database connection
$pdo = null;
try {
    $pdo = getDb();
} catch (\Throwable $e) {
    error_log("Facebook Webhook DB connection error: " . $e->getMessage());
}

// Set standard headers
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex, nofollow');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// --------------------------------------------------------------------------
// 1. GET: META WEBHOOK VERIFICATION HANDSHAKE
// --------------------------------------------------------------------------
if ($method === 'GET') {
    // Meta sends hub.mode, hub.verify_token, and hub.challenge
    // PHP transforms dots to underscores in $_GET keys
    $mode        = $_GET['hub_mode'] ?? ($_GET['hub.mode'] ?? '');
    $verifyToken = $_GET['hub_verify_token'] ?? ($_GET['hub.verify_token'] ?? '');
    $challenge   = $_GET['hub_challenge'] ?? ($_GET['hub.challenge'] ?? '');

    // If accessed directly without verification params, show friendly health check
    if (empty($mode) && empty($verifyToken)) {
        header('Content-Type: text/html; charset=utf-8');
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Pont Pont Facebook Webhook</title><style>body{font-family:system-ui,sans-serif;padding:40px;background:#f8fafc;color:#1e293b;max-width:600px;margin:auto;line-height:1.6;}h1{color:#059669;font-size:22px;}code{background:#e2e8f0;padding:2px 6px;border-radius:4px;font-size:13px;}.card{background:#fff;padding:24px;border-radius:16px;border:1px solid #cbd5e1;box-shadow:0 4px 6px -1px rgb(0 0 0 / 0.1);}</style></head><body><div class='card'><h1>🤖 Pont Pont Facebook Messenger Webhook</h1><p>ဤ Webhook Endpoint သည် ပုံ့ပုံ့ အိတ်ဆိုင် Facebook Page Auto Messaging အတွက် အဆင်သင့် ဖြစ်နေပါပြီရှင်။</p><p>Meta for Developers > Messenger > Webhooks တွင် အောက်ပါအတိုင်း ချိတ်ဆက်နိုင်ပါသည်:</p><ul><li><b>Callback URL:</b> <code>" . htmlspecialchars(getShopBaseUrl($pdo) . '/api/webhook_facebook.php') . "</code></li><li><b>Verify Token:</b> Admin Dashboard တွင် သတ်မှတ်ထားသော Token</li><li><b>Subscribed Fields:</b> <code>messages</code>, <code>messaging_postbacks</code></li></ul><p style='color:#64748b;font-size:12px;'>Status: Active & Waiting for Meta Webhook Events.</p></div></body></html>";
        exit;
    }

    // Retrieve configured verify token from database
    $expectedVerifyToken = 'pontpont_fb_verify_2026';
    try {
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'facebook_verify_token' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && trim($row['value']) !== '') {
            $expectedVerifyToken = trim($row['value']);
        }
    } catch (\Throwable $e) {}

    // Verify token match
    if ($mode === 'subscribe' && $verifyToken === $expectedVerifyToken) {
        http_response_code(200);
        header('Content-Type: text/plain; charset=utf-8');
        echo $challenge;
        exit;
    } else {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Forbidden: Verification token mismatch.";
        exit;
    }
}

// --------------------------------------------------------------------------
// 2. POST: INCOMING FACEBOOK MESSAGING WEBHOOK EVENTS
// --------------------------------------------------------------------------
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $payload = json_decode($rawInput, true);

    if (!is_array($payload) || ($payload['object'] ?? '') !== 'page') {
        http_response_code(404);
        echo "NOT_A_PAGE_EVENT";
        exit;
    }

    // Retrieve Facebook settings from database
    $fbSettings = [
        'enabled' => '0',
        'token'   => ''
    ];
    try {
        $st = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` IN ('facebook_messenger_enabled', 'facebook_page_access_token')");
        while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
            if ($r['key'] === 'facebook_messenger_enabled') $fbSettings['enabled'] = $r['value'];
            if ($r['key'] === 'facebook_page_access_token') $fbSettings['token'] = $r['value'];
        }
    } catch (\Throwable $e) {}

    // If auto-messaging is disabled or token not set, acknowledge to Meta and return
    if ($fbSettings['enabled'] !== '1' || empty($fbSettings['token'])) {
        http_response_code(200);
        echo "EVENT_RECEIVED_AUTOREPLY_DISABLED";
        exit;
    }

    $pageAccessToken = trim($fbSettings['token']);
    $shopBaseUrl = getShopBaseUrl($pdo);

    // Process each entry in webhook batch
    foreach ($payload['entry'] ?? [] as $entry) {
        $pageId = $entry['id'] ?? null;
        $messagingList = $entry['messaging'] ?? [];

        foreach ($messagingList as $msgEvent) {
            $senderPsid = $msgEvent['sender']['id'] ?? null;

            // Ignore messages from the page itself or empty sender
            if (!$senderPsid || $senderPsid === $pageId) {
                continue;
            }

            // Ignore echoes of bot replies
            if (!empty($msgEvent['message']['is_echo'])) {
                continue;
            }

            // Extract message text from text, quick reply or postback
            $userText = '';
            if (!empty($msgEvent['message']['text'])) {
                $userText = trim($msgEvent['message']['text']);
            } elseif (!empty($msgEvent['message']['quick_reply']['payload'])) {
                $userText = trim($msgEvent['message']['quick_reply']['payload']);
            } elseif (!empty($msgEvent['postback']['payload'])) {
                $userText = trim($msgEvent['postback']['payload']);
            } elseif (!empty($msgEvent['postback']['title'])) {
                $userText = trim($msgEvent['postback']['title']);
            }

            // If customer sent an image attachment with no text
            if ($userText === '' && !empty($msgEvent['message']['attachments'])) {
                $userText = 'အိတ်ဒီဇိုင်းများနှင့် ပုံများ ကြည့်ရှုလိုပါသည်';
            }

            if ($userText === '') {
                continue;
            }

            // A. Send typing indicator to let customer know AI is thinking
            sendFacebookSenderAction($senderPsid, 'typing_on', $pageAccessToken);

            // B. Run query through unified AI Chatbot engine (Live MySQL Store Context)
            $aiResult = processAiChatbotQuery($pdo, $userText);
            $replyText = $aiResult['reply'] ?? 'မင်္ဂလာပါရှင့် 🙏 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်ရှင့်။ ဘာများ ကူညီပေးရမလဲရှင်။';
            $products = $aiResult['products'] ?? [];

            // C. Clean Markdown image syntax from text message so it reads cleanly on Messenger
            $cleanReplyText = preg_replace('/!\[.*?\]\(.*?\)/u', '', $replyText);
            $cleanReplyText = trim(preg_replace("/\n{3,}/u", "\n\n", $cleanReplyText));

            // D. Send text response to user (split if > 1900 chars due to FB 2000 char limit)
            if ($cleanReplyText !== '') {
                if (mb_strlen($cleanReplyText) > 1900) {
                    $chunks = str_split($cleanReplyText, 1900);
                    foreach ($chunks as $chunk) {
                        sendFacebookTextMessage($senderPsid, $chunk, $pageAccessToken);
                    }
                } else {
                    sendFacebookTextMessage($senderPsid, $cleanReplyText, $pageAccessToken);
                }
            }

            // E. If matching bag products exist (e.g. customer asked for bag photos/prices/designs),
            // send interactive Facebook Messenger Generic Template Carousel!
            if (!empty($products)) {
                $carousel = buildFacebookProductCarousel($products, $shopBaseUrl);
                if ($carousel) {
                    sendFacebookMessage($senderPsid, $carousel, $pageAccessToken);
                }
            }

            // F. Turn off typing indicator
            sendFacebookSenderAction($senderPsid, 'typing_off', $pageAccessToken);
        }
    }

    http_response_code(200);
    echo "EVENT_RECEIVED";
    exit;
}

// Method not allowed
http_response_code(405);
echo "Method Not Allowed";

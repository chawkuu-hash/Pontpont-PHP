# Pont Pont Handbag Shop — REST API Documentation (api/shop.php)

ဤစာတမ်းသည် **Pont Pont Handbag Shop** ၏ ပင်မ Backend REST API ဖြစ်သော `api/shop.php` ၏ အလုပ်လုပ်ပုံ၊ ခေါ်ယူနိုင်သော Endpoint များနှင့် လုပ်ဆောင်ချက် (Action) ၃၃ ခု အားလုံးကို အသေးစိတ် ပြည့်စုံစွာ ရှင်းလင်းထားသော လမ်းညွှန်စာတမ်း ဖြစ်ပါသည်။

---

## ၁။ အခြေခံ အချက်အလက်များနှင့် Architecture (Overview)

- **API Endpoint:** `https://pontpontbagshop.com/api/shop.php` (သို့မဟုတ် `https://fgk.ije.mybluehost.me/api/shop.php`)
- **Format:** `JSON` (Request နှင့် Response အားလုံး UTF-8 Encoded JSON ဖြစ်ပါသည်)
- **HTTP Methods:** `GET`, `POST`, `OPTIONS` (Preflight CORS support ပါဝင်)
- **CORS Support:** Web Frontend (PHP, React, Next.js), Mobile Apps (Flutter, React Native) နှင့် Third-party Integration များ ချိတ်ဆက်ခေါ်ယူနိုင်ပါသည်။
- **Session & Security:**
  - HMAC-SHA256 Signed Token ဖြင့် Cookie (`pontpont_session`) သို့မဟုတ် `Authorization: Bearer <token>` Header ဖြင့် စစ်ဆေးပါသည်။
  - Token သက်တမ်းမှာ ၇ ရက် (7 Days) ဖြစ်ပြီး အလိုအလျောက် သက်တမ်းကုန်ဆုံးမှုနှင့် ခွင့်ပြုချက်မရှိဘဲ ပြုပြင်ထားခြင်း (Tamper protection) ကို ကာကွယ်ထားပါသည်။
  - Passwords များကို PHP ၏ `PASSWORD_DEFAULT` (Bcrypt) ဖြင့် Hash လုပ်၍ လုံခြုံစွာ သိမ်းဆည်းပါသည်။
  - Role-based Access Control (RBAC): `admin` နှင့် `customer` ဟူ၍ ၂ မျိုး ခွဲခြားထားပြီး Admin သီးသန့် လုပ်ဆောင်ချက်များကို Role စစ်ဆေးပြီးမှသာ ခွင့်ပြုပါသည်။

---

## ၂။ HTTP GET Request — ဆိုင်တစ်ခုလုံး၏ အချက်အလက်များ တစ်ပြိုင်နက် ရယူခြင်း

အက်ပ် (App) သို့မဟုတ် ဝဘ်ဆိုဒ် (Web) စတင်ပွင့်လာချိန်တွင် Round-trip တစ်ကြိမ်တည်းဖြင့် ဆိုင်၏ လိုအပ်သော အချက်အလက်အားလုံးကို ရယူနိုင်ရန် `GET` Request ကို အသုံးပြုပါသည်။

### Request:
```http
GET /api/shop.php HTTP/1.1
Host: pontpontbagshop.com
Cookie: pontpont_session=<TOKEN> (Optional)
```

### Role ပေါ်မူတည်၍ ဒေတာခွဲခြားပေးပို့မှု (Scoped Response):
| Data Field | Guest (ဧည့်သည်) | Customer (အဖွဲ့ဝင်) | Admin (ဆိုင်ပိုင်ရှင်/စီမံခန့်ခွဲသူ) |
| :--- | :--- | :--- | :--- |
| `products` | ပစ္စည်းအားလုံး | ပစ္စည်းအားလုံး | ပစ္စည်းအားလုံး |
| `categories` | အမျိုးအစားအားလုံး | အမျိုးအစားအားလုံး | အမျိုးအစားအားလုံး |
| `announcements`| ကြေညာချက်အားလုံး | ကြေညာချက်အားလုံး | ကြေညာချက်အားလုံး |
| `orders` | Empty `[]` | **မိမိတင်ထားသော အော်ဒါများသာ** | **ဆိုင်ရှိ အော်ဒါအားလုံး** |
| `users` | Empty `[]` | Empty `[]` | **အသုံးပြုသူစာရင်းအားလုံး** |
| `usersCount` | အသုံးပြုသူ အရေအတွက် | အသုံးပြုသူ အရေအတွက် | အသုံးပြုသူ အရေအတွက် |
| `coupons` | သက်တမ်းရှိ ကူပွန်များသာ | သက်တမ်းရှိ ကူပွန်များသာ | **ကူပွန်အားလုံး (အပိတ်/အဖွင့်ပါ)** |
| `settings` | အများသုံး ဆက်တင်များ | အများသုံး ဆက်တင်များ | **Telegram Token ပါဝင်သော ဆက်တင်အားလုံး** |
| `currentUser` | `null` | မိမိ Profile အချက်အလက် | Admin Profile အချက်အလက် |
| `theme` | သတ်မှတ်ထားသော Theme | သတ်မှတ်ထားသော Theme | သတ်မှတ်ထားသော Theme |
| `chatbotConfig`| အများသုံး Chatbot ဆက်တင် | အများသုံး Chatbot ဆက်တင် | **API Keys / System Prompt ပါဝင်သော ဆက်တင်** |
| `paymentMethods`| ဖွင့်ထားသော ငွေချေနည်းများ | ဖွင့်ထားသော ငွေချေနည်းများ | **ငွေချေစနစ် အားလုံး** |

---

## ၃။ HTTP POST Requests — Action များအလိုက် လုပ်ဆောင်ချက်များ (၃၃ ခု)

POST Request ခေါ်ယူရာတွင် Body အား JSON Format ဖြင့် ပေးပို့ရပြီး `"action"` အမည် သတ်မှတ်ပေးရပါသည်:
```http
POST /api/shop.php HTTP/1.1
Content-Type: application/json

{
  "action": "<ACTION_NAME>",
  ...parameters
}
```

---

### အပိုင်း (က) — အသုံးပြုသူနှင့် အကောင့်ဆိုင်ရာ (Authentication & User Profile)

#### 1. `REGISTER` (အကောင့်အသစ် ဖွင့်ခြင်း)
- **ခွင့်ပြုချက်:** အားလုံး (Public)
- **Request Parameters:**
  ```json
  {
    "action": "REGISTER",
    "name": "မပုံ့ပုံ့",
    "email": "customer@gmail.com",
    "phone": "09963000502",
    "password": "mypassword123",
    "address": "၆၅ လမ်း၊ မန္တလေးမြို့"
  }
  ```
- **လုပ်ဆောင်ချက်:** Email သို့မဟုတ် Phone တူညီမှု ရှိမရှိ စစ်ဆေးခြင်း၊ Password Hash လုပ်ခြင်း၊ အကောင့်ဖွင့်ပြီးသည်နှင့် Session Token ဖန်တီးကာ Cookie ထဲသို့ တိုက်ရိုက် ထည့်သွင်းပေးခြင်း။

#### 2. `LOGIN` (အကောင့်ဝင်ရောက်ခြင်း)
- **ခွင့်ပြုချက်:** အားလုံး (Public)
- **Request Parameters:**
  ```json
  {
    "action": "LOGIN",
    "emailOrPhone": "customer@gmail.com",
    "password": "mypassword123"
  }
  ```
- **လုပ်ဆောင်ချက်:** Email သို့မဟုတ် ဖုန်းနံပါတ်ဖြင့် Login ဝင်နိုင်ခြင်း။ Password မှန်ကန်ပါက `pontpont_session` Cookie သတ်မှတ်ပေးပြီး User အချက်အလက်နှင့် ဆိုင်ဒေတာကို ပြန်လည်ပေးပို့ခြင်း။

#### 3. `LOGOUT` (အကောင့်မှ ထွက်ခြင်း)
- **ခွင့်ပြုချက်:** အားလုံး
- **Request Parameters:**
  ```json
  {
    "action": "LOGOUT"
  }
  ```
- **လုပ်ဆောင်ချက်:** Session Cookie အား သက်တမ်းကုန်ဆုံးစေပြီး ရှင်းထုတ်ခြင်း။

#### 4. `UPDATE_PROFILE` (ကိုယ်ရေးအချက်အလက် ပြင်ဆင်ခြင်း)
- **ခွင့်ပြုချက်:** အကောင့်ဝင်ထားသူ (Authenticated User)
- **Request Parameters:**
  ```json
  {
    "action": "UPDATE_PROFILE",
    "name": "ဒေါ်ပုံ့ပုံ့",
    "phone": "09750072752",
    "address": "မန္တလေးမြို့",
    "currentPassword": "oldpassword123", // Optional (Password ပြောင်းလိုမှသာ)
    "newPassword": "newpassword456"       // Optional
  }
  ```
- **လုပ်ဆောင်ချက်:** အမည်၊ ဖုန်း၊ လိပ်စာ ပြင်ဆင်ခြင်း။ Password ပြောင်းလိုပါက မူလ Password ကို အရင်စစ်ဆေးပြီးမှသာ အသစ်သို့ ပြောင်းလဲပေးခြင်း။

#### 5. `UPLOAD_AVATAR` (Profile ပုံ တင်ခြင်း)
- **ခွင့်ပြုချက်:** အကောင့်ဝင်ထားသူ (Authenticated User)
- **Request Parameters:** `multipart/form-data` (file: `avatar`) သို့မဟုတ် JSON base64:
  ```json
  {
    "action": "UPLOAD_AVATAR",
    "avatar_data": "data:image/jpeg;base64,..."
  }
  ```
- **လုပ်ဆောင်ချက်:** `assets/uploads/avatars/` ထဲသို့ ပုံသိမ်းဆည်းပြီး Database တွင် Avatar URL အသစ် သတ်မှတ်ပေးခြင်း။ (ယခင်ပုံဟောင်းရှိပါက အလိုအလျောက် ဖျက်ပစ်ပါသည်)။

#### 6. `REMOVE_AVATAR` (Profile ပုံ ဖျက်ခြင်း)
- **ခွင့်ပြုချက်:** အကောင့်ဝင်ထားသူ
- **Request Parameters:**
  ```json
  {
    "action": "REMOVE_AVATAR"
  }
  ```
- **လုပ်ဆောင်ချက်:** လက်ရှိ Profile ဓာတ်ပုံဖိုင်အား ဆာဗာပေါ်မှ ဖျက်ပစ်ပြီး `null` သတ်မှတ်ခြင်း။

---

### အပိုင်း (ခ) — အော်ဒါနှင့် ငွေပေးချေမှု (Orders & Checkout)

#### 7. `CREATE_ORDER` (အော်ဒါအသစ် တင်ခြင်း)
- **ခွင့်ပြုချက်:** အားလုံး (Guest Checkout ရော Member Checkout ပါ ရရှိပါသည်)
- **Request Parameters:**
  ```json
  {
    "action": "CREATE_ORDER",
    "items": [
      {
        "productId": "pp-bag-01",
        "quantity": 5,
        "selectedColor": "Caramel Brown",
        "price": 38000
      }
    ],
    "customer": {
      "name": "ဒေါ်အေးအေး",
      "phone": "09963000502",
      "address": "၇၃ လမ်း၊ မန္တလေးမြို့",
      "city": "Mandalay (မန္တလေး - မြို့တွင်း)",
      "note": "ညနေ ၄ နာရီနောက်ပိုင်း ပို့ပေးပါ"
    },
    "paymentMethod": "KPay",
    "couponCode": "PONTPONT10",
    "paymentSlip": "assets/uploads/slips/slip_123.jpg" // KPay/WavePay ဖြစ်ပါက
  }
  ```
- **အလိုအလျောက် တွက်ချက်ပေးသည့် စနစ်များ:**
  1. **လက်ကားဈေး (Wholesale Discount):** အိတ် အရေအတွက် ၅ လုံးနှင့်အထက် ဝယ်ယူပါက `wholesalePrice` ဖြင့် အလိုအလျောက် ပြောင်းလဲတွက်ချက်ပေးခြင်း။
  2. **ပို့ဆောင်ခ (Delivery Fee):** ရွေးချယ်လိုက်သော မြို့အလိုက် ပို့ဆောင်ခ တွက်ချက်ပေးခြင်း။ ကုန်ပစ္စည်းတန်ဖိုး ၁ သိန်းကျပ် (100,000 Ks) အထက် ဝယ်ယူပါက ပို့ခအခမဲ့ (**Free Delivery**) အလိုအလျောက် သတ်မှတ်ပေးခြင်း။
  3. **ကူပွန် လျှော့ဈေး (Coupon Validation):** ရာခိုင်နှုန်း (%) သို့မဟုတ် ပမာဏ (Fixed Ks) အလိုက် အမှန်တကယ် လျှော့ပေးခြင်းနှင့် အသုံးပြုမှုအကြိမ်ရေ တိုးမြှင့်ခြင်း။
  4. **Stock လျှော့ချခြင်း:** ပစ္စည်းများ၏ လက်ကျန် Stock မှ အလိုအလျောက် နုတ်ယူပေးခြင်း။
  5. **Telegram Alert:** ဆိုင်ရှင်၏ Telegram Bot ထံသို့ အော်ဒါအသစ် ချက်ချင်းရောက်ရှိကြောင်း အသံနှင့်တကွ Notification စာတို အလိုအလျောက် ပေးပို့ပေးခြင်း။

#### 8. `VALIDATE_COUPON` (ကူပွန်ကုဒ် စစ်ဆေးခြင်း)
- **ခွင့်ပြုချက်:** အားလုံး
- **Request Parameters:**
  ```json
  {
    "action": "VALIDATE_COUPON",
    "code": "PONTPONT10",
    "orderAmount": 50000
  }
  ```
- **Response:** ကူပွန် မှန်/မမှန်၊ သက်တမ်းကုန်/မကုန်၊ အနည်းဆုံး ဝယ်ယူရမည့်ပမာဏ ပြည့်/မပြည့်နှင့် လျှော့ပေးမည့် ပမာဏ (`discountAmount`) ကို တွက်ချက်ပေးခြင်း။

#### 9. `UPLOAD_SLIP` (ငွေလွှဲပြေစာ ပုံတင်ခြင်း)
- **ခွင့်ပြုချက်:** အားလုံး
- **Request Parameters:** `multipart/form-data` (file: `slip`) သို့မဟုတ် base64 `slip_data`
- **လုပ်ဆောင်ချက်:** KPay/WavePay ငွေလွှဲပြေစာ Screenshot အား `assets/uploads/slips/` သို့ လုံခြုံစွာ သိမ်းဆည်းပြီး URL ပြန်ပေးခြင်း။

---

### အပိုင်း (ဂ) — AI Live Chat စနစ် (AI Chatbot & Knowledge Engine)

#### 10. `CHAT_MESSAGE` (ဖောက်သည်နှင့် AI စကားပြောခြင်း)
- **ခွင့်ပြုချက်:** အားလုံး
- **Request Parameters:**
  ```json
  {
    "action": "CHAT_MESSAGE",
    "message": "အိတ်ပုံလေးတွေ ကြည့်ချင်လို့ပါ",
    "history": [
      { "role": "user", "text": "မင်္ဂလာပါ" },
      { "role": "bot", "text": "မင်္ဂလာပါရှင်..." }
    ]
  }
  ```
- **လုပ်ဆောင်ချက်:**
  1. Google Gemini, Amazon Bedrock, OpenAI, Groq သို့မဟုတ် Custom AI Endpoint သို့ Database ရှိ ကုန်ပစ္စည်း/လိပ်စာ/ဆိုင်ဖွင့်ချိန် Live Context အပြည့်အစုံ ထည့်သွင်း၍ မေးမြန်းပေးခြင်း။
  2. AI သုံးမထားပါကလည်း မြန်မာဘာသာ အသိဉာဏ်ရှိ Rule-based Fallback Engine ဖြင့် အလိုအလျောက် တိကျစွာ အဖြေပေးခြင်း။
  3. **အိတ်ပုံများ တိုက်ရိုက်ထုတ်ပြခြင်း:** Customer က အိတ်ပုံ၊ ဒီဇိုင်း၊ ဈေးနှုန်းများ မေးမြန်းပါက MySQL Database ထဲမှ သက်ဆိုင်ရာ အိတ်များ၏ `image` (ဓာတ်ပုံ)၊ အမည်၊ ဈေးနှုန်းများကို ရှာဖွေပြီး Message ထဲတွင် Markdown ပုံနှင့် Product Cards များအဖြစ် ထည့်သွင်းပေးပို့ပေးခြင်း။

#### 11. `FETCH_AI_MODELS` (AI Provider များထံမှ Model စာရင်း အလိုအလျောက် ဆွဲယူခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "FETCH_AI_MODELS",
    "provider": "gemini", // gemini, bedrock, groq, openai, custom
    "apiKey": "AIzaSy...",
    "apiUrl": "",
    "region": "us-east-1"
  }
  ```
- **လုပ်ဆောင်ချက်:** ကြိုတင် Model Name ရေးမထားဘဲ Provider ၏ Official API ကို တိုက်ရိုက်ခေါ်ယူပြီး လက်ရှိ အသုံးပြုနိုင်သော Model စာရင်းကို Dynamic ဖော်ပြပေးခြင်း။

---

### အပိုင်း (ဃ) — ကုန်ပစ္စည်းနှင့် အမျိုးအစား စီမံခန့်ခွဲမှု (Products & Categories)

#### 12. `ADD_PRODUCT` (ကုန်ပစ္စည်း အသစ်ထည့်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "ADD_PRODUCT",
    "product": {
      "nameEn": "Luxury Leather Handbag",
      "nameMm": "အဆင့်မြင့် သားရေ လက်ဆွဲအိတ်",
      "category": "Tote",
      "categoryMm": "လက်ဆွဲအိတ်",
      "price": 45000,
      "originalPrice": 52000,
      "wholesalePrice": 38000,
      "image": "assets/uploads/products/bag_1.jpg",
      "colors": [
        { "name": "Black", "hex": "#000000" },
        { "name": "Brown", "hex": "#8B4513" }
      ],
      "badge": "Best Seller",
      "stock": 50,
      "material": "Genuine PU Leather",
      "dimensions": "30cm x 20cm x 10cm",
      "descriptionEn": "Spacious and durable handbag",
      "descriptionMm": "အသုံးခံပြီး နေရာဆံ့သော အိတ်"
    }
  }
  ```

#### 13. `UPDATE_PRODUCT` (ကုန်ပစ္စည်း အချက်အလက် ပြင်ဆင်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:** `ADD_PRODUCT` ကဲ့သို့ပင်ဖြစ်ပြီး ပြင်ဆင်လိုသော `id` ပါဝင်ရပါမည်။

#### 14. `DELETE_PRODUCT` (ကုန်ပစ္စည်း ဖျက်ပစ်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "DELETE_PRODUCT",
    "productId": "pp-bag-01"
  }
  ```

#### 15. `ADD_CATEGORY` (ပစ္စည်းအမျိုးအစား အသစ်ထည့်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "ADD_CATEGORY",
    "category": {
      "id": "Backpack",
      "labelEn": "Backpacks",
      "labelMm": "ကျောပိုးအိတ်များ"
    }
  }
  ```

#### 16. `DELETE_CATEGORY` (ပစ္စည်းအမျိုးအစား ဖျက်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "DELETE_CATEGORY",
    "categoryId": "Backpack"
  }
  ```

---

### အပိုင်း (င) — အော်ဒါစီမံခန့်ခွဲမှု (Order Status Management)

#### 17. `UPDATE_ORDER_STATUS` (အော်ဒါ အခြေအနေ ပြောင်းလဲခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "UPDATE_ORDER_STATUS",
    "orderId": "ORD-179577...",
    "status": "shipped" // pending, confirmed, processing, shipped, delivered, cancelled
  }
  ```
- **လုပ်ဆောင်ချက်:** အော်ဒါအခြေအနေ ပြောင်းလဲခြင်း။ အကယ်၍ အော်ဒါကို `cancelled` ပြုလုပ်ပါက အော်ဒါတင်စဉ်က နုတ်ယူထားသော ပစ္စည်း Stock များကို မူလအတိုင်း Database ထဲသို့ ပြန်လည် ပေါင်းထည့်ပေးခြင်း (Auto Stock Restock)။

---

### အပိုင်း (စ) — သတင်းနှင့် ပရိုမိုးရှင်း ကြေညာချက်များ (Announcements & News)

#### 18. `ADD_ANNOUNCEMENT` (ကြေညာချက် အသစ်တင်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "ADD_ANNOUNCEMENT",
    "announcement": {
      "title": "ဒီဇင်ဘာ အထူးပရိုမိုးရှင်း",
      "content": "အိတ်အားလုံး ၁၀% လျှော့ဈေး ရရှိပါပြီ...",
      "tag": "Promotion", // Promotion, New Arrival, Wholesale, Notice
      "image": "assets/uploads/announcements/ann_1.jpg",
      "authorName": "မပုံ့ (Admin)"
    }
  }
  ```

#### 19. `DELETE_ANNOUNCEMENT` (ကြေညာချက် ဖျက်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "DELETE_ANNOUNCEMENT",
    "announcementId": "ann-179577..."
  }
  ```

#### 20. `UPDATE_ANNOUNCEMENT_IMAGE` (ကြေညာချက် ဓာတ်ပုံ တိုက်ရိုက်ပြောင်းလဲခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "UPDATE_ANNOUNCEMENT_IMAGE",
    "announcementId": "ann-179577...",
    "image": "assets/uploads/announcements/ann_new.jpg"
  }
  ```

---

### အပိုင်း (ဆ) — မီဒီယာနှင့် ဖိုင်အပ်ပုဒ် (Media & Image Uploads)

#### 21. `UPLOAD_IMAGE` (လုံခြုံစိတ်ချရသော ဓာတ်ပုံတင်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:** `multipart/form-data` (`image`) သို့မဟုတ် base64 JSON (`image_data`):
  ```json
  {
    "action": "UPLOAD_IMAGE",
    "image_data": "data:image/png;base64,...",
    "folder": "announcements" // products, logos, banners, slips, announcements
  }
  ```
- **လုံခြုံရေး စစ်ဆေးမှုများ:**
  - File MIME Type စစ်ဆေးခြင်း (`image/jpeg`, `image/png`, `image/webp` သာ လက်ခံသည်)။
  - File Size 5MB ထက် မကျော်စေရန် ကန့်သတ်ခြင်း။
  - Random Hash ဖြင့် ဖိုင်အမည်သစ် သတ်မှတ်ခြင်း။
  - `assets/uploads/` ဖိုဒါထဲတွင် `.htaccess` ဖြင့် PHP Script Execution ကို တားမြစ်ထားခြင်း။

---

### အပိုင်း (ဇ) — လျှော့ဈေး ကူပွန်များ (Coupons Management)

#### 22. `ADD_COUPON` (ကူပွန် အသစ်ဖန်တီးခြင်း/ပြင်ဆင်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "ADD_COUPON",
    "coupon": {
      "code": "NEWYEAR2026",
      "discountType": "percent", // percent သို့မဟုတ် fixed
      "discountValue": 15,       // 15% သို့မဟုတ် 5000 Ks
      "minOrderAmount": 50000,
      "usageLimit": 100,
      "expiresAt": "2026-12-31T23:59:59Z",
      "isActive": 1
    }
  }
  ```

#### 23. `DELETE_COUPON` (ကူပွန် ဖျက်ပစ်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "DELETE_COUPON",
    "couponId": "cpn-1795..."
  }
  ```

---

### အပိုင်း (ဈ) — ငွေပေးချေမှု နည်းလမ်းများ (Payment Methods Management)

#### 24. `SAVE_PAYMENT_METHOD` (ငွေချေစနစ် အသစ်ထည့်ခြင်း/ပြင်ဆင်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "SAVE_PAYMENT_METHOD",
    "paymentMethod": {
      "id": "pm-kpay",
      "code": "KPay",
      "name": "KBZPay",
      "nameMm": "KBZPay (ငွေလွှဲ)",
      "type": "transfer", // transfer, cod, banking
      "accountName": "Ma Pont Pont",
      "accountNumber": "09963000502",
      "instructions": "ငွေလွှဲပြီးပါက ပြေစာတင်ပေးပါ",
      "qrCodeUrl": "assets/uploads/logos/kpay_qr.jpg",
      "requiresSlip": true,
      "isActive": true,
      "sortOrder": 1
    }
  }
  ```

#### 25. `DELETE_PAYMENT_METHOD` (ငွေချေစနစ် ဖျက်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "DELETE_PAYMENT_METHOD",
    "id": "pm-aya"
  }
  ```

#### 26. `TOGGLE_PAYMENT_METHOD` (ငွေချေစနစ် ဖွင့်/ပိတ်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "TOGGLE_PAYMENT_METHOD",
    "id": "pm-cod",
    "isActive": false
  }
  ```

---

### အပိုင်း (ည) — အသုံးပြုသူ စာရင်းနှင့် Role သတ်မှတ်ခြင်း (User Management)

#### 27. `UPDATE_USER_ROLE` (အသုံးပြုသူအား Admin/Customer ပြောင်းလဲခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "UPDATE_USER_ROLE",
    "userId": "usr-123456",
    "role": "admin" // admin သို့မဟုတ် customer
  }
  ```

#### 28. `DELETE_USER` (အသုံးပြုသူ အကောင့်အား ဖျက်ပစ်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "DELETE_USER",
    "userId": "usr-123456"
  }
  ```

---

### အပိုင်း (ဋ) — ဆိုင်ဆက်တင်များနှင့် Theme ဒီဇိုင်း (Settings & Themes)

#### 29. `UPDATE_SETTINGS` (ဆိုင်ဆက်တင်များ သိမ်းဆည်းခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "UPDATE_SETTINGS",
    "settings": {
      "shop_name": "Pont Pont Handbag Shop",
      "shop_logo": "assets/images/pontpont_logo.jpg",
      "phone_1": "09-963000502",
      "phone_2": "09-750072752",
      "announcement": "✨ ၁ သိန်းအထက် Free Delivery • နေ့စဉ် 8:00 AM မှ 7:00 PM ဖွင့်ပါသည်",
      "telegram_enabled": "1",
      "telegram_bot_token": "123456:ABC-DEF...",
      "telegram_chat_id": "987654321"
    }
  }
  ```

#### 30. `UPDATE_THEME` (ဆိုင်ဒီဇိုင်း Theme ပြောင်းလဲခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "UPDATE_THEME",
    "theme": "luxury-gold" // luxury-gold, rose-paris, minimal-noir
  }
  ```

#### 31. `UPDATE_CHATBOT_CONFIG` (AI Chatbot ဆက်တင်များ ပြင်ဆင်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "UPDATE_CHATBOT_CONFIG",
    "chatbotConfig": {
      "provider": "gemini", // gemini, bedrock, openai, groq, custom, mock
      "model": "gemini-flash-lite-latest",
      "botName": "ပုံ့ပုံ့ Live Chat",
      "welcomeMessageMm": "မင်္ဂလာပါရှင် 👋 ပုံ့ပုံ့ အိတ်ဆိုင်မှ ကြိုဆိုပါတယ်။",
      "welcomeMessageEn": "Hello! Welcome to Pont Pont Handbag Shop.",
      "enabled": true,
      "apiKey": "AIzaSy...",
      "apiUrl": "",
      "region": "us-east-1",
      "systemPrompt": "You are Pont Pont AI customer assistant..."
    }
  }
  ```

#### 32. `TEST_TELEGRAM` (Telegram Bot ချိတ်ဆက်မှု စမ်းသပ်ခြင်း)
- **ခွင့်ပြုချက်:** Admin Only
- **Request Parameters:**
  ```json
  {
    "action": "TEST_TELEGRAM",
    "botToken": "123456:ABC-DEF...",
    "chatId": "987654321"
  }
  ```
- **လုပ်ဆောင်ချက်:** ထည့်သွင်းထားသော Bot Token နှင့် Chat ID မှန်ကန်မှု ရှိမရှိ စမ်းသပ်စာတို ပို့ကြည့်ခြင်း။

---

### အပိုင်း (ဌ) — မိုဘိုင်းအက်ပ် ထိန်းချုပ်မှု (Mobile App Support)

#### 33. `CHECK_VERSION` (အက်ပ် ဗားရှင်း စစ်ဆေးခြင်း)
- **ခွင့်ပြုချက်:** အားလုံး (Mobile App Clients)
- **Request Parameters:**
  ```json
  {
    "action": "CHECK_VERSION",
    "version": "1.0.0",
    "platform": "android" // android သို့မဟုတ် ios
  }
  ```
- **Response:** အက်ပ်ဗားရှင်း အသစ်ထွက်ရှိမှု ရှိမရှိ (`hasUpdate`)၊ မဖြစ်မနေ Update လုပ်ရမည်လား (`forceUpdate`) နှင့် ဒေါင်းလုဒ်လင့်ခ် (`downloadUrl`) တို့ကို ပြန်လည်ပေးပို့ခြင်း။

#### 34. `TEST_FACEBOOK_CONNECTION` (Facebook Page Access Token စစ်ဆေးခြင်း)
- **ခွင့်ပြုချက်:** စီမံခန့်ခွဲသူ (Admin သာ)
- **Request Parameters:**
  ```json
  {
    "action": "TEST_FACEBOOK_CONNECTION",
    "token": "EAA..." // စစ်ဆေးလိုသော Page Access Token (ချန်လှပ်ထားပါက Database မှ Token ကို စစ်ဆေးပါမည်)
  }
  ```
- **Response:** Meta Graph API မှ ချိတ်ဆက်ထားသော Page ID, အမည် (`name`), နှင့် Category တို့ကို အောင်မြင်စွာ ပြန်လည်ပေးပို့ခြင်း။

---

### အပိုင်း (ဍ) — Meta Facebook Messenger Webhook စနစ် (`api/webhook_facebook.php`)

Facebook Page Messenger မှ Customer များ၏ မေးမြန်းချက်များကို AI Live Chatbot ဖြင့် ၂၄ နာရီ Auto-Reply ပေးနိုင်ရန် ဤ Webhook Endpoint ကို ဖန်တီးထားပါသည်:
- **Endpoint URL:** `https://pontpontbagshop.com/api/webhook_facebook.php`
- **GET Request:** Meta Webhook Verification (`hub.mode=subscribe`, `hub.verify_token`, `hub.challenge`) ကို စစ်ဆေးပြီး Handshake လုပ်ဆောင်ပေးခြင်း။
- **POST Request:** Facebook Page Messenger မှ ဝင်ရောက်လာသော Messaging Events များကို ဖတ်ယူပြီး Live Store Context ပါဝင်သော AI Engine ဖြင့် Auto-Reply ပြန်ကြားပေးခြင်း။ Customer များက အိတ်ပုံ/ဒီဇိုင်းများ တောင်းဆိုပါက MySQL `products` စာရင်းထဲမှ အိတ်ဓာတ်ပုံ၊ ဈေးနှုန်းနှင့် Shop Link ပါဝင်သော Interactive Generic Template Carousel ကတ်များဖြင့် ပေးပို့ပေးခြင်း။

---

## ၄။ API Response နှင့် Error Status Codes များ

API မှ အောင်မြင်ပါက အမြဲတမ်း HTTP 200 နှင့်အတူ `ok: true` ပြန်လည်ပေးပို့ပါသည်:
```json
{
  "ok": true,
  "db": { ... } // ဆိုင်၏ နောက်ဆုံးရ စာရင်းအချက်အလက်များ
}
```

အမှားအယွင်း ဖြစ်ပေါ်ပါက သက်ဆိုင်ရာ HTTP Status Code နှင့် Error Message ပြန်လည်ပေးပို့ပါသည်:
```json
{
  "ok": false,
  "error": "ဖုန်းနံပါတ် သို့မဟုတ် စကားဝှက် မှားယွင်းနေပါသည်"
}
```

| HTTP Code | အဓိပ္ပာယ် | ဖြစ်ပွားနိုင်သည့် အခြေအနေ |
| :--- | :--- | :--- |
| `200 OK` | အောင်မြင်သည် | Request အောင်မြင်စွာ ဆောင်ရွက်ပြီးစီးခြင်း |
| `400 Bad Request` | အချက်အလက် မပြည့်စုံ/မှားယွင်း | လိုအပ်သော Parameter မပါခြင်း၊ ပုံအရွယ်အစား 5MB ကျော်ခြင်း |
| `401 Unauthorized` | ခွင့်ပြုချက်မရှိပါ | Login မဝင်ဘဲ Profile ပြင်ဆင်ရန် ကြိုးစားခြင်း |
| `403 Forbidden` | လုပ်ပိုင်ခွင့် မရှိပါ | Customer အကောင့်ဖြင့် Admin Action များ လှမ်းခေါ်ခြင်း |
| `404 Not Found` | မတွေ့ရှိပါ | မရှိသော ကုန်ပစ္စည်း/အော်ဒါ ID အား ပြင်ဆင်/ဖျက်ရန် ကြိုးစားခြင်း |
| `500 Server Error` | ဆာဗာပိုင်း အမှား | Database Connection ပြတ်တောက်ခြင်း |

---

## ၅။ Code Example (JavaScript Fetch ဖြင့် ခေါ်ယူပုံ ဥပမာ)

```javascript
// ၁။ အိတ်ပုံများ နှင့် ဒီဇိုင်းများ AI ထံ မေးမြန်းခြင်း
async function askAiForBags() {
  const response = await fetch('api/shop.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'CHAT_MESSAGE',
      message: 'ရုံးတက် လက်ဆွဲအိတ် ပုံလေးတွေ ပြပေးပါရှင်'
    })
  });
  const data = await response.json();
  console.log("AI အဖြေ:", data.reply);
  console.log("Database မှ အိတ်များ:", data.products);
}

// ၂။ အော်ဒါအသစ် တင်ခြင်း
async function checkoutOrder(orderData) {
  const response = await fetch('api/shop.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'CREATE_ORDER',
      ...orderData
    })
  });
  return await response.json();
}
```

---

ဤစာတမ်းအား သိမ်းဆည်းထားသည့် ဖိုင်လမ်းကြောင်း:
- `Pontpont-PHP/shop_api_explain.md`

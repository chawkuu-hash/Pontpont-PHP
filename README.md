# ပုံ့ပုံ့ အိတ်ဆိုင် (Pont Pont Handbag Shop) - Bluehost PHP & MySQL Web Package

ဤ Package သည် **Bluehost Web Hosting** (cPanel / Apache / PHP 7.4–8.3 / MySQL & phpMyAdmin) တွင် တိုက်ရိုက်တင်ကာ အသုံးပြုနိုင်ရန် ရေးသားထားသော မူလ Next.js စနစ်နှင့် **တထေရာတည်း တူညီသော (100% Identical)** PHP & MySQL ဝဘ်ဆိုက် ဖြစ်ပါသည်။

---

## 🌟 ပါဝင်သော လုပ်ဆောင်ချက်များ (Key Features)

1. **စတိုးဆိုင် မျက်နှာစာ (Modern Luxury Storefront)**
   - မန္တလေး ပင်မဆိုင်နှင့် ဆိုင်ခွဲ (၃) ဆိုင် အမှတ်တံဆိပ်
   - Luxury Gold, Parisian Rose, Editorial Noir Theme (၃) မျိုး စိတ်ကြိုက် ပြောင်းလဲနိုင်ခြင်း
   - Category အလိုက် စစ်ထုတ်ခြင်း (Tote & Handbags, Sling & Crossbody, Shoulder Bags, Wallets, Backpacks, etc.)
   - Real-time ရှာဖွေမှု (Search) နှင့် ဈေးနှုန်း/လူကြိုက်များမှု အလိုက် အစဉ်လိုက်စီခြင်း (Sort)
   - လက်လီဈေးနှုန်းအပြင် **လက်ကားဈေးနှုန်း (၅ လုံး+)** တပြိုင်နက် ဖော်ပြပေးခြင်း
   - ပစ္စည်း အသေးစိတ်ကြည့်ရှုသည့် Modal (Quick View & Details)

2. **ဝယ်ယူမှု စနစ်နှင့် ငွေပေးချေမှု (Cart & Checkout System)**
   - ကာလာ (Color) နှင့် အရေအတွက် စိတ်ကြိုက်ရွေးချယ်နိုင်သော Cart Drawer
   - လျှော့ဈေးကူပွန် (`PONTPONT10` ဖြင့် 10% Discount ရယူနိုင်ခြင်း)
   - မြို့နယ်အလိုက် ပို့ဆောင်ခ အလိုအလျောက် တွက်ချက်ပေးခြင်း (၁ သိန်းအထက် Free Delivery)
   - KBZPay, WavePay နှင့် အိမ်ရောက်ငွေချေစနစ် (COD) ရွေးချယ်မှု
   - ငွေလွှဲ Transaction Reference ထည့်သွင်းခြင်း
   - မှာယူပြီးပါက Order Confirmation နှင့် ပြေစာ ထုတ်ပေးခြင်း

3. **အသုံးပြုသူနှင့် အော်ဒါမှတ်တမ်း (User Auth & Order History)**
   - Customer & Admin Sign In / Register
   - မှာယူထားသော အော်ဒါများ၏ Status (Pending, Confirmed, Shipped, Delivered, Cancelled) ကို တိုက်ရိုက်စစ်ဆေးနိုင်ခြင်း

4. **ပြည့်စုံသော စီမံခန့်ခွဲမှုစနစ် (Admin Dashboard Center)**
   - စုစုပေါင်း အရောင်းရငွေ၊ အော်ဒါအရေအတွက်၊ ပစ္စည်းစာရင်း Overview Analytics
   - အော်ဒါများ စစ်ဆေးခြင်းနှင့် အဆင့်ဆင့် Status ပြောင်းလဲခြင်း
   - ပစ္စည်းအသစ် ထည့်သွင်းခြင်း၊ ဈေးနှုန်းနှင့် Stock ပြင်ဆင်ခြင်း၊ ဖျက်ခြင်း
   - Category အသစ်များ ထည့်သွင်းခြင်းနှင့် စီမံခြင်း
   - ဆိုင်ကြေညာချက်နှင့် Promotion များ တင်ခြင်း
   - စတိုးဆိုင် Theme ရွေးချယ်ခြင်း

5. **စမတ်ကျသော AI စားသုံးသူအကူအညီ (AI Customer Chatbot)**
   - **Google Gemini API**, **OpenAI (GPT-4o)**, **Groq (Llama 3.3)** သို့မဟုတ် **Custom OpenAI-compatible API** (OpenWebUI, Ollama, LM Studio) တိုက်ရိုက် ချိတ်ဆက်နိုင်ခြင်း
   - API Key နှင့် URL ထည့်ပြီး **"Fetch Models"** နှိပ်ရုံဖြင့် Model စာရင်း အလိုအလျောက် ရယူနိုင်ခြင်း
   - မန္တလေးဆိုင်ခွဲလိပ်စာများ၊ ဖုန်းနံပါတ်များ၊ KPay အချက်အလက်များနှင့် ပို့ဆောင်ခနှုန်းထားများကို မြန်မာလို အလိုအလျောက် ဖြေကြားပေးခြင်း
   - အင်တာနက်/API မရှိချိန်တွင်လည်း Built-in Rule Engine ဖြင့် ချောမွေ့စွာ အလုပ်လုပ်ခြင်း

---

## 📁 ဖိုဒါနှင့် ဖိုင်များ တည်ဆောက်ပုံ (Folder Structure)

```text
Pontpont-PHP/
├── index.php                 # မူလ စတိုးဆိုင် Web Frontend + Cart + Checkout + Admin Dashboard UI
├── install.php               # Browser မှ ၁ ချက်နှိပ်ရုံဖြင့် Database သွင်းပေးသည့် 1-Click Installer
├── .htaccess                 # Bluehost Apache အတွက် Clean URLs, CORS, Gzip နှင့် လုံခြုံရေး ဆက်တင်
├── config/
│   └── db.php                # Bluehost MySQL Database ချိတ်ဆက်မှု Configuration ဖိုင် (PDO)
├── api/
│   ├── shop.php              # Full REST API (AI Chatbot, Orders, Products, Auth, etc.)
│   └── .htaccess             # API Clean URL Routing & CORS Headers
├── database/
│   └── pontpont_db.sql       # phpMyAdmin တွင် တိုက်ရိုက် Import ပြုလုပ်နိုင်သော Database Dump
└── pontpont_php_bluehost.zip # Bluehost public_html သို့ တင်ရန် အသင့်သုံး Zip ဖိုင်
```

---

## 🚀 Bluehost Web Hosting တွင် တင်သွင်းအသုံးပြုနည်း (Step-by-Step Guide)

### အဆင့် (၁) - ဖိုင်များ Upload တင်ခြင်း (Upload Files)
1. Bluehost cPanel သို့ ဝင်ရောက်ပါ။
2. **File Manager** ကို ဖွင့်ပြီး `public_html` ဖိုဒါထဲသို့ သွားပါ (Subdomain တွင် တင်လိုပါက သက်ဆိုင်ရာ Subdomain ဖိုဒါထဲသို့ သွားပါ)။
3. အသင့်ဖန်တီးပေးထားသော `pontpont_php_bluehost.zip` ဖိုင်ကို `public_html` ထဲသို့ Upload တင်ပြီး **Extract** (ဖိုင်ဖြည်) ပေးပါ။

---

### အဆင့် (၂) - MySQL Database ဖန်တီးခြင်း (Create Database in cPanel)
1. Bluehost cPanel > **Databases** ကဏ္ဍအောက်ရှိ **MySQL® Databases** (သို့မဟုတ် **MySQL Database Wizard**) ကို နှိပ်ပါ။
2. **Create New Database**: Database အမည်အသစ် သတ်မှတ်ပါ (ဥပမာ- `cpaneluser_pontpont`)။
3. **Add New User**: Database User တစ်ခု ဖန်တီးပါ (ဥပမာ- `cpaneluser_pontadmin`) နှင့် စကားဝှက် (Password) သတ်မှတ်ပါ။
4. **Add User To Database**: ဖန်တီးထားသော User ကို Database ထဲသို့ ပေါင်းထည့်ပြီး **ALL PRIVILEGES** အကွက်ကို အမှန်ခြစ်ကာ **Make Changes** ကို နှိပ်ပါ။

---

### အဆင့် (၃) - Database သွင်းခြင်း (၂ နည်းလမ်းအနက် ကြိုက်ရာရွေးနိုင်ပါသည်)

#### နည်းလမ်း (A) - 1-Click Web Installer ဖြင့် သွင်းခြင်း (အလွယ်ဆုံး)
1. သင့်ဒိုမိန်း၏ Installer link သို့ Browser မှ သွားပါ -
   `https://yourdomain.com/install.php`
2. Bluehost cPanel တွင် ဖန်တီးခဲ့သော Database Name, User, Password များကို ဖြည့်ပြီး **"🚀 Setup စတင်သွင်းမည်"** ခလုတ်ကို နှိပ်ပါ။
3. စက္ကန့်ပိုင်းအတွင်း Tables များနှင့် မူလကုန်ပစ္စည်း (၈) မျိုး၊ ဆိုင်ခွဲအချက်အလက်များနှင့် AI Chatbot ဆက်တင်များကို အလိုအလျောက် ထည့်သွင်းပေးသွားမည်ဖြစ်ပြီး `config/db.php` ကိုပါ တစ်ခါတည်း ပြင်ဆင်ပေးသွားပါမည်။

#### နည်းလမ်း (B) - phpMyAdmin မှတစ်ဆင့် Import လုပ်ခြင်း
1. Bluehost cPanel > **phpMyAdmin** ကို ဖွင့်ပါ။
2. ဘယ်ဘက်ခြမ်းမှ အဆင့် (၂) တွင် ဖန်တီးထားသော Database ကို နှိပ်ပါ။
3. အပေါ်ဘက် menu မှ **Import** tab ကို နှိပ်ပါ။
4. **Browse** ကို နှိပ်ပြီး `database/pontpont_db.sql` ဖိုင်ကို ရွေးချယ်ကာ စာမျက်နှာအောက်ခြေရှိ **Import** ခလုတ်ကို နှိပ်ပါ။
5. File Manager သို့ ပြန်သွားပြီး `config/db.php` ကို Code Editor ဖြင့် ဖွင့်၍ အောက်ပါတို့ကို ပြင်ဆင်ပါ -
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'သင်၏_Database_Name');
   define('DB_USER', 'သင်၏_Database_User');
   define('DB_PASS', 'သင်၏_Database_Password');
   ```

---

## 🔑 Default Admin Account (စီမံခန့်ခွဲသူ အကောင့်)

စနစ်စတင်ချိန်တွင် Admin အကောင့်တစ်ခုကို ကြိုတင်ထည့်သွင်းပေးထားပါသည် -
- **Admin Email:** `admin@pontpont.com`
- **Password:** `admin123`
*(ဝဘ်ဆိုက်တွင် ပထမဆုံး Register ပြုလုပ်သည့် အကောင့်သည်လည်း အလိုအလျောက် Admin ဖြစ်လာပါသည်)*

---

## 📱 Flutter Mobile App နှင့် ချိတ်ဆက်ခြင်း (Mobile App Connection)

မိုဘိုင်းဖုန်း App (`pontpont_mobile`) ကို သင်၏ Bluehost Backend နှင့် ချိတ်ဆက်ရန် -
1. `pontpont_mobile/lib/config/constants.dart` ဖိုင်ကို ဖွင့်ပါ။
2. `publicBaseUrl` ကို သင့် Bluehost ဒိုမိန်းသို့ ပြောင်းလဲပေးပါ -
   ```dart
   static const String publicBaseUrl = 'https://yourdomain.com/api/shop.php';
   ```
3. `api/shop.php` တွင် CORS Headers နှင့် JSON Output အပြည့်အစုံ ထည့်သွင်းထားပြီးဖြစ်၍ Android / iOS ဖုန်းများမှ အဆင်ပြေချောမွေ့စွာ အော်ဒါတင်ခြင်း၊ အကောင့်ဝင်ခြင်းများ ဆောင်ရွက်နိုင်ပါသည်။

---

## 🛡️ လုံခြုံရေး သတိပြုရန် (Security Notice)

Database setup အားလုံး ပြီးမြောက်သွားပါက -
1. `install.php` ဖိုင်အား လုံခြုံရေးအရ ဖျက်ပစ်ပါ (သို့မဟုတ် အခြားအမည်သို့ ပြောင်းထားပါ)။
2. Admin Dashboard သို့ ဝင်ရောက်ပြီး Admin Password ကို မိမိစိတ်ကြိုက် အသစ်ပြောင်းလဲပေးပါ။

# ပုံ့ပုံ့ အိတ်ဆိုင် (Pont Pont Handbag Shop) - Bluehost PHP & MySQL Web Package

ဤ Folder သည် **Bluehost Web Hosting** (cPanel / Apache / PHP / MySQL & phpMyAdmin) တွင် တိုက်ရိုက်တင်ကာ အသုံးပြုနိုင်ရန် ရေးသားထားသော မူလ Pont Pont Handbag Shop စနစ် အပြည့်အစုံ ဖြစ်ပါသည်။

---

## 📁 ဖိုဒါနှင့် ဖိုင်များ တည်ဆောက်ပုံ (Folder Structure)

```text
php/
├── index.php                 # မူလ စတိုးဆိုင် Web Frontend + Cart + Checkout + Admin Dashboard UI
├── install.php               # Browser မှ ၁ ချက်နှိပ်ရုံဖြင့် Database သွင်းပေးသည့် 1-Click Installer
├── .htaccess                 # Bluehost Apache အတွက် Clean URLs, CORS, Gzip နှင့် လုံခြုံရေး ဆက်တင်
├── config/
│   └── db.php                # Bluehost MySQL Database ချိတ်ဆက်မှု Configuration ဖိုင် (PDO)
├── api/
│   ├── shop.php              # Full REST API (Next.js & Flutter Mobile App နှင့် 100% ကိုက်ညီသော API)
│   └── .htaccess             # API Clean URL Routing & CORS Headers
└── database/
    └── pontpont_db.sql       # phpMyAdmin တွင် တိုက်ရိုက် Import ပြုလုပ်နိုင်သော Database Dump
```

---

## 🚀 Bluehost Web Hosting တွင် တင်သွင်းအသုံးပြုနည်း (Step-by-Step Guide)

### အဆင့် (၁) - ဖိုင်များ Upload တင်ခြင်း (Upload Files)
1. Bluehost cPanel သို့ ဝင်ရောက်ပါ။
2. **File Manager** ကို ဖွင့်ပြီး `public_html` ဖိုဒါထဲသို့ သွားပါ (Subdomain တွင် တင်လိုပါက သက်ဆိုင်ရာ Subdomain ဖိုဒါထဲသို့ သွားပါ)။
3. ဤ `php` ဖိုဒါထဲရှိ ဖိုင်အားလုံး (သို့မဟုတ် `php.zip` ပြုလုပ်၍) ကို `public_html` ထဲသို့ Upload တင်ပြီး Extract ဖြည်ပေးပါ။

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
3. စက္ကန့်ပိုင်းအတွင်း Tables များနှင့် မူလကုန်ပစ္စည်း (၈) မျိုး၊ ဆိုင်ခွဲအချက်အလက်များကို အလိုအလျောက် ထည့်သွင်းပေးသွားမည်ဖြစ်ပြီး `config/db.php` ကိုပါ တစ်ခါတည်း ပြင်ဆင်ပေးသွားပါမည်။

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

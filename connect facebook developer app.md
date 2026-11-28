# Pont Pont Handbag Shop — Facebook Developer App ချိတ်ဆက်နည်းလမ်းညွှန်
(Meta Facebook Messenger Webhook & AI Live Chat Integration Guide)

ဤလမ်းညွှန်ချက်သည် **Pont Pont Handbag Shop (မန္တလေး)** ၏ AI Live Chatbot ကို Facebook Page Messenger Auto-Reply အဖြစ် ချိတ်ဆက်အသုံးပြုရန် အဆင့်ဆင့် လုပ်ဆောင်ချက်များ ဖြစ်ပါသည်။

---

## ၁။ လိုအပ်သော ချိတ်ဆက်မှု အချက်အလက်များ (Webhook Credentials)

- **Webhook Callback URL:** `https://fgk.ije.mybluehost.me/api/webhook_facebook.php`
  *(သို့မဟုတ် Cloudflare WAF မပိတ်ထားပါက: `https://pontpontbagshop.com/api/webhook_facebook.php`)*
- **Verify Token:** `pontpont_fb_verify_2026`
- **Subscribed Fields:** `messages`, `messaging_postbacks`

---

## ၂။ Meta for Developers တွင် အဆင့်ဆင့် လုပ်ဆောင်ရန်

### အဆင့် (၁) — Meta App အသစ်တစ်ခု ဖန်တီးခြင်း
1. [developers.facebook.com](https://developers.facebook.com) သို့သွား၍ မိမိ Facebook Account ဖြင့် Log in ဝင်ပါ။
2. ညာဘက်အပေါ်ထောင့်ရှိ **"My Apps"** > **"Create App"** ကို နှိပ်ပါ။
3. App Type ရွေးချယ်ရာတွင် **"Other"** သို့မဟုတ် **"Business"** ကို ရွေးချယ်ပြီး **Next** နှိပ်ပါ။
4. App Name တွင် `Pont Pont Handbag Bot` ဟု အမည်ပေးပြီး **Create App** ကို နှိပ်ပါ။

---

### အဆင့် (၂) — Messenger Product ထည့်သွင်းခြင်း
1. App Dashboard သို့ ရောက်ရှိပါက **Add products to your app** ကဏ္ဍသို့ သွားပါ။
2. **"Messenger"** ကို ရှာဖွေပြီး **"Set Up"** ကို နှိပ်ပါ။
3. ဘယ်ဘက် Sidebar Menu ရှိ **Messenger** > **Settings** (သို့မဟုတ် App Settings) သို့ သွားပါ။

---

### အဆင့် (၃) — Webhook Callback URL နှင့် Fields များ ချိတ်ဆက်ခြင်း
1. Messenger Settings ရှိ **Webhooks** ကဏ္ဍတွင် **"Add Callback URL"** ကို နှိပ်ပါ။
2. အောက်ပါ အချက်အလက်များကို ဖြည့်သွင်းပါ:
   - **Callback URL:** `https://fgk.ije.mybluehost.me/api/webhook_facebook.php`
   - **Verify Token:** `pontpont_fb_verify_2026`
3. **"Verify and Save"** ခလုတ်ကို နှိပ်ပါ။ *(ဆာဗာမှ Token ကို အလိုအလျောက် အတည်ပြုပေးပါမည်)*
4. အတည်ပြုပြီးပါက Webhooks ကဏ္ဍအောက်ရှိ **"Add Subscriptions"** (သို့မဟုတ် Edit) ကို နှိပ်၍:
   - `messages`
   - `messaging_postbacks`
   
   အကွက် (၂) ခုကို အမှန်ခြစ် (Subscribe) လုပ်ပေးပါ။

---

### အဆင့် (၄) — Facebook Page ချိတ်ဆက်၍ Page Access Token ရယူခြင်း
1. Messenger Settings စာမျက်နှာအောက်ရှိ **"Access Tokens"** (သို့မဟုတ် Select a Page) ကဏ္ဍသို့ သွားပါ။
2. **"Add or remove Pages"** နှိပ်၍ မိမိ၏ **Pont Pont Facebook Page** ကို ရွေးချယ် ချိတ်ဆက်ပါ။
3. Page ချိတ်ဆက်ပြီးပါက အဆိုပါ Page ဘေးရှိ **"Generate Token"** ခလုတ်ကို နှိပ်ပါ။
4. "I Understand" ကို အမှန်ခြစ်ပြီး ထွက်ပေါ်လာသော ရှည်လျားသည့် **Page Access Token (EAA...)** ကို **Copy** ကူးယူပါ။
5. ဆိုင်၏ **Admin Dashboard** (`https://fgk.ije.mybluehost.me/admin.php` သို့မဟုတ် `https://pontpontbagshop.com/admin.php`) သို့ သွားပါ။
6. ဘယ်ဘက် Menu ရှိ **"AI Chatbot"** Pane သို့ သွားပြီး **"Facebook Page Auto Messaging"** ကဏ္ဍရှိ:
   - **"Facebook Auto-Reply ဖွင့်မည်"** ကို အမှန်ခြစ်ပါ။
   - **"Facebook Page Access Token"** အကွက်တွင် Copy ကူးလာသော Token ကို ထည့်ပါ။
   - **"ချိတ်ဆက်မှု စစ်ဆေးမည်"** ကို နှိပ်၍ အောင်မြင်ကြောင်း စစ်ဆေးပါ။
   - အောက်ဆုံးရှိ **"AI Chatbot ဆက်တင်များ သိမ်းဆည်းမည်"** ခလုတ်ကို နှိပ်လိုက်ပါ။

---

## ၃။ အလုပ်လုပ်ပုံနှင့် စမ်းသပ်စစ်ဆေးခြင်း

- ချိတ်ဆက်ပြီးပါက မည်သည့် Customer မဆို Facebook Page Messenger သို့ မက်ဆေ့ခ်ျ ပို့လိုက်သည်နှင့် AI Bot က ၂၄ နာရီ ချက်ချင်း အလိုအလျောက် ပြန်လည်ဖြေကြားပေးမည် ဖြစ်ပါသည်။
- အိတ်ပုံ၊ ဒီဇိုင်း သို့မဟုတ် ဈေးနှုန်းများ မေးမြန်းပါက ဆိုင်၏ MySQL Database မှ အိတ်ပုံများ၊ ဈေးနှုန်းများနှင့် ဆိုင်လင့်ခ်များပါဝင်သော Slide ကတ်များ (Carousel) ဖြင့် ပေးပို့ပေးပါမည်။

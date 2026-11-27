<?php
/**
 * Pont Pont Handbag Shop (ပုံ့ပုံ့ အိတ်ဆိုင် - မန္တလေး)
 * Dedicated User Profile, Address & Order History Page
 */

require_once __DIR__ . '/config/db.php';

try {
    $pdo = getDb();
    $pdo->query("SELECT 1 FROM users LIMIT 1");
} catch (Exception $e) {
    header("Location: install.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="my" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Pont Pont Handbag Shop</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Padauk:wght@400;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Padauk', sans-serif;
            background-color: #faf9f6;
            color: #1c1917;
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f5f5f4; }
        ::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a29e; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200/80 shadow-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">
            <a href="index.php" class="flex items-center gap-3 group">
                <div id="brandLogoContainer" class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-white border border-stone-200 text-amber-600 flex items-center justify-center shadow-xs group-hover:scale-105 transition overflow-hidden p-1">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-amber-600"></i>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-lg sm:text-xl font-extrabold tracking-tight text-stone-900">Pont Pont</span>
                        <span class="text-[10px] sm:text-xs bg-amber-100 text-amber-900 font-bold px-2 py-0.5 rounded-md">Profile</span>
                    </div>
                    <p class="text-[11px] text-stone-500 font-medium hidden sm:block">ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး</p>
                </div>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="index.php" class="px-3.5 py-2 rounded-xl border border-stone-200 bg-stone-50 hover:bg-stone-100 text-stone-800 text-xs font-bold transition flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>ဆိုင်သို့ ပြန်သွားမည်</span>
                </a>
                <a id="adminBtn" href="admin.php" class="hidden px-3.5 py-2 rounded-xl bg-stone-900 hover:bg-black text-amber-400 text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Admin</span>
                </a>
                <button id="logoutBtn" onclick="handleLogout()" class="hidden px-3.5 py-2 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold transition flex items-center gap-1.5">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">ထွက်မည်</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

        <!-- Loading State -->
        <div id="loadingState" class="py-24 text-center space-y-3">
            <div class="w-10 h-10 border-4 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="text-xs text-stone-500 font-semibold">အချက်အလက်များ ဖတ်ယူနေပါသည်...</p>
        </div>

        <!-- Guest / Not Logged In State -->
        <div id="guestState" class="hidden max-w-md mx-auto my-12 bg-white rounded-3xl p-8 border border-stone-200 shadow-lg text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto">
                <i data-lucide="lock" class="w-8 h-8"></i>
            </div>
            <h2 class="text-xl font-extrabold text-stone-900">အကောင့်ဝင်ရန် လိုအပ်ပါသည်</h2>
            <p class="text-xs text-stone-600 leading-relaxed">
                သင့်ကိုယ်ရေးအချက်အလက်၊ ပို့ဆောင်ရန်လိပ်စာနှင့် မှာယူထားသော အော်ဒါမှတ်တမ်းများကို ကြည့်ရှုရန် ကျေးဇူးပြု၍ အကောင့်ဝင်ပေးပါ။
            </p>
            <a href="index.php" class="inline-flex items-center justify-center gap-2 w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-5 rounded-xl text-xs transition shadow-md shadow-amber-600/20">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>ပင်မစာမျက်နှာသို့ သွား၍ အကောင့်ဝင်မည်</span>
            </a>
        </div>

        <!-- Authenticated Profile Content -->
        <div id="profileContent" class="hidden space-y-6">

            <!-- Profile Hero Card -->
            <section class="bg-white rounded-3xl border border-stone-200/90 shadow-xs overflow-hidden">
                <div class="h-24 sm:h-32 bg-gradient-to-r from-stone-900 via-stone-800 to-amber-950 relative">
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#d4a353_1px,transparent_1px)] [background-size:16px_16px]"></div>
                </div>
                <div class="px-5 sm:px-8 pb-6">
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-6 -mt-12 sm:-mt-14 relative z-10">
                        <!-- Avatar -->
                        <div class="relative group self-start">
                            <div id="avatarWrapper" class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-amber-100 border-4 border-white shadow-lg overflow-hidden flex items-center justify-center text-amber-800 font-extrabold text-3xl">
                                <img id="avatarPreviewImg" src="" alt="Profile Photo" class="w-full h-full object-cover hidden">
                                <span id="avatarInitials">P</span>
                            </div>
                            <button type="button" onclick="document.getElementById('avatarFileInput').click()" title="ပုံတင်ရန် / ပြောင်းရန်" class="absolute -bottom-1 -right-1 w-9 h-9 rounded-xl bg-amber-600 hover:bg-amber-700 text-white shadow-md flex items-center justify-center border-2 border-white transition cursor-pointer">
                                <i data-lucide="camera" class="w-4 h-4"></i>
                            </button>
                            <input type="file" id="avatarFileInput" accept="image/jpeg,image/png,image/webp" onchange="handleAvatarUpload(event)" class="hidden">
                        </div>

                        <!-- User Identity -->
                        <div class="flex-1 min-w-0 pt-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 id="profileDisplayName" class="text-xl sm:text-2xl font-extrabold text-stone-900 truncate"></h1>
                                <span id="profileRoleBadge" class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800"></span>
                            </div>
                            <p id="profileDisplayEmail" class="text-xs text-stone-500 mt-0.5"></p>
                            <div class="flex flex-wrap items-center gap-3 mt-2 text-[11px] text-stone-500">
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-amber-600"></i>
                                    <span id="profileDisplayPhone">-</span>
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-amber-600"></i>
                                    <span id="profileDisplayCity">-</span>
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-amber-600"></i>
                                    <span id="profileMemberSince">-</span>
                                </span>
                            </div>
                        </div>

                        <!-- Remove Avatar Action -->
                        <div class="flex items-center gap-2 self-start sm:self-end">
                            <button type="button" id="removeAvatarBtn" onclick="handleRemoveAvatar()" class="hidden text-xs text-stone-500 hover:text-red-600 font-semibold px-3 py-1.5 rounded-xl border border-stone-200 hover:border-red-200 hover:bg-red-50 transition flex items-center gap-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>ပုံဖျက်မည်</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-3 gap-3 mt-6 pt-5 border-t border-stone-100">
                        <div class="bg-stone-50 rounded-2xl p-3 sm:p-4 border border-stone-200/60">
                            <p class="text-[11px] text-stone-500 font-semibold">စုစုပေါင်း အော်ဒါ</p>
                            <p id="statTotalOrders" class="text-lg sm:text-xl font-extrabold text-stone-900 mt-0.5">0</p>
                        </div>
                        <div class="bg-emerald-50/70 rounded-2xl p-3 sm:p-4 border border-emerald-200/60">
                            <p class="text-[11px] text-emerald-700 font-semibold">ပို့ဆောင်ပြီး (Delivered)</p>
                            <p id="statDeliveredOrders" class="text-lg sm:text-xl font-extrabold text-emerald-800 mt-0.5">0</p>
                        </div>
                        <div class="bg-amber-50/70 rounded-2xl p-3 sm:p-4 border border-amber-200/60">
                            <p class="text-[11px] text-amber-800 font-semibold">စုစုပေါင်း ဝယ်ယူငွေ</p>
                            <p id="statTotalSpent" class="text-lg sm:text-xl font-extrabold text-amber-900 mt-0.5">0 Ks</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Navigation Tabs -->
            <div class="flex flex-wrap gap-2 bg-white p-1.5 rounded-2xl border border-stone-200 shadow-xs">
                <button id="tabBtnOrders" onclick="switchProfileTab('orders')" class="flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-stone-900 text-white">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>တင်ခဲ့သော အော်ဒါများ</span>
                </button>
                <button id="tabBtnInfo" onclick="switchProfileTab('info')" class="flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 text-stone-600 hover:bg-stone-100">
                    <i data-lucide="user-cog" class="w-4 h-4"></i>
                    <span>ကိုယ်ရေးအချက်အလက်</span>
                </button>
                <button id="tabBtnAddress" onclick="switchProfileTab('address')" class="flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 text-stone-600 hover:bg-stone-100">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>ပို့ဆောင်ရန် လိပ်စာ</span>
                </button>
            </div>

            <!-- TAB 1: MY ORDERS -->
            <section id="tabPanelOrders" class="space-y-4">
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-stone-200 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base sm:text-lg font-extrabold text-stone-900 flex items-center gap-2">
                                <i data-lucide="shopping-bag" class="w-5 h-5 text-amber-600"></i>
                                <span>မှာယူခဲ့သော အော်ဒါမှတ်တမ်းများ (Order History)</span>
                            </h2>
                            <p class="text-xs text-stone-500 mt-0.5">သင့်အကောင့်ဖြင့် တင်ခဲ့သော အော်ဒါများ၏ အခြေအနေကို စစ်ဆေးနိုင်ပါသည်။</p>
                        </div>
                    </div>

                    <!-- Status Filter Pills -->
                    <div id="orderStatusFilters" class="flex flex-wrap gap-2 pb-2 border-b border-stone-100">
                        <button onclick="filterOrders('All')" data-status="All" class="order-filter-btn px-3 py-1.5 rounded-full text-xs font-bold bg-amber-600 text-white transition">အားလုံး (All)</button>
                        <button onclick="filterOrders('Pending')" data-status="Pending" class="order-filter-btn px-3 py-1.5 rounded-full text-xs font-bold bg-stone-100 text-stone-700 hover:bg-stone-200 transition">Pending</button>
                        <button onclick="filterOrders('Confirmed')" data-status="Confirmed" class="order-filter-btn px-3 py-1.5 rounded-full text-xs font-bold bg-stone-100 text-stone-700 hover:bg-stone-200 transition">Confirmed</button>
                        <button onclick="filterOrders('Shipped')" data-status="Shipped" class="order-filter-btn px-3 py-1.5 rounded-full text-xs font-bold bg-stone-100 text-stone-700 hover:bg-stone-200 transition">Shipped</button>
                        <button onclick="filterOrders('Delivered')" data-status="Delivered" class="order-filter-btn px-3 py-1.5 rounded-full text-xs font-bold bg-stone-100 text-stone-700 hover:bg-stone-200 transition">Delivered</button>
                        <button onclick="filterOrders('Cancelled')" data-status="Cancelled" class="order-filter-btn px-3 py-1.5 rounded-full text-xs font-bold bg-stone-100 text-stone-700 hover:bg-stone-200 transition">Cancelled</button>
                    </div>

                    <!-- Orders Container -->
                    <div id="ordersList" class="space-y-4"></div>
                </div>
            </section>

            <!-- TAB 2: PERSONAL INFO & PASSWORD -->
            <section id="tabPanelInfo" class="hidden">
                <form onsubmit="handleSaveInfo(event)" class="bg-white rounded-3xl p-5 sm:p-8 border border-stone-200 shadow-xs space-y-6">
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-stone-900">ကိုယ်ရေးအချက်အလက်နှင့် လုံခြုံရေး</h2>
                        <p class="text-xs text-stone-500 mt-0.5">အမည်၊ ဖုန်းနံပါတ်နှင့် စကားဝှက်များကို ပြင်ဆင်နိုင်ပါသည်။</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-stone-700 mb-1.5">အမည် (Full Name) *</label>
                            <input type="text" id="inputName" required maxlength="80" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-stone-700 mb-1.5">ဖုန်းနံပါတ် (Phone) *</label>
                            <input type="tel" id="inputPhone" required maxlength="30" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-stone-700 mb-1.5">အီးမေးလ် (Email)</label>
                            <input type="email" id="inputEmail" disabled class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-100 text-stone-500 cursor-not-allowed">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-stone-100 space-y-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-stone-900">စကားဝှက် ပြောင်းလဲရန် (Optional)</h3>
                            <p class="text-[11px] text-stone-500">စကားဝှက် မပြောင်းလိုပါက အောက်ပါအကွက်များကို အလွတ်ထားပါ။</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                            <div>
                                <label class="block font-bold text-stone-700 mb-1.5">လက်ရှိစကားဝှက်</label>
                                <input type="password" id="inputCurrentPassword" placeholder="••••••••" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-stone-700 mb-1.5">စကားဝှက်အသစ်</label>
                                <input type="password" id="inputNewPassword" placeholder="အနည်းဆုံး ၆ လုံး" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-stone-700 mb-1.5">စကားဝှက်အသစ် အတည်ပြုရန်</label>
                                <input type="password" id="inputConfirmPassword" placeholder="ထပ်မံရိုက်ထည့်ပါ" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs transition shadow-md shadow-amber-600/20 flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>အချက်အလက် သိမ်းဆည်းမည်</span>
                        </button>
                    </div>
                </form>
            </section>

            <!-- TAB 3: DELIVERY ADDRESS -->
            <section id="tabPanelAddress" class="hidden">
                <form onsubmit="handleSaveAddress(event)" class="bg-white rounded-3xl p-5 sm:p-8 border border-stone-200 shadow-xs space-y-5">
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-stone-900">ပို့ဆောင်ရန် လိပ်စာ (Delivery Address)</h2>
                        <p class="text-xs text-stone-500 mt-0.5">ဤနေရာတွင် သိမ်းဆည်းထားသော လိပ်စာသည် ဈေးဝယ်ယူသည့်အခါ (Checkout) တွင် အလိုအလျောက် ဖြည့်သွင်းပေးပါမည်။</p>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-stone-700 mb-1.5">မြို့နယ် / ပို့ဆောင်မည့်ဇုန် (City / Delivery Zone) *</label>
                            <select id="inputCitySelect" onchange="toggleCustomCityInput()" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                <option value="Mandalay (မန္တလေး - မြို့တွင်း)">Mandalay (မန္တလေး - မြို့တွင်း)</option>
                                <option value="Pyin Oo Lwin (ပြင်ဦးလွင်)">Pyin Oo Lwin (ပြင်ဦးလွင်)</option>
                                <option value="Sagaing / Monywa (စစ်ကိုင်း / မုံရွာ)">Sagaing / Monywa (စစ်ကိုင်း / မုံရွာ)</option>
                                <option value="Yangon (ရန်ကုန်)">Yangon (ရန်ကုန်)</option>
                                <option value="Naypyidaw (နေပြည်တော်)">Naypyidaw (နေပြည်တော်)</option>
                                <option value="Taunggyi (တောင်ကြီး)">Taunggyi (တောင်ကြီး)</option>
                                <option value="Magway / Pakokku (မကွေး / ပခုက္ကူ)">Magway / Pakokku (မကွေး / ပခုက္ကူ)</option>
                                <option value="Other Cities / ကားဂိတ်တင် (အခြားမြို့များ)">Other Cities / ကားဂိတ်တင် (အခြားမြို့များ)</option>
                                <option value="__custom__">အခြားမြို့အမည် ကိုယ်တိုင်ရိုက်ထည့်မည် (Custom)</option>
                            </select>
                        </div>

                        <div id="customCityWrapper" class="hidden">
                            <label class="block font-bold text-stone-700 mb-1.5">မြို့အမည် ရိုက်ထည့်ပါ *</label>
                            <input type="text" id="inputCustomCity" placeholder="ဥပမာ - ပဲခူး / မော်လမြိုင်" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-stone-700 mb-1.5">အသေးစိတ် လိပ်စာ (အိမ်နံပါတ်၊ လမ်း၊ ရပ်ကွက်/မြို့နယ်) *</label>
                            <textarea id="inputAddress" rows="3" required maxlength="250" placeholder="အမှတ် (၁၂)၊ ၇၃ လမ်း၊ ၃၆×၃၇ လမ်းကြား၊ ချမ်းအေးသာစံမြို့နယ်" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs transition shadow-md shadow-amber-600/20 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            <span>လိပ်စာ သိမ်းဆည်းမည်</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <!-- Slip Preview Lightbox Modal -->
    <div id="slipModal" onclick="closeSlipModal()" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative max-w-lg w-full bg-white rounded-2xl p-2 shadow-2xl" onclick="event.stopPropagation()">
            <button onclick="closeSlipModal()" class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-stone-900 text-white flex items-center justify-center shadow-lg hover:bg-black">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
            <img id="slipModalImg" src="" alt="Payment Slip" class="w-full max-h-[80vh] object-contain rounded-xl">
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-5 right-5 z-50 hidden bg-stone-900 text-white text-xs font-bold px-4 py-3 rounded-2xl shadow-xl border border-stone-700 flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4 text-amber-400"></i>
        <span id="toastText"></span>
    </div>

    <script>
        let profileData = {
            currentUser: null,
            orders: []
        };
        let activeOrderFilter = 'All';

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            const text = document.getElementById('toastText');
            text.innerText = message;
            toast.className = `fixed bottom-5 right-5 z-50 text-xs font-bold px-4 py-3 rounded-2xl shadow-xl border flex items-center gap-2 ${
                isError ? 'bg-red-700 text-white border-red-500' : 'bg-stone-900 text-white border-stone-700'
            }`;
            toast.classList.remove('hidden');
            clearTimeout(window.__toastTimer);
            window.__toastTimer = setTimeout(() => {
                toast.classList.add('hidden');
            }, 3500);
        }

        async function loadProfileData() {
            try {
                const res = await fetch('api/shop.php');
                const data = await res.json();
                profileData.currentUser = data.currentUser || null;
                profileData.orders = data.orders || [];

                document.getElementById('loadingState').classList.add('hidden');

                if (!profileData.currentUser) {
                    document.getElementById('guestState').classList.remove('hidden');
                    lucide.createIcons();
                    return;
                }

                document.getElementById('profileContent').classList.remove('hidden');
                document.getElementById('logoutBtn').classList.remove('hidden');
                if (profileData.currentUser.role === 'admin') {
                    document.getElementById('adminBtn').classList.remove('hidden');
                }

                renderProfileHeader();
                populateForms();
                renderOrders();
                lucide.createIcons();
            } catch (err) {
                document.getElementById('loadingState').innerHTML = `<p class="text-xs text-red-600 font-bold">ဒေတာ ဖတ်ယူရာတွင် အမှားဖြစ်ပေါ်နေပါသည်။</p>`;
            }
        }

        function renderProfileHeader() {
            const u = profileData.currentUser;
            document.getElementById('profileDisplayName').innerText = u.name || 'Customer';
            document.getElementById('profileDisplayEmail').innerText = u.email || '';
            document.getElementById('profileDisplayPhone').innerText = u.phone || '-';
            document.getElementById('profileDisplayCity').innerText = u.city || '-';

            const roleBadge = document.getElementById('profileRoleBadge');
            roleBadge.innerText = u.role === 'admin' ? 'Shop Admin' : 'Verified Customer';

            if (u.createdAt) {
                const d = new Date(u.createdAt);
                document.getElementById('profileMemberSince').innerText = isNaN(d.getTime())
                    ? u.createdAt
                    : 'Joined ' + d.toLocaleDateString('en-GB', { year: 'numeric', month: 'short', day: 'numeric' });
            }

            // Avatar
            const img = document.getElementById('avatarPreviewImg');
            const initials = document.getElementById('avatarInitials');
            const removeBtn = document.getElementById('removeAvatarBtn');
            if (u.avatar) {
                img.src = u.avatar;
                img.classList.remove('hidden');
                initials.classList.add('hidden');
                removeBtn.classList.remove('hidden');
            } else {
                img.classList.add('hidden');
                initials.classList.remove('hidden');
                initials.innerText = (u.name || 'P').trim().charAt(0).toUpperCase();
                removeBtn.classList.add('hidden');
            }

            // Stats
            const orders = profileData.orders || [];
            const delivered = orders.filter(o => o.status === 'Delivered').length;
            const totalSpent = orders
                .filter(o => o.status !== 'Cancelled')
                .reduce((sum, o) => sum + (Number(o.total) || 0), 0);

            document.getElementById('statTotalOrders').innerText = orders.length;
            document.getElementById('statDeliveredOrders').innerText = delivered;
            document.getElementById('statTotalSpent').innerText = totalSpent.toLocaleString() + ' Ks';
        }

        function populateForms() {
            const u = profileData.currentUser;
            document.getElementById('inputName').value = u.name || '';
            document.getElementById('inputPhone').value = u.phone || '';
            document.getElementById('inputEmail').value = u.email || '';
            document.getElementById('inputAddress').value = u.address || '';

            const citySelect = document.getElementById('inputCitySelect');
            const customWrapper = document.getElementById('customCityWrapper');
            const customInput = document.getElementById('inputCustomCity');
            const options = Array.from(citySelect.options).map(o => o.value);

            if (u.city && options.includes(u.city)) {
                citySelect.value = u.city;
                customWrapper.classList.add('hidden');
                customInput.value = '';
            } else if (u.city) {
                citySelect.value = '__custom__';
                customWrapper.classList.remove('hidden');
                customInput.value = u.city;
            }
        }

        function toggleCustomCityInput() {
            const val = document.getElementById('inputCitySelect').value;
            document.getElementById('customCityWrapper').classList.toggle('hidden', val !== '__custom__');
        }

        function switchProfileTab(tab) {
            const tabs = {
                orders: { btn: 'tabBtnOrders', panel: 'tabPanelOrders' },
                info: { btn: 'tabBtnInfo', panel: 'tabPanelInfo' },
                address: { btn: 'tabBtnAddress', panel: 'tabPanelAddress' }
            };
            Object.keys(tabs).forEach(k => {
                const isActive = k === tab;
                const btn = document.getElementById(tabs[k].btn);
                const panel = document.getElementById(tabs[k].panel);
                panel.classList.toggle('hidden', !isActive);
                btn.className = isActive
                    ? 'flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-stone-900 text-white'
                    : 'flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 text-stone-600 hover:bg-stone-100';
            });
        }

        async function handleAvatarUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                showToast('ပုံအရွယ်အစားသည် 5MB ထက် မကျော်ရပါ', true);
                event.target.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('action', 'UPLOAD_AVATAR');
            formData.append('userId', profileData.currentUser.id);
            formData.append('avatar', file);

            try {
                showToast('ပုံတင်နေပါသည်...');
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.ok) {
                    profileData.currentUser = data.user;
                    renderProfileHeader();
                    showToast('Profile ပုံ တင်ပြီးပါပြီ ✓');
                } else {
                    showToast(data.error || 'ပုံတင်မှု မအောင်မြင်ပါ', true);
                }
            } catch (err) {
                showToast('ဆာဗာ ချိတ်ဆက်မှု မအောင်မြင်ပါ', true);
            } finally {
                event.target.value = '';
            }
        }

        async function handleRemoveAvatar() {
            if (!confirm('Profile ပုံကို ဖျက်လိုပါသလား?')) return;
            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'REMOVE_AVATAR',
                        userId: profileData.currentUser.id
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    profileData.currentUser = data.user;
                    renderProfileHeader();
                    showToast('Profile ပုံကို ဖျက်ပြီးပါပြီ');
                } else {
                    showToast(data.error || 'ဖျက်မှု မအောင်မြင်ပါ', true);
                }
            } catch (err) {
                showToast('ဆာဗာ ချိတ်ဆက်မှု မအောင်မြင်ပါ', true);
            }
        }

        async function handleSaveInfo(e) {
            e.preventDefault();
            const name = document.getElementById('inputName').value.trim();
            const phone = document.getElementById('inputPhone').value.trim();
            const currentPassword = document.getElementById('inputCurrentPassword').value;
            const newPassword = document.getElementById('inputNewPassword').value;
            const confirmPassword = document.getElementById('inputConfirmPassword').value;

            if (newPassword || confirmPassword) {
                if (newPassword.length < 6) {
                    showToast('စကားဝှက်အသစ်သည် အနည်းဆုံး ၆ လုံး ရှိရပါမည်', true);
                    return;
                }
                if (newPassword !== confirmPassword) {
                    showToast('စကားဝှက်အသစ် နှစ်ခု တူညီမှု မရှိပါ', true);
                    return;
                }
                if (!currentPassword) {
                    showToast('လက်ရှိစကားဝှက်ကို ထည့်သွင်းပေးပါ', true);
                    return;
                }
            }

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'UPDATE_PROFILE',
                        userId: profileData.currentUser.id,
                        name,
                        phone,
                        city: profileData.currentUser.city,
                        address: profileData.currentUser.address,
                        currentPassword,
                        newPassword
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    profileData.currentUser = data.user;
                    document.getElementById('inputCurrentPassword').value = '';
                    document.getElementById('inputNewPassword').value = '';
                    document.getElementById('inputConfirmPassword').value = '';
                    renderProfileHeader();
                    showToast('ကိုယ်ရေးအချက်အလက်များ သိမ်းဆည်းပြီးပါပြီ ✓');
                } else {
                    showToast(data.error || 'သိမ်းဆည်းမှု မအောင်မြင်ပါ', true);
                }
            } catch (err) {
                showToast('ဆာဗာ ချိတ်ဆက်မှု မအောင်မြင်ပါ', true);
            }
        }

        async function handleSaveAddress(e) {
            e.preventDefault();
            const citySelect = document.getElementById('inputCitySelect').value;
            const customCity = document.getElementById('inputCustomCity').value.trim();
            const city = citySelect === '__custom__' ? customCity : citySelect;
            const address = document.getElementById('inputAddress').value.trim();

            if (!city || !address) {
                showToast('မြို့နယ်နှင့် လိပ်စာအပြည့်အစုံ ဖြည့်သွင်းပေးပါ', true);
                return;
            }

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'UPDATE_PROFILE',
                        userId: profileData.currentUser.id,
                        name: profileData.currentUser.name,
                        phone: profileData.currentUser.phone,
                        city,
                        address
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    profileData.currentUser = data.user;
                    renderProfileHeader();
                    showToast('ပို့ဆောင်ရန် လိပ်စာ သိမ်းဆည်းပြီးပါပြီ ✓');
                } else {
                    showToast(data.error || 'သိမ်းဆည်းမှု မအောင်မြင်ပါ', true);
                }
            } catch (err) {
                showToast('ဆာဗာ ချိတ်ဆက်မှု မအောင်မြင်ပါ', true);
            }
        }

        function filterOrders(status) {
            activeOrderFilter = status;
            document.querySelectorAll('.order-filter-btn').forEach(btn => {
                const isActive = btn.dataset.status === status;
                btn.className = isActive
                    ? 'order-filter-btn px-3 py-1.5 rounded-full text-xs font-bold bg-amber-600 text-white transition'
                    : 'order-filter-btn px-3 py-1.5 rounded-full text-xs font-bold bg-stone-100 text-stone-700 hover:bg-stone-200 transition';
            });
            renderOrders();
        }

        function renderOrders() {
            const container = document.getElementById('ordersList');
            const allOrders = profileData.orders || [];
            const filtered = activeOrderFilter === 'All'
                ? allOrders
                : allOrders.filter(o => o.status === activeOrderFilter);

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="py-12 text-center space-y-2 border border-dashed border-stone-200 rounded-2xl">
                        <i data-lucide="package-open" class="w-10 h-10 text-stone-300 mx-auto"></i>
                        <p class="text-xs font-bold text-stone-500">ဤအခြေအနေတွင် အော်ဒါမှတ်တမ်း မရှိသေးပါ။</p>
                        <a href="index.php" class="inline-block text-xs text-amber-600 hover:underline font-bold">အိတ်များ သွားရောက်ကြည့်ရှုရန် →</a>
                    </div>
                `;
                lucide.createIcons();
                return;
            }

            const statusColors = {
                'Pending': 'bg-amber-100 text-amber-800 border-amber-200',
                'Confirmed': 'bg-blue-100 text-blue-800 border-blue-200',
                'Shipped': 'bg-purple-100 text-purple-800 border-purple-200',
                'Delivered': 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'Cancelled': 'bg-red-100 text-red-800 border-red-200'
            };

            container.innerHTML = filtered.map(o => {
                const dateObj = o.createdAt ? new Date(o.createdAt) : null;
                const dateStr = dateObj && !isNaN(dateObj.getTime())
                    ? dateObj.toLocaleString('en-GB', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
                    : escapeHtml(o.createdAt || '');

                const itemsHtml = (o.items || []).map(it => {
                    const name = escapeHtml(it.nameMm || it.nameEn || 'Handbag');
                    const color = it.selectedColor ? `<span class="text-stone-400">(${escapeHtml(it.selectedColor)})</span>` : '';
                    const qty = Number(it.quantity) || 1;
                    const price = Number(it.price) || 0;
                    return `
                        <div class="flex items-center justify-between py-1.5 border-b border-stone-100 last:border-0 text-xs">
                            <div class="min-w-0 pr-2">
                                <span class="font-semibold text-stone-800">${name}</span> ${color}
                                <span class="text-stone-500 ml-1">× ${qty}</span>
                            </div>
                            <span class="font-bold text-stone-900 flex-shrink-0">${(price * qty).toLocaleString()} Ks</span>
                        </div>
                    `;
                }).join('');

                const slipHtml = o.paymentSlip ? `
                    <button type="button" onclick="openSlipModal('${escapeHtml(o.paymentSlip)}')" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-lg border border-blue-200 transition">
                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                        <span>ငွေလွှဲပြေစာ ကြည့်ရန်</span>
                    </button>
                ` : '';

                return `
                    <div class="rounded-2xl border border-stone-200 bg-stone-50/40 p-4 sm:p-5 space-y-3 hover:border-amber-300/80 transition">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-stone-200/70">
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono text-xs sm:text-sm font-extrabold text-stone-900 bg-white px-2.5 py-1 rounded-lg border border-stone-200">${escapeHtml(o.id)}</span>
                                <span class="text-[11px] text-stone-500">${dateStr}</span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[11px] font-extrabold border ${statusColors[o.status] || 'bg-stone-100 text-stone-700 border-stone-200'}">${escapeHtml(o.status)}</span>
                        </div>

                        <div class="bg-white rounded-xl p-3 border border-stone-200/70">
                            ${itemsHtml}
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[11px] text-stone-600">
                            <div class="space-y-1">
                                <p><strong class="text-stone-800">ပို့ဆောင်မည့်လိပ်စာ:</strong> ${escapeHtml(o.address)}, ${escapeHtml(o.city)}</p>
                                <p><strong class="text-stone-800">ဖုန်း:</strong> ${escapeHtml(o.phone)}</p>
                                ${o.note ? `<p><strong class="text-stone-800">မှတ်ချက်:</strong> ${escapeHtml(o.note)}</p>` : ''}
                            </div>
                            <div class="space-y-1 sm:text-right">
                                <p><strong class="text-stone-800">ငွေချေစနစ်:</strong> ${escapeHtml(o.paymentMethod)} ${o.transactionRef ? `(Ref: ${escapeHtml(o.transactionRef)})` : ''}</p>
                                ${o.discount > 0 ? `<p class="text-emerald-700">လျှော့ဈေး ${o.couponCode ? `(${escapeHtml(o.couponCode)})` : ''}: -${Number(o.discount).toLocaleString()} Ks</p>` : ''}
                                <p>ပို့ဆောင်ခ: ${Number(o.deliveryFee) === 0 ? 'Free' : Number(o.deliveryFee).toLocaleString() + ' Ks'}</p>
                                <p class="text-sm font-extrabold text-stone-900 pt-0.5">စုစုပေါင်း: ${Number(o.total || 0).toLocaleString()} Ks</p>
                                <div class="pt-1">${slipHtml}</div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            lucide.createIcons();
        }

        function openSlipModal(url) {
            document.getElementById('slipModalImg').src = url;
            document.getElementById('slipModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeSlipModal() {
            document.getElementById('slipModal').classList.add('hidden');
        }

        async function handleLogout() {
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'LOGOUT' })
                });
            } catch (err) {}
            window.location.href = 'index.php';
        }

        document.addEventListener('DOMContentLoaded', loadProfileData);
    </script>
</body>
</html>

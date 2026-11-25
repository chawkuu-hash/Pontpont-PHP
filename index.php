<?php
/**
 * Pont Pont Handbag Shop (ပုံ့ပုံ့ အိတ်ဆိုင် - မန္တလေး)
 * Main Web Storefront for Bluehost Shared Hosting
 */

require_once __DIR__ . '/config/db.php';

// Safe check if database is set up
try {
    $pdo = getDb();
    // Test if products table exists
    $pdo->query("SELECT 1 FROM products LIMIT 1");
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
    <title>Pont Pont Handbag Shop | ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး (မန္တလေး)</title>
    <meta name="description" content="မန္တလေးမြို့၏ နာမည်ကျော် အမျိုးသမီးနှင့် အမျိုးသား လက်ကိုင်အိတ်၊ ဘေးလွယ်အိတ်၊ ကျောပိုးအိတ် လက်လီ/လက်ကား ရောင်းဝယ်ရေး">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            50: '#fdfbf7',
                            100: '#fbf7ee',
                            200: '#f5ebd3',
                            300: '#ebd8ad',
                            400: '#dfbf7e',
                            500: '#d4a353',
                            600: '#c58b38',
                            700: '#a46f2c',
                            800: '#855828',
                            900: '#6d4824',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Padauk:wght@400;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Padauk', sans-serif;
            background-color: #FAF7F2;
        }
        .theme-luxury-gold {
            --theme-bg: #FAF7F2;
            --theme-hero: linear-gradient(135deg, #1c1917, #451a03);
            --theme-accent: #d4a353;
        }
        .theme-rose-paris {
            --theme-bg: #FFF5F7;
            --theme-hero: linear-gradient(135deg, #881337, #be185d, #4c0519);
            --theme-accent: #f43f5e;
        }
        .theme-minimal-noir {
            --theme-bg: #F4F6F8;
            --theme-hero: linear-gradient(135deg, #09090b, #064e3b);
            --theme-accent: #10b981;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="text-stone-800 antialiased min-h-screen flex flex-col selection:bg-amber-200 selection:text-amber-900" id="appBody">

    <!-- Top Announcement Bar -->
    <div class="bg-stone-900 text-stone-200 text-xs py-2 px-4 border-b border-stone-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-2 overflow-hidden text-center sm:text-left">
                <span class="bg-amber-500 text-stone-950 font-extrabold text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider flex-shrink-0">Promotion</span>
                <span class="truncate">✨ PONTPONT10 ကူပွန်ဖြင့် 10% OFF • ၁ သိန်းအထက် Free Delivery • နေ့ချင်း ကားဂိတ်ပို့</span>
            </div>
            <div class="flex items-center gap-4 text-[11px] text-stone-400">
                <a href="tel:09963000502" class="hover:text-amber-400 transition flex items-center gap-1">
                    <i data-lucide="phone" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>09-963000502</span>
                </a>
                <span class="hidden md:inline">|</span>
                <span class="hidden md:inline">မန္တလေးမြို့ ဆိုင်ခွဲ (၃) ဆိုင်</span>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center gap-3 group flex-shrink-0">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-700 via-amber-600 to-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-600/20 group-hover:scale-105 transition">
                    <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xl font-extrabold tracking-tight text-stone-900">Pont Pont</span>
                        <span class="text-xs bg-amber-100 text-amber-900 font-bold px-2 py-0.5 rounded-md">Mandalay</span>
                    </div>
                    <p class="text-xs text-stone-500 font-medium">ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး</p>
                </div>
            </a>

            <!-- Search Bar (Desktop) -->
            <div class="hidden md:flex flex-1 max-w-md mx-4">
                <div class="relative w-full">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400"></i>
                    <input type="text" id="searchInput" placeholder="အိတ်အမည်၊ အမျိုးအစား ရှာဖွေရန်..." oninput="handleSearch(this.value)" class="w-full pl-10 pr-4 py-2.5 rounded-full bg-stone-100 border-none text-sm focus:bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Admin Dashboard Button (shown when admin logged in) -->
                <a id="adminBtn" href="admin.php" class="hidden px-3.5 py-2 rounded-xl bg-stone-900 hover:bg-black text-amber-400 text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Admin Dashboard</span>
                </a>

                <!-- User Account Button -->
                <button id="userBtn" onclick="handleUserBtnClick()" class="p-2.5 sm:px-4 sm:py-2.5 rounded-xl border border-stone-200 bg-stone-50 hover:bg-stone-100 text-stone-800 text-xs font-bold transition flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-stone-600"></i>
                    <span id="userBtnText" class="hidden sm:inline">အကောင့်ဝင်ရန်</span>
                </button>

                <!-- Cart Button -->
                <button onclick="toggleCartDrawer(true)" class="relative p-2.5 sm:px-4 sm:py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-md shadow-amber-600/20">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">ခြင်းတောင်း</span>
                    <span id="cartCountBadge" class="bg-stone-900 text-amber-300 text-[11px] font-extrabold px-2 py-0.5 rounded-full">0</span>
                </button>
            </div>
        </div>

        <!-- Mobile Search Bar -->
        <div class="md:hidden px-4 pb-3">
            <div class="relative w-full">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" placeholder="အိတ်အမည် ရှာရန်..." oninput="handleSearch(this.value)" class="w-full pl-9 pr-4 py-2 rounded-xl bg-stone-100 border-none text-xs focus:bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

        <!-- Hero Section -->
        <div id="heroBanner" class="rounded-3xl p-6 sm:p-10 text-white relative overflow-hidden shadow-2xl bg-gradient-to-r from-stone-900 via-stone-800 to-amber-950">
            <div class="relative z-10 max-w-2xl space-y-4">
                <div class="inline-flex items-center gap-2 bg-amber-500/20 text-amber-300 border border-amber-500/30 px-3 py-1 rounded-full text-xs font-bold">
                    <span>✨ မန္တလေး လက်လီ/လက်ကား ဆိုင်ကြီး</span>
                </div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး
                </h1>
                <p class="text-stone-300 text-sm sm:text-base leading-relaxed">
                    ရုံးတက်၊ ကျောင်းတက်၊ ပွဲတက်နှင့် နေ့စဉ်သုံး လက်ကိုင်အိတ်၊ ဘေးလွယ်အိတ်၊ ကျောပိုးအိတ်များကို မန္တလေးမြို့ ဆိုင်ခွဲ (၃) ဆိုင်မှ လက်လီ/လက်ကား စိတ်ကြိုက်ရွေးချယ် မှာယူနိုင်ပါပြီ။
                </p>
                <div class="flex flex-wrap gap-2 pt-2">
                    <span class="inline-flex items-center gap-1.5 bg-stone-800/80 px-3 py-1.5 rounded-xl text-xs text-amber-200">
                        <i data-lucide="truck" class="w-3.5 h-3.5 text-amber-400"></i> မြန်မာတစ်နိုင်ငံလုံး ကားဂိတ်ပို့
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-stone-800/80 px-3 py-1.5 rounded-xl text-xs text-amber-200">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-amber-400"></i> လက်ကား (၅) လုံးဈေး သီးသန့်
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-stone-800/80 px-3 py-1.5 rounded-xl text-xs text-amber-200">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-amber-400"></i> KPay / WavePay / COD
                    </span>
                </div>
            </div>
            <!-- Decorative circle -->
            <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Announcements / Promotion Slider -->
        <div id="announcementsSection" class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-5 h-5 text-amber-600"></i>
                    <h2 class="text-lg font-bold text-stone-900">ဆိုင်သတင်းနှင့် အထူးအစီအစဉ်များ</h2>
                </div>
            </div>
            <div id="announcementsList" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Dynamically loaded announcements -->
            </div>
        </div>

        <!-- Categories Filter Tabs -->
        <div class="space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-5 h-5 text-amber-600"></i>
                    <h2 class="text-xl font-extrabold text-stone-900">ပစ္စည်းအမျိုးအစားများ</h2>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs text-stone-500 font-medium">အစီအစဉ် -</label>
                    <select id="sortBy" onchange="renderProducts()" class="bg-white border border-stone-200 rounded-xl px-3 py-1.5 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="featured">အထူးအကြံပြု (Featured)</option>
                        <option value="price-asc">ဈေးနှုန်း: အနိမ့်မှ အမြင့်</option>
                        <option value="price-desc">ဈေးနှုန်း: အမြင့်မှ အနိမ့်</option>
                        <option value="rating">သုံးသပ်ချက် အများဆုံး</option>
                    </select>
                </div>
            </div>

            <!-- Categories Tabs List -->
            <div id="categoryTabs" class="flex gap-2 overflow-x-auto pb-2 hide-scrollbar">
                <!-- Dynamically filled categories -->
            </div>
        </div>

        <!-- Products Grid -->
        <div>
            <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Dynamically filled products -->
            </div>
            <div id="emptyState" class="hidden text-center py-16 bg-white rounded-3xl border border-stone-200/80 p-8">
                <div class="w-16 h-16 bg-stone-100 text-stone-400 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="search-x" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-bold text-stone-800">ရှာဖွေထားသော အိတ် မတွေ့ရှိပါ</h3>
                <p class="text-xs text-stone-500 mt-1">အခြား အမည် သို့မဟုတ် အမျိုးအစားတစ်ခုခုကို ရွေးချယ်ကြည့်ပါ။</p>
            </div>
        </div>

    </main>

    <!-- Footer & Shop Info -->
    <footer class="bg-stone-900 text-stone-300 mt-16 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Col 1: Brand Info -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold">
                            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        </div>
                        <span class="text-lg font-extrabold text-white">Pont Pont Handbag</span>
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed">
                        မန္တလေးမြို့၏ နာမည်ကျော် အမျိုးသမီးနှင့် အမျိုးသား လက်ကိုင်အိတ်၊ ဘေးလွယ်အိတ်၊ ကျောပိုးအိတ် လက်လီ/လက်ကား အရောင်းဆိုင်ကြီး။
                    </p>
                    <p class="text-xs text-amber-400 font-semibold">
                        ဆိုင်ဖွင့်ချိန်: နေ့စဉ် မနက် 8:00 AM မှ ည 8:00 PM အထိ
                    </p>
                </div>

                <!-- Col 2: Branches -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">မန္တလေး ဆိုင်ခွဲ (၃) ဆိုင်</h4>
                    <ul class="text-xs space-y-2.5 text-stone-400">
                        <li>
                            <strong class="text-stone-200 block">ပင်မဆိုင် - ၆၅ လမ်း</strong>
                            ၆၅ လမ်း၊ ၁၀၇ လမ်းနှင့် ၁၀၈ လမ်းကြား၊ မန္တလေးမြို့။
                        </li>
                        <li>
                            <strong class="text-stone-200 block">ဆိုင်ခွဲ (၂) - ၇၃ လမ်း</strong>
                            ၇၃ လမ်း၊ ၃၆ လမ်းနှင့် ၃၇ လမ်းကြား၊ မန္တလေးမြို့။
                        </li>
                        <li>
                            <strong class="text-stone-200 block">ဆိုင်ခွဲ (၃) - တံခွန်တိုင်</strong>
                            ၇၈ လမ်း (ရန်ကုန်-မန္တလေး လမ်းမကြီး)၊ တံခွန်တိုင်ဈေးအနီး၊ မန္တလေးမြို့။
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Contact & Payments -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">ဆက်သွယ်ရန် ဖုန်းနံပါတ်များ</h4>
                    <div class="space-y-1.5 text-xs">
                        <a href="tel:09963000502" class="block text-amber-400 font-bold hover:underline">📞 09-963000502</a>
                        <a href="tel:09750072752" class="block text-stone-300 hover:text-white">📞 09-750072752</a>
                        <a href="tel:09404448881" class="block text-stone-300 hover:text-white">📞 09-404448881</a>
                    </div>
                    <div class="pt-2">
                        <h5 class="text-xs font-semibold text-stone-300 mb-1.5">ငွေပေးချေမှု နည်းလမ်းများ</h5>
                        <div class="flex gap-2">
                            <span class="bg-blue-900/60 text-blue-300 border border-blue-700/50 text-[10px] font-bold px-2 py-1 rounded">KBZPay</span>
                            <span class="bg-amber-900/60 text-amber-300 border border-amber-700/50 text-[10px] font-bold px-2 py-1 rounded">WavePay</span>
                            <span class="bg-stone-800 text-stone-300 border border-stone-700 text-[10px] font-bold px-2 py-1 rounded">COD အိမ်ရောက်ငွေချေ</span>
                        </div>
                    </div>
                </div>

                <!-- Col 4: Wholesale Info -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">လက်ကား မှာယူလိုသူများအတွက်</h4>
                    <p class="text-xs text-stone-400 leading-relaxed">
                        နယ်ဝေးမှ ပြန်လည်ရောင်းချလိုသော လက်ကားမိတ်ဆွေများအတွက် အိတ် (၅) လုံးနှင့်အထက် လက်ကားဈေးနှုန်း သီးသန့်ဖြင့် မန္တလေးမှ နေ့ချင်း ကားဂိတ်တင်ပေးပါသည်။
                    </p>
                    <div class="p-3 rounded-xl bg-stone-800/80 border border-stone-700/60 text-xs text-amber-300">
                        ✨ လက်ကားဈေးနှုန်းများကို အိတ်တစ်ခုချင်းစီတွင် ကြည့်ရှုနိုင်ပါသည်။
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-6 border-t border-stone-800 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500 gap-3">
                <p>© 2026 Pont Pont Handbag Shop (မန္တလေး). All rights reserved.</p>
                <p>Designed for Bluehost Web Hosting & Mobile Flutter App Interoperability</p>
            </div>
        </div>
    </footer>

    <!-- =================================================================== -->
    <!-- MODALS & DRAWERS -->
    <!-- =================================================================== -->

    <!-- 1. PRODUCT DETAIL MODAL -->
    <div id="productModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl border border-stone-200 animate-in fade-in zoom-in-95 duration-150">
            <div class="relative">
                <button onclick="closeProductModal()" class="absolute right-4 top-4 z-10 w-9 h-9 rounded-full bg-black/40 text-white hover:bg-black/70 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <!-- Image -->
                    <div class="h-64 md:h-full bg-stone-100 relative">
                        <img id="modalProductImage" src="" alt="" class="w-full h-full object-cover">
                        <span id="modalProductBadge" class="absolute top-4 left-4 bg-amber-500 text-stone-950 text-xs font-black px-2.5 py-1 rounded-full shadow-md"></span>
                    </div>

                    <!-- Details -->
                    <div class="p-6 space-y-4">
                        <div>
                            <span id="modalProductCategory" class="text-xs font-bold text-amber-600 uppercase tracking-wider"></span>
                            <h3 id="modalProductNameEn" class="text-lg font-extrabold text-stone-900 mt-0.5"></h3>
                            <h4 id="modalProductNameMm" class="text-sm font-semibold text-stone-600"></h4>
                        </div>

                        <!-- Price box -->
                        <div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-1">
                            <div class="flex items-baseline gap-2">
                                <span id="modalProductPrice" class="text-2xl font-black text-amber-900"></span>
                                <span id="modalProductOriginalPrice" class="text-xs text-stone-400 line-through"></span>
                            </div>
                            <div id="modalWholesalePriceBox" class="text-xs font-bold text-amber-800 flex items-center gap-1.5 pt-1 border-t border-amber-200/60">
                                <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                <span id="modalWholesalePriceText"></span>
                            </div>
                        </div>

                        <!-- Colors Selection -->
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-2">အရောင် ရွေးချယ်ရန် (Colors) - <span id="selectedColorName" class="text-amber-600"></span></label>
                            <div id="modalColorsList" class="flex flex-wrap gap-2"></div>
                        </div>

                        <!-- Specs -->
                        <div class="text-xs space-y-1 text-stone-600 bg-stone-50 p-3 rounded-xl border border-stone-200/60">
                            <p><strong>သားရေအမျိုးအစား:</strong> <span id="modalProductMaterial"></span></p>
                            <p><strong>ဆိုဒ်အတိုင်းအတာ:</strong> <span id="modalProductDimensions"></span></p>
                            <p><strong>လက်ကျန်ပစ္စည်း:</strong> <span id="modalProductStock" class="font-bold text-emerald-600"></span></p>
                        </div>

                        <!-- Description -->
                        <p id="modalProductDescMm" class="text-xs text-stone-600 leading-relaxed"></p>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-2">
                            <button onclick="addModalProductToCart()" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-4 rounded-xl text-xs transition shadow-md shadow-amber-600/20 flex items-center justify-center gap-2">
                                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                                <span>ခြင်းတောင်းထဲ ထည့်မည်</span>
                            </button>
                            <button onclick="buyNowModalProduct()" class="px-5 bg-stone-900 hover:bg-black text-white font-bold py-3 rounded-xl text-xs transition">
                                တန်းဝယ်မည်
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. CART & CHECKOUT DRAWER -->
    <div id="cartDrawer" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs">
        <div class="absolute inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl flex flex-col h-full transform transition ease-in-out duration-300">
            <!-- Header -->
            <div class="p-4 border-b border-stone-200 flex items-center justify-between bg-stone-50">
                <div class="flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="w-5 h-5 text-amber-600"></i>
                    <h3 class="font-extrabold text-stone-900" id="cartDrawerTitle">ဝယ်ယူမည့် ခြင်းတောင်း</h3>
                </div>
                <button onclick="toggleCartDrawer(false)" class="p-2 rounded-xl text-stone-400 hover:text-stone-700 hover:bg-stone-200 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="cartDrawerBody">
                <!-- Cart Items List (Step 1) -->
                <div id="cartStepItems" class="space-y-3">
                    <div id="cartItemList" class="space-y-3"></div>

                    <!-- Promo Code Box -->
                    <div class="p-3 rounded-2xl bg-stone-50 border border-stone-200 space-y-2">
                        <label class="block text-xs font-bold text-stone-700">လျှော့ဈေးကူပွန် ကုဒ် (Promo Code)</label>
                        <div class="flex gap-2">
                            <input type="text" id="promoInput" placeholder="ဥပမာ: PONTPONT10" class="flex-1 px-3 py-1.5 rounded-xl border border-stone-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none uppercase font-mono">
                            <button onclick="applyPromoCode()" class="px-3 py-1.5 bg-stone-800 hover:bg-stone-900 text-white rounded-xl text-xs font-bold transition">Apply</button>
                        </div>
                        <p id="promoMessage" class="text-[11px] font-semibold text-emerald-600 hidden"></p>
                    </div>

                    <!-- Pricing Summary -->
                    <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 space-y-2 text-xs">
                        <div class="flex justify-between text-stone-600">
                            <span>ပစ္စည်းစုစုပေါင်း:</span>
                            <span id="cartSubtotalText" class="font-bold">0 Ks</span>
                        </div>
                        <div id="cartDiscountRow" class="flex justify-between text-emerald-600 hidden">
                            <span>ကူပွန်လျှော့ဈေး (10%):</span>
                            <span id="cartDiscountText" class="font-bold">-0 Ks</span>
                        </div>
                        <div class="flex justify-between text-stone-600">
                            <span>ပို့ဆောင်ခ (Delivery Fee):</span>
                            <span id="cartDeliveryFeeText" class="font-bold">2,500 Ks</span>
                        </div>
                        <div class="border-t border-stone-200 pt-2 flex justify-between text-sm font-extrabold text-stone-900">
                            <span>ကျသင့်ငွေ စုစုပေါင်း:</span>
                            <span id="cartTotalText" class="text-amber-700">0 Ks</span>
                        </div>
                        <p class="text-[10px] text-amber-700 pt-1">* ၁ သိန်းကျပ်အထက် ဝယ်ယူပါက ပို့ဆောင်ခ အခမဲ့ (Free Delivery) ဖြစ်ပါသည်။</p>
                    </div>
                </div>

                <!-- Checkout Form (Step 2) -->
                <div id="cartStepCheckout" class="space-y-4 hidden">
                    <button onclick="switchCartStep('items')" class="text-xs text-amber-600 font-bold hover:underline flex items-center gap-1 mb-2">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> ခြင်းတောင်းသို့ ပြန်သွားမည်
                    </button>

                    <h4 class="text-sm font-extrabold text-stone-900 border-b pb-2">ပို့ဆောင်ရမည့် လိပ်စာ ဖြည့်စွက်ပါ</h4>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">အမည် (Customer Name) *</label>
                            <input type="text" id="orderCustomerName" placeholder="မပုံ့ပုံ့" class="w-full px-3 py-2 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">ဖုန်းနံပါတ် (Phone Number) *</label>
                            <input type="tel" id="orderPhone" placeholder="09xxxxxxxxx" class="w-full px-3 py-2 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">မြို့နယ် (City / Delivery Zone) *</label>
                            <select id="orderCity" onchange="updateDeliveryFeeFromCity()" class="w-full px-3 py-2 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                                <option value="Mandalay (မန္တလေး - မြို့တွင်း)" data-fee="2500">Mandalay (မန္တလေး - မြို့တွင်း) - 2,500 Ks</option>
                                <option value="Pyin Oo Lwin (ပြင်ဦးလွင်)" data-fee="3500">Pyin Oo Lwin (ပြင်ဦးလွင်) - 3,500 Ks</option>
                                <option value="Sagaing / Monywa (စစ်ကိုင်း / မုံရွာ)" data-fee="3500">Sagaing / Monywa (စစ်ကိုင်း / မုံရွာ) - 3,500 Ks</option>
                                <option value="Yangon (ရန်ကုန်)" data-fee="4000">Yangon (ရန်ကုန်) - 4,000 Ks</option>
                                <option value="Naypyidaw (နေပြည်တော်)" data-fee="3500">Naypyidaw (နေပြည်တော်) - 3,500 Ks</option>
                                <option value="Taunggyi (တောင်ကြီး)" data-fee="4000">Taunggyi (တောင်ကြီး) - 4,000 Ks</option>
                                <option value="Magway / Pakokku (မကွေး / ပခုက္ကူ)" data-fee="4000">Magway / Pakokku (မကွေး / ပခုက္ကူ) - 4,000 Ks</option>
                                <option value="Other Cities / ကားဂိတ်တင် (အခြားမြို့များ)" data-fee="4500">Other Cities / ကားဂိတ်တင် (အခြားမြို့များ) - 4,500 Ks</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">အသေးစိတ် လိပ်စာ / အိမ်နံပါတ် / လမ်း *</label>
                            <textarea id="orderAddress" rows="2" placeholder="အမှတ် (၁၂)၊ ၇၃ လမ်း၊ ..." class="w-full px-3 py-2 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">မှတ်ချက် (Optional Note)</label>
                            <input type="text" id="orderNote" placeholder="ကားဂိတ်တင်ပေးရန် သို့မဟုတ် ဆက်သွယ်ရန်အချိန်" class="w-full px-3 py-2 rounded-xl border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>

                        <!-- Payment Method Selection -->
                        <div class="pt-2">
                            <label class="block font-bold text-stone-700 mb-1.5">ငွေပေးချေမှု နည်းလမ်း ရွေးချယ်ပါ *</label>
                            <div id="checkoutPaymentMethodsContainer" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <label class="payment-card border border-amber-500 bg-amber-50/30 rounded-xl p-2.5 text-center cursor-pointer hover:border-amber-500 transition">
                                    <input type="radio" name="paymentMethod" value="KPay" checked onchange="togglePaymentDetails()" class="hidden">
                                    <div class="font-bold text-blue-700">KBZPay</div>
                                    <div class="text-[10px] text-stone-500">ငွေလွှဲ</div>
                                </label>
                                <label class="payment-card border border-stone-200 rounded-xl p-2.5 text-center cursor-pointer hover:border-amber-500 transition">
                                    <input type="radio" name="paymentMethod" value="WavePay" onchange="togglePaymentDetails()" class="hidden">
                                    <div class="font-bold text-amber-600">WavePay</div>
                                    <div class="text-[10px] text-stone-500">ငွေလွှဲ</div>
                                </label>
                                <label class="payment-card border border-stone-200 rounded-xl p-2.5 text-center cursor-pointer hover:border-amber-500 transition">
                                    <input type="radio" name="paymentMethod" value="COD" onchange="togglePaymentDetails()" class="hidden">
                                    <div class="font-bold text-stone-800">COD</div>
                                    <div class="text-[10px] text-stone-500">အိမ်ရောက်ချေ</div>
                                </label>
                            </div>
                        </div>

                        <!-- Dynamic Transfer / Payment Info Box -->
                        <div id="transferInfoBox" class="p-3.5 rounded-2xl bg-blue-50 border border-blue-200 text-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-blue-900" id="transferTitle">KPay အကောင့်နံပါတ်</span>
                                <button type="button" id="btnCopyAccount" onclick="copyAccountNo()" class="bg-white border border-blue-200 px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-800 hover:bg-blue-100 transition">
                                    📋 Copy နံပါတ်
                                </button>
                            </div>
                            <p class="font-extrabold text-stone-900 text-sm" id="transferPhoneDisplay">09-963000502</p>
                            <p class="text-[11px] text-stone-600" id="transferNameRow">Account Name: <strong id="transferNameDisplay">Ma Pont Pont (မပုံ့)</strong></p>
                            <p id="transferInstructions" class="text-[11px] text-stone-600 bg-white/70 p-2 rounded-xl border border-blue-100 leading-relaxed hidden"></p>
                            
                            <!-- QR Code Preview if configured -->
                            <div id="transferQrBox" class="p-2 bg-white rounded-xl border border-blue-200 flex items-center gap-3 hidden">
                                <img id="transferQrImg" src="" alt="QR" class="w-16 h-16 object-contain rounded-lg border border-stone-200">
                                <div class="text-[11px] text-stone-600 space-y-0.5">
                                    <span class="font-bold text-stone-900 block">QR Code Scan ၍ ပေးချေနိုင်ပါသည်</span>
                                    <a id="transferQrLink" href="" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-bold underline">ပုံကြီးကြည့်ရန် ↗</a>
                                </div>
                            </div>

                            <div id="transferTxRefRow">
                                <label class="block font-bold text-blue-950 mt-2 mb-1" id="labelTxRef">ငွေလွှဲပြေစာ နောက်ဆုံး (၆) လုံး (Transaction Ref) *</label>
                                <input type="text" id="orderTxRef" placeholder="ဥပမာ: 849201" class="w-full px-3 py-1.5 rounded-xl border border-blue-300 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <!-- Payment Slip Upload -->
                            <div class="pt-2 border-t border-blue-200/60" id="transferSlipRow">
                                <label class="block font-bold text-blue-950 mb-1" id="labelSlipUpload">ငွေလွှဲပြေစာ Screenshot ပုံတင်ရန် (Payment Slip)</label>
                                <input type="file" id="orderSlipInput" accept="image/*" onchange="handleSlipFileSelect(event)" class="w-full text-xs text-stone-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                                <div id="slipPreviewContainer" class="mt-2 relative inline-block hidden">
                                    <img id="slipPreviewImg" class="w-20 h-24 object-cover rounded-lg border border-blue-300 shadow-sm" src="" alt="Slip Preview">
                                    <button type="button" onclick="clearSlipFile()" class="absolute -top-1.5 -right-1.5 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold hover:bg-red-700">✕</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Success Confirmation (Step 3) -->
                <div id="cartStepSuccess" class="space-y-4 text-center py-6 hidden">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
                        <i data-lucide="check-circle" class="w-10 h-10"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-stone-900">အော်ဒါမှာယူမှု အောင်မြင်ပါသည်! 🎉</h3>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        ပုံ့ပုံ့ အိတ်ဆိုင်အား အားပေးမှုကို ကျေးဇူးတင်ရှိပါသည်။ ဆိုင်ဝန်ထမ်းမှ မကြာမီ ဖုန်းဆက်သွယ်ပြီး အော်ဒါအတည်ပြုပေးပါမည်။
                    </p>
                    <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 text-left text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-stone-500">Order ID:</span>
                            <span id="successOrderId" class="font-mono font-bold text-amber-700"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-stone-500">စုစုပေါင်း ကျသင့်ငွေ:</span>
                            <span id="successOrderTotal" class="font-bold text-stone-900"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-stone-500">ငွေပေးချေမှု:</span>
                            <span id="successOrderPayment" class="font-bold text-stone-900"></span>
                        </div>
                    </div>
                    <button onclick="toggleCartDrawer(false); location.reload();" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 rounded-xl text-xs transition shadow-md shadow-amber-600/20">
                        ဈေးဝယ်ခြင်း ဆက်လုပ်မည်
                    </button>
                </div>
            </div>

            <!-- Drawer Footer Buttons -->
            <div id="cartDrawerFooter" class="p-4 border-t border-stone-200 bg-stone-50">
                <button id="cartNextBtn" onclick="proceedToCheckout()" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3.5 rounded-xl text-xs transition shadow-md shadow-amber-600/20 flex items-center justify-center gap-2">
                    <span>ငွေရှင်းမည် (Proceed to Checkout)</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
                <button id="cartSubmitBtn" onclick="submitOrder()" class="hidden w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl text-xs transition shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>အော်ဒါ အတည်ပြုမည် (Confirm Order)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 3. AUTH MODAL (LOGIN / REGISTER) -->
    <div id="authModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-stone-200 relative">
            <button onclick="closeAuthModal()" class="absolute right-4 top-4 text-stone-400 hover:text-stone-700">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Tabs -->
            <div class="flex border-b border-stone-200 mb-6">
                <button id="authTabLogin" onclick="switchAuthTab('login')" class="flex-1 pb-3 text-sm font-bold text-amber-600 border-b-2 border-amber-600 text-center">
                    အကောင့်ဝင်ရန် (Login)
                </button>
                <button id="authTabRegister" onclick="switchAuthTab('register')" class="flex-1 pb-3 text-sm font-bold text-stone-500 border-b-2 border-transparent text-center hover:text-stone-800">
                    အကောင့်သစ်ဖွင့်ရန် (Sign Up)
                </button>
            </div>

            <!-- Login Form -->
            <form id="loginForm" onsubmit="handleLogin(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">အီးမေးလ် (Email)</label>
                    <input type="email" id="loginEmail" required placeholder="admin@pontpont.com" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">စကားဝှက် (Password)</label>
                    <input type="password" id="loginPassword" required placeholder="••••••••" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div class="bg-amber-50 border border-amber-200 p-3 rounded-xl text-[11px] text-amber-900">
                    <p class="font-bold">🔑 စမ်းသပ်ရန် Demo Accounts:</p>
                    <div class="flex justify-between mt-1">
                        <span>Admin: <code>admin@pontpont.com</code></span>
                        <button type="button" onclick="fillDemo('admin@pontpont.com', 'admin123')" class="text-amber-700 font-bold underline">Fill</button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 rounded-xl text-sm transition shadow-md shadow-amber-600/20">
                    အကောင့်ဝင်မည်
                </button>
            </form>

            <!-- Register Form -->
            <form id="registerForm" onsubmit="handleRegister(event)" class="space-y-3 hidden">
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">အမည် (Full Name) *</label>
                    <input type="text" id="regName" required placeholder="မပုံ့ပုံ့" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">အီးမေးလ် (Email) *</label>
                    <input type="email" id="regEmail" required placeholder="name@gmail.com" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">စကားဝှက် (Password - အနည်းဆုံး ၆ လုံး) *</label>
                    <input type="password" id="regPassword" required minlength="6" placeholder="••••••••" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">ဖုန်းနံပါတ် (Phone Number) *</label>
                    <input type="tel" id="regPhone" required placeholder="09xxxxxxxxx" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">မြို့ (City)</label>
                    <input type="text" id="regCity" placeholder="Mandalay" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">လိပ်စာ (Address)</label>
                    <input type="text" id="regAddress" placeholder="၇၃ လမ်း၊ မန္တလေး" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 rounded-xl text-xs transition shadow-md shadow-amber-600/20">
                    အကောင့်သစ် ဖွင့်မည်
                </button>
            </form>
        </div>
    </div>

    <!-- 4. USER PROFILE & MY ORDERS MODAL -->
    <div id="profileModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-stone-200 relative max-h-[90vh] overflow-y-auto">
            <button onclick="closeProfileModal()" class="absolute right-4 top-4 text-stone-400 hover:text-stone-700">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <div class="flex items-center gap-3 border-b border-stone-100 pb-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg">
                    <i data-lucide="user" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 id="profileName" class="text-lg font-extrabold text-stone-900"></h3>
                    <p id="profileEmail" class="text-xs text-stone-500"></p>
                </div>
                <button onclick="handleLogout()" class="ml-auto text-xs text-red-600 hover:bg-red-50 font-bold px-3 py-1.5 rounded-xl border border-red-200 transition">
                    အကောင့်ထွက်မည် (Logout)
                </button>
            </div>

            <!-- Profile Info Form -->
            <form onsubmit="handleUpdateProfile(event)" class="space-y-3 mb-8 bg-stone-50 p-4 rounded-2xl border border-stone-200/80 text-xs">
                <h4 class="font-bold text-stone-900 text-sm">ကိုယ်ရေးအချက်အလက် ပြင်ဆင်ရန်</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-stone-700 mb-1">အမည်</label>
                        <input type="text" id="editName" class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-stone-700 mb-1">ဖုန်းနံပါတ်</label>
                        <input type="tel" id="editPhone" class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-stone-700 mb-1">မြို့နယ်</label>
                    <input type="text" id="editCity" class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                </div>
                <div>
                    <label class="block font-semibold text-stone-700 mb-1">လိပ်စာ</label>
                    <input type="text" id="editAddress" class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                </div>
                <div>
                    <label class="block font-semibold text-stone-700 mb-1">စကားဝှက်အသစ် (မပြောင်းလိုပါက အလွတ်ထားပါ)</label>
                    <input type="password" id="editPassword" placeholder="အနည်းဆုံး ၆ လုံး" class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                </div>
                <button type="submit" class="bg-stone-800 hover:bg-stone-900 text-white font-bold py-2 px-4 rounded-xl text-xs transition">
                    အချက်အလက် သိမ်းဆည်းမည်
                </button>
            </form>

            <!-- Order History List -->
            <div>
                <h4 class="font-extrabold text-stone-900 text-base mb-3 flex items-center gap-2">
                    <i data-lucide="package" class="w-5 h-5 text-amber-600"></i>
                    <span>မှာယူထားသော အော်ဒါမှတ်တမ်းများ (My Orders)</span>
                </h4>
                <div id="profileOrdersList" class="space-y-3">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- 5. ADMIN DASHBOARD MODAL -->
    <div id="adminModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-5xl w-full p-4 sm:p-6 shadow-2xl border border-stone-200 relative max-h-[95vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-stone-100 pb-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-stone-900 text-amber-400 flex items-center justify-center font-bold">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-stone-900">Pont Pont Shop Admin Center</h3>
                        <p class="text-xs text-stone-500">အော်ဒါများ၊ ပစ္စည်းများနှင့် ဆိုင်ဆက်တင်များ စီမံခန့်ခွဲမှု</p>
                    </div>
                </div>
                <button onclick="closeAdminModal()" class="text-stone-400 hover:text-stone-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Admin Nav Tabs -->
            <div class="flex gap-2 border-b border-stone-200 pb-2 mb-4 overflow-x-auto hide-scrollbar text-xs font-bold">
                <button onclick="switchAdminTab('overview')" id="adminTaboverview" class="admin-tab-btn px-4 py-2 rounded-xl bg-amber-600 text-white">ခြုံငုံသုံးသပ်ချက် (Overview)</button>
                <button onclick="switchAdminTab('orders')" id="adminTaborders" class="admin-tab-btn px-4 py-2 rounded-xl bg-stone-100 text-stone-700 hover:bg-stone-200">အော်ဒါများ (Orders)</button>
                <button onclick="switchAdminTab('products')" id="adminTabproducts" class="admin-tab-btn px-4 py-2 rounded-xl bg-stone-100 text-stone-700 hover:bg-stone-200">ပစ္စည်းများ (Products)</button>
                <button onclick="switchAdminTab('categories')" id="adminTabcategories" class="admin-tab-btn px-4 py-2 rounded-xl bg-stone-100 text-stone-700 hover:bg-stone-200">🏷️ ကဏ္ဍများ (Categories)</button>
                <button onclick="switchAdminTab('announcements')" id="adminTabannouncements" class="admin-tab-btn px-4 py-2 rounded-xl bg-stone-100 text-stone-700 hover:bg-stone-200">ကြေညာချက်များ (News)</button>
                <button onclick="switchAdminTab('users')" id="adminTabusers" class="admin-tab-btn px-4 py-2 rounded-xl bg-stone-100 text-stone-700 hover:bg-stone-200">အသုံးပြုသူများ (Users)</button>
                <button onclick="switchAdminTab('theme')" id="adminTabtheme" class="admin-tab-btn px-4 py-2 rounded-xl bg-stone-100 text-stone-700 hover:bg-stone-200">ဒီဇိုင်း Theme</button>
                <button onclick="switchAdminTab('chatbot')" id="adminTabchatbot" class="admin-tab-btn px-4 py-2 rounded-xl bg-stone-100 text-stone-700 hover:bg-stone-200">🤖 AI Chatbot ဆက်တင်</button>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto space-y-4" id="adminContent">
                <!-- Overview Pane -->
                <div id="adminPaneoverview" class="admin-pane space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-950">
                            <span class="text-xs font-bold text-amber-700">စုစုပေါင်း အရောင်းရငွေ</span>
                            <h4 id="statRevenue" class="text-xl font-black mt-1">0 Ks</h4>
                        </div>
                        <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-950">
                            <span class="text-xs font-bold text-blue-700">စုစုပေါင်း အော်ဒါ</span>
                            <h4 id="statOrders" class="text-xl font-black mt-1">0</h4>
                        </div>
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950">
                            <span class="text-xs font-bold text-emerald-700">တင်ထားသော ပစ္စည်း</span>
                            <h4 id="statProducts" class="text-xl font-black mt-1">0</h4>
                        </div>
                        <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200 text-purple-950">
                            <span class="text-xs font-bold text-purple-700">အကောင့်ဖွင့်ထားသူ</span>
                            <h4 id="statUsers" class="text-xl font-black mt-1">0</h4>
                        </div>
                    </div>
                </div>

                <!-- Orders Pane -->
                <div id="adminPaneorders" class="admin-pane space-y-3 hidden">
                    <div class="flex justify-between items-center">
                        <h4 class="font-extrabold text-stone-900 text-sm">အော်ဒါများ စာရင်း</h4>
                    </div>
                    <div id="adminOrdersTable" class="space-y-3">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <!-- Products Pane -->
                <div id="adminPaneproducts" class="admin-pane space-y-4 hidden">
                    <div class="flex justify-between items-center">
                        <h4 class="font-extrabold text-stone-900 text-sm">ကုန်ပစ္စည်းများ စီမံရန်</h4>
                        <button onclick="openAddProductForm()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-3 rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="plus" class="w-4 h-4"></i> ပစ္စည်းအသစ် ထည့်မည်
                        </button>
                    </div>

                    <!-- Add/Edit Product Inline Form -->
                    <div id="productFormContainer" class="hidden p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-3 text-xs">
                        <h5 class="font-bold text-stone-900" id="productFormTitle">ပစ္စည်းအသစ် ထည့်သွင်းခြင်း</h5>
                        <form onsubmit="handleSaveProduct(event)" class="space-y-3">
                            <input type="hidden" id="pFormId">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold mb-1">အင်္ဂလိပ် အမည် (Name En)</label>
                                    <input type="text" id="pFormNameEn" required class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                                </div>
                                <div>
                                    <label class="block font-bold mb-1">မြန်မာ အမည် (Name Mm)</label>
                                    <input type="text" id="pFormNameMm" required class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block font-bold mb-1">အမျိုးအစား (Category)</label>
                                    <select id="pFormCategory" class="w-full px-3 py-1.5 rounded-xl border border-stone-300 bg-white">
                                        <option value="Tote">Tote & Handbags</option>
                                        <option value="Crossbody">Sling & Crossbody</option>
                                        <option value="Shoulder">Shoulder Bags</option>
                                        <option value="Clutch">Wallets & Purses</option>
                                        <option value="Backpack">Backpacks</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold mb-1">လက်လီဈေး (Price MMK)</label>
                                    <input type="number" id="pFormPrice" required class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                                </div>
                                <div>
                                    <label class="block font-bold mb-1">လက်ကားဈေး (Wholesale MMK)</label>
                                    <input type="number" id="pFormWholesale" class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block font-bold mb-1">မူလဈေး (Original Strike Price)</label>
                                    <input type="number" id="pFormOriginalPrice" class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                                </div>
                                <div>
                                    <label class="block font-bold mb-1">လက်ကျန် (Stock)</label>
                                    <input type="number" id="pFormStock" value="20" class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                                </div>
                                <div>
                                    <label class="block font-bold mb-1">Badge</label>
                                    <select id="pFormBadge" class="w-full px-3 py-1.5 rounded-xl border border-stone-300 bg-white">
                                        <option value="">None</option>
                                        <option value="Best Seller">Best Seller</option>
                                        <option value="Wholesale Hot">Wholesale Hot</option>
                                        <option value="New Arrival">New Arrival</option>
                                        <option value="Sale">Sale</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block font-bold mb-1">ဓာတ်ပုံ URL (Image URL)</label>
                                <input type="url" id="pFormImage" required class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">မြန်မာ ဖော်ပြချက် (Description Mm)</label>
                                <textarea id="pFormDescMm" rows="2" class="w-full px-3 py-1.5 rounded-xl border border-stone-300"></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-xl transition">သိမ်းဆည်းမည်</button>
                                <button type="button" onclick="document.getElementById('productFormContainer').classList.add('hidden')" class="bg-stone-200 text-stone-700 font-bold py-2 px-4 rounded-xl transition">မလုပ်တော့ပါ</button>
                            </div>
                        </form>
                    </div>

                    <div id="adminProductsTable" class="space-y-3">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <!-- Categories Pane -->
                <div id="adminPanecategories" class="admin-pane space-y-4 hidden">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <!-- Add Category Form -->
                        <div class="md:col-span-5 bg-stone-50 p-5 rounded-2xl border border-stone-200 space-y-3 h-fit">
                            <h4 class="font-bold text-stone-900 text-sm flex items-center gap-2">
                                <i data-lucide="tag" class="w-4 h-4 text-amber-700"></i>
                                Category အသစ်ထည့်ရန်
                            </h4>
                            <form onsubmit="handleAddCategory(event)" class="space-y-3 text-xs">
                                <div>
                                    <label class="block font-bold mb-1">Category အမည် (English Label) *</label>
                                    <input type="text" id="catLabelEn" required placeholder="e.g. Travel & Luggage, Luxury Clutches" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white">
                                </div>
                                <div>
                                    <label class="block font-bold mb-1">Category Code / Tag (Optional)</label>
                                    <input type="text" id="catId" placeholder="e.g. Travel, Clutch (အလွတ်ထားပါက အလိုအလျောက် သတ်မှတ်မည်)" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white">
                                </div>
                                <button type="submit" class="w-full bg-stone-900 hover:bg-black text-white font-bold py-2.5 px-4 rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    <span>Category ထည့်သွင်းမည်</span>
                                </button>
                            </form>
                        </div>

                        <!-- Active Categories List -->
                        <div class="md:col-span-7 bg-white p-5 rounded-2xl border border-stone-200 space-y-3">
                            <div class="flex items-center justify-between border-b border-stone-200 pb-2">
                                <h4 class="font-bold text-stone-900 text-sm">လက်ရှိ Category စာရင်းများ</h4>
                                <span class="text-[11px] text-stone-500">ဆိုင်မျက်နှာစာတွင် အသုံးပြုနေသော ကဏ္ဍများ</span>
                            </div>
                            <div id="adminCategoriesList" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Announcements Pane -->
                <div id="adminPaneannouncements" class="admin-pane space-y-4 hidden">
                    <div class="flex justify-between items-center">
                        <h4 class="font-extrabold text-stone-900 text-sm">ဆိုင်သတင်းနှင့် ပရိုမိုးရှင်းများ</h4>
                    </div>
                    <!-- Add announcement form -->
                    <form onsubmit="handleAddAnnouncement(event)" class="p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-3 text-xs">
                        <h5 class="font-bold text-stone-900">ကြေညာချက်အသစ် တင်မည်</h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold mb-1">ခေါင်းစဉ် (Title)</label>
                                <input type="text" id="annTitle" required placeholder="Promotion အကြောင်းအရာ..." class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">Tag</label>
                                <select id="annTag" class="w-full px-3 py-1.5 rounded-xl border border-stone-300 bg-white">
                                    <option value="Promotion">Promotion</option>
                                    <option value="New Arrival">New Arrival</option>
                                    <option value="Wholesale">Wholesale</option>
                                    <option value="Notice">Notice</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">အသေးစိတ် အကြောင်းအရာ</label>
                            <textarea id="annContent" required rows="2" class="w-full px-3 py-1.5 rounded-xl border border-stone-300"></textarea>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">ဓာတ်ပုံ URL (Optional)</label>
                            <input type="url" id="annImage" class="w-full px-3 py-1.5 rounded-xl border border-stone-300">
                        </div>
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-xl transition">ကြေညာချက် တင်မည်</button>
                    </form>
                    <div id="adminAnnouncementsList" class="space-y-3"></div>
                </div>

                <!-- Users Pane -->
                <div id="adminPaneusers" class="admin-pane space-y-3 hidden">
                    <h4 class="font-extrabold text-stone-900 text-sm">အသုံးပြုသူ စာရင်းနှင့် Role သတ်မှတ်ခြင်း</h4>
                    <div id="adminUsersList" class="space-y-2"></div>
                </div>

                <!-- Theme Pane -->
                <div id="adminPanetheme" class="admin-pane space-y-4 hidden">
                    <h4 class="font-extrabold text-stone-900 text-sm">ဆိုင်ပုံစံ ဒီဇိုင်း (Store Theme) ရွေးချယ်ပါ</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div onclick="setShopTheme('luxury-gold')" class="p-4 rounded-2xl border-2 cursor-pointer transition hover:scale-102 border-amber-500 bg-amber-50/50">
                            <div class="h-16 rounded-xl bg-gradient-to-r from-stone-900 to-amber-900 mb-3 flex items-center justify-center text-amber-300 font-bold text-xs">
                                Luxury Gold
                            </div>
                            <h5 class="font-bold text-sm text-stone-900">1. Classic Luxury Gold</h5>
                            <p class="text-xs text-stone-500 mt-1">ရွှေအိုရောင် ဇိမ်ခံဒီဇိုင်း (မူလ ပုံစံ)</p>
                        </div>
                        <div onclick="setShopTheme('rose-paris')" class="p-4 rounded-2xl border-2 cursor-pointer transition hover:scale-102 border-stone-200 hover:border-pink-500">
                            <div class="h-16 rounded-xl bg-gradient-to-r from-rose-900 to-pink-700 mb-3 flex items-center justify-center text-pink-200 font-bold text-xs">
                                Parisian Rose
                            </div>
                            <h5 class="font-bold text-sm text-stone-900">2. Parisian Rose</h5>
                            <p class="text-xs text-stone-500 mt-1">နူးညံ့လှပသော ပန်းနုရောင် ဖက်ရှင်စတိုင်</p>
                        </div>
                        <div onclick="setShopTheme('minimal-noir')" class="p-4 rounded-2xl border-2 cursor-pointer transition hover:scale-102 border-stone-200 hover:border-emerald-500">
                            <div class="h-16 rounded-xl bg-gradient-to-r from-stone-950 to-emerald-900 mb-3 flex items-center justify-center text-emerald-300 font-bold text-xs">
                                Editorial Noir
                            </div>
                            <h5 class="font-bold text-sm text-stone-900">3. Modern Noir & Emerald</h5>
                            <p class="text-xs text-stone-500 mt-1">ခေတ်မီ အနက်နှင့် မြစိမ်းရောင် မဂ္ဂဇင်းစတိုင်</p>
                        </div>
                    </div>
                </div>

                <!-- Chatbot Configuration Pane -->
                <div id="adminPanechatbot" class="admin-pane space-y-5 hidden">
                    <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-2xl flex items-center justify-between">
                        <div>
                            <h4 class="font-extrabold text-stone-900 text-sm flex items-center gap-2">
                                🤖 AI Chatbot Configuration (ဆက်တင်များ)
                            </h4>
                            <p class="text-xs text-stone-600 mt-0.5">
                                AI Provider (Gemini / OpenAI / Groq)၊ API Key နှင့် မေးခွန်းများဖြေကြားမည့် System Prompt များကို ပြင်ဆင်နိုင်ပါသည်။
                            </p>
                        </div>
                        <label class="flex items-center gap-2 text-xs font-bold text-stone-800 cursor-pointer">
                            <input type="checkbox" id="adminBotEnabled" checked class="w-4 h-4 text-amber-600 rounded">
                            <span>Chatbot ဖွင့်ထားမည်</span>
                        </label>
                    </div>

                    <form id="adminChatbotForm" onsubmit="saveChatbotConfig(event)" class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-stone-700 mb-1.5 uppercase">AI Provider (ဝန်ဆောင်မှု ရွေးချယ်ရန်)</label>
                                <select id="adminBotProvider" onchange="handleBotProviderChange()" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white font-bold">
                                    <option value="mock">Built-in Rule Engine (API Key မလိုပါ - မူလစနစ်)</option>
                                    <option value="gemini">Google Gemini API (အကြံပြုချက် - မြန်မာစာကျွမ်းကျင်)</option>
                                    <option value="openai">OpenAI (GPT-4o-mini / GPT-4o)</option>
                                    <option value="groq">Groq (Llama 3.3 / Ultra-fast)</option>
                                    <option value="custom">Custom OpenAI-Compatible API (OpenWebUI, Ollama, LM Studio)</option>
                                </select>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block font-bold text-stone-700 uppercase">Model Name (မော်ဒယ်အမည်)</label>
                                    <span id="modelStatusBadge" class="text-[10px] text-amber-700 font-bold hidden"></span>
                                </div>
                                <div id="modelInputContainer">
                                    <input type="text" id="adminBotModel" value="gemini-1.5-flash" placeholder="e.g. gemini-1.5-flash, gpt-4o-mini" class="w-full px-3 py-2 rounded-xl border border-stone-300 font-mono">
                                    <select id="adminBotModelSelect" onchange="document.getElementById('adminBotModel').value = this.value;" class="w-full px-3 py-2 rounded-xl border border-emerald-400 bg-emerald-50/60 font-mono font-bold hidden">
                                        <!-- Populated from Fetch Models -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Custom API URL (shown for custom provider) -->
                        <div id="adminApiUrlContainer" class="p-3.5 bg-blue-50/50 rounded-2xl border border-blue-200 space-y-1.5 hidden">
                            <label class="block font-bold text-blue-900 uppercase">Custom API URL (Endpoint)</label>
                            <input type="text" id="adminBotApiUrl" placeholder="e.g. http://localhost:11434/v1 or https://my-open-webui.com/api" class="w-full px-3 py-2 rounded-xl border border-blue-300 bg-white font-mono text-xs">
                            <p class="text-[11px] text-blue-700">OpenAI-compatible chat completions endpoint (စနစ်မှ /chat/completions ကို လိုအပ်ပါက အလိုအလျောက် ပေါင်းထည့်ပေးပါမည်)။</p>
                        </div>

                        <!-- API Key & Fetch Models -->
                        <div id="adminApiKeyContainer" class="p-3.5 bg-stone-50 rounded-2xl border border-stone-200 space-y-2 hidden">
                            <label class="block font-bold text-stone-800 uppercase">AI API Key</label>
                            <div class="flex gap-2">
                                <input type="password" id="adminBotApiKey" placeholder="AIzaSy... သို့မဟုတ် sk-..." class="flex-1 px-3 py-2 rounded-xl border border-stone-300 bg-white font-mono text-xs">
                                <button type="button" id="btnFetchModels" onclick="fetchAvailableModels()" class="px-3.5 py-2 bg-amber-700 hover:bg-amber-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 shadow-xs">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    <span id="btnFetchModelsText">Fetch Models</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-stone-500">🔒 API Key သည် Bluehost MySQL Database ထဲတွင် လုံခြုံစွာ သိမ်းဆည်းထားပြီး ဧည့်သည်များထံ ပေါက်ကြားခြင်း မရှိပါ။</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-stone-700 mb-1.5 uppercase">Chatbot အမည်</label>
                                <input type="text" id="adminBotName" value="Pont Pont AI Assistant" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold text-stone-700 mb-1.5 uppercase">စတင်နှုတ်ဆက်စာ (Welcome Message)</label>
                                <input type="text" id="adminBotWelcome" value="မင်္ဂလာပါရှင် 👋 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်။" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="block font-bold text-stone-700 uppercase">AI System Prompt (ဆိုင်အချက်အလက်များနှင့် လမ်းညွှန်ချက်များ)</label>
                                <button type="button" onclick="resetDefaultPrompt()" class="text-[11px] text-amber-700 underline font-bold">Default Prompt ပြန်ထားမည်</button>
                            </div>
                            <textarea id="adminBotSystemPrompt" rows="8" class="w-full p-3 rounded-2xl border border-stone-300 font-mono leading-relaxed bg-stone-50 text-[11px]"></textarea>
                            <p class="text-[11px] text-stone-500 mt-1">ပင်မဆိုင် (၆၅ လမ်း၊ ၁၀၇×၁၀၈ လမ်းကြား၊ မန္တလေးမြို့)၊ ပို့ဆောင်ခနှုန်းထားများနှင့် ဆက်သွယ်ရမည့် ဖုန်းနံပါတ် 09-963000502 များကို ဤ System Prompt တွင် ထည့်သွင်းထားပါသည်။</p>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md transition">
                                💾 Chatbot ဆက်တင်များ သိမ်းဆည်းမည်
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- JAVASCRIPT APP LOGIC -->
    <!-- =================================================================== -->
    <script>
        // Security Helper: Escape HTML special characters to prevent XSS
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // Global App State
        let shopData = {
            products: [],
            categories: [],
            announcements: [],
            orders: [],
            users: [],
            currentUser: null,
            theme: 'luxury-gold'
        };

        let activeCategory = 'All';
        let searchQuery = '';
        let cart = []; // Array of { productId, quantity, selectedColor }
        let selectedProductForModal = null;
        let selectedColorForModal = '';
        let appliedPromo = false;
        let appliedCoupon = null;
        let selectedSlipBase64 = null;

        // Initialize App
        document.addEventListener('DOMContentLoaded', () => {
            if (window.location.search.includes('admin=1')) {
                window.location.href = 'admin.php';
                return;
            }
            loadShopData();
            loadCartFromStorage();
        });

        // Fetch Shop Data from REST API
        async function loadShopData() {
            try {
                const res = await fetch('api/shop.php');
                const data = await res.json();
                shopData = data;
                applyTheme(data.theme);
                renderCategories();
                renderAnnouncements();
                renderProducts();
                renderCheckoutPaymentMethods();
                updateUserUI();

                if (data.chatbotConfig) {
                    if (data.chatbotConfig.botName) {
                        const botTitleEl = document.getElementById('chatbotTitle');
                        if (botTitleEl) botTitleEl.innerText = data.chatbotConfig.botName;
                    }
                    if (data.chatbotConfig.welcomeMessageMm) {
                        const botWelcomeEl = document.getElementById('chatbotWelcomeMsg');
                        if (botWelcomeEl) botWelcomeEl.innerText = data.chatbotConfig.welcomeMessageMm;
                    }
                    const botBtn = document.getElementById('chatbotToggleBtn');
                    if (botBtn && data.chatbotConfig.enabled === false) {
                        botBtn.classList.add('hidden');
                    } else if (botBtn && !chatbotOpen) {
                        botBtn.classList.remove('hidden');
                    }
                }
            } catch (err) {
                console.error("Failed loading shop data:", err);
            }
        }

        // Apply Theme Styles
        function applyTheme(theme) {
            const body = document.getElementById('appBody');
            body.className = body.className.replace(/theme-[a-z-]+/g, '');
            body.classList.add('theme-' + (theme || 'luxury-gold'));

            const hero = document.getElementById('heroBanner');
            if (theme === 'rose-paris') {
                hero.className = 'rounded-3xl p-6 sm:p-10 text-white relative overflow-hidden shadow-2xl bg-gradient-to-r from-rose-950 via-rose-900 to-pink-900';
            } else if (theme === 'minimal-noir') {
                hero.className = 'rounded-3xl p-6 sm:p-10 text-white relative overflow-hidden shadow-2xl bg-gradient-to-r from-stone-950 via-neutral-900 to-emerald-950';
            } else {
                hero.className = 'rounded-3xl p-6 sm:p-10 text-white relative overflow-hidden shadow-2xl bg-gradient-to-r from-stone-900 via-stone-800 to-amber-950';
            }
        }

        // Render Announcements
        function renderAnnouncements() {
            const container = document.getElementById('announcementsList');
            container.innerHTML = '';
            (shopData.announcements || []).forEach(ann => {
                const card = document.createElement('div');
                card.className = 'p-4 rounded-2xl bg-white border border-stone-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition';
                const tagClass = ann.tag === 'Promotion' ? 'bg-amber-100 text-amber-800' : (ann.tag === 'Wholesale' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800');
                card.innerHTML = `
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full ${tagClass}">${escapeHtml(ann.tag)}</span>
                            <span class="text-[11px] text-stone-400">${escapeHtml(ann.authorName || 'ပုံ့ပုံ့ ဆိုင်')}</span>
                        </div>
                        <h4 class="font-bold text-stone-900 text-xs sm:text-sm line-clamp-1">${escapeHtml(ann.title)}</h4>
                        <p class="text-xs text-stone-600 mt-1 line-clamp-2 leading-relaxed">${escapeHtml(ann.content)}</p>
                    </div>
                `;
                container.appendChild(card);
            });
            lucide.createIcons();
        }

        // Render Categories Tabs
        function renderCategories() {
            const container = document.getElementById('categoryTabs');
            container.innerHTML = '';

            const cats = [{ id: 'All', labelEn: 'All Bags', labelMm: 'အားလုံး' }, ...(shopData.categories || []).filter(c => c.id !== 'All')];

            cats.forEach(c => {
                const isActive = (c.id === activeCategory);
                const btn = document.createElement('button');
                btn.className = `px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition ${isActive ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'bg-white border border-stone-200 text-stone-700 hover:border-amber-400'}`;
                btn.innerHTML = `<span>${escapeHtml(c.labelEn)}</span>${c.labelMm ? ` <span class="opacity-75 font-medium text-[11px]">(${escapeHtml(c.labelMm)})</span>` : ''}`;
                btn.onclick = () => {
                    activeCategory = c.id;
                    renderCategories();
                    renderProducts();
                };
                container.appendChild(btn);
            });
        }

        // Handle Search Input
        function handleSearch(val) {
            searchQuery = val.trim().toLowerCase();
            renderProducts();
        }

        // Render Products Grid
        function renderProducts() {
            const container = document.getElementById('productsGrid');
            const empty = document.getElementById('emptyState');
            const sortBy = document.getElementById('sortBy').value;

            let filtered = [...(shopData.products || [])];

            // Filter by Category
            if (activeCategory !== 'All') {
                filtered = filtered.filter(p => p.category.toLowerCase() === activeCategory.toLowerCase());
            }

            // Filter by Search Query
            if (searchQuery) {
                filtered = filtered.filter(p =>
                    p.nameEn.toLowerCase().includes(searchQuery) ||
                    p.nameMm.toLowerCase().includes(searchQuery) ||
                    p.descriptionMm.toLowerCase().includes(searchQuery) ||
                    p.category.toLowerCase().includes(searchQuery)
                );
            }

            // Sort
            if (sortBy === 'price-asc') {
                filtered.sort((a, b) => a.price - b.price);
            } else if (sortBy === 'price-desc') {
                filtered.sort((a, b) => b.price - a.price);
            } else if (sortBy === 'rating') {
                filtered.sort((a, b) => b.rating - a.rating);
            }

            container.innerHTML = '';
            if (filtered.length === 0) {
                empty.classList.remove('hidden');
                return;
            } else {
                empty.classList.add('hidden');
            }

            filtered.forEach(p => {
                const card = document.createElement('div');
                card.className = 'group bg-white rounded-3xl border border-stone-200/80 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between';

                const colorsDots = (p.colors || []).map(c => `
                    <span class="w-3.5 h-3.5 rounded-full border border-stone-300 inline-block" style="background-color: ${escapeHtml(c.hex)};" title="${escapeHtml(c.name)}"></span>
                `).join('');

                card.innerHTML = `
                    <div>
                        <!-- Image Container -->
                        <div class="relative h-60 w-full bg-stone-100 overflow-hidden cursor-pointer" onclick="openProductModal('${escapeHtml(p.id)}')">
                            <img src="${escapeHtml(p.image)}" alt="${escapeHtml(p.nameEn)}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            ${p.badge ? `<span class="absolute top-3 left-3 bg-amber-500 text-stone-950 font-black text-[10px] px-2.5 py-1 rounded-full shadow-md uppercase tracking-wider">${escapeHtml(p.badge)}</span>` : ''}
                            <div class="absolute bottom-3 right-3 bg-white/90 backdrop-blur-xs px-2 py-0.5 rounded-lg text-[10px] font-bold text-stone-800 flex items-center gap-1 shadow-xs">
                                <i data-lucide="star" class="w-3 h-3 text-amber-500 fill-amber-500"></i> ${p.rating} (${p.reviewsCount})
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4 space-y-2">
                            <div class="flex items-center gap-1.5">
                                ${colorsDots}
                            </div>
                            <div>
                                <h3 class="font-extrabold text-stone-900 text-sm line-clamp-1 group-hover:text-amber-700 transition cursor-pointer" onclick="openProductModal('${escapeHtml(p.id)}')">${escapeHtml(p.nameEn)}</h3>
                                <p class="text-xs text-stone-500 line-clamp-1 font-medium">${escapeHtml(p.nameMm)}</p>
                            </div>

                            <div class="pt-1">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-lg font-black text-amber-900">${p.price.toLocaleString()} Ks</span>
                                    ${p.originalPrice ? `<span class="text-xs text-stone-400 line-through">${p.originalPrice.toLocaleString()} Ks</span>` : ''}
                                </div>
                                ${p.wholesalePrice ? `
                                    <div class="text-[11px] font-extrabold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md inline-block mt-1">
                                        လက်ကား: ${p.wholesalePrice.toLocaleString()} Ks (၅ လုံး+)
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="p-4 pt-0">
                        <button onclick="quickAddToCart('${escapeHtml(p.id)}')" class="w-full py-2.5 px-3 rounded-xl bg-stone-900 hover:bg-black text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                            <i data-lucide="shopping-bag" class="w-4 h-4 text-amber-400"></i>
                            <span>ခြင်းတောင်းထဲ ထည့်မည်</span>
                        </button>
                    </div>
                `;
                container.appendChild(card);
            });

            lucide.createIcons();
        }

        // Open Product Detail Modal
        function openProductModal(productId) {
            const p = shopData.products.find(item => item.id === productId);
            if (!p) return;
            selectedProductForModal = p;
            selectedColorForModal = (p.colors && p.colors[0]) ? p.colors[0].name : 'Default';

            document.getElementById('modalProductImage').src = p.image;
            document.getElementById('modalProductBadge').innerText = p.badge || 'Popular';
            document.getElementById('modalProductBadge').style.display = p.badge ? 'inline-block' : 'none';
            document.getElementById('modalProductCategory').innerText = p.category;
            document.getElementById('modalProductNameEn').innerText = p.nameEn;
            document.getElementById('modalProductNameMm').innerText = p.nameMm;
            document.getElementById('modalProductPrice').innerText = p.price.toLocaleString() + ' Ks';
            document.getElementById('modalProductOriginalPrice').innerText = p.originalPrice ? (p.originalPrice.toLocaleString() + ' Ks') : '';
            document.getElementById('modalProductMaterial').innerText = p.material;
            document.getElementById('modalProductDimensions').innerText = p.dimensions;
            document.getElementById('modalProductStock').innerText = p.stock + ' လုံး အသင့်ရှိ';
            document.getElementById('modalProductDescMm').innerText = p.descriptionMm;
            document.getElementById('selectedColorName').innerText = selectedColorForModal;

            if (p.wholesalePrice) {
                document.getElementById('modalWholesalePriceBox').style.display = 'flex';
                document.getElementById('modalWholesalePriceText').innerText = `လက်ကားဈေး - ${p.wholesalePrice.toLocaleString()} Ks (၅ လုံးနှင့်အထက် မှာယူပါက)`;
            } else {
                document.getElementById('modalWholesalePriceBox').style.display = 'none';
            }

            // Colors List
            const colorsList = document.getElementById('modalColorsList');
            colorsList.innerHTML = '';
            (p.colors || []).forEach(c => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold transition ${c.name === selectedColorForModal ? 'border-amber-600 bg-amber-50 text-amber-950 ring-2 ring-amber-600/20' : 'border-stone-300 bg-white hover:border-stone-400 text-stone-700'}`;
                btn.innerHTML = `<span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color:${escapeHtml(c.hex)}"></span> <span>${escapeHtml(c.name)}</span>`;
                btn.onclick = () => {
                    selectedColorForModal = c.name;
                    document.getElementById('selectedColorName').innerText = c.name;
                    openProductModal(productId);
                };
                colorsList.appendChild(btn);
            });

            document.getElementById('productModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeProductModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        // Add to Cart
        function quickAddToCart(productId) {
            const p = shopData.products.find(item => item.id === productId);
            if (!p) return;
            const defaultColor = (p.colors && p.colors[0]) ? p.colors[0].name : 'Default';
            addToCart(p, defaultColor, 1);
            toggleCartDrawer(true);
        }

        function addModalProductToCart() {
            if (!selectedProductForModal) return;
            addToCart(selectedProductForModal, selectedColorForModal, 1);
            closeProductModal();
            toggleCartDrawer(true);
        }

        function buyNowModalProduct() {
            if (!selectedProductForModal) return;
            addToCart(selectedProductForModal, selectedColorForModal, 1);
            closeProductModal();
            toggleCartDrawer(true);
            proceedToCheckout();
        }

        function addToCart(product, color, qty) {
            const existing = cart.find(item => item.productId === product.id && item.selectedColor === color);
            if (existing) {
                existing.quantity += qty;
            } else {
                cart.push({
                    productId: product.id,
                    selectedColor: color,
                    quantity: qty
                });
            }
            saveCartToStorage();
            updateCartUI();
        }

        function updateCartQty(productId, color, delta) {
            const item = cart.find(i => i.productId === productId && i.selectedColor === color);
            if (!item) return;
            item.quantity += delta;
            if (item.quantity <= 0) {
                cart = cart.filter(i => !(i.productId === productId && i.selectedColor === color));
            }
            saveCartToStorage();
            updateCartUI();
        }

        function removeCartItem(productId, color) {
            cart = cart.filter(i => !(i.productId === productId && i.selectedColor === color));
            saveCartToStorage();
            updateCartUI();
        }

        function saveCartToStorage() {
            localStorage.setItem('pontpont_cart', JSON.stringify(cart));
        }

        function loadCartFromStorage() {
            try {
                const saved = localStorage.getItem('pontpont_cart');
                if (saved) cart = JSON.parse(saved);
            } catch {}
            updateCartUI();
        }

        // Update Cart Drawer UI
        function updateCartUI() {
            const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCountBadge').innerText = totalCount;

            const list = document.getElementById('cartItemList');
            list.innerHTML = '';

            let subtotal = 0;

            if (cart.length === 0) {
                list.innerHTML = `
                    <div class="text-center py-10 text-stone-400">
                        <i data-lucide="shopping-bag" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                        <p class="text-xs font-semibold">ခြင်းတောင်းထဲတွင် ပစ္စည်းမရှိသေးပါ</p>
                    </div>
                `;
                document.getElementById('cartNextBtn').disabled = true;
                document.getElementById('cartNextBtn').classList.add('opacity-50');
            } else {
                document.getElementById('cartNextBtn').disabled = false;
                document.getElementById('cartNextBtn').classList.remove('opacity-50');

                cart.forEach(item => {
                    const prod = shopData.products.find(p => p.id === item.productId);
                    if (!prod) return;
                    const itemTotal = prod.price * item.quantity;
                    subtotal += itemTotal;

                    const row = document.createElement('div');
                    row.className = 'flex gap-3 p-3 rounded-2xl bg-stone-50 border border-stone-200/80 items-center';
                    row.innerHTML = `
                        <img src="${escapeHtml(prod.image)}" class="w-14 h-14 object-cover rounded-xl border border-stone-200">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-xs text-stone-900 truncate">${escapeHtml(prod.nameEn)}</h4>
                            <p class="text-[11px] text-stone-500">အရောင်: ${escapeHtml(item.selectedColor)}</p>
                            <p class="text-xs font-extrabold text-amber-700">${prod.price.toLocaleString()} Ks</p>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white border border-stone-200 rounded-xl px-2 py-1">
                            <button onclick="updateCartQty('${escapeHtml(item.productId)}', '${escapeHtml(item.selectedColor)}', -1)" class="text-stone-500 hover:text-stone-900 font-bold px-1 text-xs">-</button>
                            <span class="text-xs font-bold w-4 text-center">${item.quantity}</span>
                            <button onclick="updateCartQty('${escapeHtml(item.productId)}', '${escapeHtml(item.selectedColor)}', 1)" class="text-stone-500 hover:text-stone-900 font-bold px-1 text-xs">+</button>
                        </div>
                        <button onclick="removeCartItem('${escapeHtml(item.productId)}', '${escapeHtml(item.selectedColor)}')" class="text-stone-400 hover:text-red-600 p-1">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    `;
                    list.appendChild(row);
                });
            }

            let discount = 0;
            if (appliedCoupon) {
                if (appliedCoupon.discountType === 'percent') {
                    discount = Math.round(subtotal * (appliedCoupon.discountValue / 100));
                } else {
                    discount = Math.min(subtotal, appliedCoupon.discountAmount || appliedCoupon.discountValue);
                }
            } else if (appliedPromo) {
                discount = Math.round(subtotal * 0.1);
            }
            const cityFee = getSelectedCityFee();
            const deliveryFee = (subtotal - discount >= 100000) ? 0 : cityFee;
            const total = Math.max(0, subtotal - discount + deliveryFee);

            document.getElementById('cartSubtotalText').innerText = subtotal.toLocaleString() + ' Ks';
            document.getElementById('cartDiscountText').innerText = '-' + discount.toLocaleString() + ' Ks';
            document.getElementById('cartDiscountRow').style.display = (discount > 0) ? 'flex' : 'none';
            document.getElementById('cartDeliveryFeeText').innerText = (deliveryFee === 0 ? 'Free (အခမဲ့)' : deliveryFee.toLocaleString() + ' Ks');
            document.getElementById('cartTotalText').innerText = total.toLocaleString() + ' Ks';

            lucide.createIcons();
        }

        function getSelectedCityFee() {
            const select = document.getElementById('orderCity');
            if (!select || !select.selectedOptions[0]) return 2500;
            return parseInt(select.selectedOptions[0].getAttribute('data-fee') || '2500');
        }

        function updateDeliveryFeeFromCity() {
            updateCartUI();
        }

        function handleSlipFileSelect(event) {
            const file = event.target.files[0];
            if (!file) {
                clearSlipFile();
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('ပုံအရွယ်အစားသည် 5MB ထက် မကျော်ရပါ');
                event.target.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                selectedSlipBase64 = e.target.result;
                const previewImg = document.getElementById('slipPreviewImg');
                const previewBox = document.getElementById('slipPreviewContainer');
                if (previewImg && previewBox) {
                    previewImg.src = selectedSlipBase64;
                    previewBox.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(file);
        }

        function clearSlipFile() {
            selectedSlipBase64 = null;
            const input = document.getElementById('orderSlipInput');
            if (input) input.value = '';
            const previewBox = document.getElementById('slipPreviewContainer');
            if (previewBox) previewBox.classList.add('hidden');
        }

        async function applyPromoCode() {
            const codeInput = document.getElementById('promoInput');
            const code = codeInput ? codeInput.value.trim().toUpperCase() : '';
            const msg = document.getElementById('promoMessage');
            if (!code) {
                appliedCoupon = null;
                appliedPromo = false;
                msg.classList.add('hidden');
                updateCartUI();
                return;
            }

            // Calculate current subtotal
            let subtotal = 0;
            cart.forEach(item => {
                const prod = shopData.products.find(p => p.id === item.productId);
                if (prod) subtotal += prod.price * item.quantity;
            });

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'VALIDATE_COUPON', code: code, subtotal: subtotal })
                });
                const data = await res.json();
                if (data.ok && data.coupon) {
                    appliedCoupon = data.coupon;
                    appliedPromo = true;
                    const discountText = appliedCoupon.discountType === 'percent'
                        ? `${appliedCoupon.discountValue}%`
                        : `${appliedCoupon.discountAmount.toLocaleString()} Ks`;
                    msg.innerText = `✅ ${discountText} လျှော့ဈေး အောင်မြင်စွာ ရရှိပါသည်!`;
                    msg.classList.remove('hidden', 'text-red-600');
                    msg.classList.add('text-emerald-600');
                } else {
                    appliedCoupon = null;
                    appliedPromo = false;
                    msg.innerText = `❌ ${data.error || 'ကူပွန်ကုဒ် မှားယွင်းနေပါသည်။'}`;
                    msg.classList.remove('hidden', 'text-emerald-600');
                    msg.classList.add('text-red-600');
                }
            } catch (err) {
                appliedCoupon = null;
                appliedPromo = false;
                msg.innerText = '❌ ကူပွန် စစ်ဆေး၍ မရပါ';
                msg.classList.remove('hidden', 'text-emerald-600');
                msg.classList.add('text-red-600');
            }
            updateCartUI();
        }

        function toggleCartDrawer(open) {
            const drawer = document.getElementById('cartDrawer');
            if (open) {
                drawer.classList.remove('hidden');
                switchCartStep('items');
                updateCartUI();
            } else {
                drawer.classList.add('hidden');
            }
        }

        function switchCartStep(step) {
            document.getElementById('cartStepItems').classList.toggle('hidden', step !== 'items');
            document.getElementById('cartStepCheckout').classList.toggle('hidden', step !== 'checkout');
            document.getElementById('cartStepSuccess').classList.toggle('hidden', step !== 'success');

            document.getElementById('cartNextBtn').classList.toggle('hidden', step !== 'items');
            document.getElementById('cartSubmitBtn').classList.toggle('hidden', step !== 'checkout');
            document.getElementById('cartDrawerFooter').classList.toggle('hidden', step === 'success');

            if (step === 'checkout') {
                document.getElementById('cartDrawerTitle').innerText = 'အော်ဒါ အချက်အလက် ဖြည့်ရန်';
                if (shopData.currentUser) {
                    document.getElementById('orderCustomerName').value = shopData.currentUser.name || '';
                    document.getElementById('orderPhone').value = shopData.currentUser.phone || '';
                    document.getElementById('orderAddress').value = shopData.currentUser.address || '';
                }
                renderCheckoutPaymentMethods();
            } else if (step === 'items') {
                document.getElementById('cartDrawerTitle').innerText = 'ဝယ်ယူမည့် ခြင်းတောင်း';
            }
        }

        function proceedToCheckout() {
            if (cart.length === 0) return;
            switchCartStep('checkout');
        }

        let activeCheckoutAccountNumber = '09963000502';

        function renderCheckoutPaymentMethods() {
            const container = document.getElementById('checkoutPaymentMethodsContainer');
            if (!container) return;
            container.innerHTML = '';

            const methods = (shopData.paymentMethods && shopData.paymentMethods.length > 0)
                ? shopData.paymentMethods.filter(m => m.isActive)
                : [
                    { id: 'pm-kpay', code: 'KPay', name: 'KBZPay', nameMm: 'KBZPay (ငွေလွှဲ)', type: 'transfer', accountNumber: '09963000502', accountName: 'Ma Pont Pont (မပုံ့)', instructions: 'KBZPay ဖြင့် ငွေလွှဲပြီးပါက ပြေစာ Screenshot တင်ပေးပါရန်', requiresSlip: true },
                    { id: 'pm-wave', code: 'WavePay', name: 'WavePay', nameMm: 'WavePay (ငွေလွှဲ)', type: 'transfer', accountNumber: '09963000502', accountName: 'Ma Pont Pont (မပုံ့)', instructions: 'WavePay ဖြင့် ငွေလွှဲပြီးပါက ပြေစာ Screenshot တင်ပေးပါရန်', requiresSlip: true },
                    { id: 'pm-cod', code: 'COD', name: 'Cash on Delivery', nameMm: 'အိမ်အရောက်ငွေချေ (COD)', type: 'cod', accountNumber: '', accountName: '', instructions: 'မန္တလေးမြို့တွင်း ပစ္စည်းရောက်မှ ငွေချေစနစ် ဖြစ်ပါသည်', requiresSlip: false }
                ];

            methods.forEach((pm, idx) => {
                const label = document.createElement('label');
                const isFirst = (idx === 0);
                label.className = `payment-card border border-stone-200 rounded-xl p-2.5 text-center cursor-pointer hover:border-amber-500 transition relative flex flex-col items-center justify-center text-xs ${isFirst ? 'border-amber-500 bg-amber-50/30' : ''}`;
                
                let textColor = 'text-stone-800';
                const codeUpper = (pm.code || '').toUpperCase();
                if (codeUpper.includes('KPAY') || codeUpper.includes('KBZ')) textColor = 'text-blue-700';
                else if (codeUpper.includes('WAVE')) textColor = 'text-amber-600';
                else if (codeUpper.includes('AYA')) textColor = 'text-red-700';
                else if (codeUpper.includes('CB')) textColor = 'text-sky-700';

                let subText = 'ငွေလွှဲ';
                if (pm.type === 'cod') subText = 'အိမ်ရောက်ချေ';
                else if (pm.type === 'banking') subText = 'ဘဏ်ချေ';

                label.innerHTML = `
                    <input type="radio" name="paymentMethod" value="${escapeHtml(pm.code)}" data-pm-id="${escapeHtml(pm.id)}" ${isFirst ? 'checked' : ''} onchange="togglePaymentDetails()" class="hidden">
                    <div class="font-bold ${textColor}">${escapeHtml(pm.name || pm.code)}</div>
                    <div class="text-[10px] text-stone-500">${escapeHtml(subText)}</div>
                `;
                container.appendChild(label);
            });

            togglePaymentDetails();
        }

        function togglePaymentDetails() {
            const checkedRadio = document.querySelector('input[name="paymentMethod"]:checked');
            if (!checkedRadio) return;
            const selectedCode = checkedRadio.value;
            const pmId = checkedRadio.getAttribute('data-pm-id');

            // Update card styling
            document.querySelectorAll('#checkoutPaymentMethodsContainer .payment-card').forEach(card => {
                const radio = card.querySelector('input[type="radio"]');
                if (radio && radio.checked) {
                    card.classList.add('border-amber-500', 'bg-amber-50/30');
                    card.classList.remove('border-stone-200');
                } else {
                    card.classList.remove('border-amber-500', 'bg-amber-50/30');
                    card.classList.add('border-stone-200');
                }
            });

            // Find payment method object
            const methods = shopData.paymentMethods || [];
            const pm = methods.find(m => m.id === pmId || m.code === selectedCode) || {
                code: selectedCode,
                name: selectedCode,
                type: selectedCode === 'COD' ? 'cod' : 'transfer',
                accountNumber: '09963000502',
                accountName: 'Ma Pont Pont (ပုံ့ပုံ့ အိတ်ဆိုင် - မန္တလေး)',
                instructions: '',
                requiresSlip: selectedCode !== 'COD'
            };

            const box = document.getElementById('transferInfoBox');
            if (!box) return;

            if (pm.type === 'cod') {
                box.classList.remove('bg-blue-50', 'border-blue-200');
                box.classList.add('bg-stone-50', 'border-stone-200');
                document.getElementById('transferTitle').innerText = 'COD အိမ်ရောက်ငွေချေ စနစ်';
                document.getElementById('transferPhoneDisplay').innerText = 'ပစ္စည်းရောက်မှ ငွေချေပေးပါရှင်';
                document.getElementById('btnCopyAccount').classList.add('hidden');
                document.getElementById('transferNameRow').classList.add('hidden');
                document.getElementById('transferTxRefRow').classList.add('hidden');
                document.getElementById('transferSlipRow').classList.add('hidden');
                document.getElementById('transferQrBox').classList.add('hidden');
                if (pm.instructions) {
                    document.getElementById('transferInstructions').innerText = pm.instructions;
                    document.getElementById('transferInstructions').classList.remove('hidden');
                } else {
                    document.getElementById('transferInstructions').classList.add('hidden');
                }
            } else {
                box.classList.add('bg-blue-50', 'border-blue-200');
                box.classList.remove('bg-stone-50', 'border-stone-200');
                document.getElementById('transferTitle').innerText = (pm.nameMm || pm.name || pm.code) + ' အကောင့်နံပါတ်';
                activeCheckoutAccountNumber = pm.accountNumber || '';
                document.getElementById('transferPhoneDisplay').innerText = pm.accountNumber || '09-963000502';
                
                if (pm.accountNumber) {
                    document.getElementById('btnCopyAccount').classList.remove('hidden');
                } else {
                    document.getElementById('btnCopyAccount').classList.add('hidden');
                }

                if (pm.accountName) {
                    document.getElementById('transferNameDisplay').innerText = pm.accountName;
                    document.getElementById('transferNameRow').classList.remove('hidden');
                } else {
                    document.getElementById('transferNameRow').classList.add('hidden');
                }

                if (pm.instructions) {
                    document.getElementById('transferInstructions').innerText = pm.instructions;
                    document.getElementById('transferInstructions').classList.remove('hidden');
                } else {
                    document.getElementById('transferInstructions').classList.add('hidden');
                }

                // QR Code
                if (pm.qrCodeUrl) {
                    document.getElementById('transferQrImg').src = pm.qrCodeUrl;
                    document.getElementById('transferQrLink').href = pm.qrCodeUrl;
                    document.getElementById('transferQrBox').classList.remove('hidden');
                } else {
                    document.getElementById('transferQrBox').classList.add('hidden');
                }

                document.getElementById('transferTxRefRow').classList.remove('hidden');
                document.getElementById('transferSlipRow').classList.remove('hidden');

                const labelTx = document.getElementById('labelTxRef');
                if (labelTx) {
                    labelTx.innerText = pm.requiresSlip
                        ? 'ငွေလွှဲပြေစာ နောက်ဆုံး (၆) လုံး (Transaction Ref) *'
                        : 'ငွေလွှဲပြေစာ နောက်ဆုံး (၆) လုံး (Transaction Ref - Optional)';
                }
            }
        }

        function copyAccountNo() {
            const acc = activeCheckoutAccountNumber || '09963000502';
            navigator.clipboard.writeText(acc);
            alert('အကောင့်နံပါတ် ' + acc + ' ကို ကူးယူပြီးပါပြီ။');
        }

        // Submit Order to API
        async function submitOrder() {
            const name = document.getElementById('orderCustomerName').value.trim();
            const phone = document.getElementById('orderPhone').value.trim();
            const city = document.getElementById('orderCity').value;
            const address = document.getElementById('orderAddress').value.trim();
            const note = document.getElementById('orderNote').value.trim();
            const checkedPaymentRadio = document.querySelector('input[name="paymentMethod"]:checked');
            const paymentMethod = checkedPaymentRadio ? checkedPaymentRadio.value : 'KPay';
            const pmId = checkedPaymentRadio ? checkedPaymentRadio.getAttribute('data-pm-id') : null;
            const txRef = document.getElementById('orderTxRef').value.trim();

            if (!name || !phone || !address) {
                alert('အမည်၊ ဖုန်းနံပါတ်နှင့် လိပ်စာ အားလုံး ဖြည့်စွက်ပေးပါ။');
                return;
            }

            // Find current payment method config
            const methods = shopData.paymentMethods || [];
            const currentPm = methods.find(m => m.id === pmId || m.code === paymentMethod) || {
                type: paymentMethod === 'COD' ? 'cod' : 'transfer',
                requiresSlip: paymentMethod !== 'COD'
            };

            if (currentPm.type !== 'cod' && currentPm.requiresSlip) {
                if (txRef.length < 4 && !selectedSlipBase64) {
                    alert('ငွေလွှဲပြေစာ နောက်ဆုံး ၆ လုံး (Transaction Ref) သို့မဟုတ် ပြေစာ Screenshot တင်ပေးပါရန်။');
                    return;
                }
            }

            // Upload slip if selected
            let uploadedSlipUrl = null;
            if (selectedSlipBase64 && paymentMethod !== 'COD') {
                try {
                    const slipRes = await fetch('api/shop.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'UPLOAD_SLIP', slip_data: selectedSlipBase64 })
                    });
                    const slipData = await slipRes.json();
                    if (slipData.ok && slipData.url) {
                        uploadedSlipUrl = slipData.url;
                    }
                } catch (e) {
                    console.error('Slip upload warning:', e);
                }
            }

            const items = cart.map(item => {
                const prod = shopData.products.find(p => p.id === item.productId);
                return {
                    productId: item.productId,
                    nameEn: prod ? prod.nameEn : '',
                    nameMm: prod ? prod.nameMm : '',
                    price: prod ? prod.price : 0,
                    quantity: item.quantity,
                    selectedColor: item.selectedColor,
                    image: prod ? prod.image : ''
                };
            });

            const orderPayload = {
                action: 'CREATE_ORDER',
                order: {
                    customerName: name,
                    phone: phone,
                    city: city,
                    address: address,
                    note: note,
                    paymentMethod: paymentMethod,
                    transactionRef: txRef,
                    paymentSlip: uploadedSlipUrl,
                    couponCode: appliedCoupon ? appliedCoupon.code : null,
                    discount: (appliedCoupon || appliedPromo) ? 10 : 0,
                    items: items
                }
            };

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderPayload)
                });
                const data = await res.json();
                if (data.ok && data.order) {
                    // Clear cart & slip
                    cart = [];
                    clearSlipFile();
                    appliedCoupon = null;
                    appliedPromo = false;
                    saveCartToStorage();
                    updateCartUI();

                    document.getElementById('successOrderId').innerText = data.order.id;
                    document.getElementById('successOrderTotal').innerText = data.order.total.toLocaleString() + ' Ks';
                    document.getElementById('successOrderPayment').innerText = data.order.paymentMethod;
                    switchCartStep('success');
                } else {
                    alert('Order Error: ' + (data.error || 'Server error'));
                }
            } catch (err) {
                alert('အင်တာနက် သို့မဟုတ် ဆာဗာ ချိတ်ဆက်မှု အဆင်မပြေပါ: ' + err);
            }
        }

        // ===================================================================
        // AUTH & USER LOGIC
        // ===================================================================
        function handleUserBtnClick() {
            if (shopData.currentUser) {
                openProfileModal();
            } else {
                openAuthModal();
            }
        }

        function openAuthModal() {
            document.getElementById('authModal').classList.remove('hidden');
            switchAuthTab('login');
        }

        function closeAuthModal() {
            document.getElementById('authModal').classList.add('hidden');
        }

        function switchAuthTab(tab) {
            document.getElementById('loginForm').classList.toggle('hidden', tab !== 'login');
            document.getElementById('registerForm').classList.toggle('hidden', tab !== 'register');
            document.getElementById('authTabLogin').className = tab === 'login' ? 'flex-1 pb-3 text-sm font-bold text-amber-600 border-b-2 border-amber-600 text-center' : 'flex-1 pb-3 text-sm font-bold text-stone-500 border-b-2 border-transparent text-center hover:text-stone-800';
            document.getElementById('authTabRegister').className = tab === 'register' ? 'flex-1 pb-3 text-sm font-bold text-amber-600 border-b-2 border-amber-600 text-center' : 'flex-1 pb-3 text-sm font-bold text-stone-500 border-b-2 border-transparent text-center hover:text-stone-800';
        }

        function fillDemo(email, pass) {
            document.getElementById('loginEmail').value = email;
            document.getElementById('loginPassword').value = pass;
        }

        async function handleLogin(e) {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value.trim();
            const password = document.getElementById('loginPassword').value;

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'LOGIN', email, password })
                });
                const data = await res.json();
                if (data.ok) {
                    shopData.currentUser = data.user;
                    closeAuthModal();
                    loadShopData();
                    alert(`မင်္ဂလာပါ ${data.user.name}၊ အကောင့်အောင်မြင်စွာ ဝင်ရောက်ပြီးပါပြီ။`);
                } else {
                    alert(data.error || 'အီးမေးလ် သို့မဟုတ် စကားဝှက် မှားယွင်းနေပါသည်။');
                }
            } catch (err) {
                alert('ချိတ်ဆက်မှု မအောင်မြင်ပါ: ' + err);
            }
        }

        async function handleRegister(e) {
            e.preventDefault();
            const name = document.getElementById('regName').value.trim();
            const email = document.getElementById('regEmail').value.trim();
            const password = document.getElementById('regPassword').value;
            const phone = document.getElementById('regPhone').value.trim();
            const city = document.getElementById('regCity').value.trim();
            const address = document.getElementById('regAddress').value.trim();

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'REGISTER', name, email, password, phone, city, address })
                });
                const data = await res.json();
                if (data.ok) {
                    shopData.currentUser = data.user;
                    closeAuthModal();
                    loadShopData();
                    alert('အကောင့်အသစ် အောင်မြင်စွာ ဖွင့်ပြီးပါပြီ။');
                } else {
                    alert(data.error || 'အကောင့်ဖွင့်ခြင်း မအောင်မြင်ပါ။');
                }
            } catch (err) {
                alert('Error: ' + err);
            }
        }

        async function handleLogout() {
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'LOGOUT' })
                });
                shopData.currentUser = null;
                closeProfileModal();
                loadShopData();
                location.reload();
            } catch (err) {
                location.reload();
            }
        }

        function updateUserUI() {
            const btnText = document.getElementById('userBtnText');
            const adminBtn = document.getElementById('adminBtn');

            if (shopData.currentUser) {
                btnText.innerText = shopData.currentUser.name;
                if (shopData.currentUser.role === 'admin') {
                    adminBtn.classList.remove('hidden');
                } else {
                    adminBtn.classList.add('hidden');
                }
            } else {
                btnText.innerText = 'အကောင့်ဝင်ရန်';
                adminBtn.classList.add('hidden');
            }
        }

        // ===================================================================
        // PROFILE & ORDERS
        // ===================================================================
        function openProfileModal() {
            if (!shopData.currentUser) return;
            const u = shopData.currentUser;
            document.getElementById('profileName').innerText = u.name;
            document.getElementById('profileEmail').innerText = u.email + ' (' + u.role + ')';
            document.getElementById('editName').value = u.name;
            document.getElementById('editPhone').value = u.phone;
            document.getElementById('editCity').value = u.city;
            document.getElementById('editAddress').value = u.address;

            renderProfileOrders();
            document.getElementById('profileModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
        }

        async function handleUpdateProfile(e) {
            e.preventDefault();
            const name = document.getElementById('editName').value.trim();
            const phone = document.getElementById('editPhone').value.trim();
            const city = document.getElementById('editCity').value.trim();
            const address = document.getElementById('editAddress').value.trim();
            const newPassword = document.getElementById('editPassword').value;

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'UPDATE_PROFILE',
                        userId: shopData.currentUser.id,
                        name, phone, city, address, newPassword
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    shopData.currentUser = data.user;
                    alert('ကိုယ်ရေးအချက်အလက်များကို သိမ်းဆည်းပြီးပါပြီ။');
                    openProfileModal();
                } else {
                    alert(data.error);
                }
            } catch (err) {
                alert(err);
            }
        }

        function renderProfileOrders() {
            const list = document.getElementById('profileOrdersList');
            list.innerHTML = '';
            const orders = shopData.orders || [];

            if (orders.length === 0) {
                list.innerHTML = `<p class="text-xs text-stone-400 py-4 text-center">မှာယူထားသော အော်ဒါမှတ်တမ်း မရှိသေးပါ။</p>`;
                return;
            }

            orders.forEach(o => {
                const statusColors = {
                    'Pending': 'bg-amber-100 text-amber-800',
                    'Confirmed': 'bg-blue-100 text-blue-800',
                    'Shipped': 'bg-purple-100 text-purple-800',
                    'Delivered': 'bg-emerald-100 text-emerald-800',
                    'Cancelled': 'bg-red-100 text-red-800'
                };
                const card = document.createElement('div');
                card.className = 'p-3 rounded-2xl bg-white border border-stone-200 text-xs space-y-2';
                const itemsText = o.items.map(i => `${escapeHtml(i.nameEn)} (${escapeHtml(i.selectedColor)}) x ${Number(i.quantity) || 1}`).join(', ');
                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-stone-900">${escapeHtml(o.id)}</span>
                        <span class="px-2.5 py-0.5 rounded-full font-bold ${statusColors[o.status] || 'bg-stone-100'}">${escapeHtml(o.status)}</span>
                    </div>
                    <div class="text-stone-600">
                        <p>${itemsText}</p>
                        <p class="mt-1 font-bold text-stone-900">ကျသင့်ငွေ: ${Number(o.total || 0).toLocaleString()} Ks (${escapeHtml(o.paymentMethod)})</p>
                    </div>
                `;
                list.appendChild(card);
            });
        }

        // ===================================================================
        // ADMIN DASHBOARD
        // ===================================================================
        function openAdminModal() {
            if (!shopData.currentUser || shopData.currentUser.role !== 'admin') return;
            document.getElementById('adminModal').classList.remove('hidden');
            switchAdminTab('overview');
            updateAdminStats();
            renderAdminOrders();
            renderAdminProducts();
            renderAdminCategories();
            renderAdminAnnouncements();
            renderAdminUsers();
            lucide.createIcons();
        }

        function closeAdminModal() {
            document.getElementById('adminModal').classList.add('hidden');
        }

        function switchAdminTab(tab) {
            document.querySelectorAll('.admin-pane').forEach(el => el.classList.add('hidden'));
            const targetPane = document.getElementById('adminPane' + tab);
            if (targetPane) targetPane.classList.remove('hidden');

            document.querySelectorAll('.admin-tab-btn').forEach(btn => {
                btn.className = 'admin-tab-btn px-4 py-2 rounded-xl bg-stone-100 text-stone-700 hover:bg-stone-200';
            });
            const activeBtn = document.getElementById('adminTab' + tab);
            if (activeBtn) activeBtn.className = 'admin-tab-btn px-4 py-2 rounded-xl bg-amber-600 text-white';

            if (tab === 'categories') {
                renderAdminCategories();
            } else if (tab === 'chatbot') {
                loadChatbotSettingsIntoForm();
            }
            lucide.createIcons();
        }

        // Categories Management
        function renderAdminCategories() {
            const container = document.getElementById('adminCategoriesList');
            if (!container) return;
            container.innerHTML = '';
            const cats = (shopData.categories || []).filter(c => c.id !== 'All');

            cats.forEach(cat => {
                const count = (shopData.products || []).filter(p => p.category.toLowerCase() === cat.id.toLowerCase()).length;
                const div = document.createElement('div');
                div.className = 'p-3.5 rounded-2xl border border-stone-200 bg-stone-50 flex items-center justify-between text-xs';
                div.innerHTML = `
                    <div>
                        <span class="text-[10px] font-bold uppercase text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md">${cat.id}</span>
                        <h5 class="font-bold text-stone-900 mt-1">${cat.labelEn}</h5>
                        <p class="text-[11px] text-stone-500">${count} items</p>
                    </div>
                    <div>
                        <button onclick="handleDeleteCategory('${cat.id}')" class="px-2.5 py-1 text-red-600 hover:bg-red-50 rounded-lg text-xs font-bold transition">Delete</button>
                    </div>
                `;
                container.appendChild(div);
            });
            lucide.createIcons();
        }

        async function handleAddCategory(e) {
            e.preventDefault();
            const label = document.getElementById('catLabelEn').value.trim();
            let catId = document.getElementById('catId').value.trim();
            if (!label) return;
            if (!catId) catId = label.replace(/[^a-zA-Z0-9]/g, '');

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'ADD_CATEGORY',
                        category: { id: catId, labelEn: label }
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    document.getElementById('catLabelEn').value = '';
                    document.getElementById('catId').value = '';
                    await loadShopData();
                    renderAdminCategories();
                    alert('Category အသစ် အောင်မြင်စွာ ထည့်သွင်းပြီးပါပြီ။');
                } else {
                    alert('Error: ' + (data.error || 'Server error'));
                }
            } catch (err) {
                alert(err);
            }
        }

        async function handleDeleteCategory(catId) {
            if (!confirm(`"${catId}" category အား ဖျက်ရန် သေချာပါသလား?`)) return;
            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'DELETE_CATEGORY', categoryId: catId })
                });
                const data = await res.json();
                if (data.ok) {
                    await loadShopData();
                    renderAdminCategories();
                } else {
                    alert('Error: ' + (data.error || 'Server error'));
                }
            } catch (err) {
                alert(err);
            }
        }

        const DEFAULT_PROMPT_TEMPLATE = `You are "Pont Pont AI" (ပုံ့ပုံ့ AI အကူအညီ), the polite, friendly customer assistant for "Pont Pont Handbag Shop" (ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး) based in Mandalay, Myanmar.

Store Information:
- Owner: Ma Pont Pont (မပုံ့)
- Main Branch (ပင်မဆိုင်): 65th Street, Between 107th & 108th Streets, Mandalay (၆၅ လမ်း၊ ၁၀၇×၁၀၈ လမ်းကြား၊ မန္တလေးမြို့)
- Branch (2): 73rd Street, Between 36th & 37th Streets, Mandalay (၇၃ လမ်း၊ ၃၆×၃၇ လမ်းကြား၊ မန္တလေးမြို့)
- Branch (3): 78th Street, Near Thakhun Taing Market, Mandalay (၇၈ လမ်း၊ တံခွန်တိုင်ဈေးအနီး)
- Opening Hours: Daily 8:00 AM – 8:00 PM (နေ့စဉ် မနက် 8:00 မှ ည 8:00 အထိ)
- Contact Phones / Viber: 09-963000502, 09-750072752, 09-404448881
- Delivery: Mandalay downtown 2,500 MMK (COD available), Yangon 3,500 MMK, Naypyitaw 3,500 MMK, other cities 4,000 MMK via Bus Gates. Free delivery over 100,000 MMK.
- Payment Methods: KBZPay (09-963000502), WavePay (09-963000502), Mobile Banking. No extra percentage fee.
- Wholesale: Minimum 5-10 bags for wholesale discount. Resellers can contact 09-963000502 directly.`;

        function handleBotProviderChange() {
            const prov = document.getElementById('adminBotProvider').value;
            const keyBox = document.getElementById('adminApiKeyContainer');
            const urlBox = document.getElementById('adminApiUrlContainer');
            const modelInp = document.getElementById('adminBotModel');

            if (prov === 'mock') {
                keyBox.classList.add('hidden');
                urlBox.classList.add('hidden');
                modelInp.value = 'built-in';
            } else if (prov === 'custom') {
                keyBox.classList.remove('hidden');
                urlBox.classList.remove('hidden');
                if (!modelInp.value || modelInp.value === 'built-in') modelInp.value = 'gpt-3.5-turbo';
            } else {
                keyBox.classList.remove('hidden');
                urlBox.classList.add('hidden');
                if (prov === 'gemini') modelInp.value = 'gemini-1.5-flash';
                else if (prov === 'openai') modelInp.value = 'gpt-4o-mini';
                else if (prov === 'groq') modelInp.value = 'llama-3.3-70b-versatile';
            }
        }

        async function fetchAvailableModels() {
            const prov = document.getElementById('adminBotProvider').value;
            const apiKey = document.getElementById('adminBotApiKey').value.trim();
            const apiUrl = document.getElementById('adminBotApiUrl').value.trim();
            const btnText = document.getElementById('btnFetchModelsText');
            const badge = document.getElementById('modelStatusBadge');
            const select = document.getElementById('adminBotModelSelect');
            const inp = document.getElementById('adminBotModel');

            if (!apiKey && prov !== 'custom') {
                alert('Models များ ဆွဲယူရန် API Key အရင် ဖြည့်သွင်းပေးပါရှင်။');
                return;
            }

            btnText.innerText = 'Loading...';
            badge.innerText = '⚡ Models ဆွဲယူနေပါသည်...';
            badge.classList.remove('hidden');

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'FETCH_AI_MODELS',
                        provider: prov,
                        apiKey: apiKey,
                        apiUrl: apiUrl
                    })
                });
                const data = await res.json();
                if (data.ok && Array.isArray(data.models) && data.models.length > 0) {
                    select.innerHTML = '';
                    data.models.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m;
                        opt.innerText = m;
                        if (m === inp.value) opt.selected = true;
                        select.appendChild(opt);
                    });
                    select.classList.remove('hidden');
                    inp.classList.add('hidden');
                    inp.value = select.value;
                    badge.innerText = `✓ Models (${data.models.length}) ခု အသင့်ရရှိပါသည်`;
                    badge.className = 'text-[10px] text-emerald-700 font-bold';
                } else {
                    badge.innerText = '⚠️ Models မတွေ့ပါ (Input ထဲတွင် တိုက်ရိုက် ရိုက်ထည့်နိုင်ပါသည်)';
                    badge.className = 'text-[10px] text-amber-700 font-bold';
                }
            } catch (err) {
                badge.innerText = '⚠️ API ချိတ်ဆက်မှု မအောင်မြင်ပါ';
            } finally {
                btnText.innerText = 'Fetch Models';
            }
        }

        function resetDefaultPrompt() {
            document.getElementById('adminBotSystemPrompt').value = DEFAULT_PROMPT_TEMPLATE;
        }

        function loadChatbotSettingsIntoForm() {
            const cfg = shopData.chatbotConfig || {};

            document.getElementById('adminBotProvider').value = cfg.provider || 'mock';
            document.getElementById('adminBotModel').value = cfg.model || 'gemini-1.5-flash';
            document.getElementById('adminBotApiUrl').value = cfg.apiUrl || '';
            document.getElementById('adminBotApiKey').value = cfg.apiKey || '';
            if (cfg.hasApiKey && !cfg.apiKey) {
                document.getElementById('adminBotApiKey').placeholder = '●●●●●●●● (Database တွင် သိမ်းဆည်းပြီးသား)';
            }
            document.getElementById('adminBotName').value = cfg.botName || 'ပုံ့ပုံ့ AI အကူအညီ';
            document.getElementById('adminBotWelcome').value = cfg.welcomeMessageMm || 'မင်္ဂလာပါရှင် 👋 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်။ ဘာများ ကူညီပေးရမလဲရှင်။';
            document.getElementById('adminBotSystemPrompt').value = cfg.systemPrompt || DEFAULT_PROMPT_TEMPLATE;
            document.getElementById('adminBotEnabled').checked = cfg.enabled !== false;
            handleBotProviderChange();
        }

        async function saveChatbotConfig(e) {
            e.preventDefault();
            const provider = document.getElementById('adminBotProvider').value;
            const model = document.getElementById('adminBotModel').value.trim();
            const apiUrl = document.getElementById('adminBotApiUrl').value.trim();
            const apiKey = document.getElementById('adminBotApiKey').value.trim();
            const botName = document.getElementById('adminBotName').value.trim();
            const welcomeMessageMm = document.getElementById('adminBotWelcome').value.trim();
            const systemPrompt = document.getElementById('adminBotSystemPrompt').value.trim();
            const enabled = document.getElementById('adminBotEnabled').checked;

            const payload = {
                action: 'UPDATE_CHATBOT_CONFIG',
                chatbotConfig: {
                    provider,
                    model,
                    apiUrl,
                    apiKey,
                    botName,
                    welcomeMessageMm,
                    welcomeMessageEn: 'Hello! Welcome to Pont Pont Handbag Shop Mandalay.',
                    systemPrompt,
                    enabled
                }
            };

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.ok) {
                    if (data.db && data.db.chatbotConfig) {
                        shopData.chatbotConfig = data.db.chatbotConfig;
                    }
                    alert('AI Chatbot ဆက်တင်များကို Bluehost MySQL Database ထဲသို့ အောင်မြင်စွာ သိမ်းဆည်းပြီးပါပြီရှင်။');
                } else {
                    alert('Error: ' + (data.error || 'Server error'));
                }
            } catch (err) {
                alert('သိမ်းဆည်းမှု မအောင်မြင်ပါ: ' + err);
            }
        }

        function updateAdminStats() {
            const rev = (shopData.orders || []).reduce((sum, o) => sum + (o.status !== 'Cancelled' ? o.total : 0), 0);
            document.getElementById('statRevenue').innerText = rev.toLocaleString() + ' Ks';
            document.getElementById('statOrders').innerText = (shopData.orders || []).length;
            document.getElementById('statProducts').innerText = (shopData.products || []).length;
            document.getElementById('statUsers').innerText = shopData.usersCount || 0;
        }

        function renderAdminOrders() {
            const container = document.getElementById('adminOrdersTable');
            container.innerHTML = '';
            (shopData.orders || []).forEach(o => {
                const div = document.createElement('div');
                div.className = 'p-3.5 rounded-2xl bg-stone-50 border border-stone-200 text-xs space-y-2';
                const itemsText = (o.items || []).map(i => `${escapeHtml(i.nameEn)} (${escapeHtml(i.selectedColor)}) x ${Number(i.quantity) || 1}`).join(', ');
                const refText = o.transactionRef ? `(Ref: ${escapeHtml(o.transactionRef)})` : '';
                div.innerHTML = `
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <span class="font-mono font-bold text-stone-900 text-sm">${escapeHtml(o.id)}</span>
                            <span class="text-stone-500 ml-2">ဝယ်သူ: <strong>${escapeHtml(o.customerName)}</strong> (${escapeHtml(o.phone)})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <select onchange="handleUpdateOrderStatus('${escapeHtml(o.id)}', this.value)" class="bg-white border border-stone-300 rounded-lg px-2.5 py-1 font-bold text-xs">
                                <option value="Pending" ${o.status === 'Pending' ? 'selected' : ''}>Pending (စစ်ဆေးဆဲ)</option>
                                <option value="Confirmed" ${o.status === 'Confirmed' ? 'selected' : ''}>Confirmed (အတည်ပြုပြီး)</option>
                                <option value="Shipped" ${o.status === 'Shipped' ? 'selected' : ''}>Shipped (ဂိတ်တင်ပြီး)</option>
                                <option value="Delivered" ${o.status === 'Delivered' ? 'selected' : ''}>Delivered (ရောက်ရှိပြီး)</option>
                                <option value="Cancelled" ${o.status === 'Cancelled' ? 'selected' : ''}>Cancelled (ပယ်ဖျက်)</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-stone-600">
                        <p><strong>လိပ်စာ:</strong> ${escapeHtml(o.address)}, ${escapeHtml(o.city)}</p>
                        <p><strong>ပစ္စည်းများ:</strong> ${itemsText}</p>
                        <p class="font-bold text-stone-900 mt-1">ကျသင့်ငွေ: ${Number(o.total || 0).toLocaleString()} Ks • ငွေပေးချေမှု: ${escapeHtml(o.paymentMethod)} ${refText}</p>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        async function handleUpdateOrderStatus(orderId, status) {
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'UPDATE_ORDER_STATUS', orderId, status })
                });
                loadShopData();
            } catch (err) {
                alert(err);
            }
        }

        function renderAdminProducts() {
            const container = document.getElementById('adminProductsTable');
            container.innerHTML = '';
            (shopData.products || []).forEach(p => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 p-3 rounded-2xl bg-stone-50 border border-stone-200 text-xs';
                div.innerHTML = `
                    <img src="${escapeHtml(p.image)}" class="w-12 h-12 rounded-xl object-cover border border-stone-200">
                    <div class="flex-1 min-w-0">
                        <h5 class="font-bold text-stone-900 truncate">${escapeHtml(p.nameEn)}</h5>
                        <p class="text-stone-500">${escapeHtml(p.category)} • လက်လီ: ${p.price.toLocaleString()} Ks • လက်ကား: ${p.wholesalePrice ? p.wholesalePrice.toLocaleString() + ' Ks' : '-'} • Stock: ${p.stock}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="editProduct('${escapeHtml(p.id)}')" class="px-3 py-1 bg-white border border-stone-300 rounded-lg font-bold hover:bg-stone-100">Edit</button>
                        <button onclick="deleteProduct('${escapeHtml(p.id)}')" class="px-3 py-1 bg-red-50 text-red-700 border border-red-200 rounded-lg font-bold hover:bg-red-100">Delete</button>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        function openAddProductForm() {
            document.getElementById('pFormId').value = '';
            document.getElementById('pFormNameEn').value = '';
            document.getElementById('pFormNameMm').value = '';
            document.getElementById('pFormPrice').value = '';
            document.getElementById('pFormWholesale').value = '';
            document.getElementById('pFormOriginalPrice').value = '';
            document.getElementById('pFormStock').value = '30';
            document.getElementById('pFormImage').value = '';
            document.getElementById('pFormDescMm').value = '';
            document.getElementById('productFormTitle').innerText = 'ပစ္စည်းအသစ် ထည့်သွင်းခြင်း';
            document.getElementById('productFormContainer').classList.remove('hidden');
        }

        function editProduct(productId) {
            const p = shopData.products.find(item => item.id === productId);
            if (!p) return;
            document.getElementById('pFormId').value = p.id;
            document.getElementById('pFormNameEn').value = p.nameEn;
            document.getElementById('pFormNameMm').value = p.nameMm;
            document.getElementById('pFormCategory').value = p.category;
            document.getElementById('pFormPrice').value = p.price;
            document.getElementById('pFormWholesale').value = p.wholesalePrice || '';
            document.getElementById('pFormOriginalPrice').value = p.originalPrice || '';
            document.getElementById('pFormStock').value = p.stock;
            document.getElementById('pFormBadge').value = p.badge || '';
            document.getElementById('pFormImage').value = p.image;
            document.getElementById('pFormDescMm').value = p.descriptionMm;
            document.getElementById('productFormTitle').innerText = 'ပစ္စည်း ပြင်ဆင်ခြင်း';
            document.getElementById('productFormContainer').classList.remove('hidden');
        }

        async function handleSaveProduct(e) {
            e.preventDefault();
            const id = document.getElementById('pFormId').value;
            const product = {
                id: id || ('pp-bag-' + Date.now()),
                nameEn: document.getElementById('pFormNameEn').value.trim(),
                nameMm: document.getElementById('pFormNameMm').value.trim(),
                category: document.getElementById('pFormCategory').value,
                categoryMm: 'အိတ်',
                price: parseInt(document.getElementById('pFormPrice').value),
                wholesalePrice: parseInt(document.getElementById('pFormWholesale').value) || null,
                originalPrice: parseInt(document.getElementById('pFormOriginalPrice').value) || null,
                stock: parseInt(document.getElementById('pFormStock').value) || 0,
                badge: document.getElementById('pFormBadge').value || null,
                image: document.getElementById('pFormImage').value.trim(),
                descriptionMm: document.getElementById('pFormDescMm').value.trim(),
                descriptionEn: '',
                colors: [{ name: 'Default', hex: '#9C5B34' }]
            };

            const action = id ? 'UPDATE_PRODUCT' : 'ADD_PRODUCT';
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action, product })
                });
                document.getElementById('productFormContainer').classList.add('hidden');
                loadShopData();
                renderAdminProducts();
            } catch (err) {
                alert(err);
            }
        }

        async function deleteProduct(productId) {
            if (!confirm('ဤပစ္စည်းအား ဖျက်ရန် သေချာပါသလား?')) return;
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'DELETE_PRODUCT', productId })
                });
                loadShopData();
                renderAdminProducts();
            } catch (err) {
                alert(err);
            }
        }

        function renderAdminAnnouncements() {
            const container = document.getElementById('adminAnnouncementsList');
            container.innerHTML = '';
            (shopData.announcements || []).forEach(a => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between p-3 rounded-2xl bg-stone-50 border border-stone-200 text-xs';
                div.innerHTML = `
                    <div>
                        <span class="font-bold text-stone-900">${escapeHtml(a.title)}</span>
                        <span class="text-stone-500 ml-2">(${escapeHtml(a.tag)})</span>
                    </div>
                    <button onclick="deleteAnnouncement('${escapeHtml(a.id)}')" class="px-3 py-1 bg-red-50 text-red-700 border border-red-200 rounded-lg font-bold">Delete</button>
                `;
                container.appendChild(div);
            });
        }

        async function handleAddAnnouncement(e) {
            e.preventDefault();
            const ann = {
                id: 'ann-' + Date.now(),
                title: document.getElementById('annTitle').value.trim(),
                tag: document.getElementById('annTag').value,
                content: document.getElementById('annContent').value.trim(),
                image: document.getElementById('annImage').value.trim() || null,
                authorName: shopData.currentUser ? shopData.currentUser.name : 'မပုံ့ (Admin)'
            };
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'ADD_ANNOUNCEMENT', announcement: ann })
                });
                document.getElementById('annTitle').value = '';
                document.getElementById('annContent').value = '';
                loadShopData();
                renderAdminAnnouncements();
            } catch (err) {
                alert(err);
            }
        }

        async function deleteAnnouncement(announcementId) {
            if (!confirm('ဤကြေညာချက်အား ဖျက်ရန် သေချာပါသလား?')) return;
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'DELETE_ANNOUNCEMENT', announcementId })
                });
                loadShopData();
                renderAdminAnnouncements();
            } catch (err) {
                alert(err);
            }
        }

        function renderAdminUsers() {
            const container = document.getElementById('adminUsersList');
            container.innerHTML = '';
            (shopData.users || []).forEach(u => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between p-3 rounded-2xl bg-stone-50 border border-stone-200 text-xs';
                div.innerHTML = `
                    <div>
                        <span class="font-bold text-stone-900">${escapeHtml(u.name)}</span>
                        <span class="text-stone-500 ml-2">${escapeHtml(u.email)} (${escapeHtml(u.phone)})</span>
                        <span class="ml-2 font-bold ${u.role === 'admin' ? 'text-amber-700' : 'text-stone-500'}">[${escapeHtml(u.role)}]</span>
                    </div>
                    <div class="flex gap-2">
                        ${u.id !== shopData.currentUser.id ? `
                            <button onclick="updateUserRole('${escapeHtml(u.id)}', '${u.role === 'admin' ? 'customer' : 'admin'}')" class="px-2.5 py-1 bg-white border border-stone-300 rounded-lg font-bold">${u.role === 'admin' ? 'Demote to Customer' : 'Promote to Admin'}</button>
                            <button onclick="deleteUser('${escapeHtml(u.id)}')" class="px-2.5 py-1 bg-red-50 text-red-700 border border-red-200 rounded-lg font-bold">Delete</button>
                        ` : '<span class="text-stone-400 font-semibold">(You)</span>'}
                    </div>
                `;
                container.appendChild(div);
            });
        }

        async function updateUserRole(userId, role) {
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'UPDATE_USER_ROLE', userId, role })
                });
                loadShopData();
                renderAdminUsers();
            } catch (err) {
                alert(err);
            }
        }

        async function deleteUser(userId) {
            if (!confirm('ဤ User အား ဖျက်ရန် သေချာပါသလား?')) return;
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'DELETE_USER', userId })
                });
                loadShopData();
                renderAdminUsers();
            } catch (err) {
                alert(err);
            }
        }

        async function setShopTheme(theme) {
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'UPDATE_THEME', theme })
                });
                applyTheme(theme);
                alert('Shop theme ပြောင်းလဲပြီးပါပြီ။');
            } catch (err) {
                alert(err);
            }
        }

        // ==================== AI CHATBOT ====================
        let chatbotOpen = false;
        function toggleChatbot() {
            chatbotOpen = !chatbotOpen;
            const win = document.getElementById('chatbotWindow');
            const btn = document.getElementById('chatbotToggleBtn');
            if (chatbotOpen) {
                win.classList.remove('hidden');
                win.classList.add('flex');
                btn.classList.add('hidden');
                scrollChatToBottom();
            } else {
                win.classList.add('hidden');
                win.classList.remove('flex');
                btn.classList.remove('hidden');
            }
        }

        function scrollChatToBottom() {
            const body = document.getElementById('chatMessages');
            if (body) body.scrollTop = body.scrollHeight;
        }

        function sendChatPrompt(promptText) {
            const inp = document.getElementById('chatInput');
            inp.value = promptText;
            handleChatSubmit(new Event('submit'));
        }

        async function handleChatSubmit(e) {
            if (e) e.preventDefault();
            const inp = document.getElementById('chatInput');
            const query = inp.value.trim();
            if (!query) return;

            // Append user message
            appendChatMessage('user', query);
            inp.value = '';

            // Show typing indicator
            const typingId = 'typing-' + Date.now();
            const body = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.id = typingId;
            typingDiv.className = 'flex items-center gap-2 text-stone-500 text-xs bg-white px-3 py-2 rounded-2xl border border-stone-200 w-fit';
            typingDiv.innerHTML = `<span>✨ ပုံ့ပုံ့ AI ဖြေကြားနေပါသည်...</span>`;
            body.appendChild(typingDiv);
            scrollChatToBottom();

            try {
                // If chatbot is configured with AI, query the server REST API
                const botCfg = shopData.chatbotConfig;
                let gotAiReply = false;

                if (botCfg && botCfg.enabled !== false && botCfg.provider !== 'mock') {
                    const res = await fetch('api/shop.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'CHAT_MESSAGE', message: query })
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.reply) {
                            const el = document.getElementById(typingId);
                            if (el) el.remove();
                            appendChatMessage('bot', data.reply);
                            gotAiReply = true;
                        }
                    }
                }

                if (!gotAiReply) {
                    const el = document.getElementById(typingId);
                    if (el) el.remove();
                    const reply = generateChatbotReply(query);
                    appendChatMessage('bot', reply.text, reply.action);
                }
            } catch (err) {
                const el = document.getElementById(typingId);
                if (el) el.remove();
                const reply = generateChatbotReply(query);
                appendChatMessage('bot', reply.text, reply.action);
            }
        }

        function appendChatMessage(sender, text, action) {
            const body = document.getElementById('chatMessages');
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const msgDiv = document.createElement('div');
            msgDiv.className = `flex gap-2 ${sender === 'user' ? 'justify-end' : 'justify-start'}`;

            let actionHtml = '';
            if (action) {
                actionHtml = `
                    <div class="mt-2">
                        <button onclick="${action.onClick}" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-[11px] font-bold rounded-xl border border-amber-300 transition">
                            ${action.label}
                        </button>
                    </div>
                `;
            }

            if (sender === 'user') {
                msgDiv.innerHTML = `
                    <div class="max-w-[80%] bg-amber-600 text-white rounded-2xl rounded-br-none px-3.5 py-2.5 shadow-sm text-xs">
                        <p class="whitespace-pre-line">${text}</p>
                        <span class="block text-[10px] text-amber-200 text-right mt-1">${time}</span>
                    </div>
                `;
            } else {
                msgDiv.innerHTML = `
                    <div class="w-7 h-7 rounded-xl bg-amber-100 border border-amber-300 text-amber-900 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">🤖</div>
                    <div class="max-w-[80%] bg-white border border-stone-200 text-stone-800 rounded-2xl rounded-bl-none px-3.5 py-2.5 shadow-sm text-xs">
                        <p class="whitespace-pre-line">${text}</p>
                        ${actionHtml}
                        <span class="block text-[10px] text-stone-400 mt-1">${time}</span>
                    </div>
                `;
            }
            body.appendChild(msgDiv);
            scrollChatToBottom();
        }

        function generateChatbotReply(query) {
            const q = query.toLowerCase();

            // Branches / Address
            if (q.includes('လိပ်စာ') || q.includes('ဆိုင်') || q.includes('ပင်မ') || q.includes('address') || q.includes('branch')) {
                return {
                    text: "🏪 မန္တလေးမြို့ ဆိုင်ခွဲ (၃) ဆိုင် လိပ်စာများ:\n\n• ပင်မဆိုင်: ၆၅ လမ်း၊ ၁၀၇×၁၀၈ လမ်းကြား၊ မန္တလေးမြို့\n• ဆိုင်ခွဲ (၂): ၇၃ လမ်း၊ ၃၆×၃၇ လမ်းကြား၊ မန္တလေးမြို့\n• ဆိုင်ခွဲ (၃): ၇၈ လမ်း (ရန်ကုန်-မန္တလေးလမ်း)၊ တံခွန်တိုင်ဈေးအနီး\n\n⏰ ဆိုင်ဖွင့်ချိန်: နေ့စဉ် မနက် 8:00 AM မှ ည 8:00 PM အထိ",
                    action: { label: "📍 ဆိုင်ခွဲလိပ်စာများ ကြည့်မည်", onClick: "window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'}); toggleChatbot();" }
                };
            }

            // Phone / Contact
            if (q.includes('ဖုန်း') || q.includes('ဆက်သွယ်') || q.includes('phone') || q.includes('call') || q.includes('viber')) {
                return {
                    text: "📞 ဆက်သွယ်ရန် ဖုန်းနံပါတ်များ:\n• 09-963000502 (ပင်မဖုန်း / Viber)\n• 09-750072752\n• 09-404448881\n\nနေ့စဉ် မနက် 8:00 မှ ည 8:00 ထိ စုံစမ်းမေးမြန်းနိုင်ပါတယ်ရှင်။",
                    action: { label: "📞 09-963000502 သို့ ဖုန်းခေါ်မည်", onClick: "window.open('tel:09963000502');" }
                };
            }

            // Delivery
            if (q.includes('ပို့') || q.includes('delivery') || q.includes('ကားဂိတ်') || q.includes('deli')) {
                return {
                    text: "🚚 ပို့ဆောင်ခနှုန်းထားများ:\n• မန္တလေးမြို့တွင်း: ၂,၅၀၀ ကျပ် (အိမ်အရောက်ငွေချေစနစ် ရပါသည်)\n• ရန်ကုန်မြို့တွင်း: ၃,၅၀၀ ကျပ်\n• နေပြည်တော်: ၃,၅၀၀ ကျပ်\n• နယ်ဝေးမြို့များ: ၄,၀၀၀ ကျပ် (ကားဂိတ်တင် စနစ်)\n\n⚡ အမြန်ဆုံး ၁-၃ ရက်အတွင်း ပို့ဆောင်ပေးပါသည်ရှင်။"
                };
            }

            // Payment
            if (q.includes('ငွေ') || q.includes('kpay') || q.includes('wave') || q.includes('pay') || q.includes('ဘဏ်')) {
                return {
                    text: "💰 ငွေပေးချေမှု နည်းလမ်းများ:\n• KBZPay (09-963000502 - Ma Pont Pont)\n• WavePay (09-963000502 - Ma Pont Pont)\n• မန္တလေးမြို့တွင်း အိမ်အရောက်ငွေချေ (COD)\n• Mobile Banking (KBZ, CB, AYA)\n\n% ထပ်ဆောင်းပေးရန် မလိုပါရှင်။"
                };
            }

            // Wholesale
            if (q.includes('ကား') || q.includes('လက်ကား') || q.includes('wholesale')) {
                return {
                    text: "📦 လက်ကား မှာယူမှုများ:\nဆိုင်ရှင်များနှင့် ပြန်လည်ရောင်းချသူများအတွက် အထူးလက်ကားဈေးနှုန်း သီးသန့်ရှိပါသည် (အနည်းဆုံး ၅ လုံးမှ ၁၀ လုံးအထက်)။ လက်ကားကတ်တလောက်နှင့် ဈေးနှုန်းညှိနှိုင်းရန် ဖုန်း 09-963000502 သို့ ဆက်သွယ်ပေးပါရှင်။",
                    action: { label: "📞 လက်ကားဌာန သို့ ခေါ်ဆိုရန်", onClick: "window.open('tel:09963000502');" }
                };
            }

            // Default
            return {
                text: "မင်္ဂလာပါရှင် 🙏 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) ပင်မဆိုင် (၆၅ လမ်း၊ ၁၀၇×၁၀၈ ကြား) မှ ကြိုဆိုပါတယ်။ အိတ်ဒီဇိုင်းများ၊ ဈေးနှုန်း၊ ပို့ဆောင်ခနှင့် ပတ်သက်၍ အသေးစိတ် မေးမြန်းနိုင်ပါသည်ရှင်။",
                action: { label: "👜 အိတ်အားလုံး ကြည့်ရှုမည်", onClick: "window.scrollTo({top: 500, behavior: 'smooth'}); toggleChatbot();" }
            };
        }
    </script>

    <!-- Floating Chatbot Toggle Button -->
    <button id="chatbotToggleBtn" onclick="toggleChatbot()" class="fixed bottom-6 right-5 z-50 group flex items-center gap-2.5 bg-gradient-to-r from-amber-600 via-rose-600 to-amber-700 text-white px-4 py-3 rounded-full shadow-2xl hover:scale-105 active:scale-95 transition-all duration-200 border border-amber-300/40">
        <span class="text-lg">🤖</span>
        <span class="text-xs font-bold tracking-wide">အိတ်ဆိုင် Chatbot</span>
        <span class="px-1.5 py-0.5 rounded-full bg-white/20 text-[10px] font-extrabold uppercase">AI</span>
    </button>

    <!-- Chatbot Window -->
    <div id="chatbotWindow" class="hidden fixed bottom-6 right-4 sm:right-6 z-50 w-[calc(100vw-2rem)] sm:w-96 max-h-[85vh] h-[520px] bg-white rounded-3xl shadow-2xl border border-stone-200 flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-stone-900 via-stone-800 to-amber-950 text-white px-4 py-3.5 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-600/30 border border-amber-400/40 flex items-center justify-center text-xl shadow-inner">
                    🤖
                </div>
                <div>
                    <h3 id="chatbotTitle" class="font-bold text-sm tracking-tight flex items-center gap-1.5">
                        ပုံ့ပုံ့ AI အကူအညီ ✨
                    </h3>
                    <p class="text-[11px] text-amber-300 font-medium">
                        ပုံ့ပုံ့ အိတ်ဆိုင် အကူအညီ • အသင့်ရှိပါသည်
                    </p>
                </div>
            </div>
            <button onclick="toggleChatbot()" class="p-1.5 rounded-full hover:bg-white/10 text-stone-300 hover:text-white transition">
                ✕
            </button>
        </div>

        <!-- Messages Area -->
        <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-stone-50/70 text-xs">
            <div class="flex gap-2 justify-start">
                <div class="w-7 h-7 rounded-xl bg-amber-100 border border-amber-300 text-amber-900 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">🤖</div>
                <div class="max-w-[80%] bg-white border border-stone-200 text-stone-800 rounded-2xl rounded-bl-none px-3.5 py-2.5 shadow-sm text-xs">
                    <p id="chatbotWelcomeMsg">မင်္ဂလာပါရှင် 👋 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်။ အိတ်ဒီဇိုင်းများ၊ ဈေးနှုန်း၊ ပို့ဆောင်ခ၊ ဆိုင်လိပ်စာနှင့် ဖုန်းနံပါတ်များ စိတ်ကြိုက်မေးမြန်းနိုင်ပါတယ်ရှင်။</p>
                </div>
            </div>
        </div>

        <!-- Quick Prompts -->
        <div class="px-3 py-2 bg-stone-100 border-t border-stone-200 overflow-x-auto flex gap-1.5 no-scrollbar">
            <button onclick="sendChatPrompt('📍 ပင်မဆိုင်လိပ်စာ')" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-white text-stone-700 hover:bg-amber-50 hover:text-amber-800 text-[11px] font-medium border border-stone-200 transition">📍 ပင်မဆိုင်လိပ်စာ</button>
            <button onclick="sendChatPrompt('👜 ရောင်းအားအကောင်းဆုံးအိတ်များ')" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-white text-stone-700 hover:bg-amber-50 hover:text-amber-800 text-[11px] font-medium border border-stone-200 transition">👜 အိတ်ဒီဇိုင်းများ</button>
            <button onclick="sendChatPrompt('🚚 ပို့ဆောင်ခနှင့် ကားဂိတ်')" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-white text-stone-700 hover:bg-amber-50 hover:text-amber-800 text-[11px] font-medium border border-stone-200 transition">🚚 ပို့ဆောင်ခ</button>
            <button onclick="sendChatPrompt('📞 ဆက်သွယ်ရန်ဖုန်း')" class="whitespace-nowrap px-2.5 py-1 rounded-full bg-white text-stone-700 hover:bg-amber-50 hover:text-amber-800 text-[11px] font-medium border border-stone-200 transition">📞 ဖုန်းနံပါတ်</button>
        </div>

        <!-- Input Box -->
        <form onsubmit="handleChatSubmit(event)" class="p-3 bg-white border-t border-stone-200 flex items-center gap-2">
            <input type="text" id="chatInput" placeholder="အိတ်များ၊ ဆိုင်လိပ်စာ၊ ပို့ဆောင်ခ မေးပါ..." class="flex-1 bg-stone-100 text-stone-900 text-xs px-3.5 py-2.5 rounded-xl border border-stone-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition" />
            <button type="submit" class="px-3.5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                Send
            </button>
        </form>
    </div>
</body>
</html>

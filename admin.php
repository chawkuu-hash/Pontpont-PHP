<?php
/**
 * Pont Pont Handbag Shop - Standalone Admin Dashboard
 * (ပုံ့ပုံ့ အိတ်ဆိုင် - သီးသန့် စီမံခန့်ခွဲမှု စာမျက်နှာ)
 */
require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ပုံ့ပုံ့ အိတ်ဆိုင် (Pont Pont Shop)</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Padauk:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Padauk', sans-serif;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            #invoicePrintArea, #invoicePrintArea *,
            #reportPrintArea, #reportPrintArea * {
                visibility: visible;
            }
            #invoicePrintArea, #reportPrintArea {
                position: fixed;
                left: 0;
                top: 0;
                width: 100vw;
                height: 100vh;
                margin: 0;
                padding: 24px;
                background: white;
                z-index: 99999;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-stone-100 text-stone-900 min-h-screen flex flex-col md:flex-row antialiased">

    <!-- =================================================================== -->
    <!-- LOGIN SCREEN (Shown if not logged in as Admin) -->
    <!-- =================================================================== -->
    <div id="loginScreen" class="fixed inset-0 z-50 bg-stone-900/90 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-stone-200 text-center">
            <div class="w-14 h-14 rounded-2xl bg-stone-900 text-amber-400 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-amber-500/10">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </div>
            <h2 class="text-xl font-black text-stone-900">Admin Dashboard</h2>
            <p class="text-xs text-stone-500 mt-1 mb-6">ပုံ့ပုံ့ အိတ်ဆိုင် စီမံခန့်ခွဲမှုစနစ်သို့ ဝင်ရောက်ရန်</p>

            <div id="loginError" class="hidden mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold text-left"></div>

            <form onsubmit="handleAdminLogin(event)" class="space-y-4 text-left">
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">အီးမေးလ် (Admin Email)</label>
                    <input type="email" id="loginEmail" required value="admin@pontpont.com" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">စကားဝှက် (Password)</label>
                    <input type="password" id="loginPassword" required value="admin123" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <button type="submit" id="loginSubmitBtn" class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-lg shadow-amber-600/30 transition flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>အကောင့်ဝင်မည်</span>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between text-xs">
                <a href="index.php" class="text-stone-500 hover:text-stone-900 flex items-center gap-1 font-semibold">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> စတိုးဆိုင်သို့ ပြန်သွားမည်
                </a>
                <span class="text-stone-400">Default: admin@pontpont.com</span>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- SIDEBAR NAVIGATION -->
    <!-- =================================================================== -->
    <aside id="adminSidebar" class="w-full md:w-64 bg-stone-900 text-stone-300 flex-shrink-0 flex flex-col justify-between border-r border-stone-800">
        <div>
            <!-- Brand -->
            <div class="p-5 border-b border-stone-800 flex items-center justify-between">
                <a href="admin.php" class="flex items-center gap-3">
                    <div id="adminBrandLogoContainer" class="w-9 h-9 rounded-xl bg-white border border-stone-700 text-amber-600 flex items-center justify-center font-bold shadow-xs overflow-hidden flex-shrink-0 p-0.5">
                        <i data-lucide="shield-check" class="w-5 h-5 text-amber-600"></i>
                    </div>
                    <div class="min-w-0">
                        <h1 id="adminBrandTitle" class="text-sm font-extrabold text-white tracking-wide truncate">Pont Pont Admin</h1>
                        <p id="adminBrandSubtitle" class="text-[11px] text-amber-400 font-medium truncate">မန္တလေး စတိုးဆိုင် စီမံမှု</p>
                    </div>
                </a>
                <a href="index.php" title="စတိုးဆိုင် ကြည့်မည်" class="p-2 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 transition">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Current Admin User Badge -->
            <div class="px-4 py-3 mx-3 my-3 rounded-2xl bg-stone-800/80 border border-stone-700/60 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p id="adminUserName" class="text-xs font-bold text-white truncate">မပုံ့ (Admin)</p>
                    <p id="adminUserEmail" class="text-[10px] text-stone-400 truncate">admin@pontpont.com</p>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="p-3 space-y-1 text-xs font-bold">
                <button onclick="switchTab('overview')" id="nav-overview" class="nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition bg-amber-600 text-white">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>ခြုံငုံသုံးသပ်ချက် (Overview)</span>
                </button>
                <button onclick="switchTab('orders')" id="nav-orders" class="nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <div class="flex items-center gap-3">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        <span>အော်ဒါများ (Orders)</span>
                    </div>
                    <span id="badgeOrdersCount" class="bg-amber-500 text-stone-900 text-[10px] px-2 py-0.5 rounded-full font-extrabold">0</span>
                </button>
                <button onclick="switchTab('reports')" id="nav-reports" class="nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <div class="flex items-center gap-3">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-emerald-400"></i>
                        <span>စာရင်းချုပ် (Reports)</span>
                    </div>
                    <span class="bg-emerald-950 text-emerald-400 border border-emerald-800 text-[10px] px-2 py-0.5 rounded-full font-bold">အပတ်/လ</span>
                </button>
                <button onclick="switchTab('products')" id="nav-products" class="nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <div class="flex items-center gap-3">
                        <i data-lucide="package" class="w-4 h-4"></i>
                        <span>ပစ္စည်းများ (Products)</span>
                    </div>
                    <span id="badgeProductsCount" class="bg-stone-800 text-stone-300 text-[10px] px-2 py-0.5 rounded-full font-bold">0</span>
                </button>
                <button onclick="switchTab('categories')" id="nav-categories" class="nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <div class="flex items-center gap-3">
                        <i data-lucide="tags" class="w-4 h-4"></i>
                        <span>အမျိုးအစားများ (Categories)</span>
                    </div>
                    <span id="badgeCategoriesCount" class="bg-stone-800 text-stone-300 text-[10px] px-2 py-0.5 rounded-full font-bold">0</span>
                </button>
                <button onclick="switchTab('announcements')" id="nav-announcements" class="nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <i data-lucide="megaphone" class="w-4 h-4"></i>
                    <span>ကြေညာချက်များ (News)</span>
                </button>
                <button onclick="switchTab('users')" id="nav-users" class="nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>အသုံးပြုသူများ (Users)</span>
                </button>
                <button onclick="switchTab('coupons')" id="nav-coupons" class="nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <div class="flex items-center gap-3">
                        <i data-lucide="ticket" class="w-4 h-4"></i>
                        <span>ကူပွန်များ (Coupons)</span>
                    </div>
                    <span id="badgeCouponsCount" class="bg-stone-800 text-stone-300 text-[10px] px-2 py-0.5 rounded-full font-bold">0</span>
                </button>
                <button onclick="switchTab('payments')" id="nav-payments" class="nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <div class="flex items-center gap-3">
                        <i data-lucide="credit-card" class="w-4 h-4 text-amber-400"></i>
                        <span>ငွေပေးချေမှု နည်းလမ်းများ</span>
                    </div>
                    <span id="badgePaymentsCount" class="bg-stone-800 text-stone-300 text-[10px] px-2 py-0.5 rounded-full font-bold">0</span>
                </button>
                <button onclick="switchTab('theme')" id="nav-theme" class="nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <i data-lucide="palette" class="w-4 h-4"></i>
                    <span>ဆိုင် Theme ဒီဇိုင်း</span>
                </button>
                <button onclick="switchTab('settings')" id="nav-settings" class="nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>ဆိုင် ဆက်တင်များ (Settings)</span>
                </button>
                <button onclick="switchTab('chatbot')" id="nav-chatbot" class="nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white">
                    <div class="flex items-center gap-3">
                        <i data-lucide="bot" class="w-4 h-4 text-emerald-400"></i>
                        <span>AI Chatbot ဆက်တင်များ</span>
                    </div>
                    <span id="badgeChatbotStatus" class="bg-emerald-950 text-emerald-400 border border-emerald-800 text-[10px] px-2 py-0.5 rounded-full font-bold">Active</span>
                </button>
            </nav>
        </div>

        <!-- Bottom Actions -->
        <div class="p-3 border-t border-stone-800 space-y-1 text-xs font-bold">
            <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-stone-400 hover:bg-stone-800 hover:text-white transition">
                <i data-lucide="store" class="w-4 h-4 text-amber-400"></i>
                <span>စတိုးဆိုင်သို့ သွားမည်</span>
            </a>
            <button onclick="handleLogout()" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-red-400 hover:bg-red-950/40 hover:text-red-300 transition">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>အကောင့်ထွက်မည် (Logout)</span>
            </button>
        </div>
    </aside>

    <!-- =================================================================== -->
    <!-- MAIN CONTENT AREA -->
    <!-- =================================================================== -->
    <main class="flex-1 min-w-0 flex flex-col min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white border-b border-stone-200 px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <h2 id="pageTitle" class="text-base sm:text-lg font-black text-stone-900">ခြုံငုံသုံးသပ်ချက် (Overview)</h2>
                <p id="pageSubtitle" class="text-xs text-stone-500">အရောင်းအချက်အလက်များနှင့် လုပ်ငန်းအကျဉ်းချုပ်</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="loadData(true)" title="Data ပြန်လည် Refresh လုပ်မည်" class="p-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold transition flex items-center gap-1.5">
                    <i data-lucide="refresh-cw" class="w-4 h-4" id="refreshIcon"></i>
                    <span class="hidden sm:inline">Refresh</span>
                </button>
                <a href="index.php" class="px-3.5 py-2 rounded-xl bg-stone-900 hover:bg-black text-amber-400 text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">View Shop</span>
                </a>
            </div>
        </header>

        <!-- Dynamic Panes -->
        <div class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl w-full">

            <!-- ----------------------------------------------------------- -->
            <!-- 1. OVERVIEW PANE -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-overview" class="pane space-y-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-5 rounded-3xl bg-white border border-stone-200 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                            <i data-lucide="dollar-sign" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-stone-500">စုစုပေါင်း အရောင်းရငွေ</span>
                            <h3 id="statRevenue" class="text-lg sm:text-2xl font-black text-stone-900 mt-0.5">0 Ks</h3>
                        </div>
                    </div>

                    <div class="p-5 rounded-3xl bg-white border border-stone-200 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                            <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-stone-500">စုစုပေါင်း အော်ဒါ</span>
                            <h3 id="statOrders" class="text-lg sm:text-2xl font-black text-stone-900 mt-0.5">0</h3>
                        </div>
                    </div>

                    <div class="p-5 rounded-3xl bg-white border border-stone-200 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                            <i data-lucide="package" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-stone-500">တင်ထားသော ပစ္စည်း</span>
                            <h3 id="statProducts" class="text-lg sm:text-2xl font-black text-stone-900 mt-0.5">0</h3>
                        </div>
                    </div>

                    <div class="p-5 rounded-3xl bg-white border border-stone-200 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-stone-500">အကောင့်ဖွင့်ထားသူ</span>
                            <h3 id="statUsers" class="text-lg sm:text-2xl font-black text-stone-900 mt-0.5">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Weekly & Monthly Quick Accounting Summary Banner -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- This Week Card -->
                    <div class="p-5 rounded-3xl bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-white border border-amber-200/80 shadow-xs flex flex-col justify-between">
                        <div class="flex items-center justify-between pb-3 border-b border-amber-200/60">
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 bg-amber-600 text-white rounded-xl shadow-xs"><i data-lucide="calendar" class="w-4 h-4"></i></span>
                                <div>
                                    <h4 class="font-black text-sm text-stone-900">ဒီအပတ် စာရင်းချုပ် (This Week)</h4>
                                    <p id="overviewWeekRange" class="text-[11px] text-stone-500">ယခုအပတ် တနင်္လာ မှ တနင်္ဂနွေ အထိ</p>
                                </div>
                            </div>
                            <span class="bg-amber-100 text-amber-800 text-[10px] px-2 py-0.5 rounded-full font-bold">This Week</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-3.5 text-center">
                            <div>
                                <span class="text-[10px] font-bold text-stone-400 block uppercase">ရောင်းရငွေ</span>
                                <span id="overviewWeekRev" class="text-base sm:text-lg font-black text-amber-700">0 Ks</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-stone-400 block uppercase">အော်ဒါ</span>
                                <span id="overviewWeekOrders" class="text-base sm:text-lg font-black text-stone-900">0</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-stone-400 block uppercase">ပို့ပြီး</span>
                                <span id="overviewWeekDelivered" class="text-base sm:text-lg font-black text-emerald-600">0</span>
                            </div>
                        </div>
                        <button onclick="switchTab('reports'); setReportMode('weekly');" class="w-full py-2 bg-white hover:bg-amber-50 text-amber-800 text-xs font-bold rounded-xl border border-amber-300 transition flex items-center justify-center gap-1.5 shadow-2xs">
                            <span>အပတ်စဉ် အသေးစိတ် စာရင်းချုပ် ကြည့်မည်</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- This Month Card -->
                    <div class="p-5 rounded-3xl bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-white border border-emerald-200/80 shadow-xs flex flex-col justify-between">
                        <div class="flex items-center justify-between pb-3 border-b border-emerald-200/60">
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 bg-emerald-600 text-white rounded-xl shadow-xs"><i data-lucide="bar-chart-2" class="w-4 h-4"></i></span>
                                <div>
                                    <h4 class="font-black text-sm text-stone-900">ဒီလ စာရင်းချုပ် (This Month)</h4>
                                    <p id="overviewMonthRange" class="text-[11px] text-stone-500">ယခုလ ၁ ရက်နေ့ မှ လကုန် အထိ</p>
                                </div>
                            </div>
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.5 rounded-full font-bold">This Month</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-3.5 text-center">
                            <div>
                                <span class="text-[10px] font-bold text-stone-400 block uppercase">ရောင်းရငွေ</span>
                                <span id="overviewMonthRev" class="text-base sm:text-lg font-black text-emerald-700">0 Ks</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-stone-400 block uppercase">အော်ဒါ</span>
                                <span id="overviewMonthOrders" class="text-base sm:text-lg font-black text-stone-900">0</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-stone-400 block uppercase">ပို့ပြီး</span>
                                <span id="overviewMonthDelivered" class="text-base sm:text-lg font-black text-emerald-600">0</span>
                            </div>
                        </div>
                        <button onclick="switchTab('reports'); setReportMode('monthly');" class="w-full py-2 bg-white hover:bg-emerald-50 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-300 transition flex items-center justify-center gap-1.5 shadow-2xs">
                            <span>လစဉ် အသေးစိတ် စာရင်းချုပ် ကြည့်မည်</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Recent Orders in Overview -->
                <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-extrabold text-stone-900 text-sm">လတ်တလော အော်ဒါများ</h4>
                        <button onclick="switchTab('orders')" class="text-xs font-bold text-amber-600 hover:text-amber-700">အကုန်ကြည့်မည် →</button>
                    </div>
                    <div id="overviewRecentOrders" class="space-y-3"></div>
                </div>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 2. ORDERS PANE -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-orders" class="pane space-y-4 hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <!-- Filter Tabs -->
                    <div class="flex gap-1.5 overflow-x-auto hide-scrollbar bg-stone-200/70 p-1.5 rounded-2xl text-xs font-bold">
                        <button onclick="filterOrders('ALL')" id="filter-ALL" class="order-filter-btn px-3 py-1.5 rounded-xl bg-white text-stone-900 shadow-xs flex items-center gap-1.5">
                            <span>အားလုံး</span>
                            <span id="badgeOrderAll" class="bg-stone-100 text-stone-600 text-[10px] px-1.5 py-0.5 rounded-full">0</span>
                        </button>
                        <button onclick="filterOrders('Pending')" id="filter-Pending" class="order-filter-btn px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900 flex items-center gap-1.5">
                            <span>စစ်ဆေးဆဲ (Pending)</span>
                            <span id="badgeOrderPending" class="bg-amber-100 text-amber-800 text-[10px] px-1.5 py-0.5 rounded-full">0</span>
                        </button>
                        <button onclick="filterOrders('Confirmed')" id="filter-Confirmed" class="order-filter-btn px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900 flex items-center gap-1.5">
                            <span>အတည်ပြုပြီး (Confirmed)</span>
                            <span id="badgeOrderConfirmed" class="bg-blue-100 text-blue-800 text-[10px] px-1.5 py-0.5 rounded-full">0</span>
                        </button>
                        <button onclick="filterOrders('Shipped')" id="filter-Shipped" class="order-filter-btn px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900 flex items-center gap-1.5">
                            <span>ဂိတ်တင်ပြီး (Shipped)</span>
                            <span id="badgeOrderShipped" class="bg-purple-100 text-purple-800 text-[10px] px-1.5 py-0.5 rounded-full">0</span>
                        </button>
                        <button onclick="filterOrders('Delivered')" id="filter-Delivered" class="order-filter-btn px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900 flex items-center gap-1.5">
                            <span>ရောက်ရှိပြီး (Delivered)</span>
                            <span id="badgeOrderDelivered" class="bg-teal-100 text-teal-800 text-[10px] px-1.5 py-0.5 rounded-full">0</span>
                        </button>
                        <button onclick="filterOrders('Completed')" id="filter-Completed" class="order-filter-btn px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900 flex items-center gap-1.5">
                            <span class="text-emerald-700 font-extrabold">✓ Complete Order (ပြီးစီးပြီး)</span>
                            <span id="badgeOrderCompleted" class="bg-emerald-100 text-emerald-800 text-[10px] px-1.5 py-0.5 rounded-full font-bold">0</span>
                        </button>
                        <button onclick="filterOrders('Cancelled')" id="filter-Cancelled" class="order-filter-btn px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900 flex items-center gap-1.5">
                            <span>ပယ်ဖျက် (Cancelled)</span>
                            <span id="badgeOrderCancelled" class="bg-red-100 text-red-800 text-[10px] px-1.5 py-0.5 rounded-full">0</span>
                        </button>
                    </div>
                    <!-- Order Search -->
                    <div class="relative w-full sm:w-64">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                        <input type="text" id="orderSearchInput" placeholder="Order ID / ဖုန်း ရှာရန်..." oninput="handleOrderSearch(this.value)" class="w-full pl-9 pr-3 py-2 rounded-xl border border-stone-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div id="adminOrdersContainer" class="space-y-3">
                    <!-- Populated dynamically -->
                </div>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 2.1. REPORTS PANE (စာရင်းချုပ် - အပတ်စဉ် နှင့် လစဉ်) -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-reports" class="pane space-y-6 hidden">
                <!-- Top Control Header -->
                <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-extrabold text-stone-900 text-sm flex items-center gap-2">
                            <span class="p-1.5 bg-emerald-600 text-white rounded-xl shadow-xs"><i data-lucide="bar-chart-3" class="w-4 h-4"></i></span>
                            📊 အရောင်းနှင့် ဘဏ္ဍာရေး စာရင်းချုပ် (Financial Reports)
                        </h4>
                        <p class="text-xs text-stone-500 mt-0.5">အပတ်စဉ်၊ လစဉ် အရောင်းရငွေ၊ ပို့ခ၊ လျှော့ဈေးနှင့် အမြတ်ငွေ စာရင်းချုပ်များ</p>
                    </div>

                    <!-- Period Filter Switcher & Actions -->
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex bg-stone-100 p-1 rounded-2xl text-xs font-bold border border-stone-200">
                            <button onclick="setReportMode('weekly')" id="btnReportWeekly" class="report-mode-btn px-3 py-1.5 rounded-xl bg-white text-stone-900 shadow-xs">📅 အပတ်စဉ် စာရင်းချုပ်</button>
                            <button onclick="setReportMode('monthly')" id="btnReportMonthly" class="report-mode-btn px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900">🗓️ လစဉ် စာရင်းချုပ်</button>
                            <button onclick="setReportMode('custom')" id="btnReportCustom" class="report-mode-btn px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900">📆 စိတ်ကြိုက်ရက်စွဲ</button>
                        </div>

                        <div class="flex items-center gap-2">
                            <button onclick="exportReportCSV()" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Excel / CSV ထုတ်ယူမည်</span>
                            </button>
                            <button onclick="openReportPrintModal()" class="px-3.5 py-2 bg-stone-900 hover:bg-black text-amber-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                <span>Print စာရင်းချုပ်</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Custom Date Range Bar (shown when custom mode active) -->
                <div id="customDateFilterBar" class="p-4 bg-amber-50/80 border border-amber-200 rounded-2xl flex flex-wrap items-center gap-3 text-xs hidden">
                    <span class="font-bold text-stone-700 flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4 text-amber-600"></i> ရက်စွဲအလိုက် စာရင်းချုပ်ရန်:</span>
                    <div class="flex items-center gap-2">
                        <label class="text-stone-600 font-bold">မှ:</label>
                        <input type="date" id="repStartDate" class="px-3 py-1.5 rounded-xl border border-stone-300 bg-white font-mono text-xs">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-stone-600 font-bold">ထိ:</label>
                        <input type="date" id="repEndDate" class="px-3 py-1.5 rounded-xl border border-stone-300 bg-white font-mono text-xs">
                    </div>
                    <button onclick="applyCustomDateFilter()" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition shadow-xs">စာရင်းတွက်မည်</button>
                </div>

                <!-- KPI Period Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-5 rounded-3xl bg-white border border-stone-200 shadow-xs space-y-1">
                        <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">စုစုပေါင်း အရောင်းရငွေ</span>
                        <h3 id="repStatTotalRev" class="text-xl sm:text-2xl font-black text-amber-700">0 Ks</h3>
                        <p id="repStatTotalRevSub" class="text-[11px] text-stone-400">ကာလအတွင်း စုစုပေါင်းရောင်းရငွေ</p>
                    </div>

                    <div class="p-5 rounded-3xl bg-white border border-stone-200 shadow-xs space-y-1">
                        <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">အတည်ပြု အောင်မြင်ရငွေ</span>
                        <h3 id="repStatRealizedRev" class="text-xl sm:text-2xl font-black text-emerald-600">0 Ks</h3>
                        <p id="repStatRealizedRevSub" class="text-[11px] text-stone-400">ပို့ပြီး/အတည်ပြုပြီး အော်ဒါများ</p>
                    </div>

                    <div class="p-5 rounded-3xl bg-white border border-stone-200 shadow-xs space-y-1">
                        <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">စုစုပေါင်း အော်ဒါအရေအတွက်</span>
                        <h3 id="repStatOrdersCount" class="text-xl sm:text-2xl font-black text-stone-900">0</h3>
                        <p id="repStatOrdersSub" class="text-[11px] text-stone-400">အောင်မြင် 0 • ပယ်ဖျက် 0</p>
                    </div>

                    <div class="p-5 rounded-3xl bg-white border border-stone-200 shadow-xs space-y-1">
                        <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">ပို့ဆောင်ခနှင့် လျှော့ဈေး</span>
                        <h3 id="repStatDeliveryAndDiscount" class="text-xl sm:text-2xl font-black text-blue-700">0 Ks</h3>
                        <p id="repStatDelDiscSub" class="text-[11px] text-stone-400">ပို့ခ: 0 Ks • လျှော့ငွေ: 0 Ks</p>
                    </div>
                </div>

                <!-- Grouped Report Table -->
                <div class="bg-white rounded-3xl border border-stone-200 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-stone-200 flex items-center justify-between">
                        <div>
                            <h4 id="repTableTitle" class="font-extrabold text-sm text-stone-900">အပတ်စဉ် အသေးစိတ် စာရင်းချုပ် ဇယား</h4>
                            <p id="repTableSubtitle" class="text-xs text-stone-500">ရက်သတ္တပတ်အလိုက် အော်ဒါအရေအတွက်၊ ပို့ဆောင်ခနှင့် အရောင်းရငွေများ</p>
                        </div>
                        <span id="repTableCountBadge" class="bg-stone-100 text-stone-700 text-xs px-2.5 py-1 rounded-xl font-bold">0 Weeks</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-stone-50 text-stone-600 uppercase text-[11px] font-bold border-b border-stone-200">
                                <tr>
                                    <th class="p-3.5" id="repColHeaderPeriod">ရက်သတ္တပတ် (Period)</th>
                                    <th class="p-3.5">ရက်စွဲ (Date Range)</th>
                                    <th class="p-3.5 text-center">အော်ဒါ (Orders)</th>
                                    <th class="p-3.5 text-center">ပို့ပြီး (Delivered)</th>
                                    <th class="p-3.5 text-center">ပယ်ဖျက် (Cancelled)</th>
                                    <th class="p-3.5 text-right">ပို့ခ စုစုပေါင်း</th>
                                    <th class="p-3.5 text-right">လျှော့ငွေ</th>
                                    <th class="p-3.5 text-right font-black">စုစုပေါင်း ရောင်းရငွေ (Ks)</th>
                                </tr>
                            </thead>
                            <tbody id="repTableBody" class="divide-y divide-stone-100 font-medium">
                                <!-- Populated dynamically -->
                            </tbody>
                            <tfoot id="repTableFoot" class="bg-stone-50 font-bold border-t-2 border-stone-200 text-xs">
                                <!-- Totals -->
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Additional Breakdown: Payment Method & Top Selling Bags -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Payment Methods Distribution -->
                    <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-xs space-y-4">
                        <h4 class="font-bold text-sm text-stone-900 flex items-center gap-2 border-b pb-3">
                            <i data-lucide="credit-card" class="w-4 h-4 text-amber-600"></i> ငွေပေးချေမှု နည်းလမ်းအလိုက် ခွဲခြမ်းစိတ်ဖြာမှု
                        </h4>
                        <div id="repPaymentMethodsList" class="space-y-3 text-xs">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                    <!-- Top Selling Handbags in Period -->
                    <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-xs space-y-4">
                        <h4 class="font-bold text-sm text-stone-900 flex items-center gap-2 border-b pb-3">
                            <i data-lucide="award" class="w-4 h-4 text-amber-600"></i> ရောင်းအားအကောင်းဆုံး အိတ်များ (Top Products)
                        </h4>
                        <div id="repTopProductsList" class="space-y-2.5 text-xs">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 3. PRODUCTS PANE -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-products" class="pane space-y-4 hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-stone-900 text-sm">ကုန်ပစ္စည်းများ စီမံခြင်း</h4>
                        <p class="text-xs text-stone-500">ပစ္စည်းအသစ်ထည့်သွင်းခြင်း၊ ဈေးနှုန်းနှင့် Stock ပြင်ဆင်ခြင်း</p>
                    </div>
                    <button onclick="openProductForm()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition flex items-center gap-2 shadow-md shadow-amber-600/20">
                        <i data-lucide="plus" class="w-4 h-4"></i> ပစ္စည်းအသစ် ထည့်မည်
                    </button>
                </div>

                <!-- Product Add/Edit Form Card -->
                <div id="productFormCard" class="hidden bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4 text-xs">
                    <div class="flex items-center justify-between border-b pb-3">
                        <h5 class="font-black text-sm text-stone-900" id="productFormTitle">ပစ္စည်းအသစ် ထည့်သွင်းခြင်း</h5>
                        <button onclick="closeProductForm()" class="text-stone-400 hover:text-stone-700">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form onsubmit="handleSaveProduct(event)" class="space-y-4">
                        <input type="hidden" id="pFormId">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold mb-1">အင်္ဂလိပ် အမည် (Name En) *</label>
                                <input type="text" id="pFormNameEn" required class="w-full px-3 py-2 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">မြန်မာ အမည် (Name Mm) *</label>
                                <input type="text" id="pFormNameMm" required class="w-full px-3 py-2 rounded-xl border border-stone-300">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block font-bold">အမျိုးအစား (Category) *</label>
                                    <button type="button" onclick="switchTab('categories'); openCategoryForm();" class="text-amber-600 hover:underline text-[11px] font-bold">+ အသစ်ထည့်ရန်</button>
                                </div>
                                <select id="pFormCategory" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white">
                                    <!-- Populated dynamically from shopData.categories -->
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold mb-1">လက်လီဈေး (Price MMK) *</label>
                                <input type="number" id="pFormPrice" required class="w-full px-3 py-2 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">လက်ကားဈေး (Wholesale MMK)</label>
                                <input type="number" id="pFormWholesale" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold mb-1">မူလဈေး (Original Strike Price)</label>
                                <input type="number" id="pFormOriginalPrice" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">လက်ကျန် (Stock) *</label>
                                <input type="number" id="pFormStock" value="20" required class="w-full px-3 py-2 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">Badge (အမှတ်တံဆိပ်)</label>
                                <select id="pFormBadge" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white">
                                    <option value="">None</option>
                                    <option value="Best Seller">Best Seller</option>
                                    <option value="Wholesale Hot">Wholesale Hot</option>
                                    <option value="New Arrival">New Arrival</option>
                                    <option value="Sale">Sale</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold mb-1">ဓာတ်ပုံ (Product Image) *</label>
                            <div class="flex gap-2">
                                <input type="text" id="pFormImage" required placeholder="https://... သို့မဟုတ် Upload ပုံတင်ပါ" class="flex-1 px-3 py-2 rounded-xl border border-stone-300" oninput="updateProductImagePreview(this.value)">
                                <label class="bg-stone-800 hover:bg-stone-900 text-white font-bold px-3 py-2 rounded-xl text-xs flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                                    <i data-lucide="upload" class="w-4 h-4"></i>
                                    <span>Upload ပုံတင်မည်</span>
                                    <input type="file" id="pFormUploadFile" accept="image/*" class="hidden" onchange="handleProductImageUpload(event)">
                                </label>
                            </div>
                            <div id="pFormImagePreviewBox" class="mt-2 hidden">
                                <img id="pFormImagePreview" class="h-20 w-20 object-cover rounded-xl border border-stone-200 shadow-xs" src="" alt="Preview">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold mb-1">မြန်မာ ဖော်ပြချက် (Description Mm)</label>
                            <textarea id="pFormDescMm" rows="2" class="w-full px-3 py-2 rounded-xl border border-stone-300"></textarea>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-5 rounded-xl transition">
                                သိမ်းဆည်းမည်
                            </button>
                            <button type="button" onclick="closeProductForm()" class="bg-stone-200 hover:bg-stone-300 text-stone-700 font-bold py-2.5 px-5 rounded-xl transition">
                                မလုပ်တော့ပါ
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Products Filter & Search Bar -->
                <div class="bg-white p-4 rounded-3xl border border-stone-200 shadow-sm space-y-3 text-xs">
                    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
                        <!-- Search Bar -->
                        <div class="relative flex-1">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400"></i>
                            <input type="text" id="adminProductSearch" oninput="handleAdminProductFilter()" placeholder="အိတ်အမည်၊ အမျိုးအစား၊ ID ဖြင့် ရှာဖွေရန်..." class="w-full pl-9 pr-9 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs transition">
                            <button type="button" id="clearAdminProductSearch" onclick="clearAdminProductSearchInput()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <!-- Category Filter Dropdown -->
                        <div class="flex items-center gap-2">
                            <label for="adminProductCategoryFilter" class="font-bold text-stone-700 whitespace-nowrap flex items-center gap-1.5">
                                <i data-lucide="filter" class="w-4 h-4 text-amber-600"></i> အမျိုးအစား:
                            </label>
                            <select id="adminProductCategoryFilter" onchange="handleAdminProductFilter()" class="py-2.5 px-3.5 rounded-xl border border-stone-300 bg-white font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <option value="ALL">အမျိုးအစား အားလုံး (All Categories)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category Pills Quick Bar -->
                    <div id="adminCategoryPills" class="flex items-center gap-1.5 overflow-x-auto pb-1 pt-0.5 no-scrollbar">
                        <!-- Populated dynamically -->
                    </div>

                    <!-- Filter Count & Status Bar -->
                    <div class="flex items-center justify-between pt-2 border-t border-stone-100 text-[11px] text-stone-500">
                        <span id="adminProductsCountDisplay">ပြသထားသော ကုန်ပစ္စည်း: <strong class="text-stone-800">0</strong> မျိုး</span>
                        <span id="adminProductsFilterState" class="font-bold text-amber-700"></span>
                    </div>
                </div>

                <!-- Products Table / List -->
                <div id="adminProductsContainer" class="space-y-3">
                    <!-- Populated dynamically -->
                </div>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 4. CATEGORIES PANE -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-categories" class="pane space-y-6 hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-stone-900 text-sm">အမျိုးအစားများ စီမံခန့်ခွဲခြင်း (Categories)</h4>
                        <p class="text-xs text-stone-500">အိတ်အမျိုးအစား အသစ်ထည့်သွင်းခြင်းနှင့် ရှိပြီးသား အမျိုးအစားများကို စီမံခြင်း</p>
                    </div>
                    <button onclick="openCategoryForm()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition flex items-center gap-2 shadow-md shadow-amber-600/20">
                        <i data-lucide="plus" class="w-4 h-4"></i> အမျိုးအစားအသစ် ထည့်မည်
                    </button>
                </div>

                <!-- Add Category Form Card -->
                <div id="categoryFormCard" class="hidden bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4 text-xs">
                    <div class="flex items-center justify-between border-b pb-3">
                        <h5 class="font-black text-sm text-stone-900" id="categoryFormTitle">အမျိုးအစားအသစ် ထည့်သွင်းခြင်း</h5>
                        <button onclick="closeCategoryForm()" class="text-stone-400 hover:text-stone-700">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <form onsubmit="handleSaveCategory(event)" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold mb-1">အမျိုးအစား Code / ID *</label>
                                <input type="text" id="catFormId" required placeholder="ဥပမာ: Travel (စာလုံးပေါင်းတစ်ဆက်တည်း)" class="w-full px-3.5 py-2 rounded-xl border border-stone-300">
                                <span class="text-[10px] text-stone-400">System Key (ဥပမာ: Tote, Crossbody, Travel)</span>
                            </div>
                            <div>
                                <label class="block font-bold mb-1">အင်္ဂလိပ် အမည် (Name En) *</label>
                                <input type="text" id="catFormLabelEn" required placeholder="ဥပမာ: Travel & Duffel Bags" class="w-full px-3.5 py-2 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">မြန်မာ အမည် (Name Mm) *</label>
                                <input type="text" id="catFormLabelMm" required placeholder="ဥပမာ: ခရီးဆောင်အိတ်ကြီးများ" class="w-full px-3.5 py-2 rounded-xl border border-stone-300">
                            </div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-5 rounded-xl transition">
                                သိမ်းဆည်းမည်
                            </button>
                            <button type="button" onclick="closeCategoryForm()" class="bg-stone-200 hover:bg-stone-300 text-stone-700 font-bold py-2.5 px-5 rounded-xl transition">
                                မလုပ်တော့ပါ
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Category List -->
                <div id="adminCategoriesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Populated dynamically -->
                </div>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 4. ANNOUNCEMENTS PANE -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-announcements" class="pane space-y-6 hidden">
                <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4 text-xs">
                    <h4 class="font-extrabold text-stone-900 text-sm">ကြေညာချက်အသစ် တင်မည်</h4>
                    <form onsubmit="handleAddAnnouncement(event)" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold mb-1">ခေါင်းစဉ် (Title) *</label>
                                <input type="text" id="annTitle" required placeholder="ဥပမာ: Promotion အထူးအစီအစဉ်..." class="w-full px-3.5 py-2 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">Tag *</label>
                                <select id="annTag" class="w-full px-3.5 py-2 rounded-xl border border-stone-300 bg-white">
                                    <option value="Promotion">Promotion</option>
                                    <option value="New Arrival">New Arrival</option>
                                    <option value="Wholesale">Wholesale</option>
                                    <option value="Notice">Notice</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">အသေးစိတ် အကြောင်းအရာ *</label>
                            <textarea id="annContent" required rows="3" class="w-full px-3.5 py-2 rounded-xl border border-stone-300"></textarea>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">ဓာတ်ပုံ URL (Optional)</label>
                            <input type="url" id="annImage" placeholder="https://..." class="w-full px-3.5 py-2 rounded-xl border border-stone-300">
                        </div>
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-5 rounded-xl transition">
                            ကြေညာချက် တင်မည်
                        </button>
                    </form>
                </div>

                <div class="space-y-3">
                    <h4 class="font-extrabold text-stone-900 text-sm">လက်ရှိ ကြေညာချက်များ</h4>
                    <div id="adminAnnouncementsContainer" class="space-y-3"></div>
                </div>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 5. USERS PANE -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-users" class="pane space-y-4 hidden">
                <div>
                    <h4 class="font-extrabold text-stone-900 text-sm">အသုံးပြုသူ စာရင်းနှင့် Role သတ်မှတ်ခြင်း</h4>
                    <p class="text-xs text-stone-500">Admin နှင့် Customer Role ခွဲခြားသတ်မှတ်ခြင်း</p>
                </div>
                <div id="adminUsersContainer" class="space-y-2"></div>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 6. THEME PANE -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-theme" class="pane space-y-4 hidden">
                <div>
                    <h4 class="font-extrabold text-stone-900 text-sm">ဆိုင်ပုံစံ ဒီဇိုင်း (Store Theme) ရွေးချယ်ပါ</h4>
                    <p class="text-xs text-stone-500">ရွေးချယ်လိုက်သော Theme သည် စတိုးဆိုင် Web Frontend တွင် ချက်ချင်းပြောင်းလဲသွားပါမည်</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div onclick="setShopTheme('luxury-gold')" id="theme-luxury-gold" class="theme-card p-5 rounded-3xl border-2 cursor-pointer transition hover:scale-102 bg-white">
                        <div class="h-20 rounded-2xl bg-gradient-to-r from-stone-900 to-amber-900 mb-3 flex items-center justify-center text-amber-300 font-bold text-sm shadow-inner">
                            Luxury Gold
                        </div>
                        <h5 class="font-bold text-sm text-stone-900">1. Classic Luxury Gold</h5>
                        <p class="text-xs text-stone-500 mt-1">ရွှေအိုရောင် ဇိမ်ခံဒီဇိုင်း (မူလ ပုံစံ)</p>
                    </div>

                    <div onclick="setShopTheme('rose-paris')" id="theme-rose-paris" class="theme-card p-5 rounded-3xl border-2 cursor-pointer transition hover:scale-102 bg-white">
                        <div class="h-20 rounded-2xl bg-gradient-to-r from-rose-900 to-pink-700 mb-3 flex items-center justify-center text-pink-200 font-bold text-sm shadow-inner">
                            Parisian Rose
                        </div>
                        <h5 class="font-bold text-sm text-stone-900">2. Parisian Rose</h5>
                        <p class="text-xs text-stone-500 mt-1">နူးညံ့လှပသော ပန်းနုရောင် ဖက်ရှင်စတိုင်</p>
                    </div>

                    <div onclick="setShopTheme('minimal-noir')" id="theme-minimal-noir" class="theme-card p-5 rounded-3xl border-2 cursor-pointer transition hover:scale-102 bg-white">
                        <div class="h-20 rounded-2xl bg-gradient-to-r from-stone-950 to-emerald-900 mb-3 flex items-center justify-center text-emerald-300 font-bold text-sm shadow-inner">
                            Editorial Noir
                        </div>
                        <h5 class="font-bold text-sm text-stone-900">3. Modern Noir & Emerald</h5>
                        <p class="text-xs text-stone-500 mt-1">ခေတ်မီ အနက်နှင့် မြစိမ်းရောင် မဂ္ဂဇင်းစတိုင်</p>
                    </div>
                </div>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 7. COUPONS PANE -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-coupons" class="pane space-y-4 hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-stone-900 text-sm">လျှော့ဈေး ကူပွန်များ (Coupons)</h4>
                        <p class="text-xs text-stone-500">ဝယ်ယူသူများ အသုံးပြုနိုင်သော Promotion Coupon ကုဒ်များ ဖန်တီးခြင်း</p>
                    </div>
                    <button onclick="openCouponModal()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition flex items-center gap-2 shadow-md shadow-amber-600/20">
                        <i data-lucide="plus" class="w-4 h-4"></i> ကူပွန်အသစ် ထည့်မည်
                    </button>
                </div>

                <!-- Coupons Table -->
                <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden text-xs">
                    <table class="w-full text-left">
                        <thead class="bg-stone-50 text-stone-500 font-bold border-b border-stone-200">
                            <tr>
                                <th class="p-3.5">ကူပွန်ကုဒ်</th>
                                <th class="p-3.5">အမျိုးအစား</th>
                                <th class="p-3.5">လျှော့ပေးနှုန်း</th>
                                <th class="p-3.5">အနည်းဆုံးဝယ်ယူငွေ</th>
                                <th class="p-3.5">အသုံးပြုမှု / ကန့်သတ်ချက်</th>
                                <th class="p-3.5">အခြေအနေ</th>
                                <th class="p-3.5 text-right">လုပ်ဆောင်ချက်</th>
                            </tr>
                        </thead>
                        <tbody id="couponsTableBody" class="divide-y divide-stone-100">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 7.5. PAYMENT METHODS PANE (ငွေပေးချေမှု နည်းလမ်းများ စီမံခြင်း) -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-payments" class="pane space-y-4 hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="font-extrabold text-stone-900 text-sm">ငွေပေးချေမှု နည်းလမ်းများ (Payment Methods)</h4>
                        <p class="text-xs text-stone-500">KPay, WavePay, ဘဏ်အကောင့်၊ COD စသည့် ငွေချေစနစ်များကို ကိုယ်တိုင် ထည့်သွင်းပြင်ဆင်ခြင်း</p>
                    </div>
                    <button onclick="openPaymentModal()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition flex items-center gap-2 shadow-md shadow-amber-600/20 self-start sm:self-auto">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> ငွေချေနည်းလမ်း အသစ်ထည့်မည်
                    </button>
                </div>

                <!-- Payment Methods Info / Stats Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="bg-white p-3.5 rounded-2xl border border-stone-200 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-stone-400 font-bold uppercase">စုစုပေါင်း နည်းလမ်း</p>
                            <p id="statPmTotal" class="text-base font-black text-stone-900">0</p>
                        </div>
                    </div>
                    <div class="bg-white p-3.5 rounded-2xl border border-stone-200 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-stone-400 font-bold uppercase">လက်ရှိ အသုံးပြုနေသည်</p>
                            <p id="statPmActive" class="text-base font-black text-emerald-600">0</p>
                        </div>
                    </div>
                    <div class="bg-white p-3.5 rounded-2xl border border-stone-200 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <i data-lucide="smartphone" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-stone-400 font-bold uppercase">မိုဘိုင်း / ငွေလွှဲ</p>
                            <p id="statPmTransfer" class="text-base font-black text-blue-600">0</p>
                        </div>
                    </div>
                    <div class="bg-white p-3.5 rounded-2xl border border-stone-200 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-stone-100 text-stone-700 flex items-center justify-center font-bold">
                            <i data-lucide="truck" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-stone-400 font-bold uppercase">အိမ်ရောက်ငွေချေ (COD)</p>
                            <p id="statPmCod" class="text-base font-black text-stone-800">0</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods Grid / Cards -->
                <div id="paymentMethodsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Populated dynamically via renderPaymentMethods() -->
                </div>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 8. SETTINGS PANE -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-settings" class="pane space-y-6 hidden">
                <div>
                    <h4 class="font-extrabold text-stone-900 text-sm">ဆိုင် ဆက်တင်များနှင့် Telegram အသိပေးစနစ်</h4>
                    <p class="text-xs text-stone-500">ဆိုင်အချက်အလက်များနှင့် အော်ဒါအသစ်ဝင်ပါက Telegram သို့ ချက်ချင်းအသိပေးသည့် စနစ်</p>
                </div>

                <form onsubmit="handleSaveSettings(event)" class="space-y-6">
                    <!-- Shop Branding, Logo & Title Bar Settings -->
                    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4 text-xs">
                        <h5 class="font-bold text-sm text-stone-900 flex items-center gap-2 border-b pb-3">
                            <i data-lucide="layout" class="w-4 h-4 text-amber-600"></i> ဆိုင် Logo နှင့် Title Bar ဆက်တင်များ (Branding & Display)
                        </h5>

                        <!-- Title Bar Setting -->
                        <div>
                            <label class="block font-bold mb-1">ဝဘ်ဆိုက် ခေါင်းစဉ် / Title Bar (Browser Tab & Header Title)</label>
                            <input type="text" id="setTitleBar" placeholder="Pont Pont Handbag Shop | ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး (မန္တလေး)" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 font-medium">
                            <span class="text-[11px] text-stone-400">Browser tab နှင့် ဝဘ်ဆိုက် ထိပ်စီးတွင် ပြသမည့် ခေါင်းစဉ် (Title Bar)</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Shop Subtitle / Tagline -->
                            <div>
                                <label class="block font-bold mb-1">ဆိုင် ခေါင်းစဉ်ခွဲ (Tagline / Subtitle)</label>
                                <input type="text" id="setShopSubtitle" placeholder="ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300">
                                <span class="text-[11px] text-stone-400">ဆိုင် Logo/အမည်အောက်တွင် ပြသမည့် စာတန်း</span>
                            </div>

                            <!-- Shop Top Announcement Bar -->
                            <div>
                                <label class="block font-bold mb-1">ဆိုင် အပေါ်ဘား အသိပေးစာတန်း (Top Announcement)</label>
                                <input type="text" id="setAnnouncement" placeholder="✨ PONTPONT10 ကူပွန်ဖြင့် 10% OFF • ၁ သိန်းအထက် Free Delivery" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300">
                                <span class="text-[11px] text-stone-400">ဝဘ်ဆိုက်ထိပ်ဆုံး အနက်ရောင်ဘားတွင် ပြသမည့် စာတန်း</span>
                            </div>
                        </div>

                        <!-- Shop Logo Setting -->
                        <div class="pt-3 border-t border-stone-100">
                            <label class="block font-bold mb-1.5">ဆိုင် Logo (Shop Logo)</label>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <!-- Logo Preview Box -->
                                <div id="setShopLogoPreviewBox" class="w-20 h-20 rounded-2xl border-2 border-dashed border-stone-300 flex items-center justify-center bg-white overflow-hidden flex-shrink-0 relative group shadow-xs">
                                    <img id="setShopLogoPreview" src="" alt="Logo Preview" class="w-full h-full object-contain hidden p-1">
                                    <div id="setShopLogoPlaceholder" class="text-stone-400 flex flex-col items-center justify-center text-center p-2">
                                        <i data-lucide="image" class="w-6 h-6 mb-1 text-stone-300"></i>
                                        <span class="text-[10px] font-bold text-stone-400">No Logo</span>
                                    </div>
                                </div>
                                
                                <div class="flex-1 w-full space-y-2">
                                    <div class="flex gap-2">
                                        <input type="text" id="setShopLogo" placeholder="Logo ပုံ URL (https://...) သို့မဟုတ် Upload တင်ပါ" class="flex-1 px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs font-mono" oninput="updateLogoPreview(this.value)">
                                        <label class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-3.5 py-2.5 rounded-xl text-xs flex items-center gap-1.5 cursor-pointer whitespace-nowrap transition shadow-xs">
                                            <i data-lucide="upload" class="w-4 h-4"></i>
                                            <span>Upload Logo</span>
                                            <input type="file" id="setLogoUploadFile" accept="image/*" class="hidden" onchange="handleLogoUpload(event)">
                                        </label>
                                        <button type="button" onclick="clearLogo()" class="px-3.5 py-2.5 bg-stone-100 hover:bg-red-50 text-stone-600 hover:text-red-700 border border-stone-200 rounded-xl text-xs font-bold transition">
                                            ဖျက်မည်
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-stone-400">PNG, JPG, SVG, WEBP အမျိုးအစားများ လက်ခံပါသည် (စတုရန်းပုံ သို့မဟုတ် အလျားလိုက် Transparent Logo အဆင်ပြေပါသည်)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- General Shop Contact Info -->
                    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4 text-xs">
                        <h5 class="font-bold text-sm text-stone-900 flex items-center gap-2 border-b pb-3">
                            <i data-lucide="store" class="w-4 h-4 text-amber-600"></i> ဆိုင် ဆက်သွယ်ရန် အချက်အလက်များ
                        </h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold mb-1">ဆိုင်အမည် (English)</label>
                                <input type="text" id="setShopName" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">ဆိုင်အမည် (မြန်မာ)</label>
                                <input type="text" id="setShopNameMm" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold mb-1">ဖုန်းနံပါတ် (၁)</label>
                                <input type="text" id="setPhone1" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300">
                            </div>
                            <div>
                                <label class="block font-bold mb-1">ဖုန်းနံပါတ် (၂)</label>
                                <input type="text" id="setPhone2" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300">
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">KPay / WavePay အကောင့် အချက်အလက်</label>
                            <input type="text" id="setKpayAccount" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300">
                        </div>
                    </div>

                    <!-- Telegram Notification Settings -->
                    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4 text-xs">
                        <div class="flex items-center justify-between border-b pb-3">
                            <h5 class="font-bold text-sm text-stone-900 flex items-center gap-2">
                                <i data-lucide="bell-ring" class="w-4 h-4 text-blue-600"></i> Telegram အော်ဒါ အသိပေးစနစ် (Instant Alert)
                            </h5>
                            <button type="button" onclick="testTelegramAlert()" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl font-bold flex items-center gap-1.5 transition">
                                <i data-lucide="send" class="w-3.5 h-3.5"></i> စမ်းသပ် ပေးပို့မည် (Test)
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="setTelegramEnabled" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                            <label for="setTelegramEnabled" class="font-bold text-stone-800 cursor-pointer">ဝဘ်ဆိုက်မှ အော်ဒါအသစ် တက်လာပါက Telegram သို့ ချက်ချင်း အသိပေးချက် ပို့မည်</label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block font-bold mb-1">Telegram Bot Token</label>
                                <input type="text" id="setTelegramBotToken" placeholder="123456789:ABCdefGHIjkl..." class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 font-mono">
                                <span class="text-[11px] text-stone-400">@BotFather မှ ရရှိသော API Token</span>
                            </div>
                            <div>
                                <label class="block font-bold mb-1">Telegram Chat ID / Group ID</label>
                                <input type="text" id="setTelegramChatId" placeholder="12345678 သို့မဟုတ် -10012345678" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 font-mono">
                                <span class="text-[11px] text-stone-400">အသိပေးချက် လက်ခံမည့် သင်၏ Telegram Chat ID</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-6 rounded-2xl text-xs transition shadow-lg shadow-amber-600/30 flex items-center gap-2 ml-auto">
                            <i data-lucide="save" class="w-4 h-4"></i> 💾 ဆက်တင်များ အားလုံး သိမ်းဆည်းမည်
                        </button>
                    </div>
                </form>
            </section>

            <!-- ----------------------------------------------------------- -->
            <!-- 10. AI CHATBOT PANE -->
            <!-- ----------------------------------------------------------- -->
            <section id="pane-chatbot" class="pane space-y-6 hidden">
                <div class="p-5 bg-gradient-to-r from-emerald-50 via-teal-50 to-amber-50 border border-emerald-200 rounded-3xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                    <div>
                        <h4 class="font-extrabold text-stone-900 text-sm flex items-center gap-2">
                            <span class="p-1.5 bg-emerald-600 text-white rounded-xl shadow-xs"><i data-lucide="bot" class="w-4 h-4"></i></span>
                            🤖 AI Chatbot Configuration (ဉာဏ်ရည်တု အကူအညီ စီမံခန့်ခွဲမှု)
                        </h4>
                        <p class="text-xs text-stone-600 mt-1">
                            ဝယ်ယူသူများ မေးမြန်းသမျှကို ၂၄ နာရီ မနားတမ်း ပြန်လည်ဖြေကြားပေးမည့် AI Provider (Gemini / OpenAI / Groq)၊ API Key နှင့် Prompt များကို စီမံနိုင်ပါသည်။
                        </p>
                    </div>
                    <label class="flex items-center gap-2.5 text-xs font-bold text-stone-800 cursor-pointer bg-white/90 px-4 py-2.5 rounded-2xl border border-emerald-200 shadow-xs shrink-0">
                        <input type="checkbox" id="adminBotEnabled" checked class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500">
                        <span>Chatbot ဖွင့်ထားမည် (Active)</span>
                    </label>
                </div>

                <form onsubmit="handleSaveChatbot(event)" class="space-y-6">
                    <!-- AI Provider & Model -->
                    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-xs space-y-4 text-xs">
                        <h5 class="font-bold text-sm text-stone-900 flex items-center gap-2 border-b pb-3">
                            <i data-lucide="cpu" class="w-4 h-4 text-emerald-600"></i> AI Provider နှင့် Model ရွေးချယ်ခြင်း
                        </h5>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold mb-1.5 uppercase tracking-wider text-stone-700">AI ဝန်ဆောင်မှု ပံ့ပိုးသူ (Provider)</label>
                                <select id="adminBotProvider" onchange="handleBotProviderChange()" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white font-bold text-stone-800 focus:ring-2 focus:ring-emerald-500">
                                    <option value="gemini">Google Gemini API (အကြံပြုချက် - မြန်မာစာကျွမ်းကျင်ပြီး အခမဲ့ Key ရရှိနိုင်)</option>
                                    <option value="openai">OpenAI (GPT-4o-mini / GPT-4o)</option>
                                    <option value="groq">Groq (Llama 3.3 70B - Ultra Fast အလွန်မြန်ဆန်)</option>
                                    <option value="mock">Built-in Rule Engine (API Key မလိုပါ - မူလစနစ်)</option>
                                    <option value="custom">Custom OpenAI-Compatible API (OpenWebUI, Ollama, LM Studio)</option>
                                </select>
                                <p id="providerHintText" class="text-[11px] text-stone-500 mt-1.5"></p>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block font-bold uppercase tracking-wider text-stone-700">Model Name (မော်ဒယ်အမည်)</label>
                                    <span id="modelStatusBadge" class="text-[11px] text-emerald-700 font-bold hidden"></span>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex gap-2">
                                        <input type="text" id="adminBotModel" value="gemini-flash-lite-latest" placeholder="e.g. gemini-flash-lite-latest, gpt-4o-mini" class="flex-1 px-3.5 py-2.5 rounded-xl border border-stone-300 font-mono text-stone-800">
                                        <select id="adminBotModelSelect" onchange="document.getElementById('adminBotModel').value = this.value;" class="flex-1 px-3.5 py-2.5 rounded-xl border border-emerald-400 bg-emerald-50 font-mono font-bold hidden">
                                            <!-- Dynamically populated -->
                                        </select>
                                        <button type="button" onclick="fetchAvailableModels()" id="btnFetchModels" class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 border border-stone-300 rounded-xl font-bold flex items-center gap-1.5 transition shrink-0">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5" id="fetchModelsIcon"></i>
                                            <span id="btnFetchModelsText">Fetch Models</span>
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-stone-400">Gemini: <code>gemini-flash-lite-latest</code> (Free Quota အကြံပြုချက်) | OpenAI: <code>gpt-4o-mini</code> | Groq: <code>llama-3.3-70b-versatile</code></p>
                                </div>
                            </div>
                        </div>

                        <!-- API Key & Custom URL Containers -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div id="adminApiKeyContainer">
                                <label class="block font-bold mb-1.5 uppercase tracking-wider text-stone-700 flex items-center justify-between">
                                    <span>API Key</span>
                                    <span id="apiKeyStatusBadge" class="text-[11px] font-normal text-stone-500"></span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="adminBotApiKey" placeholder="AI Provider မှ API Key ထည့်ပါ" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 font-mono text-stone-800 pr-10">
                                    <button type="button" onclick="toggleApiKeyVisibility()" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-700 p-1">
                                        <i data-lucide="eye" id="apiKeyEyeIcon" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-stone-500 mt-1.5">
                                    <span id="apiKeyLinkText"></span>
                                    <span class="text-stone-400">Database တွင် လုံခြုံစွာ သိမ်းဆည်းပါသည်</span>
                                </div>
                            </div>

                            <div id="adminApiUrlContainer" class="hidden">
                                <label class="block font-bold mb-1.5 uppercase tracking-wider text-stone-700">Custom API Base URL</label>
                                <input type="text" id="adminBotApiUrl" placeholder="https://api.example.com/v1" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 font-mono text-stone-800">
                                <span class="text-[11px] text-stone-400 block mt-1.5">OpenWebUI, Ollama သို့မဟုတ် Local LLM Base URL</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bot Persona & Welcome Message -->
                    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-xs space-y-4 text-xs">
                        <h5 class="font-bold text-sm text-stone-900 flex items-center gap-2 border-b pb-3">
                            <i data-lucide="message-square" class="w-4 h-4 text-amber-600"></i> Bot အချက်အလက်နှင့် ကြိုဆိုစကား
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold mb-1.5 uppercase tracking-wider text-stone-700">Bot အမည် (Bot Name)</label>
                                <input type="text" id="adminBotName" value="ပုံ့ပုံ့ AI အကူအညီ" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 font-bold text-stone-800">
                            </div>
                            <div>
                                <label class="block font-bold mb-1.5 uppercase tracking-wider text-stone-700">ပထမဆုံး နှုတ်ဆက်မည့် စကား (Welcome Message)</label>
                                <input type="text" id="adminBotWelcome" value="မင်္ဂလာပါရှင် 👋 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်။ ဘာများ ကူညီပေးရမလဲရှင်။" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-stone-800">
                            </div>
                        </div>
                    </div>

                    <!-- System Prompt Instructions -->
                    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-xs space-y-4 text-xs">
                        <div class="flex items-center justify-between border-b pb-3">
                            <div>
                                <h5 class="font-bold text-sm text-stone-900 flex items-center gap-2">
                                    <i data-lucide="sparkles" class="w-4 h-4 text-purple-600"></i> AI System Prompt (လမ်းညွှန်ချက်များနှင့် ဆိုင်အချက်အလက်)
                                </h5>
                                <p class="text-xs text-stone-500 mt-0.5">AI က ဆိုင်အကြောင်း မေးခွန်းများ ဖြေကြားရာတွင် လိုက်နာရမည့် ဆိုင်လိပ်စာ၊ ဖုန်း၊ ပို့ခနှင့် စည်းမျဉ်းများ</p>
                            </div>
                            <button type="button" onclick="resetDefaultPrompt()" class="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold flex items-center gap-1.5 transition">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> မူလအတိုင်း ပြန်ထားမည်
                            </button>
                        </div>
                        <textarea id="adminBotSystemPrompt" rows="8" class="w-full p-3.5 rounded-2xl border border-stone-300 font-mono text-xs text-stone-800 leading-relaxed"></textarea>
                    </div>

                    <!-- Live Test Chat Box -->
                    <div class="bg-stone-900 text-white p-6 rounded-3xl border border-stone-800 shadow-md space-y-4 text-xs">
                        <div class="flex items-center justify-between border-b border-stone-800 pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="p-1.5 bg-emerald-500/20 text-emerald-400 rounded-xl"><i data-lucide="message-circle" class="w-4 h-4"></i></span>
                                <h5 class="font-black text-sm text-white">AI Chatbot စမ်းသပ်စကားပြောကြည့်ရန် (Live Test Chat)</h5>
                            </div>
                            <span class="text-[11px] text-stone-400">ဆက်တင်မသိမ်းမီ သို့မဟုတ် သိမ်းပြီးနောက် စမ်းသပ်နိုင်ပါသည်</span>
                        </div>
                        
                        <div id="adminTestChatOutput" class="min-h-[100px] max-h-[220px] overflow-y-auto p-4 bg-stone-950/70 rounded-2xl border border-stone-800/80 space-y-2.5 text-xs">
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold shrink-0">🤖 Bot:</span>
                                <span class="text-stone-300">မင်္ဂလာပါရှင်! AI Chatbot ချိတ်ဆက်မှု အဆင်ပြေမပြေ စမ်းသပ်မေးမြန်းနိုင်ပါပြီရှင်။ (ဥပမာ- "ဆိုင်လိပ်စာ သိချင်ပါတယ်" သို့မဟုတ် "ပို့ခ ဘယ်လောက်လဲ")</span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <input type="text" id="adminTestQuery" placeholder="စမ်းသပ်မေးခွန်း ရိုက်ထည့်ပါ (ဥပမာ- မင်္ဂလာပါ ဆိုင်ဘယ်မှာရှိလဲ)..." onkeydown="if(event.key==='Enter'){event.preventDefault();testAdminChatbot();}" class="flex-1 px-4 py-2.5 rounded-xl bg-stone-800 border border-stone-700 text-white text-xs placeholder:text-stone-500 focus:ring-2 focus:ring-emerald-500">
                            <button type="button" onclick="testAdminChatbot()" id="btnTestChat" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold flex items-center gap-1.5 transition shrink-0">
                                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                <span id="btnTestChatText">စမ်းသပ်မေးမည်</span>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="text-right">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-7 rounded-2xl text-xs transition shadow-lg shadow-emerald-600/30 flex items-center gap-2 ml-auto">
                            <i data-lucide="save" class="w-4 h-4"></i> 💾 AI Chatbot ဆက်တင်များ သိမ်းဆည်းမည်
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <!-- Slip View Modal -->
    <div id="slipViewModal" class="fixed inset-0 z-50 bg-stone-900/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-white max-w-lg w-full rounded-3xl p-6 shadow-2xl border border-stone-200 space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <div>
                    <h5 class="font-black text-sm text-stone-900">ငွေလွှဲပြေစာ စစ်ဆေးခြင်း (Payment Slip)</h5>
                    <p class="text-xs text-stone-500" id="slipModalOrderInfo"></p>
                </div>
                <button onclick="closeSlipModal()" class="text-stone-400 hover:text-stone-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="text-center bg-stone-100 rounded-2xl p-2 max-h-[70vh] overflow-auto">
                <img id="slipModalImg" src="" alt="Payment Slip" class="max-w-full max-h-[60vh] mx-auto rounded-xl shadow">
            </div>
            <div class="flex justify-between items-center text-xs">
                <span id="slipModalTxRef" class="font-mono font-bold bg-amber-100 text-amber-900 px-3 py-1 rounded-lg"></span>
                <button onclick="closeSlipModal()" class="px-4 py-2 bg-stone-900 text-white font-bold rounded-xl hover:bg-black transition">
                    ပိတ်မည် (Close)
                </button>
            </div>
        </div>
    </div>

    <!-- Invoice Printable Modal -->
    <div id="invoiceModal" class="fixed inset-0 z-50 bg-stone-900/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-white max-w-2xl w-full rounded-3xl p-6 md:p-8 shadow-2xl border border-stone-200 max-h-[90vh] overflow-y-auto space-y-6">
            <div class="flex items-center justify-between border-b pb-3 no-print">
                <div class="flex items-center gap-2">
                    <i data-lucide="printer" class="w-5 h-5 text-amber-600"></i>
                    <h5 class="font-black text-base text-stone-900">အော်ဒါ ပြေစာ (Order Invoice)</h5>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.print()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-md shadow-amber-600/20">
                        <i data-lucide="printer" class="w-4 h-4"></i> 🖨️ ပုံနှိပ်မည် (Print)
                    </button>
                    <button onclick="closeInvoiceModal()" class="p-2 text-stone-400 hover:text-stone-700 rounded-xl hover:bg-stone-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Printable Area -->
            <div id="invoicePrintArea" class="bg-white p-2 md:p-4 text-stone-800 space-y-6 font-sans">
                <!-- Shop Header -->
                <div class="flex justify-between items-start border-b-2 border-amber-600 pb-4">
                    <div>
                        <h2 class="text-xl md:text-2xl font-black text-stone-900" id="invShopName">ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး)</h2>
                        <p class="text-xs text-stone-500 font-medium">Pont Pont Handbag Shop & Fashion Store</p>
                        <p class="text-xs text-stone-600 mt-1">၇၃ လမ်း၊ ၃၆ လမ်းနှင့် ၃၇ လမ်းကြား၊ မန္တလေးမြို့။</p>
                        <p class="text-xs text-stone-600">ဖုန်း: <span id="invShopPhones" class="font-bold">09-963000502, 09-750072752</span></p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block bg-amber-100 text-amber-900 text-xs font-black uppercase px-3 py-1 rounded-full mb-1">INVOICE / ပြေစာ</span>
                        <p class="font-mono font-bold text-sm text-stone-900 mt-1" id="invOrderId">#PP-2026-0000</p>
                        <p class="text-[11px] text-stone-500" id="invDate">Date: 2026-11-23</p>
                        <p class="text-xs font-bold mt-1"><span class="px-2 py-0.5 rounded text-[11px]" id="invStatusBadge">Confirmed</span></p>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="grid grid-cols-2 gap-4 text-xs bg-stone-50 p-4 rounded-2xl border border-stone-200">
                    <div>
                        <p class="text-stone-400 text-[10px] font-bold uppercase tracking-wider mb-1">ပို့ဆောင်မည့်သူ (Deliver To)</p>
                        <p class="font-bold text-stone-900 text-sm" id="invCustomerName"></p>
                        <p class="text-stone-600 mt-0.5" id="invCustomerPhone"></p>
                        <p class="text-stone-600 mt-0.5" id="invCustomerAddress"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-stone-400 text-[10px] font-bold uppercase tracking-wider mb-1">ငွေချေမှု အချက်အလက် (Payment)</p>
                        <p class="font-bold text-stone-900" id="invPaymentMethod"></p>
                        <p class="font-mono text-stone-600 text-[11px]" id="invTxRef"></p>
                        <p class="text-stone-500 italic mt-1 text-[11px]" id="invNote"></p>
                    </div>
                </div>

                <!-- Items Table -->
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b-2 border-stone-200 text-stone-500 font-bold text-[11px]">
                            <th class="py-2.5">စဉ်</th>
                            <th class="py-2.5">ပစ္စည်းအမည်</th>
                            <th class="py-2.5">အရောင်</th>
                            <th class="py-2.5 text-center">အရေအတွက်</th>
                            <th class="py-2.5 text-right">နှုန်း (Ks)</th>
                            <th class="py-2.5 text-right">ကျသင့်ငွေ (Ks)</th>
                        </tr>
                    </thead>
                    <tbody id="invItemsTable" class="divide-y divide-stone-100">
                    </tbody>
                </table>

                <!-- Summary -->
                <div class="flex justify-end pt-2">
                    <div class="w-64 space-y-1.5 text-xs text-stone-600">
                        <div class="flex justify-between">
                            <span>ပစ္စည်းစုစုပေါင်း:</span>
                            <span class="font-bold text-stone-900" id="invSubtotal">0 Ks</span>
                        </div>
                        <div class="flex justify-between text-emerald-600" id="invDiscountRow">
                            <span>လျှော့ဈေး (Discount):</span>
                            <span class="font-bold" id="invDiscount">-0 Ks</span>
                        </div>
                        <div class="flex justify-between">
                            <span>ပို့ဆောင်ခ (Delivery Fee):</span>
                            <span class="font-bold text-stone-900" id="invDeliveryFee">0 Ks</span>
                        </div>
                        <div class="flex justify-between border-t-2 border-stone-900 pt-2 text-sm font-black text-amber-700">
                            <span>စုစုပေါင်း ကျသင့်ငွေ:</span>
                            <span id="invTotal">0 Ks</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Policies -->
                <div class="border-t border-stone-200 pt-4 text-center text-[11px] text-stone-500 space-y-1">
                    <p class="font-bold text-stone-700">ပုံ့ပုံ့ အိတ်ဆိုင်အား အားပေးမှုကို အထူးပင် ကျေးဇူးတင်ရှိပါသည်! 🙏</p>
                    <p>ပစ္စည်းချို့ယွင်းချက် တစ်စုံတစ်ရာ ရှိပါက ပစ္စည်းလက်ခံရရှိပြီး (၃) ရက်အတွင်း ဆက်သွယ်လဲလှယ်နိုင်ပါသည်။</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Print Modal (A4 Printable Accounting Report) -->
    <div id="reportPrintModal" class="fixed inset-0 z-50 bg-stone-900/80 backdrop-blur-sm flex items-center justify-center p-4 hidden overflow-y-auto">
        <div class="bg-white max-w-3xl w-full rounded-3xl p-6 sm:p-8 shadow-2xl border border-stone-200 space-y-6 my-auto text-stone-900 text-xs">
            <div class="flex items-center justify-between border-b pb-4 no-print">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 bg-amber-600 text-white rounded-xl"><i data-lucide="printer" class="w-4 h-4"></i></span>
                    <h4 class="font-black text-sm text-stone-900">စာရင်းချုပ် Print အစမ်းကြည့်ရှုခြင်း (Print Preview)</h4>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.print()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition flex items-center gap-1.5 shadow-xs">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Print အခုထုတ်မည်</span>
                    </button>
                    <button onclick="closeReportPrintModal()" class="p-2 text-stone-400 hover:text-stone-700 rounded-xl hover:bg-stone-100 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Printable Sheet Area -->
            <div id="reportPrintArea" class="space-y-6">
                <!-- Shop Header -->
                <div class="flex items-start justify-between border-b-2 border-stone-900 pb-4">
                    <div>
                        <h2 class="text-xl font-black text-stone-900">ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး</h2>
                        <p class="text-xs text-stone-600 mt-0.5">ပင်မဆိုင်: ၆၅ လမ်း၊ ၁၀၇×၁၀၈ လမ်းကြား၊ မန္တလေးမြို့ • ဖုန်း: 09-963000502</p>
                        <p class="text-[11px] text-stone-500 font-mono mt-0.5">Pont Pont Luxury Handbags • Accounting & Sales Statement</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block bg-stone-900 text-amber-400 px-3 py-1 rounded-lg text-xs font-black uppercase" id="printReportTitleBadge">အပတ်စဉ် စာရင်းချုပ်</span>
                        <p class="text-[11px] text-stone-500 mt-1" id="printReportDateGenerated"></p>
                    </div>
                </div>

                <!-- Period Summary Details -->
                <div class="grid grid-cols-4 gap-3 bg-stone-50 p-4 rounded-2xl border border-stone-200 text-center">
                    <div>
                        <span class="text-[10px] text-stone-500 font-bold block uppercase">စုစုပေါင်း ရောင်းရငွေ</span>
                        <span id="printRepRev" class="text-base font-black text-stone-900">0 Ks</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-stone-500 font-bold block uppercase">အော်ဒါအရေအတွက်</span>
                        <span id="printRepOrders" class="text-base font-black text-stone-900">0</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-stone-500 font-bold block uppercase">ပို့ပြီး (Delivered)</span>
                        <span id="printRepDelivered" class="text-base font-black text-emerald-700">0</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-stone-500 font-bold block uppercase">ပို့ခ စုစုပေါင်း</span>
                        <span id="printRepDeliveryFee" class="text-base font-black text-blue-700">0 Ks</span>
                    </div>
                </div>

                <!-- Breakdown Table for Print -->
                <div>
                    <h5 class="font-bold text-xs uppercase tracking-wider text-stone-700 mb-2">အသေးစိတ် ဇယားချုပ်:</h5>
                    <table class="w-full text-left border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-stone-100 border-b border-stone-300 font-bold">
                                <th class="p-2" id="printThPeriod">ကာလ (Period)</th>
                                <th class="p-2">ရက်စွဲ (Dates)</th>
                                <th class="p-2 text-center">အော်ဒါ</th>
                                <th class="p-2 text-center">ပို့ပြီး</th>
                                <th class="p-2 text-right">ပို့ခ</th>
                                <th class="p-2 text-right">လျှော့ငွေ</th>
                                <th class="p-2 text-right font-black">ရောင်းရငွေ (Ks)</th>
                            </tr>
                        </thead>
                        <tbody id="printRepTableBody" class="divide-y divide-stone-200">
                        </tbody>
                        <tfoot id="printRepTableFoot" class="border-t-2 border-stone-900 font-black">
                        </tfoot>
                    </table>
                </div>

                <!-- Signatures -->
                <div class="pt-8 grid grid-cols-2 gap-8 text-center text-xs">
                    <div>
                        <p class="text-stone-400 mb-10">......................................................</p>
                        <p class="font-bold text-stone-800">စာရင်းစစ်ဆေးသူ (Accountant)</p>
                    </div>
                    <div>
                        <p class="text-stone-400 mb-10">......................................................</p>
                        <p class="font-bold text-stone-800">ဆိုင်ရှင် / မန်နေဂျာ (Shop Owner)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Coupon Modal -->
    <div id="couponModal" class="fixed inset-0 z-50 bg-stone-900/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-white max-w-md w-full rounded-3xl p-6 shadow-2xl border border-stone-200 space-y-4 text-xs">
            <div class="flex items-center justify-between border-b pb-3">
                <h5 class="font-black text-sm text-stone-900">ကူပွန်အသစ် ထည့်သွင်းခြင်း</h5>
                <button onclick="closeCouponModal()" class="text-stone-400 hover:text-stone-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form onsubmit="handleSaveCoupon(event)" class="space-y-3">
                <div>
                    <label class="block font-bold mb-1">ကူပွန်ကုဒ် (Coupon Code) *</label>
                    <input type="text" id="cpnCode" placeholder="ဥပမာ: SUMMER20" required class="w-full px-3 py-2 rounded-xl border border-stone-300 font-mono uppercase font-bold">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">အမျိုးအစား (Type)</label>
                        <select id="cpnType" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white">
                            <option value="percent">ရာခိုင်နှုန်း (%)</option>
                            <option value="fixed">ငွေပမာဏ (Ks)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">လျှော့ပေးနှုန်း (Value) *</label>
                        <input type="number" id="cpnValue" placeholder="10 သို့မဟုတ် 5000" required class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">အနည်းဆုံး ဝယ်ယူငွေ (Ks)</label>
                        <input type="number" id="cpnMinOrder" value="0" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                    <div>
                        <label class="block font-bold mb-1">အသုံးပြုခွင့် အကြိမ် (Limit)</label>
                        <input type="number" id="cpnLimit" value="0" placeholder="0 = အကန့်အသတ်မရှိ" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t">
                    <button type="button" onclick="closeCouponModal()" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 font-bold">မလုပ်တော့ပါ</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-md shadow-amber-600/20">သိမ်းဆည်းမည်</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add/Edit Payment Method Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 bg-stone-900/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-white max-w-lg w-full rounded-3xl p-6 shadow-2xl border border-stone-200 space-y-4 text-xs max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <h5 id="paymentModalTitle" class="font-black text-sm text-stone-900">ငွေချေနည်းလမ်း အသစ်ထည့်သွင်းခြင်း</h5>
                <button type="button" onclick="closePaymentModal()" class="text-stone-400 hover:text-stone-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form onsubmit="handleSavePaymentMethod(event)" class="space-y-3.5">
                <input type="hidden" id="pmId">
                <input type="hidden" id="pmQrCodeUrl">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1 text-stone-700">ကုဒ် (Code / Identifier) *</label>
                        <input type="text" id="pmCode" placeholder="ဥပမာ: KPay, Wave, CBPay" required class="w-full px-3 py-2 rounded-xl border border-stone-300 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-stone-700">အမျိုးအစား (Type) *</label>
                        <select id="pmType" onchange="handlePaymentTypeChange()" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white font-medium">
                            <option value="transfer">မိုဘိုင်းပိုက်ဆံအိတ် / ငွေလွှဲ (KPay, Wave)</option>
                            <option value="banking">ဘဏ်ငွေလွှဲ (CB, AYA, KBZ Bank)</option>
                            <option value="cod">အိမ်အရောက်ငွေချေ (Cash on Delivery)</option>
                            <option value="other">အခြားနည်းလမ်း (Other)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1 text-stone-700">နည်းလမ်းအမည် (မြန်မာ) *</label>
                        <input type="text" id="pmNameMm" placeholder="ဥပမာ: KBZPay (ငွေလွှဲ)" required class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-stone-700">နည်းလမ်းအမည် (English) *</label>
                        <input type="text" id="pmName" placeholder="ဥပမာ: KBZPay / WavePay" required class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                </div>

                <div id="pmAccountFields" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1 text-stone-700">အကောင့်ပိုင်ရှင်အမည် (Account Name)</label>
                        <input type="text" id="pmAccountName" placeholder="ဥပမာ: Ma Pont Pont (မပုံ့)" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-stone-700">အကောင့် / ဖုန်းနံပါတ် (Account / Phone)</label>
                        <input type="text" id="pmAccountNumber" placeholder="ဥပမာ: 09963000502" class="w-full px-3 py-2 rounded-xl border border-stone-300 font-mono font-bold">
                    </div>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-stone-700">ငွေပေးချေရန် လမ်းညွှန်ချက် (Instructions)</label>
                    <textarea id="pmInstructions" rows="2" placeholder="ဥပမာ: KBZPay ဖြင့် ငွေလွှဲပြီးပါက ပြေစာ Screenshot တင်ပေးပါရန်" class="w-full px-3 py-2 rounded-xl border border-stone-300"></textarea>
                </div>

                <div id="pmQrSection" class="p-3 bg-stone-50 rounded-2xl border border-stone-200 space-y-2">
                    <label class="block font-bold text-stone-700">ငွေလွှဲ QR Code ပုံ (QR Code Image - Optional)</label>
                    <div class="flex items-center gap-3">
                        <div id="pmQrPreviewWrapper" class="w-16 h-16 bg-white border border-stone-300 rounded-xl flex items-center justify-center overflow-hidden shrink-0 relative">
                            <img id="pmQrPreviewImg" src="" alt="QR" class="w-full h-full object-contain hidden">
                            <i id="pmQrPreviewPlaceholder" data-lucide="qr-code" class="w-8 h-8 text-stone-400"></i>
                        </div>
                        <div class="flex-1 space-y-1">
                            <input type="file" id="pmQrFileInput" accept="image/*" onchange="handleQrFileSelect(event)" class="text-[11px] text-stone-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-amber-600 file:text-white hover:file:bg-amber-700 cursor-pointer">
                            <div class="flex gap-2">
                                <button type="button" id="btnRemoveQr" onclick="removePmQr()" class="text-[11px] text-red-600 font-bold hover:underline hidden">QR ဖျက်မည်</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block font-bold mb-1 text-stone-700">ဦးစားပေး အစဉ်လိုက် (Sort Order)</label>
                        <input type="number" id="pmSortOrder" value="1" min="0" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                    <div class="space-y-2 pt-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="pmRequiresSlip" class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500">
                            <span class="font-bold text-stone-800">ပြေစာ Slip တင်ရန် လိုအပ်သည်</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="pmIsActive" checked class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500">
                            <span class="font-bold text-emerald-700">ဖွင့်ထားမည် (Active)</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="closePaymentModal()" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 font-bold text-stone-700">မလုပ်တော့ပါ</button>
                    <button type="submit" id="btnSavePm" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-md shadow-amber-600/20 flex items-center gap-1.5">
                        <i data-lucide="save" class="w-4 h-4"></i> သိမ်းဆည်းမည်
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- JAVASCRIPT LOGIC -->
    <!-- =================================================================== -->
    <script>
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        let shopData = {
            products: [],
            categories: [],
            announcements: [],
            orders: [],
            users: [],
            usersCount: 0,
            coupons: [],
            settings: {},
            currentUser: null,
            theme: 'luxury-gold'
        };

        let currentTab = 'overview';
        let currentOrderFilter = 'ALL';
        let orderSearchQuery = '';

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadData();
        });

        async function loadData(showSpin = false) {
            if (showSpin) {
                document.getElementById('refreshIcon').classList.add('animate-spin');
            }
            try {
                const res = await fetch('api/shop.php');
                const data = await res.json();
                shopData = data;

                // Check Admin authorization
                if (!shopData.currentUser || shopData.currentUser.role !== 'admin') {
                    document.getElementById('loginScreen').classList.remove('hidden');
                } else {
                    document.getElementById('loginScreen').classList.add('hidden');
                    document.getElementById('adminUserName').innerText = shopData.currentUser.name || 'Admin';
                    document.getElementById('adminUserEmail').innerText = shopData.currentUser.email || '';
                    renderAll();
                }
            } catch (err) {
                console.error("Failed to load admin data:", err);
            } finally {
                if (showSpin) {
                    setTimeout(() => {
                        document.getElementById('refreshIcon').classList.remove('animate-spin');
                    }, 500);
                }
            }
        }

        async function handleAdminLogin(e) {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value.trim();
            const password = document.getElementById('loginPassword').value;
            const errBox = document.getElementById('loginError');
            const submitBtn = document.getElementById('loginSubmitBtn');

            errBox.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span>စစ်ဆေးနေပါသည်...</span>`;

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'LOGIN', email, password })
                });
                const data = await res.json();
                if (res.ok && data.ok) {
                    if (data.user.role !== 'admin') {
                        errBox.innerText = 'ဤအကောင့်သည် Admin အကောင့် မဟုတ်ပါ။ Admin အကောင့်ဖြင့်သာ ဝင်ရောက်နိုင်ပါသည်။';
                        errBox.classList.remove('hidden');
                    } else {
                        document.getElementById('loginScreen').classList.add('hidden');
                        loadData();
                    }
                } else {
                    errBox.innerText = data.error || 'အီးမေးလ် သို့မဟုတ် စကားဝှက် မှားယွင်းနေပါသည်။';
                    errBox.classList.remove('hidden');
                }
            } catch (err) {
                errBox.innerText = 'ချိတ်ဆက်မှု မအောင်မြင်ပါ။ ပြန်လည်ကြိုးစားပါ။';
                errBox.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<i data-lucide="log-in" class="w-4 h-4"></i><span>အကောင့်ဝင်မည်</span>`;
                lucide.createIcons();
            }
        }

        async function handleLogout() {
            if (!confirm('Admin Dashboard မှ ထွက်ရန် သေချာပါသလား?')) return;
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'LOGOUT' })
                });
                window.location.href = 'index.php';
            } catch (err) {
                window.location.href = 'index.php';
            }
        }

        function switchTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.pane').forEach(el => el.classList.add('hidden'));
            document.getElementById('pane-' + tab).classList.remove('hidden');

            // Nav active states
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.className = 'nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition text-stone-400 hover:bg-stone-800 hover:text-white';
            });
            const activeBtn = document.getElementById('nav-' + tab);
            if (activeBtn) {
                activeBtn.className = 'nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition bg-amber-600 text-white';
            }

            // Update top bar titles
            const titles = {
                'overview': ['ခြုံငုံသုံးသပ်ချက် (Overview)', 'အရောင်းအချက်အလက်များနှင့် လုပ်ငန်းအကျဉ်းချုပ်'],
                'orders': ['အော်ဒါများ စီမံခန့်ခွဲမှု (Orders)', 'မှာယူထားသော အော်ဒါများကို အတည်ပြုခြင်းနှင့် ပို့ဆောင်ခြင်း'],
                'products': ['ကုန်ပစ္စည်းများ စီမံခြင်း (Products)', 'ပစ္စည်းအသစ်ထည့်သွင်းခြင်း၊ ဈေးနှုန်းနှင့် Stock ပြင်ဆင်ခြင်း'],
                'categories': ['အမျိုးအစားများ စီမံခန့်ခွဲမှု (Categories)', 'အိတ်အမျိုးအစား အသစ်ထည့်သွင်းခြင်းနှင့် စီမံခြင်း'],
                'announcements': ['သတင်းနှင့် ပရိုးမိုးရှင်းများ (News)', 'စတိုးဆိုင် ကြေညာချက်များနှင့် Discount ကူပွန်များ'],
                'users': ['အသုံးပြုသူ စာရင်း (Users)', 'အကောင့်ဖွင့်ထားသူများနှင့် Role သတ်မှတ်ခြင်း'],
                'coupons': ['လျှော့ဈေး ကူပွန်များ (Coupons)', 'ဝယ်ယူသူများ အသုံးပြုနိုင်သော Discount Coupon ကုဒ်များ'],
                'payments': ['ငွေပေးချေမှု နည်းလမ်းများ (Payment Methods)', 'ဝယ်ယူသူများ အသုံးပြုနိုင်သော ဘဏ်အကောင့်၊ KPay နှင့် ငွေချေနည်းလမ်းများ စီမံခြင်း'],
                'theme': ['ဆိုင် Theme ဒီဇိုင်း (Store Theme)', 'စတိုးဆိုင်၏ အရောင်နှင့် Layout ဒီဇိုင်း ပြောင်းလဲခြင်း'],
                'settings': ['ဆိုင် ဆက်တင်များနှင့် အသိပေးစနစ် (Settings)', 'ဆိုင်အချက်အလက်များနှင့် Telegram အော်ဒါအသိပေးစနစ် ပြင်ဆင်ခြင်း'],
                'reports': ['အရောင်း စာရင်းချုပ် (Sales Reports)', 'အပတ်စဉ်နှင့် လစဉ် အရောင်းစာရင်း၊ ဘဏ္ဍာရေး အစီရင်ခံစာနှင့် စာရင်းချုပ် ထုတ်ယူခြင်း'],
                'chatbot': ['AI Chatbot ဆက်တင်များ (AI Assistant)', 'ဖောက်သည်များနှင့် ၂၄ နာရီ စကားပြောဆိုမည့် AI Provider (Gemini / OpenAI / Groq) နှင့် Prompt များကို စီမံခြင်း']
            };
            if (titles[tab]) {
                document.getElementById('pageTitle').innerText = titles[tab][0];
                document.getElementById('pageSubtitle').innerText = titles[tab][1];
            }

            lucide.createIcons();
        }

        function renderAll() {
            updateStats();
            renderOrders();
            renderReports();
            renderProducts();
            renderCategories();
            renderAnnouncements();
            renderUsers();
            renderTheme();
            renderCoupons();
            renderPaymentMethods();
            renderSettings();
            renderChatbot();
            populateProductCategoryDropdown();
            lucide.createIcons();
        }

        function updateStats() {
            const orders = shopData.orders || [];
            const rev = orders.reduce((sum, o) => sum + (o.status !== 'Cancelled' ? o.total : 0), 0);
            document.getElementById('statRevenue').innerText = rev.toLocaleString() + ' Ks';
            document.getElementById('statOrders').innerText = orders.length;
            document.getElementById('statProducts').innerText = (shopData.products || []).length;
            document.getElementById('statUsers').innerText = shopData.usersCount || 0;

            document.getElementById('badgeOrdersCount').innerText = orders.length;
            document.getElementById('badgeProductsCount').innerText = (shopData.products || []).length;
            if (document.getElementById('badgeCategoriesCount')) {
                document.getElementById('badgeCategoriesCount').innerText = (shopData.categories || []).length;
            }
            if (document.getElementById('badgeCouponsCount')) {
                document.getElementById('badgeCouponsCount').innerText = (shopData.coupons || []).length;
            }

            // Weekly & Monthly calculations for Overview Cards
            const now = new Date();
            
            // This week: Monday to Sunday
            const currMonday = new Date(now);
            const currDay = currMonday.getDay();
            const dayDiff = currMonday.getDate() - currDay + (currDay === 0 ? -6 : 1);
            currMonday.setDate(dayDiff);
            currMonday.setHours(0, 0, 0, 0);

            const currSunday = new Date(currMonday);
            currSunday.setDate(currMonday.getDate() + 6);
            currSunday.setHours(23, 59, 59, 999);

            const weekOrders = orders.filter(o => {
                const od = new Date(o.createdAt);
                return od >= currMonday && od <= currSunday;
            });
            const weekRev = weekOrders.reduce((sum, o) => sum + (o.status !== 'Cancelled' ? Number(o.total || 0) : 0), 0);
            const weekDelivered = weekOrders.filter(o => o.status === 'Delivered' || o.status === 'Completed').length;

            const wkRangeEl = document.getElementById('overviewWeekRange');
            if (wkRangeEl) {
                wkRangeEl.innerText = `${currMonday.getDate()}/${currMonday.getMonth()+1} မှ ${currSunday.getDate()}/${currSunday.getMonth()+1} အထိ`;
            }
            if (document.getElementById('overviewWeekRev')) {
                document.getElementById('overviewWeekRev').innerText = weekRev.toLocaleString() + ' Ks';
                document.getElementById('overviewWeekOrders').innerText = weekOrders.length;
                document.getElementById('overviewWeekDelivered').innerText = weekDelivered;
            }

            // This month: 1st of month to end of month
            const monthStart = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
            const monthEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);

            const monthOrders = orders.filter(o => {
                const od = new Date(o.createdAt);
                return od >= monthStart && od <= monthEnd;
            });
            const monthRev = monthOrders.reduce((sum, o) => sum + (o.status !== 'Cancelled' ? Number(o.total || 0) : 0), 0);
            const monthDelivered = monthOrders.filter(o => o.status === 'Delivered' || o.status === 'Completed').length;

            const monthsMm = ['ဇန်နဝါရီ', 'ဖေဖော်ဝါရီ', 'မတ်', 'ဧပြီ', 'မေ', 'ဇွန်', 'ဇူလိုင်', 'ဩဂုတ်', 'စက်တင်ဘာ', 'အောက်တိုဘာ', 'နိုဝင်ဘာ', 'ဒီဇင်ဘာ'];
            const moRangeEl = document.getElementById('overviewMonthRange');
            if (moRangeEl) {
                moRangeEl.innerText = `${now.getFullYear()} ${monthsMm[now.getMonth()]} (၁ ရက် မှ ${monthEnd.getDate()} ရက် အထိ)`;
            }
            if (document.getElementById('overviewMonthRev')) {
                document.getElementById('overviewMonthRev').innerText = monthRev.toLocaleString() + ' Ks';
                document.getElementById('overviewMonthOrders').innerText = monthOrders.length;
                document.getElementById('overviewMonthDelivered').innerText = monthDelivered;
            }

            // Overview recent 5 orders
            const recentContainer = document.getElementById('overviewRecentOrders');
            recentContainer.innerHTML = '';
            const recent = orders.slice(0, 5);
            if (recent.length === 0) {
                recentContainer.innerHTML = `<p class="text-stone-400 text-xs py-4 text-center">အော်ဒါ မရှိသေးပါ။</p>`;
            } else {
                recent.forEach(o => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center justify-between p-3.5 rounded-2xl bg-stone-50 border border-stone-100 text-xs';
                    div.innerHTML = `
                        <div>
                            <span class="font-mono font-bold text-stone-900">${escapeHtml(o.id)}</span>
                            <span class="text-stone-500 ml-2">ဝယ်သူ: <strong>${escapeHtml(o.customerName)}</strong> (${escapeHtml(o.phone)})</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-stone-900">${Number(o.total).toLocaleString()} Ks</span>
                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold ${getStatusBadgeClass(o.status)}">${escapeHtml(o.status)}</span>
                        </div>
                    `;
                    recentContainer.appendChild(div);
                });
            }
        }

        function getStatusBadgeClass(status) {
            switch(status) {
                case 'Pending': return 'bg-amber-100 text-amber-800';
                case 'Confirmed': return 'bg-blue-100 text-blue-800';
                case 'Shipped': return 'bg-purple-100 text-purple-800';
                case 'Delivered': return 'bg-teal-100 text-teal-800';
                case 'Completed': return 'bg-emerald-100 text-emerald-800';
                case 'Cancelled': return 'bg-red-100 text-red-800';
                default: return 'bg-stone-100 text-stone-700';
            }
        }

        function filterOrders(filter) {
            currentOrderFilter = filter;
            document.querySelectorAll('.order-filter-btn').forEach(btn => {
                btn.className = 'order-filter-btn px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900 flex items-center gap-1.5';
            });
            const activeFilterBtn = document.getElementById('filter-' + filter);
            if (activeFilterBtn) {
                activeFilterBtn.className = 'order-filter-btn px-3 py-1.5 rounded-xl bg-white text-stone-900 shadow-xs font-bold flex items-center gap-1.5';
            }
            renderOrders();
        }

        function handleOrderSearch(query) {
            orderSearchQuery = query.toLowerCase().trim();
            renderOrders();
        }

        function renderOrders() {
            const container = document.getElementById('adminOrdersContainer');
            container.innerHTML = '';

            const allOrders = shopData.orders || [];

            // Update Status Filter Badges
            const bAll = document.getElementById('badgeOrderAll');
            if (bAll) bAll.innerText = allOrders.length;
            const bPending = document.getElementById('badgeOrderPending');
            if (bPending) bPending.innerText = allOrders.filter(o => o.status === 'Pending').length;
            const bConfirmed = document.getElementById('badgeOrderConfirmed');
            if (bConfirmed) bConfirmed.innerText = allOrders.filter(o => o.status === 'Confirmed').length;
            const bShipped = document.getElementById('badgeOrderShipped');
            if (bShipped) bShipped.innerText = allOrders.filter(o => o.status === 'Shipped').length;
            const bDelivered = document.getElementById('badgeOrderDelivered');
            if (bDelivered) bDelivered.innerText = allOrders.filter(o => o.status === 'Delivered').length;
            const bCompleted = document.getElementById('badgeOrderCompleted');
            if (bCompleted) bCompleted.innerText = allOrders.filter(o => o.status === 'Completed' || o.status === 'Delivered').length;
            const bCancelled = document.getElementById('badgeOrderCancelled');
            if (bCancelled) bCancelled.innerText = allOrders.filter(o => o.status === 'Cancelled').length;

            let filtered = allOrders;
            if (currentOrderFilter === 'Completed') {
                filtered = filtered.filter(o => o.status === 'Completed' || o.status === 'Delivered');
            } else if (currentOrderFilter !== 'ALL') {
                filtered = filtered.filter(o => o.status === currentOrderFilter);
            }
            if (orderSearchQuery) {
                filtered = filtered.filter(o =>
                    (o.id && o.id.toLowerCase().includes(orderSearchQuery)) ||
                    (o.phone && o.phone.toLowerCase().includes(orderSearchQuery)) ||
                    (o.customerName && o.customerName.toLowerCase().includes(orderSearchQuery))
                );
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="bg-white rounded-3xl p-8 text-center border border-stone-200">
                        <i data-lucide="inbox" class="w-10 h-10 text-stone-300 mx-auto mb-2"></i>
                        <p class="text-stone-500 text-xs">ကိုက်ညီသော အော်ဒါ မတွေ့ရှိပါ။</p>
                    </div>
                `;
                lucide.createIcons();
                return;
            }

            filtered.forEach(o => {
                const card = document.createElement('div');
                const isCompleted = (o.status === 'Completed' || o.status === 'Delivered');
                card.className = `bg-white p-5 rounded-3xl border ${isCompleted ? 'border-emerald-200 bg-emerald-50/10' : 'border-stone-200'} shadow-sm text-xs space-y-3`;
                const itemsText = (o.items || []).map(i => `${escapeHtml(i.nameMm || i.nameEn)} (${escapeHtml(i.selectedColor || 'Default')}) x ${Number(i.quantity) || 1}`).join(' • ');
                const refText = o.transactionRef ? `<span class="bg-amber-100 text-amber-900 px-2 py-0.5 rounded font-mono font-bold">Ref: ${escapeHtml(o.transactionRef)}</span>` : '';
                const couponBadge = o.couponCode ? `<span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-bold">🏷️ Coupon: ${escapeHtml(o.couponCode)} (-${Number(o.discount||0).toLocaleString()} Ks)</span>` : '';
                const slipBtn = o.paymentSlip ? `<button onclick="viewPaymentSlip('${escapeHtml(o.id)}')" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl font-bold flex items-center gap-1 transition"><i data-lucide="image" class="w-3.5 h-3.5"></i> 🖼️ ပြေစာပုံ ကြည့်မည်</button>` : '';
                const invoiceBtn = `<button onclick="openInvoiceModal('${escapeHtml(o.id)}')" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl font-bold flex items-center gap-1 transition"><i data-lucide="printer" class="w-3.5 h-3.5"></i> 🖨️ ပြေစာ ထုတ်မည်</button>`;
                const completeBtn = (!isCompleted && o.status !== 'Cancelled')
                    ? `<button onclick="handleUpdateOrderStatus('${escapeHtml(o.id)}', 'Completed')" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-xl font-bold flex items-center gap-1 transition shadow-xs"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i> ✓ Complete Order</button>`
                    : (isCompleted ? `<span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold flex items-center gap-1"><i data-lucide="badge-check" class="w-3.5 h-3.5 text-emerald-600"></i> ပြီးစီးပြီး (Completed)</span>` : '');

                card.innerHTML = `
                    <div class="flex items-center justify-between flex-wrap gap-2 border-b border-stone-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-extrabold text-stone-900 text-sm">${escapeHtml(o.id)}</span>
                            <span class="text-stone-400">|</span>
                            <span class="text-stone-500 font-semibold">${new Date(o.createdAt).toLocaleString()}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${getStatusBadgeClass(o.status)}">${escapeHtml(o.status)}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-[11px] font-bold text-stone-500">Status ပြောင်းရန်:</label>
                            <select onchange="handleUpdateOrderStatus('${escapeHtml(o.id)}', this.value)" class="bg-stone-50 border border-stone-300 rounded-xl px-3 py-1.5 font-bold text-xs focus:ring-2 focus:ring-amber-500">
                                <option value="Pending" ${o.status === 'Pending' ? 'selected' : ''}>Pending (စစ်ဆေးဆဲ)</option>
                                <option value="Confirmed" ${o.status === 'Confirmed' ? 'selected' : ''}>Confirmed (အတည်ပြုပြီး)</option>
                                <option value="Shipped" ${o.status === 'Shipped' ? 'selected' : ''}>Shipped (ဂိတ်တင်ပြီး)</option>
                                <option value="Delivered" ${o.status === 'Delivered' ? 'selected' : ''}>Delivered (ရောက်ရှိပြီး)</option>
                                <option value="Completed" ${o.status === 'Completed' ? 'selected' : ''}>Completed (ပြီးစီးပြီး)</option>
                                <option value="Cancelled" ${o.status === 'Cancelled' ? 'selected' : ''}>Cancelled (ပယ်ဖျက်)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-stone-700">
                        <div>
                            <p><strong>ဝယ်ယူသူ:</strong> ${escapeHtml(o.customerName)} (<a href="tel:${escapeHtml(o.phone)}" class="text-amber-700 font-bold underline">${escapeHtml(o.phone)}</a>)</p>
                            <p class="mt-1"><strong>လိပ်စာ:</strong> ${escapeHtml(o.address)}, ${escapeHtml(o.city)}</p>
                            ${o.note ? `<p class="mt-1 text-stone-500 italic">မှတ်ချက်: ${escapeHtml(o.note)}</p>` : ''}
                        </div>
                        <div class="md:text-right">
                            <p><strong>ငွေပေးချေမှု:</strong> <span class="font-bold text-stone-900">${escapeHtml(o.paymentMethod)}</span> ${refText}</p>
                            <p class="mt-1"><strong>စုစုပေါင်း ကျသင့်ငွေ:</strong> <span class="text-base font-black text-amber-700">${Number(o.total || 0).toLocaleString()} Ks</span></p>
                        </div>
                    </div>

                    <div class="bg-stone-50 p-3 rounded-2xl border border-stone-100">
                        <p class="font-bold text-stone-800 mb-1">မှာယူထားသော ပစ္စည်းများ:</p>
                        <p class="text-stone-600">${itemsText}</p>
                    </div>

                    <div class="flex items-center justify-between flex-wrap gap-2 pt-1 border-t border-stone-100">
                        <div class="flex items-center gap-2">
                            ${couponBadge}
                        </div>
                        <div class="flex items-center gap-2">
                            ${completeBtn}
                            ${slipBtn}
                            ${invoiceBtn}
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
            lucide.createIcons();
        }

        async function handleUpdateOrderStatus(orderId, status) {
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'UPDATE_ORDER_STATUS', orderId, status })
                });
                loadData();
            } catch (err) {
                alert('အော်ဒါ အခြေအနေ ပြင်ဆင်မှု မအောင်မြင်ပါ: ' + err);
            }
        }

        // ===================================================================
        // PRODUCTS MANAGEMENT
        // ===================================================================
        function populateProductCategoryDropdown(selectedVal = '') {
            const select = document.getElementById('pFormCategory');
            if (!select) return;
            const cats = (shopData.categories || []).filter(c => c.id !== 'All');
            select.innerHTML = cats.map(c => `
                <option value="${escapeHtml(c.id)}" ${c.id === selectedVal ? 'selected' : ''}>
                    ${escapeHtml(c.labelEn)} (${escapeHtml(c.labelMm || c.id)})
                </option>
            `).join('');
        }

        function updateProductImagePreview(url) {
            const previewBox = document.getElementById('pFormImagePreviewBox');
            const previewImg = document.getElementById('pFormImagePreview');
            if (url && url.trim()) {
                previewImg.src = url.trim();
                previewBox.classList.remove('hidden');
            } else {
                previewBox.classList.add('hidden');
            }
        }

        async function handleProductImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('ပုံအရွယ်အစားသည် 5MB ထက် မကျော်ရပါ');
                return;
            }
            const reader = new FileReader();
            reader.onload = async function(e) {
                const base64Data = e.target.result;
                try {
                    const res = await fetch('api/shop.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'UPLOAD_IMAGE', image_data: base64Data })
                    });
                    const data = await res.json();
                    if (data.ok && data.url) {
                        document.getElementById('pFormImage').value = data.url;
                        updateProductImagePreview(data.url);
                    } else {
                        alert('Upload Error: ' + (data.error || 'Server error'));
                    }
                } catch (err) {
                    alert('Upload မအောင်မြင်ပါ: ' + err);
                }
            };
            reader.readAsDataURL(file);
        }

        function openProductForm() {
            populateProductCategoryDropdown();
            document.getElementById('pFormId').value = '';
            document.getElementById('pFormNameEn').value = '';
            document.getElementById('pFormNameMm').value = '';
            document.getElementById('pFormPrice').value = '';
            document.getElementById('pFormWholesale').value = '';
            document.getElementById('pFormOriginalPrice').value = '';
            document.getElementById('pFormStock').value = '20';
            document.getElementById('pFormBadge').value = '';
            document.getElementById('pFormImage').value = '';
            updateProductImagePreview('');
            document.getElementById('pFormDescMm').value = '';
            document.getElementById('productFormTitle').innerText = 'ပစ္စည်းအသစ် ထည့်သွင်းခြင်း';
            document.getElementById('productFormCard').classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function closeProductForm() {
            document.getElementById('productFormCard').classList.add('hidden');
        }

        function editProduct(productId) {
            const p = (shopData.products || []).find(item => item.id === productId);
            if (!p) return;
            populateProductCategoryDropdown(p.category);
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
            updateProductImagePreview(p.image);
            document.getElementById('pFormDescMm').value = p.descriptionMm;
            document.getElementById('productFormTitle').innerText = 'ပစ္စည်း အချက်အလက် ပြင်ဆင်ခြင်း';
            document.getElementById('productFormCard').classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function handleSaveProduct(e) {
            e.preventDefault();
            const id = document.getElementById('pFormId').value;
            const selectedCat = document.getElementById('pFormCategory').value;
            const catObj = (shopData.categories || []).find(c => c.id === selectedCat);
            const categoryMm = catObj ? (catObj.labelMm || catObj.labelEn) : 'အိတ်';

            const product = {
                id: id || ('pp-bag-' + Date.now()),
                nameEn: document.getElementById('pFormNameEn').value.trim(),
                nameMm: document.getElementById('pFormNameMm').value.trim(),
                category: selectedCat,
                categoryMm: categoryMm,
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
                closeProductForm();
                loadData();
            } catch (err) {
                alert('သိမ်းဆည်းမှု မအောင်မြင်ပါ: ' + err);
            }
        }

        async function deleteProduct(productId) {
            if (!confirm('ဤကုန်ပစ္စည်းအား စာရင်းမှ ဖျက်ပစ်ရန် သေချာပါသလား?')) return;
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'DELETE_PRODUCT', productId })
                });
                loadData();
            } catch (err) {
                alert('ဖျက်ပစ်ရန် မအောင်မြင်ပါ: ' + err);
            }
        }

        let adminProductSearchQuery = '';
        let adminSelectedCategory = 'ALL';

        function handleAdminProductFilter() {
            const searchInput = document.getElementById('adminProductSearch');
            adminProductSearchQuery = (searchInput ? searchInput.value : '').trim().toLowerCase();
            const catSelect = document.getElementById('adminProductCategoryFilter');
            adminSelectedCategory = catSelect ? catSelect.value : 'ALL';

            const clearBtn = document.getElementById('clearAdminProductSearch');
            if (clearBtn) {
                if (adminProductSearchQuery) clearBtn.classList.remove('hidden');
                else clearBtn.classList.add('hidden');
            }

            renderCategoryPills();
            renderProductsList();
        }

        function clearAdminProductSearchInput() {
            const searchInput = document.getElementById('adminProductSearch');
            if (searchInput) {
                searchInput.value = '';
                handleAdminProductFilter();
            }
        }

        function selectAdminCategory(catId) {
            const catSelect = document.getElementById('adminProductCategoryFilter');
            if (catSelect) {
                catSelect.value = catId;
                handleAdminProductFilter();
            }
        }

        function clearAllProductFilters() {
            const searchInput = document.getElementById('adminProductSearch');
            if (searchInput) searchInput.value = '';
            const catSelect = document.getElementById('adminProductCategoryFilter');
            if (catSelect) catSelect.value = 'ALL';
            handleAdminProductFilter();
        }

        function renderCategoryPills() {
            const pillsContainer = document.getElementById('adminCategoryPills');
            if (!pillsContainer) return;
            const cats = shopData.categories || [];
            let html = `
                <button type="button" onclick="selectAdminCategory('ALL')" class="px-3 py-1 rounded-full text-xs font-bold transition whitespace-nowrap ${adminSelectedCategory === 'ALL' ? 'bg-amber-600 text-white shadow-xs' : 'bg-stone-100 hover:bg-stone-200 text-stone-600'}">
                    အားလုံး (${(shopData.products || []).length})
                </button>
            `;
            cats.forEach(c => {
                const count = (shopData.products || []).filter(p => p.category === c.id || p.category === c.labelEn || p.categoryMm === c.labelMm).length;
                const isSelected = (adminSelectedCategory === c.id || adminSelectedCategory === c.labelEn);
                html += `
                    <button type="button" onclick="selectAdminCategory('${escapeHtml(c.id)}')" class="px-3 py-1 rounded-full text-xs font-bold transition whitespace-nowrap ${isSelected ? 'bg-amber-600 text-white shadow-xs' : 'bg-stone-100 hover:bg-stone-200 text-stone-600'}">
                        ${escapeHtml(c.labelMm || c.labelEn)} (${count})
                    </button>
                `;
            });
            pillsContainer.innerHTML = html;
        }

        function renderProducts() {
            // Update Category Filter Dropdown options
            const catSelect = document.getElementById('adminProductCategoryFilter');
            if (catSelect) {
                const currentVal = catSelect.value || 'ALL';
                const cats = shopData.categories || [];
                let optionsHtml = `<option value="ALL">အမျိုးအစား အားလုံး (All Categories)</option>`;
                cats.forEach(c => {
                    optionsHtml += `<option value="${escapeHtml(c.id)}">${escapeHtml(c.labelMm ? (c.labelMm + ' - ' + c.labelEn) : c.labelEn)}</option>`;
                });
                catSelect.innerHTML = optionsHtml;
                catSelect.value = currentVal;
            }

            renderCategoryPills();
            renderProductsList();
        }

        function renderProductsList() {
            const container = document.getElementById('adminProductsContainer');
            if (!container) return;
            container.innerHTML = '';

            const allProducts = shopData.products || [];
            const filtered = allProducts.filter(p => {
                // Category match
                if (adminSelectedCategory !== 'ALL') {
                    const matchCat = (p.category === adminSelectedCategory) ||
                                     (p.category && p.category.toLowerCase() === adminSelectedCategory.toLowerCase()) ||
                                     (p.categoryMm && p.categoryMm === adminSelectedCategory);
                    if (!matchCat) return false;
                }
                // Search match
                if (adminProductSearchQuery) {
                    const q = adminProductSearchQuery;
                    const matchSearch = (p.nameEn && p.nameEn.toLowerCase().includes(q)) ||
                                        (p.nameMm && p.nameMm.toLowerCase().includes(q)) ||
                                        (p.category && p.category.toLowerCase().includes(q)) ||
                                        (p.categoryMm && p.categoryMm.toLowerCase().includes(q)) ||
                                        (p.id && p.id.toLowerCase().includes(q)) ||
                                        (p.material && p.material.toLowerCase().includes(q)) ||
                                        (p.badge && p.badge.toLowerCase().includes(q)) ||
                                        (p.price && p.price.toString().includes(q));
                    if (!matchSearch) return false;
                }
                return true;
            });

            // Update Count display
            const countDisplay = document.getElementById('adminProductsCountDisplay');
            if (countDisplay) {
                countDisplay.innerHTML = `ပြသထားသော ကုန်ပစ္စည်း: <strong class="text-stone-900 font-extrabold">${filtered.length}</strong> / စုစုပေါင်း: <strong>${allProducts.length}</strong> မျိုး`;
            }
            const filterState = document.getElementById('adminProductsFilterState');
            if (filterState) {
                if (adminSelectedCategory !== 'ALL' || adminProductSearchQuery) {
                    filterState.innerHTML = `<button type="button" onclick="clearAllProductFilters()" class="text-amber-700 hover:text-amber-800 underline font-bold flex items-center gap-1">Filter ဖျက်မည် (Reset)</button>`;
                } else {
                    filterState.innerHTML = '';
                }
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="bg-white p-12 rounded-3xl border border-stone-200 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                            <i data-lucide="search-x" class="w-6 h-6"></i>
                        </div>
                        <h5 class="font-extrabold text-stone-800 text-sm">ရှာဖွေမှုနှင့် ကိုက်ညီသော ကုန်ပစ္စည်း မရှိပါ</h5>
                        <p class="text-xs text-stone-500">ရှာဖွေသည့် စာလုံး သို့မဟုတ် Category ကို ပြောင်းလဲ စစ်ဆေးကြည့်ပါ</p>
                        <button type="button" onclick="clearAllProductFilters()" class="px-4 py-2 bg-stone-900 text-white rounded-xl text-xs font-bold hover:bg-stone-800 transition">
                            Filter အားလုံး ဖျက်မည်
                        </button>
                    </div>
                `;
                lucide.createIcons();
                return;
            }

            filtered.forEach(p => {
                const div = document.createElement('div');
                div.className = 'bg-white p-4 rounded-3xl border border-stone-200 shadow-sm flex items-center gap-4 text-xs hover:border-amber-200 transition';
                div.innerHTML = `
                    <img src="${escapeHtml(p.image)}" class="w-14 h-14 rounded-2xl object-cover border border-stone-200 flex-shrink-0" onerror="this.src='https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=200'">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h5 class="font-extrabold text-stone-900 text-sm truncate">${escapeHtml(p.nameEn)}</h5>
                            ${p.badge ? `<span class="bg-amber-100 text-amber-900 font-bold px-2 py-0.5 rounded text-[10px]">${escapeHtml(p.badge)}</span>` : ''}
                        </div>
                        <p class="text-stone-500 mt-0.5 truncate">${escapeHtml(p.nameMm)}</p>
                        <div class="flex flex-wrap items-center gap-3 mt-1.5 text-stone-600 font-semibold">
                            <span class="inline-flex items-center gap-1">
                                <span class="text-stone-400">Category:</span>
                                <span class="bg-stone-100 text-stone-800 px-2 py-0.5 rounded text-[10px] font-bold">${escapeHtml(p.category)}</span>
                            </span>
                            <span>လက်လီ: <strong class="text-amber-700">${p.price.toLocaleString()} Ks</strong></span>
                            <span>လက်ကား: <strong>${p.wholesalePrice ? p.wholesalePrice.toLocaleString() + ' Ks' : '-'}</strong></span>
                            <span>Stock: <strong class="${p.stock < 10 ? 'text-red-600 font-black' : 'text-emerald-700'}">${p.stock}</strong></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button onclick="editProduct('${escapeHtml(p.id)}')" class="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 border border-stone-300 rounded-xl font-bold transition">
                            Edit
                        </button>
                        <button onclick="deleteProduct('${escapeHtml(p.id)}')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-xl font-bold transition">
                            Delete
                        </button>
                    </div>
                `;
                container.appendChild(div);
            });
            lucide.createIcons();
        }

        // ===================================================================
        // CATEGORIES MANAGEMENT
        // ===================================================================
        function openCategoryForm() {
            document.getElementById('catFormId').value = '';
            document.getElementById('catFormLabelEn').value = '';
            document.getElementById('catFormLabelMm').value = '';
            document.getElementById('categoryFormCard').classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function closeCategoryForm() {
            document.getElementById('categoryFormCard').classList.add('hidden');
        }

        async function handleSaveCategory(e) {
            e.preventDefault();
            const id = document.getElementById('catFormId').value.trim();
            const labelEn = document.getElementById('catFormLabelEn').value.trim();
            const labelMm = document.getElementById('catFormLabelMm').value.trim() || labelEn;

            if (!id || !labelEn) {
                alert('Category Code/ID နှင့် အင်္ဂလိပ်အမည် ဖြည့်စွက်ပေးပါ။');
                return;
            }

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'ADD_CATEGORY',
                        category: { id, labelEn, labelMm }
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    closeCategoryForm();
                    loadData();
                } else {
                    alert(data.error || 'အမျိုးအစား ထည့်သွင်းခြင်း မအောင်မြင်ပါ။');
                }
            } catch (err) {
                alert('ချိတ်ဆက်မှု မအောင်မြင်ပါ: ' + err);
            }
        }

        async function deleteCategory(categoryId) {
            if (categoryId === 'All') {
                alert('All Bags အမျိုးအစားအား ဖျက်၍ မရပါ။');
                return;
            }
            const productCount = (shopData.products || []).filter(p => p.category === categoryId).length;
            let msg = `"${categoryId}" အမျိုးအစားအား ဖျက်ရန် သေချာပါသလား?`;
            if (productCount > 0) {
                msg += `\n(သတိပြုရန်: ဤအမျိုးအစားအောက်တွင် ပစ္စည်း ${productCount} ခု ရှိနေပါသည်)`;
            }
            if (!confirm(msg)) return;

            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'DELETE_CATEGORY', categoryId })
                });
                loadData();
            } catch (err) {
                alert('ဖျက်ပစ်မှု မအောင်မြင်ပါ: ' + err);
            }
        }

        function renderCategories() {
            const container = document.getElementById('adminCategoriesContainer');
            if (!container) return;
            container.innerHTML = '';

            (shopData.categories || []).forEach(c => {
                const productCount = (shopData.products || []).filter(p => p.category === c.id).length;
                const card = document.createElement('div');
                card.className = 'bg-white p-5 rounded-3xl border border-stone-200 shadow-sm flex flex-col justify-between text-xs space-y-4';

                card.innerHTML = `
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-lg text-xs">${escapeHtml(c.id)}</span>
                            <span class="text-[11px] font-bold text-stone-500 bg-stone-100 px-2 py-0.5 rounded-full">${productCount} items</span>
                        </div>
                        <div>
                            <h5 class="font-black text-stone-900 text-sm">${escapeHtml(c.labelEn)}</h5>
                            <p class="text-stone-500 font-medium text-xs mt-0.5">${escapeHtml(c.labelMm || c.labelEn)}</p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-stone-100 flex items-center justify-between">
                        <span class="text-[11px] text-stone-400">Sort Order: #${c.sortOrder || 0}</span>
                        ${c.id !== 'All' ? `
                            <button onclick="deleteCategory('${escapeHtml(c.id)}')" class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-xl font-bold transition flex items-center gap-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                            </button>
                        ` : '<span class="text-stone-400 font-bold text-[11px]">Default Category</span>'}
                    </div>
                `;
                container.appendChild(card);
            });
            lucide.createIcons();
        }

        // ===================================================================
        // ANNOUNCEMENTS MANAGEMENT
        // ===================================================================
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
                document.getElementById('annImage').value = '';
                loadData();
            } catch (err) {
                alert('ကြေညာချက် တင်သွင်းမှု မအောင်မြင်ပါ: ' + err);
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
                loadData();
            } catch (err) {
                alert('ဖျက်ပစ်မှု မအောင်မြင်ပါ: ' + err);
            }
        }

        function renderAnnouncements() {
            const container = document.getElementById('adminAnnouncementsContainer');
            container.innerHTML = '';
            (shopData.announcements || []).forEach(a => {
                const div = document.createElement('div');
                div.className = 'bg-white p-4 rounded-3xl border border-stone-200 shadow-sm flex items-center justify-between text-xs';
                div.innerHTML = `
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-stone-900 text-sm">${escapeHtml(a.title)}</span>
                            <span class="bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded text-[10px]">${escapeHtml(a.tag)}</span>
                        </div>
                        <p class="text-stone-600 line-clamp-1">${escapeHtml(a.content)}</p>
                        <p class="text-[11px] text-stone-400">တင်သည့်ရက်: ${new Date(a.createdAt).toLocaleDateString()} • တင်သူ: ${escapeHtml(a.authorName)}</p>
                    </div>
                    <button onclick="deleteAnnouncement('${escapeHtml(a.id)}')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-xl font-bold transition">
                        Delete
                    </button>
                `;
                container.appendChild(div);
            });
        }

        // ===================================================================
        // USERS MANAGEMENT
        // ===================================================================
        function renderUsers() {
            const container = document.getElementById('adminUsersContainer');
            container.innerHTML = '';
            (shopData.users || []).forEach(u => {
                const div = document.createElement('div');
                div.className = 'bg-white p-4 rounded-3xl border border-stone-200 shadow-sm flex items-center justify-between text-xs';
                div.innerHTML = `
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-stone-900 text-sm">${escapeHtml(u.name)}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold ${u.role === 'admin' ? 'bg-amber-100 text-amber-900' : 'bg-stone-100 text-stone-600'}">[${escapeHtml(u.role)}]</span>
                        </div>
                        <p class="text-stone-500 mt-0.5">${escapeHtml(u.email)} • ${escapeHtml(u.phone)} • ${escapeHtml(u.city)}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        ${u.id !== shopData.currentUser.id ? `
                            <button onclick="updateUserRole('${escapeHtml(u.id)}', '${u.role === 'admin' ? 'customer' : 'admin'}')" class="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 border border-stone-300 rounded-xl font-bold transition">
                                ${u.role === 'admin' ? 'Demote to Customer' : 'Promote to Admin'}
                            </button>
                            <button onclick="deleteUser('${escapeHtml(u.id)}')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-xl font-bold transition">
                                Delete
                            </button>
                        ` : '<span class="text-stone-400 font-bold px-3 py-1">(Your Account)</span>'}
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
                loadData();
            } catch (err) {
                alert('Role ပြင်ဆင်မှု မအောင်မြင်ပါ: ' + err);
            }
        }

        async function deleteUser(userId) {
            if (!confirm('ဤ User အကောင့်အား ဖျက်ရန် သေချာပါသလား?')) return;
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'DELETE_USER', userId })
                });
                loadData();
            } catch (err) {
                alert('User ဖျက်ပစ်မှု မအောင်မြင်ပါ: ' + err);
            }
        }

        // ===================================================================
        // THEME MANAGEMENT
        // ===================================================================
        function renderTheme() {
            const current = shopData.theme || 'luxury-gold';
            document.querySelectorAll('.theme-card').forEach(card => {
                card.classList.remove('border-amber-500', 'border-pink-500', 'border-emerald-500', 'shadow-md');
                card.classList.add('border-stone-200');
            });
            const active = document.getElementById('theme-' + current);
            if (active) {
                active.classList.remove('border-stone-200');
                active.classList.add('border-amber-500', 'shadow-md');
            }
        }

        async function setShopTheme(theme) {
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'UPDATE_THEME', theme })
                });
                shopData.theme = theme;
                renderTheme();
                alert('Shop theme ပြောင်းလဲပြီးပါပြီ။');
            } catch (err) {
                alert('Theme ပြောင်းလဲမှု မအောင်မြင်ပါ: ' + err);
            }
        }

        // ===================================================================
        // PAYMENT SLIP & INVOICE MODALS
        // ===================================================================
        function viewPaymentSlip(orderId) {
            const o = (shopData.orders || []).find(item => item.id === orderId);
            if (!o || !o.paymentSlip) {
                alert('ဤအော်ဒါအတွက် ငွေလွှဲပြေစာ မရှိပါ');
                return;
            }
            document.getElementById('slipModalOrderInfo').innerText = `Order: ${o.id} • ${o.customerName} (${o.phone})`;
            document.getElementById('slipModalImg').src = o.paymentSlip;
            document.getElementById('slipModalTxRef').innerText = `Ref: ${o.transactionRef || 'N/A'}`;
            document.getElementById('slipViewModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeSlipModal() {
            document.getElementById('slipViewModal').classList.add('hidden');
        }

        function openInvoiceModal(orderId) {
            const o = (shopData.orders || []).find(item => item.id === orderId);
            if (!o) return;

            const settings = shopData.settings || {};
            document.getElementById('invShopName').innerText = settings.shop_name_mm || 'ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး)';
            const phones = [settings.phone_1 || '09-963000502', settings.phone_2 || '09-750072752'].filter(Boolean).join(', ');
            document.getElementById('invShopPhones').innerText = phones;

            document.getElementById('invOrderId').innerText = `#${o.id}`;
            document.getElementById('invDate').innerText = `Date: ${new Date(o.createdAt).toLocaleString()}`;
            document.getElementById('invStatusBadge').innerText = o.status;
            document.getElementById('invStatusBadge').className = `px-2 py-0.5 rounded text-[11px] font-bold ${
                o.status === 'Confirmed' || o.status === 'Delivered' || o.status === 'Completed' ? 'bg-emerald-100 text-emerald-800' :
                (o.status === 'Cancelled' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800')
            }`;

            document.getElementById('invCustomerName').innerText = o.customerName || 'Customer';
            document.getElementById('invCustomerPhone').innerText = `ဖုန်း: ${o.phone || '-'}`;
            document.getElementById('invCustomerAddress').innerText = `လိပ်စာ: ${o.address || ''}, ${o.city || ''}`;

            document.getElementById('invPaymentMethod').innerText = `နည်းလမ်း: ${o.paymentMethod || 'COD'}`;
            document.getElementById('invTxRef').innerText = o.transactionRef ? `Ref No: ${o.transactionRef}` : '';
            document.getElementById('invNote').innerText = o.note ? `မှတ်ချက်: ${o.note}` : '';

            const table = document.getElementById('invItemsTable');
            table.innerHTML = '';
            (o.items || []).forEach((item, index) => {
                const tr = document.createElement('tr');
                const qty = Number(item.quantity) || 1;
                const price = Number(item.price) || 0;
                const total = qty * price;
                tr.innerHTML = `
                    <td class="py-2 text-stone-400">${index + 1}</td>
                    <td class="py-2 font-bold text-stone-800">${escapeHtml(item.nameMm || item.nameEn || 'Handbag')}</td>
                    <td class="py-2 text-stone-500">${escapeHtml(item.selectedColor || '-')}</td>
                    <td class="py-2 text-center font-bold">${qty}</td>
                    <td class="py-2 text-right">${price.toLocaleString()}</td>
                    <td class="py-2 text-right font-bold text-stone-900">${total.toLocaleString()}</td>
                `;
                table.appendChild(tr);
            });

            const subtotal = Number(o.subtotal || 0);
            const discount = Number(o.discount || 0);
            const fee = Number(o.deliveryFee || 0);
            const netTotal = Number(o.total || 0);

            document.getElementById('invSubtotal').innerText = subtotal.toLocaleString() + ' Ks';
            document.getElementById('invDiscount').innerText = '-' + discount.toLocaleString() + ' Ks';
            document.getElementById('invDiscountRow').style.display = discount > 0 ? 'flex' : 'none';
            document.getElementById('invDeliveryFee').innerText = fee === 0 ? 'Free (အခမဲ့)' : fee.toLocaleString() + ' Ks';
            document.getElementById('invTotal').innerText = netTotal.toLocaleString() + ' Ks';

            document.getElementById('invoiceModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeInvoiceModal() {
            document.getElementById('invoiceModal').classList.add('hidden');
        }

        // ===================================================================
        // COUPONS MANAGEMENT
        // ===================================================================
        function renderCoupons() {
            const tbody = document.getElementById('couponsTableBody');
            if (!tbody) return;
            tbody.innerHTML = '';
            const coupons = shopData.coupons || [];

            if (coupons.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="p-6 text-center text-stone-400">ကူပွန်များ မရှိသေးပါ။</td></tr>`;
                return;
            }

            coupons.forEach(c => {
                const tr = document.createElement('tr');
                const valDisplay = c.discountType === 'percent' ? `${c.discountValue}%` : `${Number(c.discountValue).toLocaleString()} Ks`;
                const limitDisplay = c.usageLimit > 0 ? `${c.usedCount} / ${c.usageLimit}` : `${c.usedCount} / Unlimited`;
                const statusBadge = (c.isActive == 1) ? `<span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-bold">Active</span>` : `<span class="bg-stone-100 text-stone-600 px-2 py-0.5 rounded">Disabled</span>`;

                tr.innerHTML = `
                    <td class="p-3.5 font-mono font-bold text-amber-700">${escapeHtml(c.code)}</td>
                    <td class="p-3.5 capitalize">${escapeHtml(c.discountType)}</td>
                    <td class="p-3.5 font-bold text-stone-900">${valDisplay}</td>
                    <td class="p-3.5">${Number(c.minOrderAmount || 0).toLocaleString()} Ks</td>
                    <td class="p-3.5">${limitDisplay}</td>
                    <td class="p-3.5">${statusBadge}</td>
                    <td class="p-3.5 text-right">
                        <button onclick="deleteCoupon('${escapeHtml(c.id)}')" class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg font-bold transition">ဖျက်မည်</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function openCouponModal() {
            document.getElementById('cpnCode').value = '';
            document.getElementById('cpnType').value = 'percent';
            document.getElementById('cpnValue').value = '';
            document.getElementById('cpnMinOrder').value = '0';
            document.getElementById('cpnLimit').value = '0';
            document.getElementById('couponModal').classList.remove('hidden');
        }

        function closeCouponModal() {
            document.getElementById('couponModal').classList.add('hidden');
        }

        async function handleSaveCoupon(e) {
            e.preventDefault();
            const coupon = {
                code: document.getElementById('cpnCode').value.trim().toUpperCase(),
                discountType: document.getElementById('cpnType').value,
                discountValue: parseInt(document.getElementById('cpnValue').value),
                minOrderAmount: parseInt(document.getElementById('cpnMinOrder').value) || 0,
                usageLimit: parseInt(document.getElementById('cpnLimit').value) || 0,
                isActive: 1
            };
            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'ADD_COUPON', coupon })
                });
                const data = await res.json();
                if (data.ok) {
                    closeCouponModal();
                    loadData();
                } else {
                    alert('Error: ' + (data.error || 'Failed to save coupon'));
                }
            } catch (err) {
                alert('ကူပွန်သိမ်းဆည်းမှု မအောင်မြင်ပါ: ' + err);
            }
        }

        async function deleteCoupon(couponId) {
            if (!confirm('ဤကူပွန်အား ဖျက်ပစ်ရန် သေချာပါသလား?')) return;
            try {
                await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'DELETE_COUPON', couponId })
                });
                loadData();
            } catch (err) {
                alert('ကူပွန်ဖျက်ပစ်မှု မအောင်မြင်ပါ: ' + err);
            }
        }

        // ===================================================================
        // PAYMENT METHODS MANAGEMENT (ငွေပေးချေမှု နည်းလမ်းများ စီမံခြင်း)
        // ===================================================================
        let selectedQrBase64 = null;

        function renderPaymentMethods() {
            const grid = document.getElementById('paymentMethodsGrid');
            if (!grid) return;
            grid.innerHTML = '';
            const methods = shopData.paymentMethods || [];

            // Update stats & badge
            const total = methods.length;
            const active = methods.filter(m => m.isActive).length;
            const transfer = methods.filter(m => m.type === 'transfer' || m.type === 'banking').length;
            const cod = methods.filter(m => m.type === 'cod').length;

            const badge = document.getElementById('badgePaymentsCount');
            if (badge) badge.innerText = total;

            const stTotal = document.getElementById('statPmTotal');
            if (stTotal) stTotal.innerText = total;
            const stActive = document.getElementById('statPmActive');
            if (stActive) stActive.innerText = active;
            const stTrans = document.getElementById('statPmTransfer');
            if (stTrans) stTrans.innerText = transfer;
            const stCod = document.getElementById('statPmCod');
            if (stCod) stCod.innerText = cod;

            if (methods.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full bg-white p-8 rounded-3xl border border-stone-200 text-center space-y-3">
                        <i data-lucide="credit-card" class="w-10 h-10 text-stone-300 mx-auto"></i>
                        <p class="text-sm font-bold text-stone-600">ငွေပေးချေမှု နည်းလမ်းများ မရှိသေးပါ။</p>
                        <button onclick="openPaymentModal()" class="px-4 py-2 bg-amber-600 text-white rounded-xl font-bold text-xs hover:bg-amber-700 transition">
                            + ပထမဆုံး နည်းလမ်း အသစ်ထည့်မည်
                        </button>
                    </div>
                `;
                lucide.createIcons();
                return;
            }

            methods.forEach(pm => {
                const card = document.createElement('div');
                const isActive = !!pm.isActive;
                const requiresSlip = !!pm.requiresSlip;

                // Type label & icon
                let typeBadge = '<span class="bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-full text-[10px] font-bold">ငွေလွှဲ / Wallet</span>';
                let typeIcon = 'smartphone';
                if (pm.type === 'banking') {
                    typeBadge = '<span class="bg-indigo-50 text-indigo-700 border border-indigo-200 px-2 py-0.5 rounded-full text-[10px] font-bold">ဘဏ်ငွေလွှဲ</span>';
                    typeIcon = 'building-2';
                } else if (pm.type === 'cod') {
                    typeBadge = '<span class="bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full text-[10px] font-bold">COD အိမ်ရောက်ချေ</span>';
                    typeIcon = 'truck';
                } else if (pm.type === 'other') {
                    typeBadge = '<span class="bg-stone-100 text-stone-700 border border-stone-200 px-2 py-0.5 rounded-full text-[10px] font-bold">အခြား</span>';
                    typeIcon = 'credit-card';
                }

                const slipBadge = requiresSlip
                    ? '<span class="bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded-full text-[10px] font-bold">Slip လိုအပ်</span>'
                    : '<span class="bg-stone-100 text-stone-600 px-2 py-0.5 rounded-full text-[10px]">Slip မလိုပါ</span>';

                const statusBadge = isActive
                    ? `<span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-[11px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> အသုံးပြုနေသည် (Active)
                       </span>`
                    : `<span class="inline-flex items-center gap-1 bg-stone-100 text-stone-500 border border-stone-200 px-2.5 py-1 rounded-full text-[11px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-stone-400"></span> ပိတ်ထားသည် (Inactive)
                       </span>`;

                card.className = `bg-white rounded-3xl border ${isActive ? 'border-stone-200' : 'border-stone-200/60 opacity-80'} p-5 shadow-sm space-y-3.5 relative overflow-hidden transition hover:shadow-md`;

                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl ${isActive ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-stone-100 text-stone-400 border border-stone-200'} flex items-center justify-center font-bold shrink-0">
                                <i data-lucide="${typeIcon}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h5 class="font-extrabold text-stone-900 text-sm">${escapeHtml(pm.nameMm || pm.name)}</h5>
                                    <span class="font-mono text-[10px] bg-stone-100 text-stone-600 px-1.5 py-0.5 rounded">${escapeHtml(pm.code)}</span>
                                </div>
                                <p class="text-[11px] text-stone-500">${escapeHtml(pm.name)}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-[10px] text-stone-400 font-mono">#${pm.sortOrder || 0}</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        ${typeBadge}
                        ${slipBadge}
                    </div>

                    <!-- Details Box -->
                    <div class="bg-stone-50 rounded-2xl p-3 border border-stone-100 space-y-1.5 text-xs">
                        ${pm.accountNumber ? `
                            <div class="flex items-center justify-between">
                                <span class="text-stone-500 text-[11px]">အကောင့် / ဖုန်း:</span>
                                <span class="font-mono font-bold text-amber-800 bg-amber-50/80 px-2 py-0.5 rounded border border-amber-200/50">${escapeHtml(pm.accountNumber)}</span>
                            </div>
                        ` : ''}
                        ${pm.accountName ? `
                            <div class="flex items-center justify-between">
                                <span class="text-stone-500 text-[11px]">အကောင့်ပိုင်ရှင်:</span>
                                <span class="font-bold text-stone-800">${escapeHtml(pm.accountName)}</span>
                            </div>
                        ` : ''}
                        ${pm.instructions ? `
                            <div class="pt-1 text-[11px] text-stone-600 border-t border-stone-200/60 leading-relaxed">
                                <i data-lucide="info" class="w-3.5 h-3.5 inline text-amber-600 mr-1"></i>${escapeHtml(pm.instructions)}
                            </div>
                        ` : ''}
                        ${pm.qrCodeUrl ? `
                            <div class="pt-2 flex items-center gap-2 border-t border-stone-200/60">
                                <a href="${escapeHtml(pm.qrCodeUrl)}" target="_blank" class="flex items-center gap-1.5 text-[11px] text-blue-600 hover:text-blue-800 font-bold">
                                    <i data-lucide="qr-code" class="w-3.5 h-3.5"></i> QR Code ပုံကြည့်ရန်
                                </a>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Status & Actions -->
                    <div class="pt-1 flex items-center justify-between border-t border-stone-100">
                        <div>
                            ${statusBadge}
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button onclick="togglePaymentMethod('${escapeHtml(pm.id)}')" title="${isActive ? 'ပိတ်မည်' : 'ဖွင့်မည်'}" class="p-1.5 rounded-lg border text-stone-500 hover:bg-stone-100 hover:text-stone-800 transition">
                                <i data-lucide="${isActive ? 'power-off' : 'power'}" class="w-4 h-4 ${isActive ? 'text-stone-400 hover:text-rose-600' : 'text-emerald-600'}"></i>
                            </button>
                            <button onclick="openPaymentModal('${escapeHtml(pm.id)}')" title="ပြင်ဆင်မည်" class="p-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deletePaymentMethod('${escapeHtml(pm.id)}')" title="ဖျက်မည်" class="p-1.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });

            lucide.createIcons();
        }

        function handlePaymentTypeChange() {
            const type = document.getElementById('pmType').value;
            const reqSlip = document.getElementById('pmRequiresSlip');
            const accFields = document.getElementById('pmAccountFields');
            const qrSec = document.getElementById('pmQrSection');
            if (type === 'cod') {
                reqSlip.checked = false;
                if (accFields) accFields.classList.add('opacity-50');
                if (qrSec) qrSec.classList.add('hidden');
            } else {
                if (accFields) accFields.classList.remove('opacity-50');
                if (qrSec) qrSec.classList.remove('hidden');
            }
        }

        function handleQrFileSelect(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('QR Code ပုံအရွယ်အစား 5MB ထက် မကျော်ရပါ');
                e.target.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(evt) {
                selectedQrBase64 = evt.target.result;
                document.getElementById('pmQrPreviewImg').src = selectedQrBase64;
                document.getElementById('pmQrPreviewImg').classList.remove('hidden');
                document.getElementById('pmQrPreviewPlaceholder').classList.add('hidden');
                document.getElementById('btnRemoveQr').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        function removePmQr() {
            selectedQrBase64 = null;
            document.getElementById('pmQrCodeUrl').value = '';
            document.getElementById('pmQrFileInput').value = '';
            document.getElementById('pmQrPreviewImg').src = '';
            document.getElementById('pmQrPreviewImg').classList.add('hidden');
            document.getElementById('pmQrPreviewPlaceholder').classList.remove('hidden');
            document.getElementById('btnRemoveQr').classList.add('hidden');
        }

        function openPaymentModal(pmId = null) {
            selectedQrBase64 = null;
            document.getElementById('pmQrFileInput').value = '';

            if (pmId) {
                const pm = (shopData.paymentMethods || []).find(m => m.id === pmId);
                if (!pm) return;
                document.getElementById('paymentModalTitle').innerText = 'ငွေချေနည်းလမ်း ပြင်ဆင်ခြင်း';
                document.getElementById('pmId').value = pm.id;
                document.getElementById('pmCode').value = pm.code || '';
                document.getElementById('pmType').value = pm.type || 'transfer';
                document.getElementById('pmName').value = pm.name || '';
                document.getElementById('pmNameMm').value = pm.nameMm || '';
                document.getElementById('pmAccountName').value = pm.accountName || '';
                document.getElementById('pmAccountNumber').value = pm.accountNumber || '';
                document.getElementById('pmInstructions').value = pm.instructions || '';
                document.getElementById('pmQrCodeUrl').value = pm.qrCodeUrl || '';
                document.getElementById('pmRequiresSlip').checked = !!pm.requiresSlip;
                document.getElementById('pmIsActive').checked = !!pm.isActive;
                document.getElementById('pmSortOrder').value = pm.sortOrder || 1;

                if (pm.qrCodeUrl) {
                    document.getElementById('pmQrPreviewImg').src = pm.qrCodeUrl;
                    document.getElementById('pmQrPreviewImg').classList.remove('hidden');
                    document.getElementById('pmQrPreviewPlaceholder').classList.add('hidden');
                    document.getElementById('btnRemoveQr').classList.remove('hidden');
                } else {
                    document.getElementById('pmQrPreviewImg').src = '';
                    document.getElementById('pmQrPreviewImg').classList.add('hidden');
                    document.getElementById('pmQrPreviewPlaceholder').classList.remove('hidden');
                    document.getElementById('btnRemoveQr').classList.add('hidden');
                }
            } else {
                document.getElementById('paymentModalTitle').innerText = 'ငွေချေနည်းလမ်း အသစ်ထည့်သွင်းခြင်း';
                document.getElementById('pmId').value = '';
                document.getElementById('pmCode').value = '';
                document.getElementById('pmType').value = 'transfer';
                document.getElementById('pmName').value = '';
                document.getElementById('pmNameMm').value = '';
                document.getElementById('pmAccountName').value = '';
                document.getElementById('pmAccountNumber').value = '';
                document.getElementById('pmInstructions').value = 'ငွေလွှဲပြီးပါက ပြေစာ Screenshot တင်ပေးပါရန်';
                document.getElementById('pmQrCodeUrl').value = '';
                document.getElementById('pmRequiresSlip').checked = true;
                document.getElementById('pmIsActive').checked = true;
                const nextSort = ((shopData.paymentMethods || []).length) + 1;
                document.getElementById('pmSortOrder').value = nextSort;

                document.getElementById('pmQrPreviewImg').src = '';
                document.getElementById('pmQrPreviewImg').classList.add('hidden');
                document.getElementById('pmQrPreviewPlaceholder').classList.remove('hidden');
                document.getElementById('btnRemoveQr').classList.add('hidden');
            }

            handlePaymentTypeChange();
            document.getElementById('paymentModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        async function handleSavePaymentMethod(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSavePm');
            btn.disabled = true;
            btn.innerHTML = `<span>သိမ်းဆည်းနေပါသည်...</span>`;

            try {
                let qrUrl = document.getElementById('pmQrCodeUrl').value || null;

                // If user selected a new QR image file, upload it first
                if (selectedQrBase64) {
                    const upRes = await fetch('api/shop.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'UPLOAD_IMAGE', image_data: selectedQrBase64 })
                    });
                    const upData = await upRes.json();
                    if (upData.ok && upData.url) {
                        qrUrl = upData.url;
                    }
                }

                const paymentMethod = {
                    id: document.getElementById('pmId').value.trim() || null,
                    code: document.getElementById('pmCode').value.trim(),
                    type: document.getElementById('pmType').value,
                    name: document.getElementById('pmName').value.trim(),
                    nameMm: document.getElementById('pmNameMm').value.trim(),
                    accountName: document.getElementById('pmAccountName').value.trim(),
                    accountNumber: document.getElementById('pmAccountNumber').value.trim(),
                    instructions: document.getElementById('pmInstructions').value.trim(),
                    qrCodeUrl: qrUrl,
                    requiresSlip: document.getElementById('pmRequiresSlip').checked ? 1 : 0,
                    isActive: document.getElementById('pmIsActive').checked ? 1 : 0,
                    sortOrder: parseInt(document.getElementById('pmSortOrder').value) || 0
                };

                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'SAVE_PAYMENT_METHOD', paymentMethod })
                });
                const data = await res.json();
                if (data.ok) {
                    closePaymentModal();
                    loadData();
                } else {
                    alert('Error: ' + (data.error || 'Failed to save payment method'));
                }
            } catch (err) {
                alert('ငွေချေနည်းလမ်း သိမ်းဆည်းမှု မအောင်မြင်ပါ: ' + err);
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="save" class="w-4 h-4"></i> သိမ်းဆည်းမည်`;
                lucide.createIcons();
            }
        }

        async function togglePaymentMethod(pmId) {
            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'TOGGLE_PAYMENT_METHOD', paymentMethodId: pmId })
                });
                const data = await res.json();
                if (data.ok) {
                    loadData();
                }
            } catch (err) {
                alert('အခြေအနေ ပြောင်းလဲမှု မအောင်မြင်ပါ: ' + err);
            }
        }

        async function deletePaymentMethod(pmId) {
            if (!confirm('ဤငွေပေးချေမှု နည်းလမ်းအား ဖျက်ပစ်ရန် သေချာပါသလား?')) return;
            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'DELETE_PAYMENT_METHOD', paymentMethodId: pmId })
                });
                const data = await res.json();
                if (data.ok) {
                    loadData();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete payment method'));
                }
            } catch (err) {
                alert('ငွေချေနည်းလမ်း ဖျက်ပစ်မှု မအောင်မြင်ပါ: ' + err);
            }
        }

        // ===================================================================
        // REPORTS MANAGEMENT (စာရင်းချုပ် - အပတ်စဉ် နှင့် လစဉ်)
        // ===================================================================
        let currentReportMode = 'weekly'; // 'weekly', 'monthly', 'custom'
        let repCustomStartDate = null;
        let repCustomEndDate = null;
        let cachedReportRows = [];
        let cachedReportSummary = {};

        function setReportMode(mode) {
            currentReportMode = mode;
            document.querySelectorAll('.report-mode-btn').forEach(btn => {
                btn.className = 'report-mode-btn px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900';
            });
            const activeMap = {
                'weekly': 'btnReportWeekly',
                'monthly': 'btnReportMonthly',
                'custom': 'btnReportCustom'
            };
            const btn = document.getElementById(activeMap[mode]);
            if (btn) btn.className = 'report-mode-btn px-3 py-1.5 rounded-xl bg-white text-stone-900 shadow-xs font-bold';

            const filterBar = document.getElementById('customDateFilterBar');
            if (filterBar) {
                if (mode === 'custom') {
                    filterBar.classList.remove('hidden');
                    const sInp = document.getElementById('repStartDate');
                    const eInp = document.getElementById('repEndDate');
                    const now = new Date();
                    if (!sInp.value) {
                        const mStart = new Date(now.getFullYear(), now.getMonth(), 1);
                        sInp.value = mStart.toISOString().split('T')[0];
                    }
                    if (!eInp.value) {
                        eInp.value = now.toISOString().split('T')[0];
                    }
                } else {
                    filterBar.classList.add('hidden');
                }
            }
            renderReports();
        }

        function applyCustomDateFilter() {
            repCustomStartDate = document.getElementById('repStartDate').value;
            repCustomEndDate = document.getElementById('repEndDate').value;
            renderReports();
        }

        function getWeekInfo(date) {
            const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
            const dayNum = d.getUTCDay() || 7;
            d.setUTCDate(d.getUTCDate() + 4 - dayNum);
            const yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
            const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1)/7);
            
            const monday = new Date(date);
            const day = monday.getDay();
            const diff = monday.getDate() - day + (day === 0 ? -6 : 1);
            monday.setDate(diff);
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            
            const formatDate = (dt) => `${dt.getDate()}/${dt.getMonth()+1}`;
            const rangeLabel = `${formatDate(monday)} - ${formatDate(sunday)}`;
            const year = monday.getFullYear();
            const monthName = monday.toLocaleString('en-US', { month: 'short' });
            
            return {
                key: `${year}-W${String(weekNo).padStart(2,'0')}`,
                label: `${year} ${monthName} - အပတ် (${weekNo})`,
                range: rangeLabel,
                year: year,
                week: weekNo,
                startDate: monday,
                endDate: sunday
            };
        }

        function getMonthInfo(date) {
            const year = date.getFullYear();
            const month = date.getMonth();
            const monthsMm = ['ဇန်နဝါရီ', 'ဖေဖော်ဝါရီ', 'မတ်', 'ဧပြီ', 'မေ', 'ဇွန်', 'ဇူလိုင်', 'ဩဂုတ်', 'စက်တင်ဘာ', 'အောက်တိုဘာ', 'နိုဝင်ဘာ', 'ဒီဇင်ဘာ'];
            const monthsEn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return {
                key: `${year}-${String(month+1).padStart(2,'0')}`,
                label: `${year} ${monthsMm[month]} (${monthsEn[month]})`,
                range: `၁ ရက် မှ လကုန် အထိ`,
                year: year,
                month: month + 1
            };
        }

        function renderReports() {
            const allOrders = shopData.orders || [];
            let orders = allOrders;

            if (currentReportMode === 'custom' && repCustomStartDate && repCustomEndDate) {
                const sDate = new Date(repCustomStartDate + 'T00:00:00');
                const eDate = new Date(repCustomEndDate + 'T23:59:59');
                orders = allOrders.filter(o => {
                    const od = new Date(o.createdAt);
                    return od >= sDate && od <= eDate;
                });
            }

            // Grouping logic
            const groups = {};

            orders.forEach(o => {
                const od = new Date(o.createdAt);
                if (isNaN(od.getTime())) return;

                let info;
                if (currentReportMode === 'monthly') {
                    info = getMonthInfo(od);
                } else {
                    info = getWeekInfo(od);
                }

                if (!groups[info.key]) {
                    groups[info.key] = {
                        key: info.key,
                        label: info.label,
                        range: info.range,
                        ordersCount: 0,
                        deliveredCount: 0,
                        cancelledCount: 0,
                        deliveryFeeTotal: 0,
                        discountTotal: 0,
                        revenueTotal: 0,
                        realizedRevenue: 0,
                        orders: []
                    };
                }

                const g = groups[info.key];
                g.ordersCount += 1;
                const tot = Number(o.total || 0);
                const fee = Number(o.deliveryFee || 0);
                const disc = Number(o.discount || 0);

                if (o.status === 'Cancelled') {
                    g.cancelledCount += 1;
                } else {
                    g.revenueTotal += tot;
                    g.deliveryFeeTotal += fee;
                    g.discountTotal += disc;
                    if (o.status === 'Delivered' || o.status === 'Completed') {
                        g.deliveredCount += 1;
                        g.realizedRevenue += tot;
                    }
                }
                g.orders.push(o);
            });

            // Convert to sorted array (latest first)
            const rows = Object.values(groups).sort((a, b) => b.key.localeCompare(a.key));
            cachedReportRows = rows;

            // Overall Summary for Selected Period
            let sumTotalRev = 0;
            let sumRealizedRev = 0;
            let sumOrders = 0;
            let sumDelivered = 0;
            let sumCancelled = 0;
            let sumFee = 0;
            let sumDiscount = 0;

            rows.forEach(r => {
                sumTotalRev += r.revenueTotal;
                sumRealizedRev += r.realizedRevenue;
                sumOrders += r.ordersCount;
                sumDelivered += r.deliveredCount;
                sumCancelled += r.cancelledCount;
                sumFee += r.deliveryFeeTotal;
                sumDiscount += r.discountTotal;
            });

            cachedReportSummary = {
                sumTotalRev, sumRealizedRev, sumOrders, sumDelivered, sumCancelled, sumFee, sumDiscount
            };

            // Update KPI cards
            const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };
            setTxt('repStatTotalRev', sumTotalRev.toLocaleString() + ' Ks');
            setTxt('repStatRealizedRev', sumRealizedRev.toLocaleString() + ' Ks');
            setTxt('repStatOrdersCount', sumOrders);
            setTxt('repStatOrdersSub', `အောင်မြင်: ${sumDelivered} • ပယ်ဖျက်: ${sumCancelled}`);
            setTxt('repStatDeliveryAndDiscount', (sumFee + sumDiscount).toLocaleString() + ' Ks');
            setTxt('repStatDelDiscSub', `ပို့ခ: ${sumFee.toLocaleString()} Ks • လျှော့ငွေ: ${sumDiscount.toLocaleString()} Ks`);

            // Titles
            const titleMap = {
                'weekly': ['အပတ်စဉ် အသေးစိတ် စာရင်းချုပ် ဇယား', 'ရက်သတ္တပတ်အလိုက် အော်ဒါအရေအတွက်၊ ပို့ဆောင်ခနှင့် အရောင်းရငွေများ', 'ရက်သတ္တပတ် (Week)'],
                'monthly': ['လစဉ် အသေးစိတ် စာရင်းချုပ် ဇယား', 'လအလိုက် အော်ဒါအရေအတွက်၊ ပို့ဆောင်ခနှင့် အရောင်းရငွေများ', 'လ (Month)'],
                'custom': ['ရက်စွဲအလိုက် စာရင်းချုပ် ဇယား', 'ရွေးချယ်ထားသော ရက်စွဲကာလအတွင်း စာရင်းချုပ်', 'ရက်သတ္တပတ် / လ']
            };
            const tInfo = titleMap[currentReportMode];
            setTxt('repTableTitle', tInfo[0]);
            setTxt('repTableSubtitle', tInfo[1]);
            setTxt('repColHeaderPeriod', tInfo[2]);
            setTxt('repTableCountBadge', `${rows.length} ${currentReportMode === 'monthly' ? 'Months' : 'Weeks'}`);

            // Populate Table
            const tbody = document.getElementById('repTableBody');
            const tfoot = document.getElementById('repTableFoot');
            tbody.innerHTML = '';
            tfoot.innerHTML = '';

            if (rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-stone-400">ကာလအတွင်း စာရင်းမှတ်တမ်းများ မရှိသေးပါ။</td></tr>`;
            } else {
                rows.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-amber-50/40 transition';
                    tr.innerHTML = `
                        <td class="p-3.5 font-bold text-stone-900">${escapeHtml(r.label)}</td>
                        <td class="p-3.5 text-stone-500 font-mono">${escapeHtml(r.range)}</td>
                        <td class="p-3.5 text-center font-bold text-stone-900">${r.ordersCount}</td>
                        <td class="p-3.5 text-center font-bold text-emerald-600">${r.deliveredCount}</td>
                        <td class="p-3.5 text-center font-bold text-red-500">${r.cancelledCount}</td>
                        <td class="p-3.5 text-right font-mono text-blue-700">${Number(r.deliveryFeeTotal).toLocaleString()} Ks</td>
                        <td class="p-3.5 text-right font-mono text-amber-600">${r.discountTotal > 0 ? '-' + Number(r.discountTotal).toLocaleString() + ' Ks' : '0 Ks'}</td>
                        <td class="p-3.5 text-right font-mono font-black text-amber-700 text-sm">${Number(r.revenueTotal).toLocaleString()} Ks</td>
                    `;
                    tbody.appendChild(tr);
                });

                tfoot.innerHTML = `
                    <tr class="bg-stone-100/90 font-black text-stone-900">
                        <td class="p-3.5">စုစုပေါင်း စာရင်းချုပ် (Total)</td>
                        <td class="p-3.5 text-stone-500">${rows.length} ${currentReportMode === 'monthly' ? 'Months' : 'Weeks'}</td>
                        <td class="p-3.5 text-center">${sumOrders}</td>
                        <td class="p-3.5 text-center text-emerald-700">${sumDelivered}</td>
                        <td class="p-3.5 text-center text-red-600">${sumCancelled}</td>
                        <td class="p-3.5 text-right font-mono text-blue-800">${sumFee.toLocaleString()} Ks</td>
                        <td class="p-3.5 text-right font-mono text-amber-800">${sumDiscount > 0 ? '-' + sumDiscount.toLocaleString() + ' Ks' : '0 Ks'}</td>
                        <td class="p-3.5 text-right font-mono text-amber-800 text-sm sm:text-base">${sumTotalRev.toLocaleString()} Ks</td>
                    </tr>
                `;
            }

            // Payment Methods breakdown
            const payMethods = { 'KPay': { rev: 0, count: 0 }, 'WavePay': { rev: 0, count: 0 }, 'COD': { rev: 0, count: 0 } };
            orders.forEach(o => {
                if (o.status === 'Cancelled') return;
                const pm = (o.paymentMethod || '').toLowerCase();
                let key = 'KPay';
                if (pm.includes('wave')) key = 'WavePay';
                else if (pm.includes('cod') || pm.includes('cash') || pm.includes('ငွေချေ')) key = 'COD';
                else if (pm.includes('kpay') || pm.includes('kbz')) key = 'KPay';

                if (!payMethods[key]) payMethods[key] = { rev: 0, count: 0 };
                payMethods[key].rev += Number(o.total || 0);
                payMethods[key].count += 1;
            });

            const payList = document.getElementById('repPaymentMethodsList');
            payList.innerHTML = '';
            Object.keys(payMethods).forEach(k => {
                const item = payMethods[k];
                const pct = sumTotalRev > 0 ? Math.round((item.rev / sumTotalRev) * 100) : 0;
                const div = document.createElement('div');
                div.className = 'space-y-1';
                div.innerHTML = `
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-stone-800">${k} (${item.count} Orders)</span>
                        <span class="font-mono font-bold text-stone-900">${item.rev.toLocaleString()} Ks (${pct}%)</span>
                    </div>
                    <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-amber-600 h-2 rounded-full" style="width: ${pct}%"></div>
                    </div>
                `;
                payList.appendChild(div);
            });

            // Top Selling Handbags
            const prodSales = {};
            orders.forEach(o => {
                if (o.status === 'Cancelled') return;
                let items = [];
                try {
                    items = typeof o.itemsJson === 'string' ? JSON.parse(o.itemsJson) : (o.itemsJson || o.items || []);
                } catch(e) {}

                items.forEach(it => {
                    const name = it.nameMm || it.name || 'အိတ်ဒီဇိုင်း';
                    const qty = Number(it.quantity || 1);
                    const pr = Number(it.price || 0) * qty;
                    if (!prodSales[name]) prodSales[name] = { qty: 0, rev: 0 };
                    prodSales[name].qty += qty;
                    prodSales[name].rev += pr;
                });
            });

            const topProducts = Object.entries(prodSales).sort((a, b) => b[1].qty - a[1].qty).slice(0, 5);
            const topList = document.getElementById('repTopProductsList');
            topList.innerHTML = '';
            if (topProducts.length === 0) {
                topList.innerHTML = `<p class="text-stone-400 py-3 text-center">အရောင်းမှတ်တမ်း မရှိသေးပါ။</p>`;
            } else {
                topProducts.forEach(([name, data], idx) => {
                    const row = document.createElement('div');
                    row.className = 'flex items-center justify-between p-2.5 rounded-xl bg-stone-50 border border-stone-100';
                    row.innerHTML = `
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 font-bold text-xs flex items-center justify-center">${idx + 1}</span>
                            <span class="font-bold text-stone-800">${escapeHtml(name)}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-stone-900 font-mono">${data.qty} လုံး</span>
                            <span class="text-stone-500 text-[11px] block font-mono">${data.rev.toLocaleString()} Ks</span>
                        </div>
                    `;
                    topList.appendChild(row);
                });
            }

            lucide.createIcons();
        }

        // CSV Export Function
        function exportReportCSV() {
            if (!cachedReportRows || cachedReportRows.length === 0) {
                alert('ထုတ်ယူရန် စာရင်းမှတ်တမ်း မရှိသေးပါ။');
                return;
            }

            const modeLabel = currentReportMode === 'monthly' ? 'monthly' : 'weekly';
            const filename = `pontpont_sales_report_${modeLabel}_${new Date().toISOString().split('T')[0]}.csv`;

            let csv = '\uFEFF'; // UTF-8 BOM for Microsoft Excel
            csv += 'ကာလ (Period),ရက်စွဲ (Date Range),အော်ဒါအရေအတွက်,ပို့ဆောင်ပြီး,ပယ်ဖျက်,ပို့ခ စုစုပေါင်း (Ks),လျှော့ငွေ (Ks),ရောင်းရငွေ စုစုပေါင်း (Ks)\n';

            cachedReportRows.forEach(r => {
                csv += `"${r.label}","${r.range}",${r.ordersCount},${r.deliveredCount},${r.cancelledCount},${r.deliveryFeeTotal},${r.discountTotal},${r.revenueTotal}\n`;
            });

            // Summary row
            const s = cachedReportSummary;
            csv += `"စုစုပေါင်း စာရင်းချုပ် (Total)","-",${s.sumOrders},${s.sumDelivered},${s.sumCancelled},${s.sumFee},${s.sumDiscount},${s.sumTotalRev}\n`;

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Print Report Modal Functions
        function openReportPrintModal() {
            const s = cachedReportSummary;
            const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };

            setTxt('printReportTitleBadge', currentReportMode === 'monthly' ? 'လစဉ် စာရင်းချုပ် (Monthly Report)' : 'အပတ်စဉ် စာရင်းချုပ် (Weekly Report)');
            setTxt('printReportDateGenerated', 'ထုတ်ယူသည့်ရက်: ' + new Date().toLocaleString());
            setTxt('printRepRev', (s.sumTotalRev || 0).toLocaleString() + ' Ks');
            setTxt('printRepOrders', s.sumOrders || 0);
            setTxt('printRepDelivered', s.sumDelivered || 0);
            setTxt('printRepDeliveryFee', (s.sumFee || 0).toLocaleString() + ' Ks');
            setTxt('printThPeriod', currentReportMode === 'monthly' ? 'လ (Month)' : 'ရက်သတ္တပတ် (Week)');

            const tbody = document.getElementById('printRepTableBody');
            const tfoot = document.getElementById('printRepTableFoot');
            tbody.innerHTML = '';
            tfoot.innerHTML = '';

            cachedReportRows.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="p-2 font-bold">${escapeHtml(r.label)}</td>
                    <td class="p-2 text-stone-500 font-mono">${escapeHtml(r.range)}</td>
                    <td class="p-2 text-center font-bold">${r.ordersCount}</td>
                    <td class="p-2 text-center font-bold text-emerald-800">${r.deliveredCount}</td>
                    <td class="p-2 text-right font-mono">${Number(r.deliveryFeeTotal).toLocaleString()} Ks</td>
                    <td class="p-2 text-right font-mono text-amber-700">${r.discountTotal > 0 ? '-' + Number(r.discountTotal).toLocaleString() + ' Ks' : '0 Ks'}</td>
                    <td class="p-2 text-right font-mono font-bold">${Number(r.revenueTotal).toLocaleString()} Ks</td>
                `;
                tbody.appendChild(tr);
            });

            tfoot.innerHTML = `
                <tr class="bg-stone-100 font-black">
                    <td class="p-2">စုစုပေါင်း (Total)</td>
                    <td class="p-2">-</td>
                    <td class="p-2 text-center">${s.sumOrders}</td>
                    <td class="p-2 text-center">${s.sumDelivered}</td>
                    <td class="p-2 text-right font-mono">${(s.sumFee || 0).toLocaleString()} Ks</td>
                    <td class="p-2 text-right font-mono text-amber-800">${(s.sumDiscount || 0) > 0 ? '-' + (s.sumDiscount || 0).toLocaleString() + ' Ks' : '0 Ks'}</td>
                    <td class="p-2 text-right font-mono text-amber-900">${(s.sumTotalRev || 0).toLocaleString()} Ks</td>
                </tr>
            `;

            document.getElementById('reportPrintModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeReportPrintModal() {
            document.getElementById('reportPrintModal').classList.add('hidden');
        }

        // ===================================================================
        // SETTINGS, BRANDING & TELEGRAM
        // ===================================================================
        function renderSettings() {
            const s = shopData.settings || {};
            const setIf = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
            
            // Branding & Display
            setIf('setTitleBar', s.title_bar || 'Pont Pont Handbag Shop | ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး (မန္တလေး)');
            setIf('setShopSubtitle', s.shop_subtitle || 'ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး');
            setIf('setAnnouncement', s.announcement || '✨ PONTPONT10 ကူပွန်ဖြင့် 10% OFF • ၁ သိန်းအထက် Free Delivery • နေ့ချင်း ကားဂိတ်ပို့');
            setIf('setShopLogo', s.shop_logo);
            updateLogoPreview(s.shop_logo);

            // General Info
            setIf('setShopName', s.shop_name);
            setIf('setShopNameMm', s.shop_name_mm);
            setIf('setPhone1', s.phone_1);
            setIf('setPhone2', s.phone_2);
            setIf('setKpayAccount', s.kpay_account);
            setIf('setTelegramBotToken', s.telegram_bot_token);
            setIf('setTelegramChatId', s.telegram_chat_id);
            const tgCheck = document.getElementById('setTelegramEnabled');
            if (tgCheck) tgCheck.checked = (s.telegram_enabled === '1');

            updateBrandDisplay();
        }

        function updateBrandDisplay() {
            const s = shopData.settings || {};
            if (s.title_bar) {
                document.title = 'Admin - ' + s.title_bar;
            }
            if (s.shop_name) {
                const titleEl = document.getElementById('adminBrandTitle');
                if (titleEl) titleEl.innerText = s.shop_name;
            }
            if (s.shop_subtitle || s.shop_name_mm) {
                const subEl = document.getElementById('adminBrandSubtitle');
                if (subEl) subEl.innerText = s.shop_subtitle || s.shop_name_mm;
            }
            const logoBox = document.getElementById('adminBrandLogoContainer');
            if (logoBox) {
                if (s.shop_logo && s.shop_logo.trim()) {
                    logoBox.innerHTML = `<img src="${escapeHtml(s.shop_logo.trim())}" class="w-full h-full object-contain">`;
                } else {
                    logoBox.innerHTML = `<i data-lucide="shield-check" class="w-5 h-5 text-amber-600"></i>`;
                    lucide.createIcons();
                }
            }
        }

        function updateLogoPreview(url) {
            const img = document.getElementById('setShopLogoPreview');
            const placeholder = document.getElementById('setShopLogoPlaceholder');
            if (url && url.trim()) {
                if (img) {
                    img.src = url.trim();
                    img.classList.remove('hidden');
                }
                if (placeholder) placeholder.classList.add('hidden');
            } else {
                if (img) {
                    img.src = '';
                    img.classList.add('hidden');
                }
                if (placeholder) placeholder.classList.remove('hidden');
            }
        }

        function clearLogo() {
            const input = document.getElementById('setShopLogo');
            if (input) input.value = '';
            updateLogoPreview('');
        }

        async function handleLogoUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('Logo ပုံ အရွယ်အစားသည် 5MB ထက် မကျော်ရပါ');
                return;
            }
            const reader = new FileReader();
            reader.onload = async function(e) {
                const base64Data = e.target.result;
                try {
                    const res = await fetch('api/shop.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'UPLOAD_IMAGE', image_data: base64Data, folder: 'logos' })
                    });
                    const data = await res.json();
                    if (data.ok && data.url) {
                        const logoInput = document.getElementById('setShopLogo');
                        if (logoInput) logoInput.value = data.url;
                        updateLogoPreview(data.url);
                        alert('Logo ပုံ အောင်မြင်စွာ တင်ပြီးပါပြီ! အတည်ပြု သိမ်းဆည်းရန် စာမျက်နှာအောက်ခြေရှိ "ဆက်တင်များ အားလုံး သိမ်းဆည်းမည်" ကို နှိပ်ပေးပါ။ ✅');
                    } else {
                        alert('Upload မအောင်မြင်ပါ: ' + (data.error || 'Unknown error'));
                    }
                } catch (err) {
                    alert('Upload Error: ' + err);
                }
            };
            reader.readAsDataURL(file);
        }

        async function handleSaveSettings(e) {
            e.preventDefault();
            const settings = {
                title_bar: document.getElementById('setTitleBar') ? document.getElementById('setTitleBar').value.trim() : '',
                shop_subtitle: document.getElementById('setShopSubtitle') ? document.getElementById('setShopSubtitle').value.trim() : '',
                announcement: document.getElementById('setAnnouncement') ? document.getElementById('setAnnouncement').value.trim() : '',
                shop_logo: document.getElementById('setShopLogo') ? document.getElementById('setShopLogo').value.trim() : '',
                shop_name: document.getElementById('setShopName').value.trim(),
                shop_name_mm: document.getElementById('setShopNameMm').value.trim(),
                phone_1: document.getElementById('setPhone1').value.trim(),
                phone_2: document.getElementById('setPhone2').value.trim(),
                kpay_account: document.getElementById('setKpayAccount').value.trim(),
                telegram_bot_token: document.getElementById('setTelegramBotToken').value.trim(),
                telegram_chat_id: document.getElementById('setTelegramChatId').value.trim(),
                telegram_enabled: document.getElementById('setTelegramEnabled').checked ? '1' : '0'
            };
            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'UPDATE_SETTINGS', settings })
                });
                const data = await res.json();
                if (data.ok) {
                    alert('ဆိုင် Logo, Title Bar နှင့် ဆက်တင်များ အားလုံး အောင်မြင်စွာ သိမ်းဆည်းပြီးပါပြီ! ✅');
                    loadData();
                } else {
                    alert('Error: ' + (data.error || 'Failed to save settings'));
                }
            } catch (err) {
                alert('ဆက်တင်များ သိမ်းဆည်းမှု မအောင်မြင်ပါ: ' + err);
            }
        }

        async function testTelegramAlert() {
            const botToken = document.getElementById('setTelegramBotToken').value.trim();
            const chatId = document.getElementById('setTelegramChatId').value.trim();
            if (!botToken || !chatId) {
                alert('ကျေးဇူးပြု၍ Telegram Bot Token နှင့် Chat ID အရင်ဖြည့်သွင်းပြီး သိမ်းဆည်းပေးပါ။');
                return;
            }
            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'TEST_TELEGRAM' })
                });
                const data = await res.json();
                alert(data.message || 'Telegram စမ်းသပ်မက်ဆေ့ခ်ျ ပေးပို့ပြီးပါပြီ။ သင်၏ Telegram ကို စစ်ဆေးကြည့်ပါ။');
            } catch (err) {
                alert('Telegram ချိတ်ဆက်မှု မအောင်မြင်ပါ: ' + err);
            }
        }

        // ===================================================================
        // AI CHATBOT MANAGEMENT
        // ===================================================================
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
            const hint = document.getElementById('providerHintText');
            const linkText = document.getElementById('apiKeyLinkText');

            if (prov === 'mock') {
                keyBox.classList.add('hidden');
                urlBox.classList.add('hidden');
                modelInp.value = 'built-in-rules';
                hint.innerText = '💡 Built-in Engine သည် API Key မလိုဘဲ ဆိုင်အချက်အလက်များကို အလိုအလျောက် ဖြေကြားပေးပါသည်။';
                linkText.innerHTML = '';
            } else if (prov === 'custom') {
                keyBox.classList.remove('hidden');
                urlBox.classList.remove('hidden');
                if (!modelInp.value || modelInp.value === 'built-in-rules') modelInp.value = 'gpt-3.5-turbo';
                hint.innerText = '💡 Local LLM (Ollama, OpenWebUI, LM Studio) သို့မဟုတ် Custom Endpoint ဖြင့် ချိတ်ဆက်နိုင်ပါသည်။';
                linkText.innerHTML = '';
            } else {
                keyBox.classList.remove('hidden');
                urlBox.classList.add('hidden');
                if (prov === 'gemini') {
                    if (!modelInp.value || modelInp.value === 'built-in-rules' || modelInp.value === 'gemini-1.5-flash') modelInp.value = 'gemini-flash-lite-latest';
                    hint.innerText = '💡 Google Gemini သည် မြန်မာစာအထူးကျွမ်းကျင်ပြီး အခမဲ့ Rate Limits ကောင်းမွန်ပါသည်။';
                    linkText.innerHTML = '<a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-emerald-600 hover:underline font-bold">👉 Google AI Studio တွင် အခမဲ့ Gemini Key ရယူရန်</a>';
                } else if (prov === 'openai') {
                    if (!modelInp.value || modelInp.value === 'built-in-rules') modelInp.value = 'gpt-4o-mini';
                    hint.innerText = '💡 OpenAI GPT-4o-mini သည် အလွန်တိကျပြီး စကားပြော သဘာဝကျပါသည်။';
                    linkText.innerHTML = '<a href="https://platform.openai.com/api-keys" target="_blank" class="text-emerald-600 hover:underline font-bold">👉 OpenAI Platform မှ API Key ရယူရန်</a>';
                } else if (prov === 'groq') {
                    if (!modelInp.value || modelInp.value === 'built-in-rules') modelInp.value = 'llama-3.3-70b-versatile';
                    hint.innerText = '💡 Groq သည် LPU chip ဖြင့် အမြန်ဆုံး အမြန်နှုန်းဖြင့် ချက်ချင်း ပြန်ဖြေပေးနိုင်ပါသည်။';
                    linkText.innerHTML = '<a href="https://console.groq.com/keys" target="_blank" class="text-emerald-600 hover:underline font-bold">👉 Groq Console မှ API Key ရယူရန်</a>';
                }
            }
        }

        function toggleApiKeyVisibility() {
            const inp = document.getElementById('adminBotApiKey');
            const icon = document.getElementById('apiKeyEyeIcon');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                inp.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
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
                const cfg = shopData.chatbotConfig || {};
                if (!cfg.hasApiKey) {
                    alert('Models များ ဆွဲယူရန် API Key အရင် ဖြည့်သွင်းပေးပါရှင်။');
                    return;
                }
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
                if (data.ok && data.models && data.models.length > 0) {
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
                    badge.innerText = `✓ Models (${data.models.length}) ခု အသင့်ရရှိပါသည်`;
                    badge.className = 'text-[11px] text-emerald-700 font-bold';
                } else {
                    badge.innerText = '⚠️ Models မတွေ့ပါ (Input ထဲတွင် တိုက်ရိုက် ရိုက်ထည့်နိုင်ပါသည်)';
                    badge.className = 'text-[11px] text-amber-700 font-bold';
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

        function renderChatbot() {
            const cfg = shopData.chatbotConfig || {};
            const provEl = document.getElementById('adminBotProvider');
            if (provEl) provEl.value = cfg.provider || 'gemini';
            
            const modelEl = document.getElementById('adminBotModel');
            if (modelEl) modelEl.value = cfg.model || 'gemini-flash-lite-latest';
            
            const urlEl = document.getElementById('adminBotApiUrl');
            if (urlEl) urlEl.value = cfg.apiUrl || '';
            
            const keyEl = document.getElementById('adminBotApiKey');
            const keyBadge = document.getElementById('apiKeyStatusBadge');
            if (keyEl) {
                keyEl.value = cfg.apiKey || '';
                if (cfg.hasApiKey && !cfg.apiKey) {
                    keyEl.placeholder = '●●●●●●●● (Database တွင် သိမ်းဆည်းပြီးသား)';
                    if (keyBadge) keyBadge.innerHTML = '<span class="text-emerald-700 font-bold">✓ Key သိမ်းဆည်းထားပြီး</span>';
                } else if (cfg.apiKey) {
                    if (keyBadge) keyBadge.innerHTML = '<span class="text-emerald-700 font-bold">✓ Key ထည့်သွင်းထားသည်</span>';
                } else {
                    keyEl.placeholder = 'AI Provider မှ API Key ထည့်ပါ';
                    if (keyBadge) keyBadge.innerHTML = '<span class="text-amber-700">Key မထည့်ရသေးပါ</span>';
                }
            }

            const nameEl = document.getElementById('adminBotName');
            if (nameEl) nameEl.value = cfg.botName || 'ပုံ့ပုံ့ AI အကူအညီ';

            const welcomeEl = document.getElementById('adminBotWelcome');
            if (welcomeEl) welcomeEl.value = cfg.welcomeMessageMm || 'မင်္ဂလာပါရှင် 👋 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်။ ဘာများ ကူညီပေးရမလဲရှင်။';

            const promptEl = document.getElementById('adminBotSystemPrompt');
            if (promptEl) promptEl.value = cfg.systemPrompt || DEFAULT_PROMPT_TEMPLATE;

            const enabledEl = document.getElementById('adminBotEnabled');
            const isEnabled = (cfg.enabled !== false && cfg.enabled != 0);
            if (enabledEl) enabledEl.checked = isEnabled;

            // Update badge on sidebar nav
            const badgeEl = document.getElementById('badgeChatbotStatus');
            if (badgeEl) {
                if (isEnabled) {
                    badgeEl.innerText = 'Active';
                    badgeEl.className = 'bg-emerald-950 text-emerald-400 border border-emerald-800 text-[10px] px-2 py-0.5 rounded-full font-bold';
                } else {
                    badgeEl.innerText = 'Disabled';
                    badgeEl.className = 'bg-stone-800 text-stone-400 border border-stone-700 text-[10px] px-2 py-0.5 rounded-full font-bold';
                }
            }

            handleBotProviderChange();
        }

        async function handleSaveChatbot(e) {
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
                    alert('AI Chatbot ဆက်တင်များ အားလုံး အောင်မြင်စွာ သိမ်းဆည်းပြီးပါပြီ! ✅');
                    loadData();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update chatbot config'));
                }
            } catch (err) {
                alert('Chatbot ဆက်တင် သိမ်းဆည်းမှု မအောင်မြင်ပါ: ' + err);
            }
        }

        async function testAdminChatbot() {
            const queryInp = document.getElementById('adminTestQuery');
            const q = queryInp.value.trim();
            if (!q) return;

            const output = document.getElementById('adminTestChatOutput');
            const btn = document.getElementById('btnTestChat');
            const btnText = document.getElementById('btnTestChatText');

            // Append User Question
            const userMsg = document.createElement('div');
            userMsg.className = 'flex items-start gap-2 justify-end';
            userMsg.innerHTML = `<span class="bg-amber-600/30 text-amber-200 px-3 py-1.5 rounded-xl border border-amber-600/40">${escapeHtml(q)}</span><span class="text-amber-400 font-bold shrink-0">👤 Admin:</span>`;
            output.appendChild(userMsg);
            queryInp.value = '';
            output.scrollTop = output.scrollHeight;

            btn.disabled = true;
            btnText.innerText = 'ဖြေကြားနေသည်...';

            try {
                const res = await fetch('api/shop.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'CHAT_MESSAGE', message: q })
                });
                const data = await res.json();
                let reply = data.reply;
                let badge = '';
                if (data.model) {
                    badge = `<div class="mt-1 text-[10px] text-emerald-400 font-mono flex items-center gap-1"><span>✨ AI:</span><span class="bg-emerald-950 px-1.5 py-0.5 rounded border border-emerald-800">${escapeHtml(data.model)}</span></div>`;
                } else if (data.fallback) {
                    badge = `<div class="mt-1 text-[10px] text-amber-400 font-mono flex items-center gap-1"><span>🏪 မူလဆိုင်အကူအညီ စနစ် (Built-in Knowledge)</span></div>`;
                }

                if (!reply) {
                    reply = 'မင်္ဂလာပါရှင့် မေးခွန်းအတွက် အချက်အလက်များ ရှာမတွေ့ပါသဖြင့် ဖုန်း 09-963000502 သို့ တိုက်ရိုက် ဆက်သွယ်ပေးပါရှင်။';
                }

                const botMsg = document.createElement('div');
                botMsg.className = 'flex items-start gap-2';
                botMsg.innerHTML = `<span class="text-emerald-400 font-bold shrink-0 mt-0.5">🤖 Bot:</span><div class="bg-stone-800/90 text-stone-200 px-3.5 py-2.5 rounded-2xl border border-stone-700/80 leading-relaxed text-xs"><div class="whitespace-pre-line">${escapeHtml(reply)}</div>${badge}</div>`;
                output.appendChild(botMsg);
                output.scrollTop = output.scrollHeight;
            } catch (err) {
                const errMsg = document.createElement('div');
                errMsg.className = 'text-red-400 text-xs';
                errMsg.innerText = '⚠️ ချိတ်ဆက်မှု အမှားဖြစ်ပေါ်ပါသည်: ' + err;
                output.appendChild(errMsg);
            } finally {
                btn.disabled = false;
                btnText.innerText = 'စမ်းသပ်မေးမည်';
            }
        }
    </script>
</body>
</html>

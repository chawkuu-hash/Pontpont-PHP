-- ============================================================
-- Pont Pont Handbag Shop Database Dump for MySQL / phpMyAdmin
-- Ready for Bluehost cPanel & phpMyAdmin Import
-- Charset: utf8mb4 (Full Unicode & Myanmar text support)
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- --------------------------------------------------------
-- Table structure for `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `labelEn` VARCHAR(100) NOT NULL,
  `labelMm` VARCHAR(100) DEFAULT NULL,
  `sortOrder` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `nameEn` VARCHAR(255) NOT NULL,
  `nameMm` VARCHAR(255) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `categoryMm` VARCHAR(100) NOT NULL,
  `price` INT NOT NULL,
  `originalPrice` INT DEFAULT NULL,
  `wholesalePrice` INT DEFAULT NULL,
  `image` TEXT NOT NULL,
  `colorsJson` TEXT NOT NULL,
  `badge` VARCHAR(50) DEFAULT NULL,
  `rating` DECIMAL(3,2) NOT NULL DEFAULT 5.00,
  `reviewsCount` INT NOT NULL DEFAULT 1,
  `stock` INT NOT NULL DEFAULT 0,
  `material` VARCHAR(255) NOT NULL,
  `dimensions` VARCHAR(100) NOT NULL,
  `descriptionEn` TEXT NOT NULL,
  `descriptionMm` TEXT NOT NULL,
  `sortOrder` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'customer',
  `createdAt` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `orders`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `customerName` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `note` TEXT DEFAULT NULL,
  `paymentMethod` VARCHAR(30) NOT NULL,
  `transactionRef` VARCHAR(50) DEFAULT NULL,
  `paymentSlip` VARCHAR(255) DEFAULT NULL,
  `couponCode` VARCHAR(50) DEFAULT NULL,
  `itemsJson` LONGTEXT NOT NULL,
  `subtotal` INT NOT NULL,
  `discount` INT NOT NULL DEFAULT 0,
  `deliveryFee` INT NOT NULL DEFAULT 0,
  `total` INT NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'Pending',
  `createdAt` VARCHAR(50) NOT NULL,
  `userEmail` VARCHAR(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `announcements`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `tag` VARCHAR(50) NOT NULL,
  `image` TEXT DEFAULT NULL,
  `createdAt` VARCHAR(50) NOT NULL,
  `authorName` VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `coupons`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discountType` VARCHAR(20) NOT NULL DEFAULT 'percent',
  `discountValue` INT NOT NULL,
  `minOrderAmount` INT NOT NULL DEFAULT 0,
  `usageLimit` INT NOT NULL DEFAULT 0,
  `usedCount` INT NOT NULL DEFAULT 0,
  `expiresAt` VARCHAR(50) DEFAULT NULL,
  `isActive` TINYINT(1) NOT NULL DEFAULT 1,
  `createdAt` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `key` VARCHAR(50) NOT NULL PRIMARY KEY,
  `value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `chatbot_config`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `chatbot_config`;
CREATE TABLE `chatbot_config` (
  `id` INT NOT NULL PRIMARY KEY DEFAULT 1,
  `provider` VARCHAR(50) NOT NULL DEFAULT 'mock',
  `apiUrl` TEXT DEFAULT NULL,
  `apiKey` TEXT DEFAULT NULL,
  `model` VARCHAR(100) NOT NULL DEFAULT 'gemini-1.5-flash',
  `systemPrompt` TEXT NOT NULL,
  `botName` VARCHAR(100) NOT NULL DEFAULT 'ပုံ့ပုံ့ AI အကူအညီ',
  `welcomeMessageMm` TEXT NOT NULL,
  `welcomeMessageEn` TEXT NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `updatedAt` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Dumping Data for Tables
-- ============================================================

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `labelEn`, `labelMm`, `sortOrder`) VALUES ('All', 'All Bags', 'အိတ်အားလုံး', 0);
INSERT INTO `categories` (`id`, `labelEn`, `labelMm`, `sortOrder`) VALUES ('Tote', 'Tote & Handbags', 'လက်ဆွဲအိတ် / တုတ်အိတ်', 1);
INSERT INTO `categories` (`id`, `labelEn`, `labelMm`, `sortOrder`) VALUES ('Crossbody', 'Sling & Crossbody', 'ဘေးလွယ်အိတ်', 2);
INSERT INTO `categories` (`id`, `labelEn`, `labelMm`, `sortOrder`) VALUES ('Shoulder', 'Shoulder Bags', 'ပုခုံးလွယ်အိတ်', 3);
INSERT INTO `categories` (`id`, `labelEn`, `labelMm`, `sortOrder`) VALUES ('Clutch', 'Wallets & Purses', 'ပိုက်ဆံအိတ် / ပွဲတက်အိတ်', 4);
INSERT INTO `categories` (`id`, `labelEn`, `labelMm`, `sortOrder`) VALUES ('Backpack', 'Backpacks', 'ကျောပိုးအိတ်', 5);

-- Dumping data for table `products`
INSERT INTO `products` (`id`, `nameEn`, `nameMm`, `category`, `categoryMm`, `price`, `originalPrice`, `wholesalePrice`, `image`, `colorsJson`, `badge`, `rating`, `reviewsCount`, `stock`, `material`, `dimensions`, `descriptionEn`, `descriptionMm`, `sortOrder`) VALUES ('pp-bag-01', 'Pont Pont Signature Ladies Office Tote Bag', 'ပုံ့ပုံ့ ရုံးတက် လက်ဆွဲ သားရေ တုတ်အိတ်ကြီး', 'Tote', 'လက်ဆွဲအိတ် / တုတ်အိတ်', 38000, 45000, 31000, 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=900&q=80', '[{"name":"Caramel Brown","hex":"#9C5B34"},{"name":"Midnight Black","hex":"#1C1917"},{"name":"Cream Ivory","hex":"#F5EFE6"}]', 'Best Seller', 4.9, 215, 45, 'High-Grade PU Pebbled Leather', '34cm x 28cm x 14cm', 'Pont Pont Mandalay best-selling spacious tote bag for office and daily use. Available for both retail and wholesale orders.', 'ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) ၏ လူကြိုက်အများဆုံး ရုံးတက်၊ ကျောင်းတက် နေ့စဉ်သုံး သားရေ လက်ဆွဲတုတ်အိတ်။ လက်လီဈေး 38,000 ကျပ် (လက်ကား ၅ လုံးအထက် 31,000 ကျပ်ဖြင့် မှာယူနိုင်ပါသည်)။', 0);
INSERT INTO `products` (`id`, `nameEn`, `nameMm`, `category`, `categoryMm`, `price`, `originalPrice`, `wholesalePrice`, `image`, `colorsJson`, `badge`, `rating`, `reviewsCount`, `stock`, `material`, `dimensions`, `descriptionEn`, `descriptionMm`, `sortOrder`) VALUES ('pp-bag-02', 'Pont Pont Diamond Quilted Chain Sling Bag', 'ပုံ့ပုံ့ စိန်ကွက်ဖော် ရွှေကြိုးချိတ် ဘေးလွယ်အိတ်', 'Crossbody', 'ဘေးလွယ်အိတ်', 29500, 36000, 24000, 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=900&q=80', '[{"name":"Champagne Rose","hex":"#E2B6B1"},{"name":"Onyx Black","hex":"#18181B"},{"name":"Emerald Green","hex":"#065F46"}]', 'Wholesale Hot', 4.9, 184, 59, 'Soft Quilted Leather & Gold Chain Strap', '23cm x 15cm x 7.5cm', 'Trendy diamond-quilted women\'s sling bag with adjustable leather-woven gold chain. Popular wholesale item across Myanmar.', 'ပွဲတက်ရော အပြင်သွားပါ လိုက်ဖက်ညီသော စိန်ကွက်ဖော် ရွှေကြိုးချိတ် ဘေးလွယ်အိတ်။ နယ်ပြန်လက်ကားဆိုင်များ အထူးအဝယ်လိုက်သော ဒီဇိုင်းဖြစ်ပါသည် (လက်ကားဈေး - 24,000 ကျပ်)။', 1);
INSERT INTO `products` (`id`, `nameEn`, `nameMm`, `category`, `categoryMm`, `price`, `originalPrice`, `wholesalePrice`, `image`, `colorsJson`, `badge`, `rating`, `reviewsCount`, `stock`, `material`, `dimensions`, `descriptionEn`, `descriptionMm`, `sortOrder`) VALUES ('pp-bag-03', 'Pont Pont Crescent Shoulder & Underarm Bag', 'ပုံ့ပုံ့ လခြမ်းပုံ ခေတ်မီ ပုခုံးလွယ်အိတ်', 'Shoulder', 'ပုခုံးလွယ်အိတ်', 26000, 32000, 21000, 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=900&q=80', '[{"name":"Espresso","hex":"#4A2C2A"},{"name":"Warm Sand","hex":"#D6C5B3"},{"name":"Cherry Wine","hex":"#7F1D1D"}]', 'New Arrival', 4.8, 142, 50, 'Smooth Microfiber Leather', '26cm x 16cm x 6.5cm', 'Chic minimalist crescent shoulder bag with smooth finish and durable brass hardware.', 'လူငယ်အကြိုက် ခေတ်မီလှပသော လခြမ်းပုံစံ ပုခုံးလွယ်အိတ်။ ပေါ့ပါးကျစ်လျစ်ပြီး မည်သည့်ဝတ်စုံနှင့်မဆို စတိုင်ကျလှပစေပါသည် (လက်ကားဈေး - 21,000 ကျပ်)။', 2);
INSERT INTO `products` (`id`, `nameEn`, `nameMm`, `category`, `categoryMm`, `price`, `originalPrice`, `wholesalePrice`, `image`, `colorsJson`, `badge`, `rating`, `reviewsCount`, `stock`, `material`, `dimensions`, `descriptionEn`, `descriptionMm`, `sortOrder`) VALUES ('pp-bag-04', 'Pont Pont Pearl & Velvet Evening Purse', 'ပုံ့ပုံ့ ပုလဲစီ ပွဲတက် လက်ကိုင်အိတ်နှင့် ပိုက်ဆံအိတ်', 'Clutch', 'ပိုက်ဆံအိတ် / ပွဲတက်အိတ်', 32000, 39000, 26500, 'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?auto=format&fit=crop&w=900&q=80', '[{"name":"Ruby Red","hex":"#991B1B"},{"name":"Royal Gold","hex":"#CA8A04"},{"name":"Pearl White","hex":"#FAF8F5"}]', 'Best Seller', 5, 98, 30, 'Satin Velvet & Pearl Clasp Frame', '21cm x 12cm x 5cm', 'Elegant pearl-clasp evening clutch and purse for weddings and ceremonies. Comes with detachable chain strap.', 'အလှူ၊ မင်္ဂလာဆောင်နှင့် ပွဲလမ်းသဘင်များအတွက် အထူးသင့်တော်သော ပုလဲစီ ပွဲတက်လက်ကိုင်အိတ်။ ဖြုတ်တပ်နိုင်သော ကြိုးအရှည်ပါဝင်ပါသည် (လက်ကားဈေး - 26,500 ကျပ်)။', 3);
INSERT INTO `products` (`id`, `nameEn`, `nameMm`, `category`, `categoryMm`, `price`, `originalPrice`, `wholesalePrice`, `image`, `colorsJson`, `badge`, `rating`, `reviewsCount`, `stock`, `material`, `dimensions`, `descriptionEn`, `descriptionMm`, `sortOrder`) VALUES ('pp-bag-05', 'Pont Pont Top-Handle Satchel (3-Compartment)', 'ပုံ့ပုံ့ အကန့် (၃) ကန့်ပါ လက်ဆွဲနှင့် ဘေးလွယ်အိတ်', 'Shoulder', 'ပုခုံးလွယ်အိတ်', 42000, 50000, 35000, 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=900&q=80', '[{"name":"Cognac Tan","hex":"#B45309"},{"name":"Classic Black","hex":"#171717"},{"name":"Olive Taupe","hex":"#6B705C"}]', 'Wholesale Hot', 4.9, 163, 38, 'Scratch-Resistant Saffiano Textured Leather', '29cm x 22cm x 12cm', 'Structured triple-compartment handbag with top handle and detachable long sling strap.', 'အစင်းရာခံနိုင်ရည်ရှိသော Saffiano သားရေသားဖြင့် ပြုလုပ်ထားပြီး အကန့် (၃) ကန့် ပါဝင်သောကြောင့် ပစ္စည်းများကို စနစ်တကျ ခွဲခြားထည့်နိုင်ပါသည် (လက်ကားဈေး - 35,000 ကျပ်)။', 4);
INSERT INTO `products` (`id`, `nameEn`, `nameMm`, `category`, `categoryMm`, `price`, `originalPrice`, `wholesalePrice`, `image`, `colorsJson`, `badge`, `rating`, `reviewsCount`, `stock`, `material`, `dimensions`, `descriptionEn`, `descriptionMm`, `sortOrder`) VALUES ('pp-bag-06', 'Pont Pont Unisex Waterproof Travel & Daily Backpack', 'ပုံ့ပုံ့ ရေစိုခံ ကျား/မ နှစ်မျိုးသုံး ကျောပိုးအိတ်', 'Backpack', 'ကျောပိုးအိတ်', 35000, 42000, 28500, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=900&q=80', '[{"name":"Mocha Brown","hex":"#78350F"},{"name":"Jet Black","hex":"#111827"},{"name":"Blush Pink","hex":"#F4ACB7"}]', 'New Arrival', 4.8, 112, 55, 'Waterproof Soft Leather & Nylon Lining', '26cm x 31cm x 14cm', 'Versatile waterproof backpack suitable for both men and women, travel, and school use.', 'အမျိုးသားရော အမျိုးသမီးပါ အသုံးပြုနိုင်သော ရေစိုခံ သားရေ ကျောပိုးအိတ်။ ခရီးသွားနှင့် ကျောင်းသုံးအတွက် အထူးခိုင်ခံ့ပါသည် (လက်ကားဈေး - 28,500 ကျပ်)။', 5);
INSERT INTO `products` (`id`, `nameEn`, `nameMm`, `category`, `categoryMm`, `price`, `originalPrice`, `wholesalePrice`, `image`, `colorsJson`, `badge`, `rating`, `reviewsCount`, `stock`, `material`, `dimensions`, `descriptionEn`, `descriptionMm`, `sortOrder`) VALUES ('pp-bag-07', 'Pont Pont Woven Bucket Sling Bag', 'ပုံ့ပုံ့ ရက်လုပ် ဒီဇိုင်းဆန်း ပုံးပုံစံ ဘေးလွယ်အိတ်', 'Crossbody', 'ဘေးလွယ်အိတ်', 28000, 34000, 23000, 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?auto=format&fit=crop&w=900&q=80', '[{"name":"Warm Hazelnut","hex":"#A16207"},{"name":"Creamy Oat","hex":"#EAE0D5"}]', 'Sale', 4.8, 89, 32, 'Hand-Woven PU Leather with Inner Pouch', '20cm x 22cm x 14cm', 'Artisanal woven texture bucket bag with removable inner drawstring pouch.', 'အနုစိတ်ရက်လုပ်ထားသော ဒီဇိုင်းဆန်း ဘေးလွယ်အိတ်ဖြစ်ပြီး အတွင်းအိတ်အပို တစ်လုံးပါဝင်ပါသည် (လက်ကားဈေး - 23,000 ကျပ်)။', 6);
INSERT INTO `products` (`id`, `nameEn`, `nameMm`, `category`, `categoryMm`, `price`, `originalPrice`, `wholesalePrice`, `image`, `colorsJson`, `badge`, `rating`, `reviewsCount`, `stock`, `material`, `dimensions`, `descriptionEn`, `descriptionMm`, `sortOrder`) VALUES ('pp-bag-08', 'Pont Pont Luxury Long Zip Wallet & Clutch', 'ပုံ့ပုံ့ ဇစ်ဆွဲ ပိုက်ဆံအိတ်ရှည်နှင့် လက်ကိုင်အိတ်', 'Clutch', 'ပိုက်ဆံအိတ် / ပွဲတက်အိတ်', 18500, 23000, 14500, 'https://images.unsplash.com/photo-1575032617751-6ddec2089882?auto=format&fit=crop&w=900&q=80', '[{"name":"Champagne Gold","hex":"#D4AF37"},{"name":"Midnight Navy","hex":"#1E3A8A"},{"name":"Rose Quartz","hex":"#FDA4AF"}]', 'Wholesale Hot', 4.9, 240, 80, 'Satin & Textured Leather Multi-Card Slot', '20cm x 10.5cm x 3.5cm', 'Spacious long wallet and clutch that fits smartphones, cash notes, and 12 cards effortlessly.', 'ဖုန်းအကြီး၊ ငွေစက္ကူနှင့် ကတ်များ လုံလောက်စွာ ထည့်သွင်းနိုင်သော ပုံ့ပုံ့ ပိုက်ဆံအိတ်ရှည်။ လက်ဆောင်ပေးရန်နှင့် လက်ကားပြန်ရောင်းရန် အရောင်းရဆုံးပစ္စည်း (လက်ကားဈေး - 14,500 ကျပ်)။', 7);

-- Dumping data for table `announcements`
INSERT INTO `announcements` (`id`, `title`, `content`, `tag`, `image`, `createdAt`, `authorName`) VALUES ('ann-03', 'နယ်ဝေး လက်ကားအော်ဒါများ နေ့ချင်း ကားဂိတ်တင်ပေးနေပါပြီ', 'နယ်ပြန်ရောင်းချလိုသော လက်ကားမိတ်ဆွေများအတွက် အိတ် (၅) လုံးနှင့်အထက် လက်ကားဈေးနှုန်းဖြင့် မှာယူနိုင်ပြီး မန္တလေးမှ မြန်မာနိုင်ငံအနှံ့ ကားဂိတ်များသို့ နေ့ချင်း အမြန်ပို့ဆောင်ပေးနေပါသည်။ ဖုန်း 09-963000502 သို့ ဆက်သွယ်နိုင်ပါသည်။', 'Wholesale', 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=900&q=80', '2026-11-16T10:00:00Z', 'မပုံ့ (Admin)');
INSERT INTO `announcements` (`id`, `title`, `content`, `tag`, `image`, `createdAt`, `authorName`) VALUES ('ann-02', 'ဒီတစ်ပတ် အသစ်ဝင်ထားသော ဒီဇိုင်းဆန်း လက်ကိုင်အိတ်များ ရောက်ရှိပါပြီ!', 'မန္တလေး ဆိုင်ခွဲ (၁)၊ (၂)၊ (၃) အားလုံးတွင် 2026 ဒီဇိုင်းသစ် Tote Bags, Sling Bags နှင့် Wallets အသစ်များ ရောက်ရှိနေပါပြီ။ လက်လီရော လက်ကားပါ စိတ်ကြိုက်ရွေးချယ် မှာယူနိုင်ပါသည်။', 'New Arrival', 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=900&q=80', '2026-11-16T09:30:00Z', 'မပုံ့ (Admin)');
INSERT INTO `announcements` (`id`, `title`, `content`, `tag`, `image`, `createdAt`, `authorName`) VALUES ('ann-01', 'ပုံ့ပုံ့ အိတ်ဆိုင် အထူး Promotion — 10% OFF ကူပွန်!', 'အွန်လိုင်းမှ ဝယ်ယူအားပေးသူများအတွက် PONTPONT10 ကူပွန်ကုဒ်ကို အသုံးပြုပြီး အိတ်တိုင်းအတွက် 10% လျှော့ဈေး ရယူနိုင်ပါပြီ။ ၁ သိန်းကျပ်အထက် ဝယ်ယူပါက ပို့ဆောင်ခ အခမဲ့ (Free Delivery) ဖြစ်ပါသည်။', 'Promotion', 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=900&q=80', '2026-11-16T08:00:00Z', 'မပုံ့ (Admin)');

-- Dumping data for table `users`
-- Pre-configured Admin User: admin@pontpont.com (Password: admin123)
-- Pre-configured Demo User: customer@pontpont.com (Password: 123456)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `city`, `address`, `role`, `createdAt`) VALUES
('usr-admin-01', 'မပုံ့ (Shop Admin)', 'admin@pontpont.com', '$2y$10$QP0IgyHZhotviPRX2IEUcucFvG26Lh12hhK0M2EzSKX7Ni24qTrAi', '09963000502', 'Mandalay (မန္တလေး - မြို့တွင်း)', '၇၃ လမ်း၊ ၃၆ လမ်းနှင့် ၃၇ လမ်းကြား၊ မန္တလေးမြို့', 'admin', '2026-11-16T10:00:00Z'),
('usr-cust-01', 'မမြတ်နိုး (Customer)', 'customer@pontpont.com', '$2y$10$QP0IgyHZhotviPRX2IEUcucFvG26Lh12hhK0M2EzSKX7Ni24qTrAi', '09750072752', 'Yangon (ရန်ကုန်)', 'လှည်းတန်း၊ ကမာရွတ်မြို့နယ်', 'customer', '2026-11-16T11:00:00Z');

-- Dumping data for table `settings`
INSERT INTO `settings` (`key`, `value`) VALUES
('theme', 'luxury-gold'),
('shop_name', 'Pont Pont Handbag Shop'),
('shop_name_mm', 'ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး (မန္တလေး)'),
('phone_1', '09-963000502'),
('phone_2', '09-750072752'),
('kpay_account', '09-963000502 / 09-750072752 (Ma Pont Pont)'),
('telegram_bot_token', ''),
('telegram_chat_id', ''),
('telegram_enabled', '0'),
('app_version', '1.2.0'),
('app_min_version', '1.0.0'),
('app_download_url', 'https://pontpont.com/download');

-- Dumping data for table `coupons`
INSERT INTO `coupons` (`id`, `code`, `discountType`, `discountValue`, `minOrderAmount`, `usageLimit`, `usedCount`, `expiresAt`, `isActive`, `createdAt`) VALUES
('cpn-01', 'PONTPONT10', 'percent', 10, 0, 0, 0, NULL, 1, '2026-11-23T00:00:00Z'),
('cpn-02', 'PONTWELCOME', 'fixed', 5000, 30000, 100, 0, NULL, 1, '2026-11-23T00:00:00Z'),
('cpn-03', 'VIPLUXURY', 'percent', 15, 100000, 50, 0, NULL, 1, '2026-11-23T00:00:00Z');

-- --------------------------------------------------------
-- Table structure for `login_attempts` (Rate Limiting)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `ip` VARCHAR(45) NOT NULL PRIMARY KEY,
  `attempts` INT NOT NULL DEFAULT 1,
  `lastAttempt` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `chatbot_config`
INSERT INTO `chatbot_config` (`id`, `provider`, `apiUrl`, `apiKey`, `model`, `systemPrompt`, `botName`, `welcomeMessageMm`, `welcomeMessageEn`, `enabled`, `updatedAt`) VALUES
(1, 'mock', '', '', 'gemini-1.5-flash', 'You are \"Pont Pont AI\" (ပုံ့ပုံ့ AI အကူအညီ), the polite, friendly and helpful customer assistant for \"Pont Pont Handbag Shop\" (ပုံ့ပုံ့ အိတ်အမျိုးမျိုး ရောင်းဝယ်ရေး) based in Mandalay, Myanmar.\n\nKey Information to strictly use:\n- Owner: Ma Pont Pont (မပုံ့)\n- Main Branch (ပင်မဆိုင်): 65th Street, Between 107th & 108th Streets, Mandalay (၆၅ လမ်း၊ ၁၀၇×၁၀၈ လမ်းကြား၊ မန္တလေးမြို့)\n- Branch 2: 73rd Street, Between 36th & 37th Streets, Mandalay (၇၃ လမ်း၊ ၃၆×၃၇ လမ်းကြား၊ မန္တလေးမြို့)\n- Branch 3: 78th Street, Near Thakhun Taing Market, Mandalay (၇၈ လမ်း၊ တံခွန်တိုင်ဈေးအနီး)\n- Contact Phone / Viber: 09-963000502, 09-750072752, 09-404448881\n- Delivery: Mandalay City 2,500 Ks (COD available), Yangon 3,500 Ks, Other cities 4,000 Ks via bus gates. Free delivery for orders 100,000 Ks and above.\n- Payment: KBZPay (09-963000502), WavePay (09-963000502).\n- Wholesale: Minimum 5 to 10 bags for wholesale discount.\n\nAlways reply politely in natural Myanmar language (Burmese) with warm emojis. Keep answers concise, helpful, and courteous.', 'ပုံ့ပုံ့ AI အကူအညီ', 'မင်္ဂလာပါရှင် 👋 ပုံ့ပုံ့ အိတ်ဆိုင် (မန္တလေး) မှ ကြိုဆိုပါတယ်။ ဘာများ ကူညီပေးရမလဲရှင်။', 'Hello! Welcome to Pont Pont Handbag Shop Mandalay. How can I assist you today?', 1, '2026-11-23T03:00:00Z');

COMMIT;

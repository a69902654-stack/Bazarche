<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
$auth->require_user();

$page_title = 'پروفایل کاربری';

// Get user data
$user_id = $_SESSION['user_id'];
$db = Database::getInstance();

// Get user information
$user_query = "SELECT * FROM users WHERE id = ?";
$user = $db->fetchRow($user_query, [$user_id]);

// Get user orders count
$orders_count = $db->fetchRow("SELECT COUNT(*) as count FROM orders WHERE user_id = ?", [$user_id]);
$total_orders = $orders_count['count'];

// Get user wishlist count
$wishlist_count = $db->fetchRow("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?", [$user_id]);
$total_wishlist = $wishlist_count['count'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    
    // Update user information
    $db->update('users', [
        'full_name' => $full_name,
        'phone' => $phone,
        'address' => $address,
        'email' => $email
    ], 'id = ?', [$user_id]);
    
    // Update session
    $_SESSION['full_name'] = $full_name;
    
    // Redirect to show success message
    header("Location: profile.php?success=updated");
    exit;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $db->update('users', [
                    'password' => $hashed_password
                ], 'id = ?', [$user_id]);
                
                header("Location: profile.php?success=password_updated");
                exit;
            } else {
                $error = "رمز عبور جدید باید حداقل 6 کاراکتر باشد";
            }
        } else {
            $error = "رمز عبور جدید و تأیید آن یکسان نیستند";
        }
    } else {
        $error = "رمز عبور فعلی نادرست است";
    }
}

// Success messages
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>

<!DOCTYPE html>
<html class="light" dir="rtl" lang="fa"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
<!-- Google Fonts: Inter and Material Symbols -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Theme Configuration -->
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Custom scrollbar for cleaner look */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-[#0d121b] font-display min-h-screen flex flex-col overflow-hidden">
<!-- Top Navigation -->
<header class="sticky top-0 z-50 flex items-center justify-between border-b border-solid border-[#e7ebf3] bg-white dark:bg-[#1a202c] px-6 py-3 shadow-sm">
<div class="flex items-center gap-4 text-[#0d121b] dark:text-white">
<div class="size-8 text-primary flex items-center justify-center">
<!-- Logo Icon -->
<svg class="w-full h-full" fill="none" viewbox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
<path clip-rule="evenodd" d="M24 0.757355L47.2426 24L24 47.2426L0.757355 24L24 0.757355ZM21 35.7574V12.2426L9.24264 24L21 35.7574Z" fill="currentColor" fill-rule="evenodd"></path>
</svg>
</div>
<h2 class="text-[#0d121b] dark:text-white text-xl font-bold leading-tight tracking-[-0.015em]">فروشگاه آنلاین</h2>
</div>
<div class="flex flex-1 justify-end items-center gap-6">
<!-- Search Bar -->
<label class="hidden md:flex flex-col min-w-40 h-10 w-96">
<div class="flex w-full flex-1 items-stretch rounded-lg h-full bg-[#f0f2f5] dark:bg-gray-800">
<div class="text-[#4c669a] flex items-center justify-center px-4">
<span class="material-symbols-outlined text-[20px]">search</span>
</div>
<input class="flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg bg-transparent text-[#0d121b] dark:text-white focus:outline-0 placeholder:text-[#4c669a] px-2 text-sm font-normal leading-normal border-none focus:ring-0" placeholder="جستجو در محصولات..."/>
</div>
</label>
<!-- Actions -->
<div class="flex gap-3 items-center">
<a href="../cart.php"><button class="relative flex items-center justify-center size-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-[#0d121b] dark:text-white">
<span class="material-symbols-outlined">shopping_cart</span>
<?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
    <span class="absolute top-1 right-1 size-2 bg-red-500 rounded-full"></span>
<?php endif; ?>
</button></a>
<a href="../index.php"><button class="flex items-center justify-center size-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-[#0d121b] dark:text-white">
<span class="material-symbols-outlined">home</span>
</button></a>
</div>
</div>
</header>

<!-- Main Layout -->
<div class="flex flex-1 overflow-hidden">
<!-- Sidebar Navigation -->
<aside class="hidden lg:flex w-72 flex-col justify-between border-l border-[#e7ebf3] bg-white dark:bg-[#1a202c] p-6 overflow-y-auto">
<div class="flex flex-col gap-8">
<!-- User Brief -->
<div class="flex gap-4 items-center">
<div class="bg-center bg-no-repeat bg-cover rounded-full size-12 shadow-sm" data-alt="User profile picture" style='background-image: url("https://picsum.photos/seed/<?php echo $user['id']; ?>/100/100.jpg");'></div>
<div class="flex flex-col">
<h1 class="text-[#0d121b] dark:text-white text-base font-bold leading-normal"><?php echo htmlspecialchars($user['full_name']); ?></h1>
<p class="text-[#4c669a] text-xs font-normal leading-normal">کاربر عادی</p>
</div>
</div>
<!-- Navigation Links -->
<nav class="flex flex-col gap-2">
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#4c669a] hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-[#0d121b] dark:hover:text-white transition-colors" href="dashboard.php">
<span class="material-symbols-outlined">dashboard</span>
<span class="text-sm font-medium leading-normal">داشبورد</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#4c669a] hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-[#0d121b] dark:hover:text-white transition-colors" href="orders.php">
<span class="material-symbols-outlined">inventory_2</span>
<span class="text-sm font-medium leading-normal">سفارش‌های من</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#4c669a] hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-[#0d121b] dark:hover:text-white transition-colors" href="products.php">
<span class="material-symbols-outlined">store</span>
<span class="text-sm font-medium leading-normal">محصولات من</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#4c669a] hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-[#0d121b] dark:hover:text-white transition-colors" href="wishlist.php">
<span class="material-symbols-outlined">favorite</span>
<span class="text-sm font-medium leading-normal">علاقه‌مندی‌ها</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary transition-colors" href="#">
<span class="material-symbols-outlined fill-1">manage_accounts</span>
<span class="text-sm font-medium leading-normal">جزئیات حساب</span>
</a>
</nav>
</div>
<div class="mt-auto pt-6 border-t border-[#e7ebf3]">
<a href="../logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-500 hover:bg-red-50 transition-colors">
<span class="material-symbols-outlined">logout</span>
<span class="text-sm font-medium leading-normal">خروج از حساب</span>
</a>
</div>
</aside>

<!-- Main Content Scrollable Area -->
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-10">
<div class="max-w-6xl mx-auto flex flex-col gap-8">
<!-- Page Heading -->
<div class="flex flex-wrap justify-between items-end gap-4">
<div class="flex flex-col gap-2">
<h1 class="text-[#0d121b] dark:text-white text-3xl font-bold leading-tight">سلام، <?php echo htmlspecialchars($user['full_name']); ?> 👋</h1>
<p class="text-[#4c669a] text-sm font-normal">به پنل کاربری خود خوش آمدید. آخرین فعالیت‌های شما در اینجا قابل مشاهده است.</p>
</div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="flex flex-col justify-between gap-4 rounded-xl bg-white dark:bg-[#1a202c] p-6 shadow-sm border border-[#e7ebf3] dark:border-gray-700 hover:shadow-md transition-shadow">
<div class="flex justify-between items-start">
<div class="flex flex-col gap-1">
<p class="text-[#4c669a] text-sm font-medium">سفارش‌های من</p>
<h3 class="text-[#0d121b] dark:text-white text-3xl font-bold"><?php echo $total_orders; ?></h3>
</div>
<div class="size-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
<span class="material-symbols-outlined">local_shipping</span>
</div>
</div>
<a href="orders.php" class="text-xs text-primary font-medium hover:underline">مشاهده سفارش‌ها →</a>
</div>
<div class="flex flex-col justify-between gap-4 rounded-xl bg-white dark:bg-[#1a202c] p-6 shadow-sm border border-[#e7ebf3] dark:border-gray-700 hover:shadow-md transition-shadow">
<div class="flex justify-between items-start">
<div class="flex flex-col gap-1">
<p class="text-[#4c669a] text-sm font-medium">محصولات من</p>
<h3 class="text-[#0d121b] dark:text-white text-3xl font-bold"><?php echo count($products_obj->get_user_products($user_id)); ?></h3>
</div>
<div class="size-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center">
<span class="material-symbols-outlined">store</span>
</div>
</div>
<a href="products.php" class="text-xs text-primary font-medium hover:underline">مشاهده محصولات →</a>
</div>
<div class="flex flex-col justify-between gap-4 rounded-xl bg-white dark:bg-[#1a202c] p-6 shadow-sm border border-[#e7ebf3] dark:border-gray-700 hover:shadow-md transition-shadow">
<div class="flex justify-between items-start">
<div class="flex flex-col gap-1">
<p class="text-[#4c669a] text-sm font-medium">علاقه‌مندی‌ها</p>
<h3 class="text-[#0d121b] dark:text-white text-3xl font-bold"><?php echo $total_wishlist; ?></h3>
</div>
<div class="size-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center">
<span class="material-symbols-outlined">favorite</span>
</div>
</div>
<a href="wishlist.php" class="text-xs text-primary font-medium hover:underline">مشاهده لیست →</a>
</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
<!-- Personal Information Section (Takes 2/3 width on LG) -->
<div class="lg:col-span-2 flex flex-col gap-6">
<!-- Personal Information Card -->
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 p-6 shadow-sm">
<div class="flex items-center justify-between mb-6">
<h2 class="text-[#0d121b] dark:text-white text-lg font-bold">اطلاعات شخصی</h2>
<a href="profile.php" class="text-primary text-sm hover:underline">ویرایش</a>
</div>
<form method="post" class="space-y-6">
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div>
<label for="full_name" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">نام و نام خانوادگی *</label>
<input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" 
       class="w-full px-4 py-2 bg-[#f0f2f5] dark:bg-gray-800 border border-[#e7ebf3] dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
       required>
</div>
<div>
<label for="email" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">ایمیل *</label>
<input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
       class="w-full px-4 py-2 bg-[#f0f2f5] dark:bg-gray-800 border border-[#e7ebf3] dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
       required>
</div>
</div>
<div>
<label for="phone" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">شماره تماس *</label>
<input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" 
       class="w-full px-4 py-2 bg-[#f0f2f5] dark:bg-gray-800 border border-[#e7ebf3] dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
       required>
</div>
<div>
<label for="address" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">آدرس *</label>
<textarea id="address" name="address" rows="3" 
          class="w-full px-4 py-2 bg-[#f0f2f5] dark:bg-gray-800 border border-[#e7ebf3] dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white resize-none" 
          required><?php echo htmlspecialchars($user['address']); ?></textarea>
</div>
<div class="flex gap-4 pt-4">
<button type="submit" class="flex-1 px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-primary/30">
<span class="material-symbols-outlined text-[20px] mr-2">save</span>
ذخیره تغییرات
</button>
<a href="dashboard.php" class="flex-1 px-6 py-3 bg-[#f0f2f5] dark:bg-gray-800 text-[#0d121b] dark:text-white font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
<span class="material-symbols-outlined text-[20px] mr-2">arrow_back</span>
بازگشت به داشبورد
</a>
</div>
</form>
</div>

<!-- Change Password Card -->
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 p-6 shadow-sm">
<div class="flex items-center justify-between mb-6">
<h2 class="text-[#0d121b] dark:text-white text-lg font-bold">تغییر رمز عبور</h2>
</div>
<form method="post" class="space-y-6">
<input type="hidden" name="change_password" value="1">
<div>
<label for="current_password" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">رمز عبور فعلی *</label>
<input type="password" id="current_password" name="current_password" 
       class="w-full px-4 py-2 bg-[#f0f2f5] dark:bg-gray-800 border border-[#e7ebf3] dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
       required>
</div>
<div>
<label for="new_password" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">رمز عبور جدید *</label>
<input type="password" id="new_password" name="new_password" 
       class="w-full px-4 py-2 bg-[#f0f2f5] dark:bg-gray-800 border border-[#e7ebf3] dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
       required>
</div>
<div>
<label for="confirm_password" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">تأیید رمز عبور جدید *</label>
<input type="password" id="confirm_password" name="confirm_password" 
       class="w-full px-4 py-2 bg-[#f0f2f5] dark:bg-gray-800 border border-[#e7ebf3] dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
       required>
</div>
<button type="submit" class="w-full px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-primary/30">
<span class="material-symbols-outlined text-[20px] mr-2">lock</span>
تغییر رمز عبور
</button>
</form>
</div>
</div>

<!-- Quick Details (Takes 1/3 width on LG) -->
<div class="flex flex-col gap-6">
<!-- Account Info Card -->
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 p-6 shadow-sm flex flex-col gap-4">
<div class="flex justify-between items-center">
<h3 class="font-bold text-[#0d121b] dark:text-white">اطلاعات حساب</h3>
</div>
<div class="flex flex-col gap-3">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-[#4c669a] text-[20px]">badge</span>
<div>
<p class="text-xs text-[#4c669a">نام کاربری</p>
<p class="text-sm text-[#0d121b] dark:text-white"><?php echo htmlspecialchars($user['username']); ?></p>
</div>
</div>
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-[#4c669a] text-[20px]">calendar_month</span>
<div>
<p class="text-xs text-[#4c669a">عضو از</p>
<p class="text-sm text-[#0d121b] dark:text-white"><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></p>
</div>
</div>
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-[#4c669a] text-[20px]">mail</span>
<div>
<p class="text-xs text-[#4c669a">ایمیل</p>
<p class="text-sm text-[#0d121b] dark:text-white"><?php echo htmlspecialchars($user['email']); ?></p>
</div>
</div>
</div>
</div>

<!-- Default Address Card -->
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 p-6 shadow-sm flex flex-col gap-4">
<div class="flex justify-between items-center">
<h3 class="font-bold text-[#0d121b] dark:text-white">آدرس پیش‌فرض</h3>
<button class="text-primary text-sm hover:underline">تغییر</button>
</div>
<div class="relative h-24 w-full rounded-lg overflow-hidden">
<div class="bg-cover bg-center w-full h-full opacity-80" data-location="Map" style='background-image: url("https://picsum.photos/seed/address/400/200.jpg");'></div>
</div>
<p class="text-sm text-[#4c669a] leading-relaxed">
<?php echo htmlspecialchars($user['address']); ?>
</p>
</div>

<!-- Quick Actions Card -->
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 p-6 shadow-sm flex flex-col gap-4">
<h3 class="font-bold text-[#0d121b] dark:text-white">اقدامات سریع</h3>
<div class="space-y-2">
<a href="add-product.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-[#f0f2f5] dark:bg-gray-800 text-[#0d121b] dark:text-white hover:bg-primary hover:text-white transition-colors">
<span class="material-symbols-outlined">add</span>
<span class="text-sm font-medium">افزودن محصول</span>
</a>
<a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-[#f0f2f5] dark:bg-gray-800 text-[#0d121b] dark:text-white hover:bg-primary hover:text-white transition-colors">
<span class="material-symbols-outlined">shopping_bag</span>
<span class="text-sm font-medium">سفارشات من</span>
</a>
<a href="products.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-[#f0f2f5] dark:bg-gray-800 text-[#0d121b] dark:text-white hover:bg-primary hover:text-white transition-colors">
<span class="material-symbols-outlined">inventory_2</span>
<span class="text-sm font-medium">محصولات من</span>
</a>
</div>
</div>
</div>
</div>
</div>
</main>
</div>
</body>
</html>
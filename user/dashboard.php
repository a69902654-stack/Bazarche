<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/products.php';
require_once '../includes/orders.php';
require_once '../includes/functions.php';

// Check if user is logged in
$auth->require_user();

$page_title = 'داشبورد کاربری';

// Get user information
$user_id = $_SESSION['user_id'];
$db = Database::getInstance();

// Get user data
$user_query = "SELECT * FROM users WHERE id = ?";
$user = $db->fetchRow($user_query, [$user_id]);

// Get user's products
$products_obj = new Products();
$user_products = $products_obj->get_user_products($user_id);

// Get user's orders
$orders_obj = new Orders();
$user_orders = $orders_obj->get_user_orders($user_id, 5); // Get last 5 orders

// Get user's statistics
$total_products = count($user_products);
$total_orders = count($user_orders);
$pending_orders = 0;
$processing_orders = 0;
$delivered_orders = 0;

foreach ($user_orders as $order) {
    switch ($order['status']) {
        case 'pending':
            $pending_orders++;
            break;
        case 'processing':
            $processing_orders++;
            break;
        case 'delivered':
            $delivered_orders++;
            break;
    }
}
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
<div class="bg-center bg-no-repeat bg-cover rounded-full size-12 shadow-sm" data-alt="User profile picture" style='background-image: url("https://picsum.photos/seed/<?php echo $user_id; ?>/100/100.jpg");'></div>
<div class="flex flex-col">
<h1 class="text-[#0d121b] dark:text-white text-base font-bold leading-normal"><?php echo htmlspecialchars($_SESSION['user_full_name']); ?></h1>
<p class="text-[#4c669a] text-xs font-normal leading-normal">کاربر عادی</p>
</div>
</div>
<!-- Navigation Links -->
<nav class="flex flex-col gap-2">
<a class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary transition-colors" href="#">
<span class="material-symbols-outlined fill-1">dashboard</span>
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
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#4c669a] hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-[#0d121b] dark:hover:text-white transition-colors" href="profile.php">
<span class="material-symbols-outlined">manage_accounts</span>
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
<h1 class="text-[#0d121b] dark:text-white text-3xl font-bold leading-tight">سلام، <?php echo htmlspecialchars($_SESSION['user_full_name']); ?> 👋</h1>
<p class="text-[#4c669a] text-sm font-normal">به پنل کاربری خود خوش آمدید. آخرین فعالیت‌های شما در اینجا قابل مشاهده است.</p>
</div>
<a href="add-product.php" class="bg-primary hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
<span class="material-symbols-outlined text-[20px]">add</span>
افزودن محصول
</a>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="flex flex-col justify-between gap-4 rounded-xl bg-white dark:bg-[#1a202c] p-6 shadow-sm border border-[#e7ebf3] dark:border-gray-700 hover:shadow-md transition-shadow">
<div class="flex justify-between items-start">
<div class="flex flex-col gap-1">
<p class="text-[#4c669a] text-sm font-medium">محصولات من</p>
<h3 class="text-[#0d121b] dark:text-white text-3xl font-bold"><?php echo $total_products; ?></h3>
</div>
<div class="size-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
<span class="material-symbols-outlined">store</span>
</div>
</div>
<a href="products.php" class="text-xs text-primary font-medium hover:underline">مشاهده محصولات →</a>
</div>
<div class="flex flex-col justify-between gap-4 rounded-xl bg-white dark:bg-[#1a202c] p-6 shadow-sm border border-[#e7ebf3] dark:border-gray-700 hover:shadow-md transition-shadow">
<div class="flex justify-between items-start">
<div class="flex flex-col gap-1">
<p class="text-[#4c669a] text-sm font-medium">سفارش‌های من</p>
<h3 class="text-[#0d121b] dark:text-white text-3xl font-bold"><?php echo $total_orders; ?></h3>
</div>
<div class="size-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center">
<span class="material-symbols-outlined">inventory_2</span>
</div>
</div>
<a href="orders.php" class="text-xs text-primary font-medium hover:underline">مشاهده سفارشات →</a>
</div>
<div class="flex flex-col justify-between gap-4 rounded-xl bg-white dark:bg-[#1a202c] p-6 shadow-sm border border-[#e7ebf3] dark:border-gray-700 hover:shadow-md transition-shadow">
<div class="flex justify-between items-start">
<div class="flex flex-col gap-1">
<p class="text-[#4c669a] text-sm font-medium">در حال پردازش</p>
<h3 class="text-[#0d121b] dark:text-white text-3xl font-bold"><?php echo $processing_orders; ?></h3>
</div>
<div class="size-10 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center">
<span class="material-symbols-outlined">pending</span>
</div>
</div>
<a href="orders.php" class="text-xs text-primary font-medium hover:underline">مشاهده سفارشات →</a>
</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
<!-- Recent Orders Section (Takes 2/3 width on LG) -->
<div class="lg:col-span-2 flex flex-col gap-4">
<div class="flex items-center justify-between">
<h2 class="text-[#0d121b] dark:text-white text-lg font-bold">سفارش‌های اخیر</h2>
<a href="orders.php" class="text-primary text-sm font-medium hover:underline">مشاهده همه</a>
</div>
<?php if (!empty($user_orders)): ?>
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 overflow-hidden shadow-sm">
<div class="overflow-x-auto">
<table class="w-full text-right">
<thead class="bg-gray-50 dark:bg-gray-800 border-b border-[#e7ebf3] dark:border-gray-700">
<tr>
<th class="px-6 py-4 text-xs font-semibold text-[#4c669a] uppercase tracking-wider">شماره سفارش</th>
<th class="px-6 py-4 text-xs font-semibold text-[#4c669a] uppercase tracking-wider">تاریخ</th>
<th class="px-6 py-4 text-xs font-semibold text-[#4c669a] uppercase tracking-wider">وضعیت</th>
<th class="px-6 py-4 text-xs font-semibold text-[#4c669a] uppercase tracking-wider">مبلغ کل</th>
<th class="px-6 py-4 text-xs font-semibold text-[#4c669a] uppercase tracking-wider">عملیات</th>
</tr>
</thead>
<tbody class="divide-y divide-[#e7ebf3] dark:divide-gray-700">
<?php foreach ($user_orders as $order): ?>
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#0d121b] dark:text-white">#<?php echo $order['order_number']; ?></td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-[#4c669a]"><?php echo date('Y/m/d', strtotime($order['created_at'])); ?></td>
<td class="px-6 py-4 whitespace-nowrap">
<?php
$status_class = '';
$status_text = '';
switch ($order['status']) {
    case 'delivered':
        $status_class = 'bg-green-100 text-green-800';
        $status_text = 'تحویل شده';
        break;
    case 'processing':
        $status_class = 'bg-yellow-100 text-yellow-800';
        $status_text = 'در حال پردازش';
        break;
    case 'shipped':
        $status_class = 'bg-blue-100 text-blue-800';
        $status_text = 'ارسال شده';
        break;
    case 'pending':
        $status_class = 'bg-gray-100 text-gray-800';
        $status_text = 'در انتظار';
        break;
    case 'cancelled':
        $status_class = 'bg-red-100 text-red-800';
        $status_text = 'لغو شده';
        break;
}
?>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_class; ?>">
    <?php echo $status_text; ?>
</span>
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-[#0d121b] dark:text-white"><?php echo format_price($order['total_amount']); ?></td>
<td class="px-6 py-4 whitespace-nowrap text-sm">
<a href="orders.php" class="text-primary hover:text-blue-700 font-medium">جزئیات</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php else: ?>
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 p-8 text-center">
<div class="text-gray-400 mb-4">
<span class="material-symbols-outlined text-[64px]">shopping_bag</span>
</div>
<h3 class="text-lg font-medium text-gray-600 dark:text-gray-400 mb-2">هنوز سفارشی ثبت نکرده‌اید</h3>
<p class="text-sm text-gray-500 dark:text-gray-400 mb-6">برای مشاهده سفارشات خود، ابتدا محصولی را خریداری کنید.</p>
<a href="../products.php" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-primary/30">
<span class="material-symbols-outlined text-[20px">shopping</span>
مشاهده محصولات
</a>
</div>
<?php endif; ?>
</div>

<!-- Quick Details (Takes 1/3 width on LG) -->
<div class="flex flex-col gap-6">
<!-- Personal Info Card -->
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 p-6 shadow-sm flex flex-col gap-4">
<div class="flex justify-between items-center">
<h3 class="font-bold text-[#0d121b] dark:text-white">اطلاعات شخصی</h3>
<a href="profile.php" class="text-primary text-sm hover:underline">ویرایش</a>
</div>
<div class="flex flex-col gap-3">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-[#4c669a] text-[20px]">person</span>
<div>
<p class="text-xs text-[#4c669a">نام و نام خانوادگی</p>
<p class="text-sm text-[#0d121b] dark:text-white"><?php echo htmlspecialchars($_SESSION['user_full_name']); ?></p>
</div>
</div>
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-[#4c669a] text-[20px]">mail</span>
<div>
<p class="text-xs text-[#4c669a">ایمیل</p>
<p class="text-sm text-[#0d121b] dark:text-white"><?php echo htmlspecialchars($user['email']); ?></p>
</div>
</div>
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-[#4c669a] text-[20px]">calendar_month</span>
<div>
<p class="text-xs text-[#4c669a">عضو از</p>
<p class="text-sm text-[#0d121b] dark:text-white"><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></p>
</div>
</div>
</div>
</div>

<!-- Quick Actions Card -->
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 p-6 shadow-sm flex flex-col gap-4">
<h3 class="font-bold text-[#0d121b] dark:text-white">اقدامات سریع</h3>
<div class="grid grid-cols-2 gap-3">
<a href="add-product.php" class="flex flex-col items-center justify-center p-4 bg-[#f0f2f5] dark:bg-gray-800 rounded-lg hover:bg-primary hover:text-white transition-colors">
<span class="material-symbols-outlined text-[32px] mb-2">add</span>
<span class="text-sm font-medium">افزودن محصول</span>
</a>
<a href="orders.php" class="flex flex-col items-center justify-center p-4 bg-[#f0f2f5] dark:bg-gray-800 rounded-lg hover:bg-primary hover:text-white transition-colors">
<span class="material-symbols-outlined text-[32px] mb-2">receipt_long</span>
<span class="text-sm font-medium">سفارشات من</span>
</a>
<a href="profile.php" class="flex flex-col items-center justify-center p-4 bg-[#f0f2f5] dark:bg-gray-800 rounded-lg hover:bg-primary hover:text-white transition-colors">
<span class="material-symbols-outlined text-[32px] mb-2">person</span>
<span class="text-sm font-medium">پروفایل کاربری</span>
</a>
<a href="wishlist.php" class="flex flex-col items-center justify-center p-4 bg-[#f0f2f5] dark:bg-gray-800 rounded-lg hover:bg-primary hover:text-white transition-colors">
<span class="material-symbols-outlined text-[32px] mb-2">favorite</span>
<span class="text-sm font-medium">علاقه‌مندی‌ها</span>
</a>
</div>
</div>

<!-- Recent Products Card -->
<?php if (!empty($user_products)): ?>
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 p-6 shadow-sm">
<div class="flex items-center justify-between mb-4">
<h3 class="font-bold text-[#0d121b] dark:text-white">محصولات اخیر</h3>
<a href="products.php" class="text-primary text-sm hover:underline">مشاهده همه</a>
</div>
<div class="space-y-3">
<?php foreach (array_slice($user_products, 0, 3) as $product): ?>
<div class="flex items-center gap-3 p-3 bg-[#f0f2f5] dark:bg-gray-800 rounded-lg">
<?php if (!empty($product['image'])): ?>
<img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-12 h-12 rounded-lg object-cover"/>
<?php else: ?>
<img src="https://via.placeholder.com/48x48?text=No+Image" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-12 h-12 rounded-lg object-cover"/>
<?php endif; ?>
<div class="flex-1">
<h4 class="text-sm font-medium text-[#0d121b] dark:text-white line-clamp-1"><?php echo htmlspecialchars($product['name']); ?></h4>
<p class="text-xs text-[#4c669a]"><?php echo format_price($product['price']); ?></p>
</div>
<span class="text-xs px-2 py-1 rounded-full <?php echo $product['status'] == 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
<?php echo $product['status'] == 'active' ? 'فعال' : 'غیرفعال'; ?>
</span>
</div>
<?php endforeach; ?>
</div>
</div>
<?php else: ?>
<div class="bg-white dark:bg-[#1a202c] rounded-xl border border-[#e7ebf3] dark:border-gray-700 p-6 text-center">
<div class="text-gray-400 mb-4">
<span class="material-symbols-outlined text-[48px]">inventory_2</span>
</div>
<h3 class="text-lg font-medium text-gray-600 dark:text-gray-400 mb-2">هنوز محصولی ثبت نکرده‌اید</h3>
<a href="add-product.php" class="inline-block text-primary text-sm font-medium hover:underline">افزودن محصول اول →</a>
</div>
<?php endif; ?>
</div>
</div>
</div>
</main>
</div>
</body>
</html>
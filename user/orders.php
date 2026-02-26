<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/orders.php';
require_once '../includes/functions.php';

// Check if user is logged in
$auth->require_user();

$page_title = 'سفارشات من';

// Get user orders
$orders_obj = new Orders();
$user_id = $_SESSION['user_id'];

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get status filter
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$where = ["o.user_id = ?"];
$params = [$user_id];

if ($status) {
    $where[] = "o.status = ?";
    $params[] = $status;
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Get total orders for pagination
$total_query = "SELECT COUNT(*) as total FROM orders o " . $where_clause;
$db = Database::getInstance();
$total_result = $db->fetchRow($total_query, $params);
$total_orders = $total_result['total'];
$total_pages = ceil($total_orders / $limit);

// Get orders
$query = "SELECT o.*, u.full_name as user_name, u.phone as user_phone, u.address as user_address
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id
          $where_clause
          ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";

$orders = $db->fetchAll($query, $params);

// Get order status labels
$status_labels = [
    'pending' => 'در انتظار پرداخت',
    'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده',
    'delivered' => 'تحویل داده شده',
    'cancelled' => 'لغو شده'
];

// Get order status colors
$status_colors = [
    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300',
    'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
    'shipped' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300',
    'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300',
    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'
];
?>

<!DOCTYPE html>
<html class="light" dir="rtl" lang="fa"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Vazirmatn:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://cdn.tailwindcss.com?plugins=forms,container-queries" rel="stylesheet"/>
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
                        "display": ["Vazirmatn", "Inter", "sans-serif"],
                        "body": ["Vazirmatn", "Inter", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
<style>
        body {
            font-family: 'Vazirmatn', 'Inter', sans-serif;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-[#0d121b] dark:text-white transition-colors duration-200">
<div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
<!-- Header -->
<header class="sticky top-0 z-50 flex items-center justify-between border-b border-solid border-[#e7ebf3] dark:border-gray-800 bg-white dark:bg-[#1a2233] px-6 py-4 shadow-sm">
<div class="flex items-center gap-8 w-full">
<!-- Logo -->
<div class="flex items-center gap-3 text-[#0d121b] dark:text-white shrink-0">
<div class="flex items-center justify-center size-10 rounded-xl bg-primary/10 text-primary">
<span class="material-symbols-outlined text-[24px]">storefront</span>
</div>
<h2 class="text-lg font-bold leading-tight tracking-[-0.015em] hidden sm:block">فروشگاه آنلاین</h2>
</div>
<!-- Navigation & Actions -->
<div class="flex flex-1 justify-end gap-6 items-center">
<div class="hidden lg:flex items-center gap-6">
<a class="text-[#0d121b] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors" href="../index.php">خانه</a>
<a class="text-[#0d121b] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors" href="../products.php">محصولات</a>
<a class="text-[#0d121b] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors" href="#">تخفیف‌ها</a>
</div>
<div class="flex gap-3">
<a href="../cart.php"><button class="flex size-10 items-center justify-center rounded-xl bg-background-light dark:bg-gray-800 text-[#0d121b] dark:text-white hover:bg-primary hover:text-white transition-all">
<span class="material-symbols-outlined text-[20px]">shopping_cart</span>
</button></a>
<a href="dashboard.php"><button class="flex size-10 items-center justify-center rounded-xl bg-primary text-white flex items-center justify-center">
<span class="material-symbols-outlined text-[20px]">account_circle</span>
</button></a>
</div>
</div>
</div>
</header>

<!-- Main Content -->
<div class="max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
<!-- Breadcrumb -->
<nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
<a class="hover:text-primary transition-colors" href="../index.php">خانه</a>
<span class="text-gray-300 dark:text-gray-600">/</span>
<a class="hover:text-primary transition-colors" href="dashboard.php">داشبورد</a>
<span class="text-gray-300 dark:text-gray-600">/</span>
<span class="text-[#0d121b] dark:text-white font-medium">سفارشات من</span>
</nav>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
<h1 class="text-3xl font-bold text-[#0d121b] dark:text-white">سفارشات من</h1>
</div>

<!-- Status Filter -->
<div class="bg-white dark:bg-[#1a2233] rounded-2xl p-6 border border-[#e7ebf3] dark:border-gray-800 mb-8">
<form method="get" class="flex flex-wrap gap-4">
<div class="flex gap-2">
<a href="orders.php" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">
<span class="material-symbols-outlined text-[20px]">shopping_bag</span>
همه سفارشات
</a>
<?php foreach ($status_labels as $key => $label): ?>
    <a href="orders.php?status=<?php echo $key; ?>" 
       class="px-4 py-2 bg-background-light dark:bg-gray-800 text-[#0d121b] dark:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors <?php echo $status == $key ? 'bg-primary text-white shadow-lg shadow-primary/30' : ''; ?>">
        <?php echo $label; ?>
    </a>
<?php endforeach; ?>
</div>
</form>
</div>

<!-- Orders List -->
<?php if (!empty($orders)): ?>
    <div class="space-y-6">
        <?php foreach ($orders as $order): ?>
            <div class="bg-white dark:bg-[#1a2233] rounded-2xl border border-[#e7ebf3] dark:border-gray-800 overflow-hidden">
                <div class="p-6 border-b border-[#e7ebf3] dark:border-gray-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-[#0d121b] dark:text-white">سفارش شماره #<?php echo $order['id']; ?></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">تاریخ: <?php echo date('Y/m/d H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$order['status']]; ?>">
                                <?php echo $status_labels[$order['status']]; ?>
                            </span>
                            <span class="text-sm font-medium text-[#0d121b] dark:text-white">
                                <?php echo format_price($order['total_amount']); ?> تومان
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Order Items -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">محصولات سفارش داده شده</h4>
                            <div class="space-y-3">
                                <?php 
                                $order_items = $orders_obj->get_order_items($order['id']);
                                foreach ($order_items as $item): ?>
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-full object-cover rounded"/>
                                            <?php else: ?>
                                                <span class="material-symbols-outlined text-[20px] text-gray-400">inventory_2</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <h5 class="text-sm font-medium text-[#0d121b] dark:text-white"><?php echo htmlspecialchars($item['name']); ?></h5>
                                            <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo $item['quantity']; ?> عدد × <?php echo format_price($item['price']); ?> تومان</p>
                                        </div>
                                        <div class="text-sm font-medium text-[#0d121b] dark:text-white">
                                            <?php echo format_price($item['quantity'] * $item['price']); ?> تومان
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Order Details -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">جزئیات سفارش</h4>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">نام و نام خانوادگی:</span>
                                    <span class="text-[#0d121b] dark:text-white"><?php echo htmlspecialchars($order['user_name']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">شماره تماس:</span>
                                    <span class="text-[#0d121b] dark:text-white"><?php echo htmlspecialchars($order['user_phone']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">آدرس:</span>
                                    <span class="text-[#0d121b] dark:text-white"><?php echo htmlspecialchars($order['user_address']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">روش پرداخت:</span>
                                    <span class="text-[#0d121b] dark:text-white">پرداخت آنلاین</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">هزینه ارسال:</span>
                                    <span class="text-[#0d121b] dark:text-white">رایگان</span>
                                </div>
                                <div class="border-t border-[#e7ebf3] dark:border-gray-800 pt-3 flex justify-between">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">مجموع:</span>
                                    <span class="font-bold text-lg text-[#0d121b] dark:text-white"><?php echo format_price($order['total_amount']); ?> تومان</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex gap-3 mt-6 pt-6 border-t border-[#e7ebf3] dark:border-gray-800">
                        <?php if ($order['status'] == 'delivered'): ?>
                            <button class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <span class="material-symbols-outlined text-[20px] mr-2">rate_review</span>
                                ثبت نظر
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] == 'pending'): ?>
                            <form method="post" action="../includes/orders.php" class="flex-1">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="cancel_order" class="w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                                    <span class="material-symbols-outlined text-[20px] mr-2">cancel</span>
                                    لغو سفارش
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <button class="flex-1 px-4 py-2 bg-background-light dark:bg-gray-800 text-[#0d121b] dark:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <span class="material-symbols-outlined text-[20px] mr-2">receipt_long</span>
                            مشاهده فاکتور
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="flex items-center justify-center gap-2 mt-10">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="orders.php?page=<?php echo $i; ?><?php echo $status ? '&status=' . $status : ''; ?>" 
                   class="size-10 flex items-center justify-center rounded-lg border border-[#e7ebf3] dark:border-gray-800 text-[#0d121b] dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 font-medium <?php echo $i == $page ? 'bg-primary text-white shadow-lg shadow-primary/30' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="text-center py-12">
        <div class="text-gray-400 mb-4">
            <span class="material-symbols-outlined text-[64px">shopping_bag</span>
        </div>
        <h3 class="text-lg font-medium text-gray-600 dark:text-gray-400 mb-2">هنوز سفارشی ثبت نکرده‌اید</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">برای مشاهده سفارشات خود، ابتدا محصولی را خریداری کنید.</p>
        <a href="../products.php" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-primary/30">
            <span class="material-symbols-outlined text-[20px]">shopping</span>
            شopping
        </a>
    </div>
<?php endif; ?>
</div>
</div>

<script>
// Add any JavaScript functionality here
</script>
</body></html>
<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/products.php';
require_once '../includes/functions.php';

// Check if user is logged in
$auth->require_user();

$page_title = 'لیست علاقه‌مندی‌ها';

// Get user products
$products_obj = new Products();
$user_id = $_SESSION['user_id'];

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build query for wishlist items
$where = ["w.user_id = ?"];
$params = [$user_id];

if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where[] = "p.category_id = ?";
    $params[] = $category;
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Get total wishlist items for pagination
$total_query = "SELECT COUNT(*) as total FROM wishlist w 
                LEFT JOIN products p ON w.product_id = p.id 
                " . $where_clause;
$db = Database::getInstance();
$total_result = $db->fetchRow($total_query, $params);
$total_items = $total_result['total'];
$total_pages = ceil($total_items / $limit);

// Get wishlist items
$query = "SELECT w.*, p.*, c.name as category_name 
          FROM wishlist w 
          LEFT JOIN products p ON w.product_id = p.id 
          LEFT JOIN categories c ON p.category_id = c.id 
          $where_clause 
          ORDER BY w.created_at DESC LIMIT $limit OFFSET $offset";

$wishlist_items = $db->fetchAll($query, $params);

// Get categories for filter
$categories = $products_obj->get_categories();

// Handle remove from wishlist
if (isset($_GET['remove']) && isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    $remove_query = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $db->delete('wishlist', 'user_id = ? AND product_id = ?', [$user_id, $product_id]);
    header("Location: wishlist.php?success=removed");
    exit;
}
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
<span class="text-[#0d121b] dark:text-white font-medium">لیست علاقه‌مندی‌ها</span>
</nav>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
<h1 class="text-3xl font-bold text-[#0d121b] dark:text-white">لیست علاقه‌مندی‌ها</h1>
</div>

<!-- Success Messages -->
<?php if (isset($_GET['success'])): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <div class="flex items-center gap-2 text-green-700 dark:text-green-300">
            <span class="material-symbols-outlined">check_circle</span>
            <?php
            switch($_GET['success']) {
                case 'removed':
                    echo 'محصول با موفقیت از لیست علاقه‌مندی‌ها حذف شد.';
                    break;
            }
            ?>
        </div>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="bg-white dark:bg-[#1a2233] rounded-2xl p-6 border border-[#e7ebf3] dark:border-gray-800 mb-8">
<form method="get" class="flex flex-wrap gap-4">
<div class="flex-1 min-w-[200px]">
<input type="text" name="search" placeholder="جستجو در علاقه‌مندی‌ها..." 
       value="<?php echo htmlspecialchars($search); ?>"
       class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white">
</div>
<div class="min-w-[150px]">
<select name="category" class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white">
<option value="">همه دسته‌بندی‌ها</option>
<?php foreach ($categories as $category): ?>
    <option value="<?php echo $category['id']; ?>" 
            <?php echo $category == $category['id'] ? 'selected' : ''; ?>>
        <?php echo htmlspecialchars($category['name']); ?>
    </option>
<?php endforeach; ?>
</select>
</div>
<div class="flex gap-2">
<button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">
<span class="material-symbols-outlined text-[20px]">search</span>
جستجو
</button>
<a href="wishlist.php" class="px-4 py-2 bg-background-light dark:bg-gray-800 text-[#0d121b] dark:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
<span class="material-symbols-outlined text-[20px]">refresh</span>
بازنشانی
</a>
</div>
</form>
</div>

<!-- Wishlist Items -->
<?php if (!empty($wishlist_items)): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-10">
        <?php foreach ($wishlist_items as $item): ?>
            <div class="group flex flex-col bg-white dark:bg-[#1a2233] rounded-2xl border border-[#e7ebf3] dark:border-gray-800 overflow-hidden hover:shadow-xl hover:shadow-primary/5 hover:border-primary/30 transition-all duration-300">
                <div class="relative pt-[100%] bg-gray-50 dark:bg-gray-800/50">
                    <?php if (!empty($item['image'])): ?>
                        <img class="absolute inset-0 w-full h-full object-contain p-6 group-hover:scale-110 transition-transform duration-500" 
                             src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                             onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'"/>
                    <?php else: ?>
                        <img class="absolute inset-0 w-full h-full object-contain p-6 group-hover:scale-110 transition-transform duration-500" 
                             src="https://via.placeholder.com/300x300?text=No+Image" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>"/>
                    <?php endif; ?>
                    
                    <?php if ($item['discount_price'] && $item['discount_price'] < $item['price']): ?>
                        <?php 
                        $discount_percentage = round(($item['price'] - $item['discount_price']) / $item['price'] * 100);
                        ?>
                        <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-md shadow-sm"><?php echo $discount_percentage; ?>٪ تخفیف</span>
                    <?php endif; ?>
                    
                    <a href="wishlist.php?remove=1&product_id=<?php echo $item['id']; ?>" 
                       class="absolute top-3 left-3 size-8 rounded-full bg-white dark:bg-gray-700 shadow-sm flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all duration-300"
                       onclick="return confirm('آیا از حذف این محصول از لیست علاقه‌مندی‌ها مطمئن هستید؟');">
                        <span class="material-symbols-outlined text-[20px]">favorite</span>
                    </a>
                </div>
                <div class="p-4 flex flex-col flex-1 gap-2">
                    <?php if (!empty($item['category_name'])): ?>
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-primary font-medium"><?php echo htmlspecialchars($item['category_name']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="text-[#0d121b] dark:text-gray-100 font-bold leading-snug line-clamp-2 min-h-[3rem]">
                        <?php echo htmlspecialchars($item['name']); ?>
                    </h3>
                    
                    <div class="mt-auto pt-4 flex items-end justify-between">
                        <div class="flex flex-col">
                            <?php if ($item['discount_price'] && $item['discount_price'] < $item['price']): ?>
                                <span class="text-xs text-gray-400 line-through decoration-red-500/50">
                                    <?php echo format_price($item['price']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <div class="flex items-center gap-1">
                                <?php if ($item['discount_price'] && $item['discount_price'] < $item['price']): ?>
                                    <span class="text-lg font-black text-[#0d121b] dark:text-white">
                                        <?php echo format_price($item['discount_price']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-lg font-black text-[#0d121b] dark:text-white">
                                        <?php echo format_price($item['price']); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="text-xs text-gray-500 font-light">تومان</span>
                            </div>
                        </div>
                        
                        <div class="flex gap-1">
                            <a href="../product.php?id=<?php echo $item['id']; ?>" 
                               class="size-8 rounded-xl bg-primary text-white flex items-center justify-center hover:bg-blue-700 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">shopping_cart</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="flex items-center justify-center gap-2 mt-auto">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="wishlist.php?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>" 
                   class="size-10 flex items-center justify-center rounded-lg border border-[#e7ebf3] dark:border-gray-800 text-[#0d121b] dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 font-medium <?php echo $i == $page ? 'bg-primary text-white shadow-lg shadow-primary/30' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="text-center py-12">
        <div class="text-gray-400 mb-4">
            <span class="material-symbols-outlined text-[64px]">favorite</span>
        </div>
        <h3 class="text-lg font-medium text-gray-600 dark:text-gray-400 mb-2">هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">برای افزودن محصول به لیست علاقه‌مندی‌ها، روی آیکون ❤️ در صفحه محصولات کلیک کنید.</p>
        <a href="../products.php" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-primary/30">
            <span class="material-symbols-outlined text-[20px]">shopping</span>
            مشاهده محصولات
        </a>
    </div>
<?php endif; ?>
</div>
</div>

<script>
// Add any JavaScript functionality here
</script>
</body></html>
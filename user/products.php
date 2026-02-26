<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/products.php';
require_once '../includes/functions.php';

// Check if user is logged in
$auth->require_user();

$page_title = 'مدیریت محصولات من';

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
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$where = ["p.created_by = ?"];
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

if ($status) {
    $where[] = "p.status = ?";
    $params[] = $status;
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Get total products for pagination
$total_query = "SELECT COUNT(*) as total FROM products p " . $where_clause;
$db = Database::getInstance();
$total_result = $db->fetchRow($total_query, $params);
$total_products = $total_result['total'];
$total_pages = ceil($total_products / $limit);

// Get products
$query = "SELECT p.*, c.name as category_name
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          $where_clause
          ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";

$products = $db->fetchAll($query, $params);

// Get categories for filter
$categories = $products_obj->get_categories();

// Get actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle product actions
if ($action && $product_id) {
    // Verify product belongs to user
    $product = $products_obj->get_product($product_id);
    if (!$product || $product['created_by'] != $user_id) {
        header("Location: products.php");
        exit;
    }
    
    if ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        // Handle product update
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $discount_price = $_POST['discount_price'] ?: null;
        $stock_quantity = $_POST['stock_quantity'];
        $category_id = $_POST['category_id'];
        $status = $_POST['status'];
        
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $filename;
            }
        }
        
        $update_data = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'discount_price' => $discount_price,
            'stock_quantity' => $stock_quantity,
            'category_id' => $category_id,
            'status' => $status
        ];
        
        if ($image) {
            $update_data['image'] = $image;
        }
        
        if ($products_obj->update_product($product_id, $update_data, $image)) {
            header("Location: products.php?success=product_updated");
            exit;
        }
    }
    
    if ($action == 'delete' && isset($_POST['confirm'])) {
        if ($products_obj->delete_product($product_id)) {
            header("Location: products.php?success=product_deleted");
            exit;
        }
    }
}

// Get single product for editing
$product = null;
if ($action == 'edit' && $product_id) {
    $product = $products_obj->get_product($product_id);
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
<link href="../css/style.css" rel="stylesheet"/>
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
<span class="text-[#0d121b] dark:text-white font-medium">محصولات من</span>
</nav>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
<h1 class="text-3xl font-bold text-[#0d121b] dark:text-white">محصولات من</h1>
<div class="flex gap-3">
<a href="add-product.php" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-primary/30">
<span class="material-symbols-outlined text-[20px]">add</span>
افزودن محصول
</a>
</div>
</div>

<!-- Success Messages -->
<?php if (isset($_GET['success'])): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <div class="flex items-center gap-2 text-green-700 dark:text-green-300">
            <span class="material-symbols-outlined">check_circle</span>
            <?php
            switch($_GET['success']) {
                case 'product_added':
                    echo 'محصول با موفقیت اضافه شد.';
                    break;
                case 'product_updated':
                    echo 'محصول با موفقیت ویرایش شد.';
                    break;
                case 'product_deleted':
                    echo 'محصول با موفقیت حذف شد.';
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
<input type="text" name="search" placeholder="جستجو در محصولات..." 
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
<div class="min-w-[120px]">
<select name="status" class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white">
<option value="">همه وضعیت‌ها</option>
<option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>فعال</option>
<option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
</select>
</div>
<div class="flex gap-2">
<button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">
<span class="material-symbols-outlined text-[20px]">search</span>
جستجو
</button>
<a href="products.php" class="px-4 py-2 bg-background-light dark:bg-gray-800 text-[#0d121b] dark:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
<span class="material-symbols-outlined text-[20px]">refresh</span>
بازنشانی
</a>
</div>
</form>
</div>

<!-- Products Grid -->
<?php if (!$product): ?>
    <?php if (!empty($products)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-10">
            <?php foreach ($products as $product): ?>
                <div class="group flex flex-col bg-white dark:bg-[#1a2233] rounded-2xl border border-[#e7ebf3] dark:border-gray-800 overflow-hidden hover:shadow-xl hover:shadow-primary/5 hover:border-primary/30 transition-all duration-300">
                    <div class="relative pt-[100%] bg-gray-50 dark:bg-gray-800/50">
                        <?php if (!empty($product['image'])): ?>
                            <img class="absolute inset-0 w-full h-full object-contain p-6 group-hover:scale-110 transition-transform duration-500" 
                                 src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'"/>
                        <?php else: ?>
                            <img class="absolute inset-0 w-full h-full object-contain p-6 group-hover:scale-110 transition-transform duration-500" 
                                 src="https://via.placeholder.com/300x300?text=No+Image" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"/>
                        <?php endif; ?>
                        
                        <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                            <?php 
                            $discount_percentage = round(($product['price'] - $product['discount_price']) / $product['price'] * 100);
                            ?>
                            <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-md shadow-sm"><?php echo $discount_percentage; ?>٪ تخفیف</span>
                        <?php endif; ?>
                        
                        <span class="absolute top-3 left-3 size-8 rounded-full bg-white dark:bg-gray-700 shadow-sm flex items-center justify-center text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                            <span class="material-symbols-outlined text-[20px]">favorite</span>
                        </span>
                    </div>
                    <div class="p-4 flex flex-col flex-1 gap-2">
                        <?php if (!empty($product['category_name'])): ?>
                            <div class="flex items-center gap-1">
                                <span class="text-xs text-primary font-medium"><?php echo htmlspecialchars($product['category_name']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="text-[#0d121b] dark:text-gray-100 font-bold leading-snug line-clamp-2 min-h-[3rem]">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>
                        
                        <div class="mt-auto pt-4 flex items-end justify-between">
                            <div class="flex flex-col">
                                <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                    <span class="text-xs text-gray-400 line-through decoration-red-500/50">
                                        <?php echo format_price($product['price']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <div class="flex items-center gap-1">
                                    <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                        <span class="text-lg font-black text-[#0d121b] dark:text-white">
                                            <?php echo format_price($product['discount_price']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-lg font-black text-[#0d121b] dark:text-white">
                                            <?php echo format_price($product['price']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="text-xs text-gray-500 font-light">تومان</span>
                                </div>
                            </div>
                            
                            <div class="flex gap-1">
                                <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" 
                                   class="size-8 rounded-xl bg-background-light dark:bg-gray-700 text-primary dark:text-white flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                </a>
                                <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                   class="size-8 rounded-xl bg-background-light dark:bg-gray-700 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-colors"
                                   onclick="return confirm('آیا از حذف این محصول مطمئن هستید؟');">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
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
                    <a href="products.php?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $status ? '&status=' . $status : ''; ?>" 
                       class="size-10 flex items-center justify-center rounded-lg border border-[#e7ebf3] dark:border-gray-800 text-[#0d121b] dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 font-medium <?php echo $i == $page ? 'bg-primary text-white shadow-lg shadow-primary/30' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <span class="material-symbols-outlined text-[64px]">inventory_2</span>
            </div>
            <h3 class="text-lg font-medium text-gray-600 dark:text-gray-400 mb-2">محصولی یافت نشد</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">محصولی با مشخصات درخواستی شما وجود ندارد.</p>
            <a href="add-product.php" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-primary/30">
                <span class="material-symbols-outlined text-[20px]">add</span>
                افزودن محصول جدید
            </a>
        </div>
    <?php endif; ?>
<?php else: ?>
    <!-- Edit Product Form -->
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-[#1a2233] rounded-2xl p-6 border border-[#e7ebf3] dark:border-gray-800">
            <h2 class="text-2xl font-bold text-[#0d121b] dark:text-white mb-6">ویرایش محصول</h2>
            
            <form method="post" enctype="multipart/form-data" class="space-y-6">
                <!-- Product Image -->
                <div>
                    <label class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">عکس محصول</label>
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 bg-background-light dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Preview" class="w-full h-full object-cover rounded-lg"/>
                                <?php else: ?>
                                    <span class="material-symbols-outlined text-[48px] text-gray-400">add_photo_alternate</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="image" id="image" accept="image/*" class="hidden" onchange="previewImage(event)">
                            <label for="image" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">upload</span>
                                تغییر عکس
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">فرمت‌های مجاز: JPG, PNG, GIF (حداکثر 5MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Product Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">نام محصول *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" 
                           class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
                           required>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">توضیحات محصول *</label>
                    <textarea id="description" name="description" rows="4" 
                              class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white resize-none" 
                              required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <!-- Price and Discount -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">قیمت (تومان) *</label>
                        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" 
                               min="0" step="1000" 
                               class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
                               required>
                    </div>
                    <div>
                        <label for="discount_price" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">قیمت تخفیف (تومان)</label>
                        <input type="number" id="discount_price" name="discount_price" value="<?php echo htmlspecialchars($product['discount_price']); ?>" 
                               min="0" step="1000" 
                               class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white">
                    </div>
                </div>

                <!-- Stock and Category -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="stock_quantity" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">موجودی *</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" 
                               min="0" 
                               class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
                               required>
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">دسته‌بندی *</label>
                        <select id="category_id" name="category_id" 
                                class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
                                required>
                            <option value="">انتخاب دسته‌بندی</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">وضعیت محصول</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="active" <?php echo $product['status'] == 'active' ? 'checked' : ''; ?> class="text-primary focus:ring-primary/20">
                            <span class="text-sm text-[#0d121b] dark:text-white">فعال</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="inactive" <?php echo $product['status'] == 'inactive' ? 'checked' : ''; ?> class="text-primary focus:ring-primary/20">
                            <span class="text-sm text-[#0d121b] dark:text-white">غیرفعال</span>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4 pt-6">
                    <button type="submit" class="flex-1 px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-primary/30">
                        <span class="material-symbols-outlined text-[20px] mr-2">save</span>
                        ذخیره تغییرات
                    </button>
                    <a href="products.php" class="flex-1 px-6 py-3 bg-background-light dark:bg-gray-800 text-[#0d121b] dark:text-white font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <span class="material-symbols-outlined text-[20px] mr-2">arrow_back</span>
                        بازگشت به لیست
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
</div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.bg-background-light.dark\\:bg-gray-800');
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover rounded-lg">`;
        }
        reader.readAsDataURL(file);
    }
}
</script>
</body></html>
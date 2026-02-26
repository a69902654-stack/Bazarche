<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/products.php';
require_once '../includes/functions.php';

// Check if user is logged in
$auth->require_user();

$page_title = 'افزودن محصول جدید';

// Get categories
$products_obj = new Products();
$categories = $products_obj->get_categories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $discount_price = $_POST['discount_price'] ?: null;
    $stock_quantity = $_POST['stock_quantity'];
    $category_id = $_POST['category_id'];
    $status = $_POST['status'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'نام محصول الزامی است';
    }
    
    if (empty($description)) {
        $errors[] = 'توضیحات محصول الزامی است';
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = 'قیمت معتبر نیست';
    }
    
    if (empty($stock_quantity) || !is_numeric($stock_quantity) || $stock_quantity < 0) {
        $errors[] = 'موجودی معتبر نیست';
    }
    
    if (empty($category_id)) {
        $errors[] = 'دسته‌بندی الزامی است';
    }
    
    if (empty($errors)) {
        // Handle image upload
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
        
        // Add product
        $product_id = $products_obj->add_product([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'discount_price' => $discount_price,
            'stock_quantity' => $stock_quantity,
            'category_id' => $category_id,
            'status' => $status,
            'created_by' => $_SESSION['user_id']
        ], $image);
        
        if ($product_id) {
            header("Location: products.php?success=product_added");
            exit;
        } else {
            $errors[] = 'خطا در افزودن محصول';
        }
    }
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
<span class="text-[#0d121b] dark:text-white font-medium">افزودن محصول</span>
</nav>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
<h1 class="text-3xl font-bold text-[#0d121b] dark:text-white">افزودن محصول جدید</h1>
<a href="dashboard.php" class="inline-flex items-center gap-2 px-4 py-2 bg-background-light dark:bg-gray-800 text-[#0d121b] dark:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
<span class="material-symbols-outlined text-[20px]">arrow_back</span>
بازگشت به داشبورد
</a>
</div>

<!-- Form -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
<!-- Main Form -->
<div class="lg:col-span-2">
<div class="bg-white dark:bg-[#1a2233] rounded-2xl p-6 border border-[#e7ebf3] dark:border-gray-800">
<form method="post" enctype="multipart/form-data" class="space-y-6">
<?php if (!empty($errors)): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
        <div class="flex items-center gap-2 text-red-700 dark:text-red-300">
            <span class="material-symbols-outlined">error</span>
            <span class="font-medium">خطاها:</span>
        </div>
        <ul class="mt-2 space-y-1 text-sm text-red-600 dark:text-red-400">
            <?php foreach ($errors as $error): ?>
                <li>• <?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Product Image -->
<div>
<label class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">عکس محصول</label>
<div class="flex items-center gap-4">
<div class="flex-shrink-0">
<div class="w-32 h-32 bg-background-light dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center">
<?php if (isset($_POST['image_preview'])): ?>
    <img src="../uploads/<?php echo htmlspecialchars($_POST['image_preview']); ?>" alt="Preview" class="w-full h-full object-cover rounded-lg"/>
<?php else: ?>
    <span class="material-symbols-outlined text-[48px] text-gray-400">add_photo_alternate</span>
<?php endif; ?>
</div>
</div>
<div class="flex-1">
<input type="file" name="image" id="image" accept="image/*" class="hidden" onchange="previewImage(event)">
<label for="image" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">
<span class="material-symbols-outlined text-[20px]">upload</span>
انتخاب عکس
</label>
<p class="text-xs text-gray-500 dark:text-gray-400 mt-2">فرمت‌های مجاز: JPG, PNG, GIF (حداکثر 5MB)</p>
</div>
</div>
</div>

<!-- Product Name -->
<div>
<label for="name" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">نام محصول *</label>
<input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
       class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
       required>
</div>

<!-- Description -->
<div>
<label for="description" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">توضیحات محصول *</label>
<textarea id="description" name="description" rows="4" 
          class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white resize-none" 
          required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
</div>

<!-- Price and Discount -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label for="price" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">قیمت (تومان) *</label>
<input type="number" id="price" name="price" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" 
       min="0" step="1000" 
       class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white" 
       required>
</div>
<div>
<label for="discount_price" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">قیمت تخفیف (تومان)</label>
<input type="number" id="discount_price" name="discount_price" value="<?php echo isset($_POST['discount_price']) ? htmlspecialchars($_POST['discount_price']) : ''; ?>" 
       min="0" step="1000" 
       class="w-full px-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-transparent text-[#0d121b] dark:text-white">
</div>
</div>

<!-- Stock and Category -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label for="stock_quantity" class="block text-sm font-medium text-[#0d121b] dark:text-white mb-2">موجودی *</label>
<input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo isset($_POST['stock_quantity']) ? htmlspecialchars($_POST['stock_quantity']) : ''; ?>" 
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
    <option value="<?php echo $category['id']; ?>" <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $category['id'] ? 'selected' : ''; ?>>
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
<input type="radio" name="status" value="active" checked class="text-primary focus:ring-primary/20">
<span class="text-sm text-[#0d121b] dark:text-white">فعال</span>
</label>
<label class="flex items-center gap-2 cursor-pointer">
<input type="radio" name="status" value="inactive" class="text-primary focus:ring-primary/20">
<span class="text-sm text-[#0d121b] dark:text-white">غیرفعال</span>
</label>
</div>
</div>

<!-- Submit Button -->
<div class="pt-6">
<button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-primary/30">
<span class="material-symbols-outlined text-[20px] mr-2">add</span>
افزودن محصول
</button>
</div>
</form>
</div>
</div>

<!-- Sidebar -->
<div class="lg:col-span-1">
<div class="bg-white dark:bg-[#1a2233] rounded-2xl p-6 border border-[#e7ebf3] dark:border-gray-800">
<h3 class="text-lg font-bold text-[#0d121b] dark:text-white mb-4">راهنما</h3>
<div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-[20px] flex-shrink-0">check_circle</span>
<div>
<p class="font-medium text-[#0d121b] dark:text-white">عکس محصول</p>
<p class="mt-1">از عکس‌های با کیفیت و مرتبط با محصول استفاده کنید</p>
</div>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-[20px] flex-shrink-0">check_circle</span>
<div>
<p class="font-medium text-[#0d121b] dark:text-white">توضیحات کامل</p>
<p class="mt-1">ویژگی‌ها و مشخصات محصول را به طور کامل توضیح دهید</p>
</div>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-[20px] flex-shrink-0">check_circle</span>
<div>
<p class="font-medium text-[#0d121b] dark:text-white">قیمت‌گذاری</p>
<p class="mt-1">قیمت‌ها را به تومان وارد کنید و برای فروش‌های ویژه تخفیف در نظر بگیرید</p>
</div>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-[20px] flex-shrink-0">check_circle</span>
<div>
<p class="font-medium text-[#0d121b] dark:text-white">موجودی</p>
<p class="mt-1">مقدار موجودی انبار را به دقت وارد کنید</p>
</div>
</div>
</div>
</div>
</div>
</div>
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
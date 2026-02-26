<?php
require_once 'includes/products.php';
require_once 'includes/db.php';

// Get products from database
$products_obj = new Products();
$products = $products_obj->get_all_products(12); // Get first 12 products
$total_products = $products_obj->get_product_count();

// Get categories for filters
$categories = $products_obj->get_categories();
?>

<!DOCTYPE html>

<html class="light" dir="rtl" lang="fa"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>صفحه لیست محصولات</title>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Vazirmatn:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
        /* Hide scrollbar for clean horizontal scrolling if needed */
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
<!-- Search Bar -->
<label class="hidden md:flex flex-col min-w-40 flex-1 max-w-[480px]">
<div class="flex w-full items-stretch rounded-xl h-12 bg-background-light dark:bg-gray-800 border-none transition-all focus-within:ring-2 focus-within:ring-primary/50">
<div class="text-[#4c669a] flex items-center justify-center pr-4 pl-2">
<span class="material-symbols-outlined text-[24px]">search</span>
</div>
<input class="form-input flex w-full flex-1 resize-none bg-transparent border-none text-base font-normal text-[#0d121b] dark:text-white placeholder:text-[#4c669a] focus:ring-0 focus:outline-none h-full" placeholder="جستجو در بین هزاران محصول..."/>
</div>
</label>
<!-- Navigation & Actions -->
<div class="flex flex-1 justify-end gap-6 items-center">
<div class="hidden lg:flex items-center gap-6">
<a class="text-[#0d121b] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors" href="index.php">خانه</a>
<a class="text-[#0d121b] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors" href="#">محصولات</a>
<a class="text-[#0d121b] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors" href="#">تخفیف‌ها</a>
</div>
<div class="flex gap-3">
<a href="cart.php"><button class="flex size-10 items-center justify-center rounded-xl bg-background-light dark:bg-gray-800 text-[#0d121b] dark:text-white hover:bg-primary hover:text-white transition-all">
<span class="material-symbols-outlined text-[20px]">shopping_cart</span>
</button></a>
<a href="login.php"><button class="flex size-10 items-center justify-center rounded-xl bg-background-light dark:bg-gray-800 text-[#0d121b] dark:text-white hover:bg-primary hover:text-white transition-all">
<span class="material-symbols-outlined text-[20px]">account_circle</span>
</button></a>
</div>
</div>
</div>
</header>
<!-- Main Layout -->
<div class="flex flex-col lg:flex-row max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 gap-6">
<!-- Sidebar Filters -->
<aside class="w-full lg:w-72 shrink-0 hidden lg:flex flex-col gap-6">
<div class="bg-white dark:bg-[#1a2233] rounded-2xl p-5 border border-[#e7ebf3] dark:border-gray-800 sticky top-28">
<div class="flex items-center justify-between mb-6">
<h1 class="text-[#0d121b] dark:text-white text-lg font-bold">فیلترها</h1>
<button class="text-primary text-sm font-medium hover:underline">حذف همه</button>
</div>
<!-- Filter Groups -->
<div class="flex flex-col gap-6">
<!-- Category Filter -->
<div class="flex flex-col gap-3">
<div class="flex items-center gap-2 text-[#0d121b] dark:text-white font-medium">
<span class="material-symbols-outlined text-[20px] text-primary">category</span>
<span>دسته‌بندی</span>
</div>
<div class="pr-2 flex flex-col gap-2">
<?php if (!empty($categories)): ?>
    <?php foreach ($categories as $category): ?>
        <label class="flex items-center gap-3 cursor-pointer group">
            <input class="size-4 rounded border-gray-300 text-primary focus:ring-primary/20 dark:bg-gray-700 dark:border-gray-600" type="checkbox"/>
            <span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-primary transition-colors"><?php echo htmlspecialchars($category['name']); ?></span>
        </label>
    <?php endforeach; ?>
<?php else: ?>
    <label class="flex items-center gap-3 cursor-pointer group">
        <input class="size-4 rounded border-gray-300 text-primary focus:ring-primary/20 dark:bg-gray-700 dark:border-gray-600" type="checkbox"/>
        <span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-primary transition-colors">موبایل</span>
    </label>
    <label class="flex items-center gap-3 cursor-pointer group">
        <input class="size-4 rounded border-gray-300 text-primary focus:ring-primary/20 dark:bg-gray-700 dark:border-gray-600" type="checkbox"/>
        <span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-primary transition-colors">لپ‌تاپ</span>
    </label>
    <label class="flex items-center gap-3 cursor-pointer group">
        <input class="size-4 rounded border-gray-300 text-primary focus:ring-primary/20 dark:bg-gray-700 dark:border-gray-600" type="checkbox"/>
        <span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-primary transition-colors">لوازم جانبی</span>
    </label>
<?php endif; ?>
</div>
</div>
<hr class="border-[#e7ebf3] dark:border-gray-700"/>
<!-- Brand Filter -->
<div class="flex flex-col gap-3">
<div class="flex items-center gap-2 text-[#0d121b] dark:text-white font-medium">
<span class="material-symbols-outlined text-[20px] text-primary">label</span>
<span>برند</span>
</div>
<div class="pr-2 flex flex-col gap-2">
<label class="flex items-center gap-3 cursor-pointer group">
<input class="size-4 rounded border-gray-300 text-primary focus:ring-primary/20 dark:bg-gray-700 dark:border-gray-600" type="checkbox"/>
<span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-primary transition-colors">سامسونگ</span>
</label>
<label class="flex items-center gap-3 cursor-pointer group">
<input class="size-4 rounded border-gray-300 text-primary focus:ring-primary/20 dark:bg-gray-700 dark:border-gray-600" type="checkbox"/>
<span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-primary transition-colors">اپل</span>
</label>
<label class="flex items-center gap-3 cursor-pointer group">
<input class="size-4 rounded border-gray-300 text-primary focus:ring-primary/20 dark:bg-gray-700 dark:border-gray-600" type="checkbox"/>
<span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-primary transition-colors">شیائومی</span>
</label>
</div>
</div>
<hr class="border-[#e7ebf3] dark:border-gray-700"/>
<!-- Price Filter -->
<div class="flex flex-col gap-3">
<div class="flex items-center gap-2 text-[#0d121b] dark:text-white font-medium">
<span class="material-symbols-outlined text-[20px] text-primary">attach_money</span>
<span>محدوده قیمت</span>
</div>
<div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
<div class="bg-background-light dark:bg-gray-800 px-3 py-2 rounded-lg flex-1 text-center">۰</div>
<span>تا</span>
<div class="bg-background-light dark:bg-gray-800 px-3 py-2 rounded-lg flex-1 text-center">۱۰۰٪</div>
</div>
<input class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-primary" type="range"/>
</div>
<div class="mt-4">
<button class="w-full py-3 bg-primary hover:bg-blue-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-primary/20">
                                اعمال فیلترها
                            </button>
</div>
</div>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 flex flex-col min-w-0">
<!-- Breadcrumbs -->
<nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
<a class="hover:text-primary transition-colors" href="index.php">خانه</a>
<span class="text-gray-300 dark:text-gray-600">/</span>
<a class="hover:text-primary transition-colors" href="#">کالای دیجیتال</a>
<span class="text-gray-300 dark:text-gray-600">/</span>
<span class="text-[#0d121b] dark:text-white font-medium">موبایل</span>
</nav>
<!-- Header Section -->
<div class="flex flex-col gap-6 mb-8">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<h1 class="text-3xl font-bold text-[#0d121b] dark:text-white">گوشی موبایل</h1>
<span class="text-sm text-gray-500 dark:text-gray-400 bg-white dark:bg-[#1a2233] px-3 py-1 rounded-full border border-gray-100 dark:border-gray-800 shadow-sm">
    <?php echo number_format($total_products); ?> محصول
</span>
</div>
<!-- Active Filters & Sorting -->
<div class="flex flex-col gap-4">
<!-- Chips -->
<div class="flex flex-wrap gap-2">
<div class="flex h-8 items-center gap-2 rounded-lg bg-primary/10 pl-2 pr-3 border border-primary/10">
<span class="material-symbols-outlined text-[16px] text-primary">check</span>
<p class="text-primary text-sm font-bold">موجود در انبار</p>
</div>
<div class="flex h-8 items-center gap-2 rounded-lg bg-background-light dark:bg-[#1a2233] pl-2 pr-3 border border-gray-200 dark:border-gray-700">
<span class="material-symbols-outlined text-[16px] text-gray-500 cursor-pointer hover:text-red-500">close</span>
<p class="text-[#0d121b] dark:text-gray-200 text-sm font-medium">سامسونگ</p>
</div>
<div class="flex h-8 items-center gap-2 rounded-lg bg-background-light dark:bg-[#1a2233] pl-2 pr-3 border border-gray-200 dark:border-gray-700">
<span class="material-symbols-outlined text-[16px] text-gray-500 cursor-pointer hover:text-red-500">close</span>
<p class="text-[#0d121b] dark:text-gray-200 text-sm font-medium">رنگ: مشکی</p>
</div>
</div>
<!-- Sort & Mobile Filter Toggle -->
<div class="flex items-center justify-between bg-white dark:bg-[#1a2233] p-2 rounded-xl border border-[#e7ebf3] dark:border-gray-800 shadow-sm">
<div class="flex items-center gap-2 overflow-x-auto no-scrollbar pl-2">
<span class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap px-2">
<span class="material-symbols-outlined text-[20px]">sort</span>
                                    مرتب‌سازی:
                                </span>
<button class="px-3 py-1.5 text-sm font-medium text-primary bg-primary/10 rounded-lg whitespace-nowrap">پربازدیدترین</button>
<button class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg whitespace-nowrap transition-colors">جدیدترین</button>
<button class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg whitespace-nowrap transition-colors">ارزان‌ترین</button>
<button class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg whitespace-nowrap transition-colors">گران‌ترین</button>
</div>
<!-- Mobile Filter Button -->
<button class="lg:hidden flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm font-bold text-[#0d121b] dark:text-white ml-2">
<span class="material-symbols-outlined text-[20px]">filter_list</span>
                                فیلتر
                            </button>
</div>
</div>
</div>
<!-- Products Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-6 mb-10">
<?php if (!empty($products)): ?>
    <?php foreach ($products as $product): ?>
        <div class="group flex flex-col bg-white dark:bg-[#1a2233] rounded-2xl border border-[#e7ebf3] dark:border-gray-800 overflow-hidden hover:shadow-xl hover:shadow-primary/5 hover:border-primary/30 transition-all duration-300">
            <div class="relative pt-[100%] bg-gray-50 dark:bg-gray-800/50">
                <?php if (!empty($product['image'])): ?>
                    <img class="absolute inset-0 w-full h-full object-contain p-6 group-hover:scale-110 transition-transform duration-500"
                         src="<?php echo htmlspecialchars($product['image']); ?>"
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
                
                <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                    <span class="absolute top-3 right-3 bg-primary text-white text-xs font-bold px-2 py-1 rounded-md shadow-sm">فروش ویژه</span>
                <?php endif; ?>
                
                <?php if ($product['stock_quantity'] <= 0): ?>
                    <span class="absolute top-3 right-3 bg-gray-800 text-white text-xs font-bold px-2 py-1 rounded-md shadow-sm">اتمام موجودی</span>
                <?php endif; ?>
                
                <button class="absolute top-3 left-3 size-8 rounded-full bg-white dark:bg-gray-700 shadow-sm flex items-center justify-center text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                    <span class="material-symbols-outlined text-[20px]">favorite</span>
                </button>
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
                    
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <a href="product.php?id=<?php echo $product['id']; ?>">
                            <button class="size-10 rounded-xl bg-primary text-white flex items-center justify-center hover:bg-blue-700 transition-colors shadow-lg shadow-primary/30">
                                <span class="material-symbols-outlined text-[20px]">add_shopping_cart</span>
                            </button>
                        </a>
                    <?php else: ?>
                        <button class="size-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center cursor-not-allowed" disabled="">
                            <span class="material-symbols-outlined text-[20px]">notifications</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="col-span-full text-center py-12">
        <div class="text-gray-400 mb-4">
            <span class="material-symbols-outlined text-[64px]">shopping_bag</span>
        </div>
        <h3 class="text-lg font-medium text-gray-600 dark:text-gray-400 mb-2">محصولی یافت نشد</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">متأسفانه محصولی با مشخصات درخواستی شما وجود ندارد.</p>
    </div>
<?php endif; ?>
</div>
<!-- Pagination -->
<div class="flex items-center justify-center gap-2 mt-auto">
<button class="size-10 flex items-center justify-center rounded-lg border border-[#e7ebf3] dark:border-gray-800 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800">
<span class="material-symbols-outlined">chevron_right</span>
</button>
<button class="size-10 flex items-center justify-center rounded-lg bg-primary text-white font-bold shadow-lg shadow-primary/30">۱</button>
<button class="size-10 flex items-center justify-center rounded-lg border border-[#e7ebf3] dark:border-gray-800 text-[#0d121b] dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 font-medium">۲</button>
<button class="size-10 flex items-center justify-center rounded-lg border border-[#e7ebf3] dark:border-gray-800 text-[#0d121b] dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 font-medium">۳</button>
<span class="text-gray-400 px-2">...</span>
<button class="size-10 flex items-center justify-center rounded-lg border border-[#e7ebf3] dark:border-gray-800 text-[#0d121b] dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 font-medium">۱۲</button>
<button class="size-10 flex items-center justify-center rounded-lg border border-[#e7ebf3] dark:border-gray-800 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800">
<span class="material-symbols-outlined">chevron_left</span>
</button>
</div>
</main>
</div>
</div>
</body></html>
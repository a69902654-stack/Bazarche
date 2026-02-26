
<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/products.php';
require_once 'includes/cart.php';
require_once 'includes/auth.php';

$page_title = 'بازارچه - فروشگاه آنلاین';
$products = new Products();
$cart = new Cart();

// Get featured products
$featured_products = $products->get_featured_products(4);

// Get latest products
$latest_products = $products->get_latest_products(4);

// Get categories
$categories = $products->get_categories();

// Get cart count if user is logged in
$cart_count = 0;
if ($auth->is_logged_in()) {
    $cart_count = $cart->get_cart_item_count($_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html class="light" dir="rtl" lang="fa">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>صفحه اصلی فروشگاه - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&family=Vazirmatn:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f8f9fc",
                        "background-dark": "#101622",
                    },
                    fontFamily: {
                        "display": ["Vazirmatn", "Inter", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.5rem", "lg": "0.75rem", "xl": "1rem", "full": "9999px"},
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Vazirmatn', 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-[#0d121b] dark:text-white">
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    
    <!-- Navigation Bar -->
    <header class="sticky top-0 z-50 w-full border-b border-gray-200 bg-white/80 dark:bg-[#1a202c]/90 dark:border-gray-800 backdrop-blur-md px-4 sm:px-10 py-3">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4">
            
            <!-- Right Side: Logo & Links -->
            <div class="flex items-center gap-8">
                <a class="flex items-center gap-2" href="index.php">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <span class="material-symbols-outlined text-3xl">shopping_bag</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white"><?php echo SITE_NAME; ?></h2>
                </a>
                
                <nav class="hidden md:flex items-center gap-6">
                    <a class="text-sm font-medium text-gray-900 hover:text-primary dark:text-gray-100 transition-colors" href="index.php">خانه</a>
                    <a class="text-sm font-medium text-gray-600 hover:text-primary dark:text-gray-400 transition-colors" href="products.php">محصولات</a>
                    <a class="text-sm font-medium text-gray-600 hover:text-primary dark:text-gray-400 transition-colors" href="contact.php">درباره ما</a>
                    <a class="text-sm font-medium text-gray-600 hover:text-primary dark:text-gray-400 transition-colors" href="contact.php">تماس با ما</a>
                </nav>
            </div>
            
            <!-- Middle: Search Bar (Hidden on small screens) -->
            <div class="hidden lg:flex flex-1 max-w-md mx-4">
                <div class="relative w-full">
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <span class="material-symbols-outlined text-gray-400">search</span>
                    </div>
                    <input class="block w-full rounded-lg border-none bg-gray-100 py-2.5 pr-10 pl-4 text-sm text-gray-900 focus:ring-2 focus:ring-primary dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" placeholder="جستجو در هزاران محصول..." type="text"/>
                </div>
            </div>
            
            <!-- Left Side: Actions -->
            <div class="flex items-center gap-3">
                <a href="cart.php" class="relative flex size-10 items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-700 dark:text-gray-200">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <?php if ($cart_count > 0): ?>
                        <span class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                
                <?php if ($auth->is_logged_in()): ?>
                    <div class="relative">
                        <button class="flex h-10 items-center gap-2 rounded-lg bg-primary px-4 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                            <span class="hidden sm:inline"><?php echo $_SESSION['user_full_name']; ?></span>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 rounded-lg bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 hidden">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">پروفایل</a>
                            <a href="orders.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">سفارشات</a>
                            <hr class="my-1 border-gray-200 dark:border-gray-700">
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">خروج</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="flex h-10 items-center gap-2 rounded-lg bg-primary px-4 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">login</span>
                        <span class="hidden sm:inline">ورود / ثبت‌نام</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-1">
        
        <!-- Hero Section -->
        <section class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-2xl bg-gray-900 text-white shadow-xl">
                <!-- Background Image -->
                <div class="absolute inset-0 bg-cover bg-center" data-alt="Modern bright living room with stylish furniture representing summer sale vibes" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBYkPQLEdOD_-Bf6XEgISbbJXot88sVP2xP5DhlxCofNhnQtcoT5WkyUW8L5BlDsgOBZ1K9vNY_ejlW17qBrjYkJW76ajCCP8QJz775zrukig44i8dRwG3n2Edo9_jD9Jyrz-m6KqyqnxKK5KcQcxx-YpIM1UQd6e8K_51GwwJtfdyGdzOoEjYu7MDIi24Xi6l1-sHdme7AdQXudUACumlN4r6aEJYazErAD37G7zndP5kuoEPOEJwNKMBoTjjngDlx4vM7SzLS4XKb');">
                </div>
                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-l from-gray-900/90 via-gray-900/50 to-transparent"></div>
                
                <div class="relative z-10 flex min-h-[480px] flex-col justify-center px-8 py-12 sm:max-w-xl lg:px-12">
                    <span class="mb-4 inline-block w-fit rounded-full bg-primary/20 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-blue-200 backdrop-blur-sm border border-blue-500/30">
                        پیشنهاد ویژه
                    </span>
                    
                    <h1 class="mb-6 text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                        جشنواره فروش <span class="text-primary">تابستانه</span>
                    </h1>
                    
                    <p class="mb-8 text-lg font-light text-gray-200 sm:text-xl">
                        تخفیف‌های باورنکردنی تا ۵۰٪ روی محصولات دیجیتال، پوشاک و لوازم خانه. فرصت را از دست ندهید!
                    </p>
                    
                    <div class="flex flex-wrap gap-4">
                        <a href="products.php" class="flex h-12 items-center justify-center rounded-lg bg-primary px-8 text-base font-bold text-white shadow-lg shadow-blue-600/30 transition-transform hover:scale-105 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            مشاهده محصولات
                        </a>
                        <button class="flex h-12 items-center justify-center rounded-lg bg-white/10 px-6 text-base font-bold text-white backdrop-blur-md transition-colors hover:bg-white/20">
                            کد تخفیف: SUMMER50
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">دسته‌بندی‌های محبوب</h2>
                <a href="products.php" class="flex items-center text-sm font-medium text-primary hover:text-blue-700">
                    مشاهده همه
                    <span class="material-symbols-outlined text-lg mr-1 rotate-180">arrow_right_alt</span>
                </a>
            </div>
            
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-6">
                <?php foreach ($categories as $category): ?>
                    <a href="category.php?id=<?php echo $category['id']; ?>" class="group flex flex-col items-center gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 transition-all hover:-translate-y-1 hover:shadow-md dark:bg-gray-800 dark:ring-gray-700">
                        <div class="flex size-16 items-center justify-center rounded-full bg-blue-50 text-primary group-hover:bg-primary group-hover:text-white transition-colors dark:bg-blue-900/30">
                            <span class="material-symbols-outlined text-3xl">category</span>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white"><?php echo $category['name']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Best Sellers Grid -->
        <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8 bg-white dark:bg-[#1a202c] rounded-none sm:rounded-3xl my-8 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">پرفروش‌ترین محصولات</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">محصولاتی که کاربران بیشتر پسندیده‌اند</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
                <?php foreach ($latest_products as $product): ?>
                    <div class="group relative flex flex-col">
                        <div class="aspect-square w-full overflow-hidden rounded-xl bg-gray-100 relative">
                            <?php if ($product['discount_price']): ?>
                                <div class="absolute top-3 right-3 z-10 rounded-full bg-red-500 px-2 py-1 text-xs font-bold text-white shadow-sm">
                                    <?php echo round((1 - ($product['discount_price'] / $product['price'])) * 100); ?>٪ تخفیف
                                </div>
                            <?php endif; ?>
                            
                            <img class="h-full w-full object-cover object-center group-hover:opacity-90 group-hover:scale-105 transition-all duration-300" 
                                 src="<?php echo $product['image'] ? 'uploads/' . $product['image'] : 'https://picsum.photos/seed/product' . $product['id'] . '/400/400'; ?>" 
                                 alt="<?php echo $product['name']; ?>"/>
                            
                            <button class="absolute bottom-3 left-3 right-3 flex items-center justify-center gap-2 rounded-lg bg-white/90 py-2 text-sm font-bold text-gray-900 opacity-0 shadow-lg backdrop-blur-sm transition-all duration-300 group-hover:opacity-100 hover:bg-primary hover:text-white"
                                    onclick="addToCart(<?php echo $product['id']; ?>)">
                                <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                                افزودن به سبد
                            </button>
                        </div>
                        
                        <div class="mt-4 flex justify-between">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                    <a href="product.php?id=<?php echo $product['id']; ?>">
                                        <span aria-hidden="true" class="absolute inset-0"></span>
                                        <?php echo $product['name']; ?>
                                    </a>
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400"><?php echo $product['category_name']; ?></p>
                            </div>
                            
                            <div class="flex flex-col items-end">
                                <?php if ($product['discount_price']): ?>
                                    <p class="text-sm font-medium text-primary"><?php echo format_price($product['discount_price']); ?></p>
                                    <p class="text-xs text-gray-400 line-through"><?php echo format_price($product['price']); ?></p>
                                <?php else: ?>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo format_price($product['price']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Promo Banner -->
        <section class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-gradient-to-r from-indigo-900 via-primary to-blue-500 p-8 sm:p-12 text-center sm:text-right relative overflow-hidden">
                <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between gap-8">
                    <div class="flex-1 text-white">
                        <h2 class="text-3xl font-black tracking-tight sm:text-4xl mb-4">باشگاه مشتریان وفادار</h2>
                        <p class="text-blue-100 text-lg mb-6 max-w-2xl">با عضویت در باشگاه مشتریان، از تخفیف‌های اختصاصی و ارسال رایگان بهره‌مند شوید. اولین خرید شما شامل ۱۰٪ تخفیف اضافه خواهد بود.</p>
                        
                        <form class="flex flex-col sm:flex-row gap-3 max-w-md" onsubmit="subscribeNewsletter(event)">
                            <input type="email" class="flex-1 rounded-lg border-0 px-4 py-3 text-gray-900 placeholder:text-gray-500 focus:ring-2 focus:ring-white" 
                                   placeholder="آدرس ایمیل خود را وارد کنید" required/>
                            <button type="submit" class="bg-gray-900 text-white font-bold py-3 px-6 rounded-lg hover:bg-gray-800 transition-colors">
                                عضویت
                            </button>
                        </form>
                    </div>
                    
                    <div class="hidden sm:block">
                        <span class="material-symbols-outlined text-[120px] text-white/20">loyalty</span>
                    </div>
                </div>
                
                <!-- Decorative Circle -->
                <div class="absolute -top-24 -left-24 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-blue-400/20 blur-3xl"></div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-[#1a202c] border-t border-gray-200 dark:border-gray-800 mt-12">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">خدمات مشتریان</h3>
                    <ul class="space-y-3">
                        <li><a href="faq.php" class="text-sm text-gray-500 hover:text-primary dark:text-gray-400">پاسخ به پرسش‌های متداول</a></li>
                        <li><a href="returns.php" class="text-sm text-gray-500 hover:text-primary dark:text-gray-400">رویه‌های بازگرداندن کالا</a></li>
                        <li><a href="terms.php" class="text-sm text-gray-500 hover:text-primary dark:text-gray-400">شرایط استفاده</a></li>
                        <li><a href="privacy.php" class="text-sm text-gray-500 hover:text-primary dark:text-gray-400">حریم خصوصی</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">راهنمای خرید</h3>
                    <ul class="space-y-3">
                        <li><a href="how-to-order.php" class="text-sm text-gray-500 hover:text-primary dark:text-gray-400">نحوه ثبت سفارش</a></li>
                        <li><a href="shipping.php" class="text-sm text-gray-500 hover:text-primary dark:text-gray-400">رویه ارسال سفارش</a></li>
                        <li><a href="payment.php" class="text-sm text-gray-500 hover:text-primary dark:text-gray-400">شیوه‌های پرداخت</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">همراه با ما</h3>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 hover:text-primary">
                            <span class="sr-only">Instagram</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path clip-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.468.937c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" fill-rule="evenodd"></path></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary">
                            <span class="sr-only">Twitter</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path></svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <div class="flex flex-col gap-4 items-start">
                        <div class="flex items-center gap-2">
                            <div class="flex size-10 items-center justify-center rounded-lg bg-primary text-white">
                                <span class="material-symbols-outlined">shopping_bag</span>
                            </div>
                            <span class="text-xl font-bold"><?php echo SITE_NAME; ?></span>
                        </div>
                        <p class="text-sm text-gray-500 leading-relaxed dark:text-gray-400">
                            ما در <?php echo SITE_NAME; ?> بهترین محصولات را با بالاترین کیفیت و بهترین قیمت برای شما فراهم کرده‌ایم.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 border-t border-gray-200 dark:border-gray-800 pt-8 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">© <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. تمامی حقوق محفوظ است.</p>
            </div>
        </div>
    </footer>
    </div>

    <!-- Toast Notification -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>

    <script>
        // Add to cart function
        function addToCart(productId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    updateCartCount();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('خطا در ارتباط با سرور', 'error');
                console.error('Error:', error);
            });
        }

        // Update cart count
        function updateCartCount() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartCountElements = document.querySelectorAll('.cart-count');
                    cartCountElements.forEach(element => {
                        element.textContent = data.count;
                        element.style.display = data.count > 0 ? 'flex' : 'none';
                    });
                })
                .catch(error => {
                    console.error('Error updating cart count:', error);
                });
        }

        // Show toast notification
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container');
            
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0 mb-3`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }

        // Newsletter subscription
        function subscribeNewsletter(event) {
            event.preventDefault();
            const email = event.target.querySelector('input[type="email"]').value;
            
            fetch('subscribe_newsletter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${email}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    event.target.reset();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('خطا در ارتباط با سرور', 'error');
                console.error('Error:', error);
            });
        }

        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.querySelector('.mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
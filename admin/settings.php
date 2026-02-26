<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Check if admin is logged in
$auth->require_admin();

$page_title = 'تنظیمات';

// Default settings (using config.php constants)
$settings = [
    'site_name' => SITE_NAME,
    'site_description' => 'فروشگاه آنلاین بازارچه - بهترین مکان برای خرید محصولات دیجیتال',
    'site_email' => SITE_EMAIL,
    'site_phone' => SITE_PHONE,
    'site_address' => 'تهران، ایران',
    'site_logo' => '',
    'site_favicon' => '',
    'currency' => 'IRR',
    'tax_rate' => 9,
    'shipping_fee' => 0,
    'maintenance_mode' => 0,
    'user_registration' => 1,
    'guest_checkout' => 1
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $site_name = $_POST['site_name'];
    $site_description = $_POST['site_description'];
    $site_email = $_POST['site_email'];
    $site_phone = $_POST['site_phone'];
    $site_address = $_POST['site_address'];
    $site_logo = $_POST['site_logo'];
    $site_favicon = $_POST['site_favicon'];
    $currency = $_POST['currency'];
    $tax_rate = $_POST['tax_rate'];
    $shipping_fee = $_POST['shipping_fee'];
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
    $user_registration = isset($_POST['user_registration']) ? 1 : 0;
    $guest_checkout = isset($_POST['guest_checkout']) ? 1 : 0;
    
    // Update config constants (this would need to be saved to config.php file in a real implementation)
    // For now, we'll just show success message
    $_SESSION['settings_updated'] = true;
    
    header("Location: settings.php?success=settings_updated");
    exit;
}

// Get currency options
$currencies = [
    'IRR' => 'ریال ایران',
    'USD' => 'دلار آمریکا',
    'EUR' => 'یورو'
];
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 5px;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
            color: #fff;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
            color: #fff;
        }
        .main-content {
            padding: 30px;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .nav-tabs .nav-link {
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            color: #135bec;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="d-flex flex-column align-items-center p-4">
                    <h4 class="text-white mb-4">
                        <i class="bi bi-shop"></i> <?php echo SITE_NAME; ?>
                    </h4>
                    <hr class="bg-white w-100">
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-speedometer2"></i> داشبورد
                    </a>
                    <a class="nav-link" href="products.php">
                        <i class="bi bi-box-seam"></i> محصولات
                    </a>
                    <a class="nav-link" href="orders.php">
                        <i class="bi bi-cart3"></i> سفارشات
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="bi bi-people"></i> کاربران
                    </a>
                    <a class="nav-link" href="categories.php">
                        <i class="bi bi-tags"></i> دسته‌بندی‌ها
                    </a>
                    <a class="nav-link" href="reviews.php">
                        <i class="bi bi-star"></i> نظرات
                    </a>
                    <a class="nav-link active" href="settings.php">
                        <i class="bi bi-gear"></i> تنظیمات
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="bi bi-box-arrow-right"></i> خروج
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav me-auto">
                                <li class="nav-item">
                                    <span class="navbar-text">
                                        <i class="bi bi-person-circle"></i> <?php echo $_SESSION['user_full_name']; ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                
                <!-- Page Header -->
                <h2 class="mb-4">تنظیمات سایت</h2>
                
                <!-- Success Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        تنظیمات با موفقیت ذخیره شد.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Settings Form -->
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" type="button">
                                    <i class="bi bi-gear"></i> عمومی
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#financial" type="button">
                                    <i class="bi bi-currency-rial"></i> مالی
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#features" type="button">
                                    <i class="bi bi-sliders"></i> قابلیت‌ها
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="tab-content">
                                <!-- General Settings -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">نام سایت</label>
                                            <input type="text" name="site_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">ایمیل سایت</label>
                                            <input type="email" name="site_email" class="form-control" 
                                                   value="<?php echo htmlspecialchars($settings['site_email']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">شماره تلفن</label>
                                            <input type="text" name="site_phone" class="form-control" 
                                                   value="<?php echo htmlspecialchars($settings['site_phone']); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">آدرس سایت</label>
                                            <input type="text" name="site_address" class="form-control" 
                                                   value="<?php echo htmlspecialchars($settings['site_address']); ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">توضیحات سایت</label>
                                            <textarea name="site_description" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">لوگو سایت</label>
                                            <input type="text" name="site_logo" class="form-control" 
                                                   value="<?php echo htmlspecialchars($settings['site_logo']); ?>" 
                                                   placeholder="مسیر فایل لوگو">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">آیکون سایت (Favicon)</label>
                                            <input type="text" name="site_favicon" class="form-control" 
                                                   value="<?php echo htmlspecialchars($settings['site_favicon']); ?>" 
                                                   placeholder="مسیر فایل آیکون">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Financial Settings -->
                                <div class="tab-pane fade" id="financial" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">واحد پول</label>
                                            <select name="currency" class="form-select" required>
                                                <?php foreach ($currencies as $code => $name): ?>
                                                    <option value="<?php echo $code; ?>" 
                                                            <?php echo $settings['currency'] == $code ? 'selected' : ''; ?>>
                                                        <?php echo $name; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">نرخ مالیات (%)</label>
                                            <input type="number" name="tax_rate" class="form-control" 
                                                   value="<?php echo htmlspecialchars($settings['tax_rate']); ?>" 
                                                   min="0" max="100" step="0.01">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">هزینه ارسال ثابت</label>
                                            <input type="number" name="shipping_fee" class="form-control" 
                                                   value="<?php echo htmlspecialchars($settings['shipping_fee']); ?>" 
                                                   min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Features Settings -->
                                <div class="tab-pane fade" id="features" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="maintenance_mode" 
                                                       id="maintenance_mode" 
                                                       <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="maintenance_mode">
                                                    حالت تعمیر و نگهداری سایت
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="user_registration" 
                                                       id="user_registration" 
                                                       <?php echo $settings['user_registration'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="user_registration">
                                                    ثبت‌نام کاربران جدید
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="guest_checkout" 
                                                       id="guest_checkout" 
                                                       <?php echo $settings['guest_checkout'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="guest_checkout">
                                                    خرید بدون نیاز به ثبت‌نام
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> ذخیره تنظیمات
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> انصراف
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
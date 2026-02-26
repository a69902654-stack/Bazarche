<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/products.php';
require_once '../includes/orders.php';

// Check if admin is logged in
$auth->require_admin();

$page_title = 'پنل مدیریت';

// Get statistics
$products_class = new Products();
$orders_class = new Orders();

$stats = [
    'total_products' => $products_class->get_product_count(),
    'total_orders' => $orders_class->get_order_count(),
    'total_users' => Database::getInstance()->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'user'"),
    'total_revenue' => Database::getInstance()->fetchColumn("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'"),
];

// Get recent orders
$recent_orders = $orders_class->get_all_orders(5);

// Get low stock products
$low_stock_products = Database::getInstance()->fetchAll(
    "SELECT * FROM products WHERE stock_quantity > 0 AND stock_quantity <= 10 ORDER BY stock_quantity ASC LIMIT 5"
);

// Get recent reviews
$recent_reviews = Database::getInstance()->fetchAll(
    "SELECT r.*, p.name as product_name, u.full_name as user_name 
     FROM reviews r 
     LEFT JOIN products p ON r.product_id = p.id 
     LEFT JOIN users u ON r.user_id = u.id 
     ORDER BY r.created_at DESC LIMIT 5"
);
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
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
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
                    <a class="nav-link active" href="index.php">
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
                    <a class="nav-link" href="settings.php">
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
                
                <!-- Dashboard Content -->
                <h2 class="mb-4">داشبورد مدیریت</h2>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <i class="bi bi-box-seam stat-icon text-primary"></i>
                                <div class="stat-value text-primary"><?php echo $stats['total_products']; ?></div>
                                <div class="stat-label">تعداد کل محصولات</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <i class="bi bi-cart3 stat-icon text-success"></i>
                                <div class="stat-value text-success"><?php echo $stats['total_orders']; ?></div>
                                <div class="stat-label">تعداد کل سفارشات</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <i class="bi bi-people stat-icon text-info"></i>
                                <div class="stat-value text-info"><?php echo $stats['total_users']; ?></div>
                                <div class="stat-label">تعداد کاربران</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <i class="bi bi-currency-rial stat-icon text-warning"></i>
                                <div class="stat-value text-warning"><?php echo format_price($stats['total_revenue']); ?></div>
                                <div class="stat-label">مجموع درآمد</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="table-container">
                            <h5 class="mb-3">سفارشات اخیر</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>شماره سفارش</th>
                                            <th>مشتری</th>
                                            <th>مبلغ</th>
                                            <th>وضعیت</th>
                                            <th>تاریخ</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td><?php echo $order['order_number']; ?></td>
                                                <td><?php echo $order['full_name']; ?></td>
                                                <td><?php echo format_price($order['total_amount']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        switch ($order['status']) {
                                                            case 'pending': echo 'warning'; break;
                                                            case 'processing': echo 'info'; break;
                                                            case 'shipped': echo 'primary'; break;
                                                            case 'delivered': echo 'success'; break;
                                                            case 'cancelled': echo 'danger'; break;
                                                            default: echo 'secondary';
                                                        }
                                                    ?>">
                                                        <?php echo $order['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('Y/m/d', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Low Stock Products -->
                    <div class="col-md-4">
                        <div class="table-container">
                            <h5 class="mb-3">محصولات با موجودی کم</h5>
                            <?php if (empty($low_stock_products)): ?>
                                <p class="text-muted">هیچ محصولی با موجودی کم وجود ندارد</p>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($low_stock_products as $product): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?php echo $product['name']; ?></h6>
                                                    <small class="text-muted">موجودی: <?php echo $product['stock_quantity']; ?></small>
                                                </div>
                                                <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Reviews -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container">
                            <h5 class="mb-3">نظرات اخیر</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>محصول</th>
                                            <th>کاربر</th>
                                            <th>امتیاز</th>
                                            <th>تاریخ</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_reviews as $review): ?>
                                            <tr>
                                                <td><?php echo $review['product_name']; ?></td>
                                                <td><?php echo $review['user_name']; ?></td>
                                                <td>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                                    <?php endfor; ?>
                                                </td>
                                                <td><?php echo date('Y/m/d', strtotime($review['created_at'])); ?></td>
                                                <td>
                                                    <a href="reviews.php?action=view&id=<?php echo $review['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
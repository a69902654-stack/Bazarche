<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/orders.php';

// Check if admin is logged in
$auth->require_admin();

$page_title = 'مدیریت سفارشات';

// Get orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$where = [];
$params = [];

if ($search) {
    $where[] = "(o.order_number LIKE ? OR u.full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "o.status = ?";
    $params[] = $status;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get total orders for pagination
$total_query = "SELECT COUNT(*) as total FROM orders o " . $where_clause;
$total_result = Database::getInstance()->fetchRow($total_query, $params);
$total_orders = $total_result['total'];
$total_pages = ceil($total_orders / $limit);

// Get orders
$query = "SELECT o.*, u.full_name, u.email, u.phone
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id
          $where_clause
          ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";

$orders = Database::getInstance()->fetchAll($query, $params);

// Get order status options
$status_options = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

// Get actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle order status update
if ($action == 'update_status' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    if (Database::getInstance()->update('orders', ['status' => $status], 'id = ?', [$order_id])) {
        header("Location: orders.php?success=status_updated");
        exit;
    }
}

// Get single order details
$order_details = null;
$order_items = null;
if ($action == 'view' && $order_id) {
    $order_details = Database::getInstance()->fetchRow("SELECT * FROM orders WHERE id = ?", [$order_id]);
    
    if ($order_details) {
        $order_items = Database::getInstance()->fetchAll("SELECT oi.*, p.name as product_name
                                                          FROM order_items oi
                                                          LEFT JOIN products p ON oi.product_id = p.id
                                                          WHERE oi.order_id = ?", [$order_id]);
    }
}
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
                    <a class="nav-link active" href="orders.php">
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
                
                <!-- Page Header -->
                <h2 class="mb-4">مدیریت سفارشات</h2>
                
                <!-- Success Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        switch($_GET['success']) {
                            case 'status_updated':
                                echo 'وضعیت سفارش با موفقیت به‌روزرسانی شد.';
                                break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="جستجو در سفارشات..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">همه وضعیت‌ها</option>
                                    <?php foreach ($status_options as $status_option): ?>
                                        <option value="<?php echo $status_option; ?>" 
                                                <?php echo $status == $status_option ? 'selected' : ''; ?>>
                                            <?php echo $status_option == 'pending' ? 'در انتظار' : 
                                                    ($status_option == 'processing' ? 'در حال پردازش' : 
                                                    ($status_option == 'shipped' ? 'ارسال شده' : 
                                                    ($status_option == 'delivered' ? 'تحویل داده شده' : 'لغو شده'))); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> جستجو
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="orders.php" class="btn btn-secondary w-100">
                                    <i class="bi bi-arrow-return-left"></i> بازگشت
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Orders Table -->
                <?php if (!$order_details): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>شماره سفارش</th>
                                            <th>مشتری</th>
                                            <th>تاریخ</th>
                                            <th>مبلغ</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td><?php echo $order['order_number']; ?></td>
                                                <td>
                                                    <div><?php echo $order['full_name']; ?></div>
                                                    <small class="text-muted"><?php echo $order['email']; ?></small>
                                                </td>
                                                <td><?php echo date('Y/m/d H:i', strtotime($order['created_at'])); ?></td>
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
                                                        <?php echo $order['status'] == 'pending' ? 'در انتظار' : 
                                                                ($order['status'] == 'processing' ? 'در حال پردازش' : 
                                                                ($order['status'] == 'shipped' ? 'ارسال شده' : 
                                                                ($order['status'] == 'delivered' ? 'تحویل داده شده' : 'لغو شده'))); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="orders.php?action=update_status&id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="orders.php?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . $status : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Order Details View -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">جزئیات سفارش #<?php echo $order_details['order_number']; ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>اطلاعات مشتری</h6>
                                    <p><strong>نام:</strong> <?php echo $order_details['full_name']; ?></p>
                                    <p><strong>ایمیل:</strong> <?php echo $order_details['email']; ?></p>
                                    <p><strong>تلفن:</strong> <?php echo $order_details['phone']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>اطلاعات سفارش</h6>
                                    <p><strong>تاریخ:</strong> <?php echo date('Y/m/d H:i', strtotime($order_details['created_at'])); ?></p>
                                    <p><strong>مبلغ کل:</strong> <?php echo format_price($order_details['total_amount']); ?></p>
                                    <p><strong>وضعیت پرداخت:</strong> 
                                        <span class="badge bg-<?php echo $order_details['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo $order_details['payment_status'] == 'paid' ? 'پرداخت شده' : 'در انتظار پرداخت'; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">محصولات سفارش</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>محصول</th>
                                            <th>تعداد</th>
                                            <th>قیمت واحد</th>
                                            <th>قیمت کل</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td><?php echo $item['product_name']; ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo format_price($item['price']); ?></td>
                                                <td><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>مجموع:</strong></td>
                                            <td><strong><?php echo format_price($order_details['total_amount']); ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Update Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">به‌روزرسانی وضعیت</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="orders.php?action=update_status&id=<?php echo $order_details['id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">وضعیت سفارش</label>
                                    <select name="status" class="form-select" required>
                                        <option value="pending" <?php echo $order_details['status'] == 'pending' ? 'selected' : ''; ?>>در انتظار</option>
                                        <option value="processing" <?php echo $order_details['status'] == 'processing' ? 'selected' : ''; ?>>در حال پردازش</option>
                                        <option value="shipped" <?php echo $order_details['status'] == 'shipped' ? 'selected' : ''; ?>>ارسال شده</option>
                                        <option value="delivered" <?php echo $order_details['status'] == 'delivered' ? 'selected' : ''; ?>>تحویل داده شده</option>
                                        <option value="cancelled" <?php echo $order_details['status'] == 'cancelled' ? 'selected' : ''; ?>>لغو شده</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> ذخیره تغییرات
                                </button>
                                <a href="orders.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-return-left"></i> بازگشت به لیست
                                </a>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
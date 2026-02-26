<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Check if admin is logged in
$auth->require_admin();

$page_title = 'مدیریت کاربران';

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';

// Build query
$where = [];
$params = [];

if ($search) {
    $where[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role) {
    $where[] = "u.role = ?";
    $params[] = $role;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get total users for pagination
$total_query = "SELECT COUNT(*) as total FROM users u " . $where_clause;
$total_result = Database::getInstance()->fetchRow($total_query, $params);
$total_users = $total_result['total'];
$total_pages = ceil($total_users / $limit);

// Get users
$query = "SELECT u.* FROM users u " . $where_clause . " ORDER BY u.created_at DESC LIMIT $limit OFFSET $offset";

$users = Database::getInstance()->fetchAll($query, $params);

// Get role options
$role_options = ['user', 'admin'];

// Get actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle user actions
if ($action && $user_id) {
    if ($action == 'update_role' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $role = $_POST['role'];
        if (Database::getInstance()->update('users', ['role' => $role], 'id = ?', [$user_id])) {
            header("Location: users.php?success=role_updated");
            exit;
        }
    }
    
    if ($action == 'delete' && isset($_POST['confirm'])) {
        if (Database::getInstance()->delete('users', 'id = ?', [$user_id])) {
            header("Location: users.php?success=user_deleted");
            exit;
        }
    }
    
    if ($action == 'toggle_status' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $status = $_POST['status'];
        if (Database::getInstance()->update('users', ['status' => $status], 'id = ?', [$user_id])) {
            header("Location: users.php?success=status_updated");
            exit;
        }
    }
}

// Get single user details
$user_details = null;
if ($action == 'view' && $user_id) {
    $user_details = Database::getInstance()->fetchRow("SELECT * FROM users WHERE id = ?", [$user_id]);
    
    if ($user_details) {
        // Get user orders
        $order_result = Database::getInstance()->fetchRow("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?", [$user_id]);
        $order_count = $order_result['order_count'];
        
        // Get user reviews
        $review_result = Database::getInstance()->fetchRow("SELECT COUNT(*) as review_count FROM reviews WHERE user_id = ?", [$user_id]);
        $review_count = $review_result['review_count'];
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
                    <a class="nav-link" href="orders.php">
                        <i class="bi bi-cart3"></i> سفارشات
                    </a>
                    <a class="nav-link active" href="users.php">
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
                <h2 class="mb-4">مدیریت کاربران</h2>
                
                <!-- Success Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        switch($_GET['success']) {
                            case 'role_updated':
                                echo 'نقش کاربر با موفقیت به‌روزرسانی شد.';
                                break;
                            case 'user_deleted':
                                echo 'کاربر با موفقیت حذف شد.';
                                break;
                            case 'status_updated':
                                echo 'وضعیت کاربر با موفقیت به‌روزرسانی شد.';
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
                                <input type="text" name="search" class="form-control" placeholder="جستجو در کاربران..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="role" class="form-select">
                                    <option value="">همه نقش‌ها</option>
                                    <option value="user" <?php echo $role == 'user' ? 'selected' : ''; ?>>کاربر عادی</option>
                                    <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>ادمین</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> جستجو
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="users.php" class="btn btn-secondary w-100">
                                    <i class="bi bi-arrow-return-left"></i> بازگشت
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Users Table -->
                <?php if (!$user_details): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>نام</th>
                                            <th>ایمیل</th>
                                            <th>تلفن</th>
                                            <th>نقش</th>
                                            <th>وضعیت</th>
                                            <th>تاریخ عضویت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo $user['full_name']; ?></td>
                                                <td><?php echo $user['email']; ?></td>
                                                <td><?php echo $user['phone'] ?: '-'; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                                        <?php echo $user['role'] == 'admin' ? 'ادمین' : 'کاربر عادی'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo $user['status'] == 'active' ? 'فعال' : 'غیرفعال'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <a href="users.php?action=view&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="users.php?action=update_role&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="users.php?action=toggle_status&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-toggle-on"></i>
                                                    </a>
                                                    <?php if ($user['role'] != 'admin'): ?>
                                                        <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('آیا از حذف این کاربر مطمئن هستید؟');">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
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
                                                <a class="page-link" href="users.php?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $role ? '&role=' . $role : ''; ?>">
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
                    <!-- User Details View -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">جزئیات کاربر</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>اطلاعات کاربری</h6>
                                    <p><strong>نام:</strong> <?php echo $user_details['full_name']; ?></p>
                                    <p><strong>ایمیل:</strong> <?php echo $user_details['email']; ?></p>
                                    <p><strong>تلفن:</strong> <?php echo $user_details['phone'] ?: '-'; ?></p>
                                    <p><strong>نقش:</strong> 
                                        <span class="badge bg-<?php echo $user_details['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo $user_details['role'] == 'admin' ? 'ادمین' : 'کاربر عادی'; ?>
                                        </span>
                                    </p>
                                    <p><strong>وضعیت:</strong> 
                                        <span class="badge bg-<?php echo $user_details['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $user_details['status'] == 'active' ? 'فعال' : 'غیرفعال'; ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>اطلاعات عمومی</h6>
                                    <p><strong>تاریخ عضویت:</strong> <?php echo date('Y/m/d H:i', strtotime($user_details['created_at'])); ?></p>
                                    <p><strong>تعداد سفارشات:</strong> <?php echo $order_count; ?></p>
                                    <p><strong>تعداد نظرات:</strong> <?php echo $review_count; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Forms -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">تغییر نقش کاربری</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="users.php?action=update_role&id=<?php echo $user_details['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">نقش کاربری</label>
                                            <select name="role" class="form-select" required>
                                                <option value="user" <?php echo $user_details['role'] == 'user' ? 'selected' : ''; ?>>کاربر عادی</option>
                                                <option value="admin" <?php echo $user_details['role'] == 'admin' ? 'selected' : ''; ?>>ادمین</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> ذخیره تغییرات
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">تغییر وضعیت</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="users.php?action=toggle_status&id=<?php echo $user_details['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">وضعیت کاربر</label>
                                            <select name="status" class="form-select" required>
                                                <option value="active" <?php echo $user_details['status'] == 'active' ? 'selected' : ''; ?>>فعال</option>
                                                <option value="inactive" <?php echo $user_details['status'] == 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> ذخیره تغییرات
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="users.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-return-left"></i> بازگشت به لیست
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
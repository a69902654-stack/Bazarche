<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Check if admin is logged in
$auth->require_admin();

$page_title = 'مدیریت نظرات';

// Get reviews with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

// Build query
$where = [];
$params = [];

if ($search) {
    $where[] = "(r.comment LIKE ? OR p.name LIKE ? OR u.full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($product_id) {
    $where[] = "r.product_id = ?";
    $params[] = $product_id;
}

if ($rating) {
    $where[] = "r.rating = ?";
    $params[] = $rating;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get total reviews for pagination
$total_query = "SELECT COUNT(*) as total FROM reviews r " . $where_clause;
$total_stmt = Database::getInstance()->prepare($total_query);
$total_stmt->execute($params);
$total_reviews = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_reviews / $limit);

// Get reviews
$query = "SELECT r.*, p.name as product_name, u.full_name as user_name 
          FROM reviews r 
          LEFT JOIN products p ON r.product_id = p.id 
          LEFT JOIN users u ON r.user_id = u.id 
          $where_clause 
          ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";

$stmt = Database::getInstance()->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get products for filter
$products = Database::getInstance()->fetchAll("SELECT * FROM products ORDER BY name");

// Get actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle review actions
if ($action && $review_id) {
    if ($action == 'delete' && isset($_POST['confirm'])) {
        $stmt = Database::getInstance()->prepare("DELETE FROM reviews WHERE id = ?");
        if ($stmt->execute([$review_id])) {
            header("Location: reviews.php?success=review_deleted");
            exit;
        }
    }
    
    if ($action == 'toggle_status' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $status = $_POST['status'];
        $stmt = Database::getInstance()->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $review_id])) {
            header("Location: reviews.php?success=status_updated");
            exit;
        }
    }
}

// Get single review details
$review_details = null;
if ($action == 'view' && $review_id) {
    $stmt = Database::getInstance()->prepare("SELECT r.*, p.name as product_name, u.full_name as user_name, u.email 
                                              FROM reviews r 
                                              LEFT JOIN products p ON r.product_id = p.id 
                                              LEFT JOIN users u ON r.user_id = u.id 
                                              WHERE r.id = ?");
    $stmt->execute([$review_id]);
    $review_details = $stmt->fetch(PDO::FETCH_ASSOC);
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
        .star-rating {
            color: #ffc107;
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
                    <a class="nav-link active" href="reviews.php">
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
                <h2 class="mb-4">مدیریت نظرات</h2>
                
                <!-- Success Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        switch($_GET['success']) {
                            case 'review_deleted':
                                echo 'نظر با موفقیت حذف شد.';
                                break;
                            case 'status_updated':
                                echo 'وضعیت نظر با موفقیت به‌روزرسانی شد.';
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
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="جستجو در نظرات..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="product_id" class="form-select">
                                    <option value="">همه محصولات</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo $product['id']; ?>" 
                                                <?php echo $product_id == $product['id'] ? 'selected' : ''; ?>>
                                            <?php echo $product['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="rating" class="form-select">
                                    <option value="">همه امتیازات</option>
                                    <option value="5" <?php echo $rating == 5 ? 'selected' : ''; ?>>5 ستاره</option>
                                    <option value="4" <?php echo $rating == 4 ? 'selected' : ''; ?>>4 ستاره</option>
                                    <option value="3" <?php echo $rating == 3 ? 'selected' : ''; ?>>3 ستاره</option>
                                    <option value="2" <?php echo $rating == 2 ? 'selected' : ''; ?>>2 ستاره</option>
                                    <option value="1" <?php echo $rating == 1 ? 'selected' : ''; ?>>1 ستاره</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> جستجو
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="reviews.php" class="btn btn-secondary w-100">
                                    <i class="bi bi-arrow-return-left"></i> بازگشت
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Reviews Table -->
                <?php if (!$review_details): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>محصول</th>
                                            <th>کاربر</th>
                                            <th>امتیاز</th>
                                            <th>نظر</th>
                                            <th>تاریخ</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reviews as $review): ?>
                                            <tr>
                                                <td><?php echo $review['product_name']; ?></td>
                                                <td>
                                                    <div><?php echo $review['user_name']; ?></div>
                                                    <small class="text-muted"><?php echo $review['email']; ?></small>
                                                </td>
                                                <td>
                                                    <div class="star-rating">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($review['comment']); ?>">
                                                        <?php echo htmlspecialchars($review['comment']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo date('Y/m/d', strtotime($review['created_at'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $review['status'] == 'approved' ? 'success' : 'warning'; ?>">
                                                        <?php echo $review['status'] == 'approved' ? 'تایید شده' : 'در انتظار تایید'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="reviews.php?action=view&id=<?php echo $review['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="reviews.php?action=toggle_status&id=<?php echo $review['id']; ?>" 
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-toggle-on"></i>
                                                    </a>
                                                    <a href="reviews.php?action=delete&id=<?php echo $review['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('آیا از حذف این نظر مطمئن هستید؟');">
                                                        <i class="bi bi-trash"></i>
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
                                                <a class="page-link" href="reviews.php?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $product_id ? '&product_id=' . $product_id : ''; ?><?php echo $rating ? '&rating=' . $rating : ''; ?>">
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
                    <!-- Review Details View -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">جزئیات نظر</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>اطلاعات نظر</h6>
                                    <p><strong>محصول:</strong> <?php echo $review_details['product_name']; ?></p>
                                    <p><strong>کاربر:</strong> <?php echo $review_details['user_name']; ?></p>
                                    <p><strong>ایمیل:</strong> <?php echo $review_details['email']; ?></p>
                                    <p><strong>امتیاز:</strong> 
                                        <span class="star-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $review_details['rating'] ? '-fill' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </p>
                                    <p><strong>تاریخ:</strong> <?php echo date('Y/m/d H:i', strtotime($review_details['created_at'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>وضعیت</h6>
                                    <p><strong>وضعیت فعلی:</strong> 
                                        <span class="badge bg-<?php echo $review_details['status'] == 'approved' ? 'success' : 'warning'; ?>">
                                            <?php echo $review_details['status'] == 'approved' ? 'تایید شده' : 'در انتظار تایید'; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Review Comment -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">متن نظر</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light">
                                <?php echo nl2br(htmlspecialchars($review_details['comment'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Forms -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">مدیریت نظر</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="reviews.php?action=toggle_status&id=<?php echo $review_details['id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">وضعیت نظر</label>
                                    <select name="status" class="form-select" required>
                                        <option value="approved" <?php echo $review_details['status'] == 'approved' ? 'selected' : ''; ?>>تایید شده</option>
                                        <option value="pending" <?php echo $review_details['status'] == 'pending' ? 'selected' : ''; ?>>در انتظار تایید</option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> ذخیره تغییرات
                                    </button>
                                    <a href="reviews.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-return-left"></i> بازگشت به لیست
                                    </a>
                                    <a href="reviews.php?action=delete&id=<?php echo $review_details['id']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('آیا از حذف این نظر مطمئن هستید؟');">
                                        <i class="bi bi-trash"></i> حذف نظر
                                    </a>
                                </div>
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
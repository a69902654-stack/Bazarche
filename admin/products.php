<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/products.php';

// Check if admin is logged in
$auth->require_admin();

$page_title = 'مدیریت محصولات';

// Get products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build query
$where = [];
$params = [];

if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where[] = "p.category_id = ?";
    $params[] = $category;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get total products for pagination
$total_query = "SELECT COUNT(*) as total FROM products p " . $where_clause;
$total_result = Database::getInstance()->fetchRow($total_query, $params);
$total_products = $total_result['total'];
$total_pages = ceil($total_products / $limit);

// Get products
$query = "SELECT p.*, c.name as category_name
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          $where_clause
          ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";

$products = Database::getInstance()->fetchAll($query, $params);

// Get categories for filter
$categories = Database::getInstance()->fetchAll("SELECT * FROM categories ORDER BY name");

// Get actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle add/edit actions
if ($action && $product_id) {
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
            $upload_dir = '../../uploads/';
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
        
        if (Database::getInstance()->update('products', $update_data, 'id = ?', [$product_id])) {
            header("Location: products.php?success=product_updated");
            exit;
        }
    }
    
    if ($action == 'delete' && isset($_POST['confirm'])) {
        if (Database::getInstance()->delete('products', 'id = ?', [$product_id])) {
            header("Location: products.php?success=product_deleted");
            exit;
        }
    }
    
    if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        // Handle product creation
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $discount_price = $_POST['discount_price'] ?: null;
        $stock_quantity = $_POST['stock_quantity'];
        $category_id = $_POST['category_id'];
        $status = $_POST['status'];
        
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = '../../uploads/';
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
        
        $insert_data = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'discount_price' => $discount_price,
            'stock_quantity' => $stock_quantity,
            'category_id' => $category_id,
            'status' => $status,
            'image' => $image
        ];
        
        if (Database::getInstance()->insert('products', $insert_data)) {
            header("Location: products.php?success=product_added");
            exit;
        }
    }
}

// Get single product for editing
$product = null;
if ($action == 'edit' && $product_id) {
    $product = Database::getInstance()->fetchRow("SELECT * FROM products WHERE id = ?", [$product_id]);
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
                    <a class="nav-link active" href="products.php">
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
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>مدیریت محصولات</h2>
                    <a href="products.php?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> افزودن محصول
                    </a>
                </div>
                
                <!-- Success Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="جستجو در محصولات..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select">
                                    <option value="">همه دسته‌بندی‌ها</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $category == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo $category['name']; ?>
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
                                <a href="products.php" class="btn btn-secondary w-100">
                                    <i class="bi bi-arrow-return-left"></i> بازگشت
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Products Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>عکس</th>
                                        <th>نام محصول</th>
                                        <th>دسته‌بندی</th>
                                        <th>قیمت</th>
                                        <th>موجودی</th>
                                        <th>وضعیت</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <?php if ($product['image']): ?>
                                                    <img src="../../uploads/<?php echo $product['image']; ?>" 
                                                         alt="<?php echo $product['name']; ?>" 
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <img src="https://picsum.photos/seed/<?php echo $product['id']; ?>/50/50.jpg" 
                                                         alt="<?php echo $product['name']; ?>" 
                                                         class="img-thumbnail">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $product['name']; ?></td>
                                            <td><?php echo $product['category_name']; ?></td>
                                            <td>
                                                <?php if ($product['discount_price']): ?>
                                                    <span class="text-decoration-line-through text-muted small">
                                                        <?php echo format_price($product['price']); ?>
                                                    </span><br>
                                                    <span class="text-danger fw-bold">
                                                        <?php echo format_price($product['discount_price']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <?php echo format_price($product['price']); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['stock_quantity'] > 10 ? 'success' : 
                                                                        ($product['stock_quantity'] > 0 ? 'warning' : 'danger'); ?>">
                                                    <?php echo $product['stock_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $product['status'] == 'active' ? 'فعال' : 'غیرفعال'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('آیا از حذف این محصول مطمئن هستید؟');">
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
                                            <a class="page-link" href="products.php?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
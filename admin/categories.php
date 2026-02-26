<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Check if admin is logged in
$auth->require_admin();

$page_title = 'مدیریت دسته‌بندی‌ها';

// Get categories
$categories = Database::getInstance()->fetchAll("SELECT * FROM categories ORDER BY parent_id, name");

// Build nested categories tree
function build_category_tree($categories, $parent_id = 0, $level = 0) {
    $tree = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id) {
            $category['level'] = $level;
            $category['children'] = build_category_tree($categories, $category['id'], $level + 1);
            $tree[] = $category;
        }
    }
    return $tree;
}

$category_tree = build_category_tree($categories);

// Helper function to check if category is descendant
function is_descendant($category_id, $tree) {
    foreach ($tree as $category) {
        if ($category['id'] == $category_id) return true;
        if (!empty($category['children']) && is_descendant($category_id, $category['children'])) return true;
    }
    return false;
}

// Get actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle category actions
if ($action && $category_id) {
    if ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $parent_id = $_POST['parent_id'] ?: null;
        $status = $_POST['status'];
        
        $stmt = Database::getInstance()->prepare("UPDATE categories SET name = ?, description = ?, parent_id = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $parent_id, $status, $category_id])) {
            header("Location: categories.php?success=category_updated");
            exit;
        }
    }
    
    if ($action == 'delete' && isset($_POST['confirm'])) {
        // Check if category has products
        $stmt = Database::getInstance()->prepare("SELECT COUNT(*) as product_count FROM products WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $product_count = $stmt->fetch(PDO::FETCH_ASSOC)['product_count'];
        
        if ($product_count > 0) {
            header("Location: categories.php?error=category_has_products");
            exit;
        }
        
        // Check if category has subcategories
        $stmt = Database::getInstance()->prepare("SELECT COUNT(*) as subcategory_count FROM categories WHERE parent_id = ?");
        $stmt->execute([$category_id]);
        $subcategory_count = $stmt->fetch(PDO::FETCH_ASSOC)['subcategory_count'];
        
        if ($subcategory_count > 0) {
            header("Location: categories.php?error=category_has_subcategories");
            exit;
        }
        
        $stmt = Database::getInstance()->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$category_id])) {
            header("Location: categories.php?success=category_deleted");
            exit;
        }
    }
    
    if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $parent_id = $_POST['parent_id'] ?: null;
        $status = $_POST['status'];
        
        $stmt = Database::getInstance()->prepare("INSERT INTO categories (name, description, parent_id, status) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $parent_id, $status])) {
            header("Location: categories.php?success=category_added");
            exit;
        }
    }
}

// Get single category for editing
$category = null;
if ($action == 'edit' && $category_id) {
    $stmt = Database::getInstance()->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get all categories for parent selection (excluding current category and its children)
    $all_categories = Database::getInstance()->fetchAll("SELECT * FROM categories ORDER BY name");
    $available_parents = array_filter($all_categories, function($cat) use ($category_id, $category_tree) {
        return $cat['id'] != $category_id && !is_descendant($cat['id'], $category_tree);
    });
}

// Recursive function to display categories
function display_category($category, $level = 0) {
    echo '<div class="category-item level-' . $level . '">';
    echo '<div class="d-flex justify-content-between align-items-start">';
    echo '<div>';
    echo '<h6 class="mb-1">' . htmlspecialchars($category['name']) . '</h6>';
    if ($category['description']) {
        echo '<small class="text-muted">' . htmlspecialchars($category['description']) . '</small>';
    }
    echo '</div>';
    echo '<div class="btn-group btn-group-sm">';
    echo '<a href="categories.php?action=edit&id=' . $category['id'] . '" class="btn btn-outline-primary">';
    echo '<i class="bi bi-pencil"></i>';
    echo '</a>';
    echo '<a href="categories.php?action=delete&id=' . $category['id'] . '" class="btn btn-outline-danger" onclick="return confirm(\'آیا از حذف این دسته‌بندی مطمئن هستید؟\');">';
    echo '<i class="bi bi-trash"></i>';
    echo '</a>';
    echo '</div>';
    echo '</div>';
    
    if (!empty($category['children'])) {
        foreach ($category['children'] as $child) {
            display_category($child, $level + 1);
        }
    }
    
    echo '</div>';
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
        .category-item {
            border-left: 3px solid #dee2e6;
            padding-right: 15px;
            margin-bottom: 10px;
        }
        .category-item.level-1 {
            border-left-color: #6c757d;
            margin-right: 20px;
        }
        .category-item.level-2 {
            border-left-color: #007bff;
            margin-right: 40px;
        }
        .category-item.level-3 {
            border-left-color: #28a745;
            margin-right: 60px;
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
                    <a class="nav-link active" href="categories.php">
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
                    <h2>مدیریت دسته‌بندی‌ها</h2>
                    <a href="categories.php?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> افزودن دسته‌بندی
                    </a>
                </div>
                
                <!-- Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        switch($_GET['success']) {
                            case 'category_added':
                                echo 'دسته‌بندی با موفقیت اضافه شد.';
                                break;
                            case 'category_updated':
                                echo 'دسته‌بندی با موفقیت ویرایش شد.';
                                break;
                            case 'category_deleted':
                                echo 'دسته‌بندی با موفقیت حذف شد.';
                                break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php
                        switch($_GET['error']) {
                            case 'category_has_products':
                                echo 'این دسته‌بندی شامل محصولات است و نمی‌توان آن را حذف کرد.';
                                break;
                            case 'category_has_subcategories':
                                echo 'این دسته‌بندی شامل زیردسته‌ها است و نمی‌توان آن را حذف کرد.';
                                break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Add/Edit Category Form -->
                <?php if ($action == 'add' || $action == 'edit'): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><?php echo $action == 'add' ? 'افزودن دسته‌بندی جدید' : 'ویرایش دسته‌بندی'; ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">نام دسته‌بندی</label>
                                        <input type="text" name="name" class="form-control" 
                                               value="<?php echo $category ? htmlspecialchars($category['name']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">دسته‌بندی والد</label>
                                        <select name="parent_id" class="form-select">
                                            <option value="">بدون والد (دسته‌بندی اصلی)</option>
                                            <?php foreach ($available_parents as $parent): ?>
                                                <option value="<?php echo $parent['id']; ?>" 
                                                        <?php echo ($category && $category['parent_id'] == $parent['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $parent['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">توضیحات</label>
                                        <textarea name="description" class="form-control" rows="3"><?php echo $category ? htmlspecialchars($category['description']) : ''; ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">وضعیت</label>
                                        <select name="status" class="form-select" required>
                                            <option value="active" <?php echo (!$category || $category['status'] == 'active') ? 'selected' : ''; ?>>فعال</option>
                                            <option value="inactive" <?php echo ($category && $category['status'] == 'inactive') ? 'selected' : ''; ?>>غیرفعال</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> ذخیره تغییرات
                                        </button>
                                        <a href="categories.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle"></i> انصراف
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Categories List -->
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="mt-3 text-muted">هیچ دسته‌بندی وجود ندارد</p>
                                <a href="categories.php?action=add" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> افزودن دسته‌بندی اول
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="categories-list">
                                <?php foreach ($category_tree as $category_item): ?>
                                    <?php display_category($category_item); ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
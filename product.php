<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/products.php';
require_once 'includes/cart.php';
require_once 'includes/auth.php';

$page_title = 'جزئیات محصول';
$products = new Products();
$cart = new Cart();

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    redirect('index.php');
}

$product = $products->get_product($product_id);

if (!$product) {
    set_message('محصول یافت نشد', 'error');
    redirect('index.php');
}

// Get related products
$related_products = $products->get_related_products($product_id, $product['category_id'], 4);

// Get product reviews
$reviews = $products->get_product_reviews($product_id);

// Get product rating
$rating_info = $products->get_product_rating($product_id);

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity <= 0) {
        $quantity = 1;
    }
    
    if ($auth->is_logged_in()) {
        $result = $cart->add_to_cart($_SESSION['user_id'], $product_id, $quantity);
        set_message($result['message'], $result['success'] ? 'success' : 'error');
    } else {
        set_message('لطفاً ابتدا وارد حساب کاربری خود شوید', 'warning');
        redirect('login.php?redirect=product.php?id=' . $product_id);
    }
}

// Handle add review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    if ($auth->is_logged_in()) {
        $result = $products->add_review([
            'product_id' => $product_id,
            'user_id' => $_SESSION['user_id'],
            'rating' => (int)$_POST['rating'],
            'comment' => sanitize_input($_POST['comment'])
        ]);
        
        set_message($result['message'], $result['success'] ? 'success' : 'error');
        
        if ($result['success']) {
            redirect('product.php?id=' . $product_id);
        }
    } else {
        set_message('لطفاً ابتدا وارد حساب کاربری خود شوید', 'warning');
        redirect('login.php?redirect=product.php?id=' . $product_id);
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - <?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
        .product-image {
            max-height: 500px;
            object-fit: contain;
            width: 100%;
        }
        .product-price {
            font-size: 2rem;
            font-weight: 700;
            color: #dc3545;
        }
        .product-price .old-price {
            font-size: 1.2rem;
            color: #6c757d;
            text-decoration: line-through;
            margin-right: 10px;
        }
        .rating {
            color: #ffc107;
        }
        .review-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .related-product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
            height: 100%;
        }
        .related-product-card:hover {
            transform: translateY(-5px);
        }
        .related-product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .stock-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .in-stock {
            background-color: #d4edda;
            color: #155724;
        }
        .low-stock {
            background-color: #fff3cd;
            color: #856404;
        }
        .out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">خانه</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">محصولات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">تماس با ما</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="search.php">
                            <i class="bi bi-search"></i> جستجو
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="bi bi-cart3"></i>
                        </a>
                    </li>
                    <?php if ($auth->is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo $_SESSION['user_full_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">پروفایل کاربری</a></li>
                                <li><a class="dropdown-item" href="orders.php">سفارشات</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">خروج</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">ورود</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">ثبت نام</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">خانه</a></li>
                <li class="breadcrumb-item"><a href="products.php">محصولات</a></li>
                <li class="breadcrumb-item active"><?php echo $product['name']; ?></li>
            </ol>
        </nav>
    </div>

    <!-- Product Details -->
    <div class="container mt-4">
        <?php display_message(); ?>
        
        <div class="row">
            <!-- Product Image -->
            <div class="col-lg-6">
                <div class="card">
                    <img src="<?php echo $product['image'] ? 'uploads/' . $product['image'] : 'https://picsum.photos/seed/product' . $product['id'] . '/600/500'; ?>" 
                         alt="<?php echo $product['name']; ?>" class="product-image">
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title"><?php echo $product['name']; ?></h1>
                        <p class="text-muted"><?php echo $product['category_name']; ?></p>
                        
                        <div class="rating mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= $rating_info['avg_rating'] ? '-fill' : ''; ?>"></i>
                            <?php endfor; ?>
                            <span class="me-2"><?php echo number_format($rating_info['avg_rating'], 1); ?></span>
                            <span class="text-muted">(<?php echo $rating_info['review_count']; ?> نظر)</span>
                        </div>
                        
                        <div class="product-price mb-3">
                            <?php if ($product['discount_price']): ?>
                                <span class="old-price"><?php echo format_price($product['price']); ?></span>
                                <?php echo format_price($product['discount_price']); ?>
                            <?php else: ?>
                                <?php echo format_price($product['price']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <span class="stock-badge <?php 
                                if ($product['stock_quantity'] > 10) echo 'in-stock';
                                elseif ($product['stock_quantity'] > 0) echo 'low-stock';
                                else echo 'out-of-stock';
                            ?>">
                                <?php 
                                if ($product['stock_quantity'] > 10) echo 'موجود در انبار';
                                elseif ($product['stock_quantity'] > 0) echo 'موجود محدود';
                                else echo 'ناموجود';
                                ?>
                            </span>
                        </div>
                        
                        <p class="card-text"><?php echo $product['description']; ?></p>
                        
                        <form method="POST" action="product.php?id=<?php echo $product['id']; ?>">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label for="quantity" class="form-label">تعداد</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary w-100">
                                        <i class="bi bi-cart-plus"></i> افزودن به سبد خرید
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="mt-4">
                            <h5>اطلاعات محصول</h5>
                            <ul class="list-unstyled">
                                <li><strong>دسته بندی:</strong> <?php echo $product['category_name']; ?></li>
                                <li><strong>وضعیت:</strong> <?php echo $product['status']; ?></li>
                                <li><strong>تعداد موجودی:</strong> <?php echo $product['stock_quantity']; ?> عدد</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>محصولات مشابه</h3>
            </div>
            <?php foreach ($related_products as $related_product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card related-product-card">
                        <img src="<?php echo $related_product['image'] ? 'uploads/' . $related_product['image'] : 'https://picsum.photos/seed/product' . $related_product['id'] . '/300/200'; ?>" 
                             alt="<?php echo $related_product['name']; ?>" class="related-product-image">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $related_product['name']; ?></h6>
                            <div class="price">
                                <?php if ($related_product['discount_price']): ?>
                                    <span class="old-price"><?php echo format_price($related_product['price']); ?></span>
                                    <?php echo format_price($related_product['discount_price']); ?>
                                <?php else: ?>
                                    <?php echo format_price($related_product['price']); ?>
                                <?php endif; ?>
                            </div>
                            <a href="product.php?id=<?php echo $related_product['id']; ?>" class="btn btn-outline-primary btn-sm w-100">مشاهده</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Reviews Section -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>نظرات کاربران</h3>
            </div>
            
            <!-- Add Review Form -->
            <?php if ($auth->is_logged_in()): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>نظر خود را بنویسید</h5>
                            <form method="POST" action="product.php?id=<?php echo $product['id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">امتیاز</label>
                                    <select class="form-select" name="rating" required>
                                        <option value="">انتخاب کنید</option>
                                        <option value="5">5 - عالی</option>
                                        <option value="4">4 - خوب</option>
                                        <option value="3">3 - متوسط</option>
                                        <option value="2">2 - ضعیف</option>
                                        <option value="1">1 - بسیار ضعیف</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="comment" class="form-label">نظر</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                                </div>
                                <button type="submit" name="add_review" class="btn btn-primary">ثبت نظر</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <p>برای ثبت نظر باید <a href="login.php?redirect=product.php?id=<?php echo $product['id']; ?>">وارد حساب کاربری</a> شوید</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Reviews List -->
            <div class="col-md-6">
                <?php if (empty($reviews)): ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <p>هنوز نظری برای این محصول ثبت نشده است</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="card review-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title"><?php echo $review['user_name']; ?></h6>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo date('Y/m/d', strtotime($review['created_at'])); ?></small>
                                </div>
                                <?php if ($review['comment']): ?>
                                    <p class="card-text mt-2"><?php echo $review['comment']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>فروشگاه آنلاین با بهترین قیمت‌ها و کیفیت‌ها</p>
                </div>
                <div class="col-md-4">
                    <h5>تماس با ما</h5>
                    <p><i class="bi bi-telephone"></i> <?php echo SITE_PHONE; ?></p>
                    <p><i class="bi bi-envelope"></i> <?php echo SITE_EMAIL; ?></p>
                </div>
                <div class="col-md-4">
                    <h5>دسترسی سریع</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">خانه</a></li>
                        <li><a href="products.php" class="text-white-50">محصولات</a></li>
                        <li><a href="contact.php" class="text-white-50">تماس با ما</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
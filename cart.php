<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/products.php';
require_once 'includes/cart.php';
require_once 'includes/auth.php';
require_once 'includes/orders.php';

$page_title = 'سبد خرید';
$products = new Products();
$cart = new Cart();
$orders = new Orders();

if (!$auth->is_logged_in()) {
    set_message('لطفاً ابتدا وارد حساب کاربری خود شوید', 'warning');
    redirect('login.php?redirect=cart.php');
}

$cart_items = $cart->get_cart_items($_SESSION['user_id']);
$cart_total = $cart->get_cart_total($_SESSION['user_id']);
$cart_count = $cart->get_cart_item_count($_SESSION['user_id']);

// Handle update cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        if ($quantity > 0) {
            $result = $cart->update_cart_quantity($_SESSION['user_id'], $product_id, $quantity);
            if (!$result['success']) {
                set_message($result['message'], 'error');
            }
        }
    }
    redirect('cart.php');
}

// Handle remove from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $result = $cart->remove_from_cart($_SESSION['user_id'], $product_id);
    set_message($result['message'], $result['success'] ? 'success' : 'error');
    redirect('cart.php');
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $user = $auth->current_user();
    
    $order_data = [
        'total_amount' => $cart_total,
        'payment_method' => $_POST['payment_method'],
        'payment_status' => 'pending',
        'shipping_address' => $_POST['shipping_address'],
        'notes' => $_POST['notes'],
        'items' => []
    ];
    
    foreach ($cart_items as $item) {
        $order_data['items'][] = [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'stock_quantity' => $item['stock_quantity']
        ];
    }
    
    $result = $orders->create_order($_SESSION['user_id'], $order_data);
    
    if ($result['success']) {
        set_message($result['message'], 'success');
        redirect('order_success.php?order_id=' . $result['order_id']);
    } else {
        set_message($result['message'], 'error');
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
        .cart-item {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .cart-item-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #dc3545;
        }
        .cart-item-price .old-price {
            font-size: 0.9rem;
            color: #6c757d;
            text-decoration: line-through;
            margin-right: 5px;
        }
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: #333;
        }
        .order-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .order-summary .total-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #dc3545;
        }
        .empty-cart {
            text-align: center;
            padding: 50px;
        }
        .empty-cart i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
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
                            <?php if ($cart_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">خانه</a></li>
                <li class="breadcrumb-item active">سبد خرید</li>
            </ol>
        </nav>
    </div>

    <!-- Cart Content -->
    <div class="container mt-4">
        <?php display_message(); ?>
        
        <h2 class="section-title">سبد خرید</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="bi bi-cart-x"></i>
                <h3>سبد خرید شما خالی است</h3>
                <p>برای ادامه خرید به محصولات ما بروید</p>
                <a href="index.php" class="btn btn-primary">شopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <form method="POST" action="cart.php">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="card cart-item">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="<?php echo $item['image'] ? 'uploads/' . $item['image'] : 'https://picsum.photos/seed/product' . $item['product_id'] . '/100/100'; ?>" 
                                                 alt="<?php echo $item['name']; ?>" class="cart-item-image">
                                        </div>
                                        <div class="col-md-4">
                                            <h5 class="card-title"><?php echo $item['name']; ?></h5>
                                            <p class="text-muted"><?php echo $item['category_name']; ?></p>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="cart-item-price">
                                                <?php if ($item['discount_price']): ?>
                                                    <span class="old-price"><?php echo format_price($item['price']); ?></span>
                                                    <?php echo format_price($item['discount_price']); ?>
                                                <?php else: ?>
                                                    <?php echo format_price($item['price']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="input-group">
                                                <span class="input-group-text">تعداد</span>
                                                <input type="number" class="form-control" name="quantity[<?php echo $item['product_id']; ?>]" 
                                                       value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" name="update_cart" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-arrow-repeat"></i> به‌روزرسانی
                                            </button>
                                            <button type="submit" name="remove_from_cart" class="btn btn-sm btn-outline-danger mt-2">
                                                <i class="bi bi-trash"></i> حذف
                                            </button>
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h4>خلاصه سفارش</h4>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>تعداد کالا:</span>
                            <span><?php echo $cart_count; ?> عدد</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>مجموع قیمت:</span>
                            <span><?php echo format_price($cart_total); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span><strong>مجموع کل:</strong></span>
                            <span class="total-price"><?php echo format_price($cart_total); ?></span>
                        </div>
                        
                        <form method="POST" action="cart.php">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">روش پرداخت</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">انتخاب کنید</option>
                                    <option value="online">پرداخت آنلاین</option>
                                    <option value="cash">پرداخت در محل</option>
                                    <option value="bank">واریز به حساب</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">آدرس ارسال</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">توضیحات</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                            
                            <button type="submit" name="checkout" class="btn btn-success w-100">
                                <i class="bi bi-credit-card"></i> نهایی کردن سفارش
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
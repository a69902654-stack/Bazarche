<?php
require_once 'config.php';
require_once 'includes/auth.php';

$page_title = 'ثبت نام حساب کاربری';

if ($auth->is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => sanitize_input($_POST['username']),
        'email' => sanitize_input($_POST['email']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'full_name' => sanitize_input($_POST['full_name']),
        'phone' => sanitize_input($_POST['phone']),
        'address' => sanitize_input($_POST['address'])
    ];
    
    if ($data['password'] !== $data['confirm_password']) {
        set_message('رمز عبور و تکرار رمز عبور یکسان نیستند', 'error');
    } else {
        unset($data['confirm_password']);
        $result = $auth->register($data);
        
        if ($result['success']) {
            set_message($result['message'], 'success');
            redirect('login.php');
        } else {
            set_message($result['message'], 'error');
        }
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
            background-color: #f8f9fa;
            font-family: 'Vazirmatn', sans-serif;
        }
        .register-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h2 {
            color: #333;
            font-weight: 600;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px;
        }
        .btn-register {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background-color: #28a745;
            border: none;
            color: white;
            font-weight: 500;
        }
        .btn-register:hover {
            background-color: #218838;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h2><i class="bi bi-person-plus"></i> ثبت نام حساب کاربری</h2>
                <p class="text-muted">در <?php echo SITE_NAME; ?> ثبت نام کنید</p>
            </div>
            
            <?php display_message(); ?>
            
            <form method="POST" action="register.php">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label">نام کامل</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">نام کاربری</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-at"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">ایمیل</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">شماره تماس</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="09123456789" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">آدرس</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">رمز عبور</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <small class="form-text text-muted">رمز عبور باید حداقل 6 کاراکتر باشد</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">تکرار رمز عبور</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        من <a href="#">قوانین و شرایط</a> را می‌پذیرم
                    </label>
                </div>
                
                <button type="submit" class="btn btn-register">
                    <i class="bi bi-person-plus"></i> ثبت نام
                </button>
            </form>
            
            <div class="login-link">
                <p>قبلاً ثبت نام کرده‌اید؟ <a href="login.php">ورود کنید</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
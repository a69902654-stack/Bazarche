<?php
require_once 'config.php';
require_once 'includes/auth.php';

$page_title = 'ورود به حساب کاربری';

if ($auth->is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        set_message('لطفاً تمام فیلدها را پر کنید', 'error');
    } else {
        if ($auth->login($username, $password)) {
            set_message('ورود با موفقیت انجام شد', 'success');
            // Redirect based on user role
            if ($auth->is_admin()) {
                redirect('admin/index.php');
            } else {
                redirect('user/dashboard.php');
            }
        } else {
            set_message('نام کاربری یا رمز عبور اشتباه است', 'error');
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
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #333;
            font-weight: 600;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background-color: #007bff;
            border: none;
            color: white;
            font-weight: 500;
        }
        .btn-login:hover {
            background-color: #0056b3;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2><i class="bi bi-box-arrow-in-right"></i> ورود به حساب کاربری</h2>
                <p class="text-muted">به <?php echo SITE_NAME; ?> خوش آمدید</p>
            </div>
            
            <?php display_message(); ?>
            
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="username" class="form-label">نام کاربری یا ایمیل</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">رمز عبور</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">مرا به خاطر بسپار</label>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> ورود
                </button>
            </form>
            
            <div class="register-link">
                <p>حساب کاربری ندارید؟ <a href="register.php">ثبت نام کنید</a></p>
                <p><a href="forgot-password.php">رمز عبور خود را فراموش کرده‌اید؟</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
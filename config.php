<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bazarche');

// Site configuration
define('SITE_NAME', 'بازارچه');
define('SITE_URL', 'http://localhost/بازارچه');
define('SITE_EMAIL', 'info@bazarche.com');
define('SITE_PHONE', '021-12345678');

// File upload configuration
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Tehran');

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Function to redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Function to set session message
function set_message($message, $type = 'info') {
    $_SESSION['message'] = [
        'text' => $message,
        'type' => $type
    ];
}

// Function to display session message
function display_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $message['type'];
        $text = $message['text'];
        
        $alert_class = '';
        switch ($type) {
            case 'success':
                $alert_class = 'alert-success';
                break;
            case 'error':
                $alert_class = 'alert-danger';
                break;
            case 'warning':
                $alert_class = 'alert-warning';
                break;
            default:
                $alert_class = 'alert-info';
        }
        
        echo "<div class='alert {$alert_class} alert-dismissible fade show' role='alert'>";
        echo $text;
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
        echo "</div>";
        
        unset($_SESSION['message']);
    }
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to get current user
function current_user() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Function to format price
function format_price($price) {
    return number_format($price, 0, ',', '.') . ' تومان';
}

// Function to generate unique order number
function generate_order_number() {
    return 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
}

// Function to validate email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate phone number
function is_valid_phone($phone) {
    return preg_match('/^09[0-9]{9}$/', $phone);
}

// Function to upload file
function upload_file($file, $allowed_types = null) {
    global $allowed_image_types;
    
    if ($allowed_types === null) {
        $allowed_types = $allowed_image_types;
    }
    
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload'];
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'File size exceeds limit'];
        case UPLOAD_ERR_PARTIAL:
            return ['success' => false, 'message' => 'File was only partially uploaded'];
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'No file was uploaded'];
        default:
            return ['success' => false, 'message' => 'Unknown upload error'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = UPLOAD_DIR . $new_filename;
    
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
    
    return ['success' => true, 'filename' => $new_filename];
}

// Start session
session_start();
<?php
require_once 'db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($username, $password) {
        $user = $this->db->fetchRow(
            "SELECT * FROM users WHERE username = ? OR email = ?",
            [$username, $username]
        );
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_full_name'] = $user['full_name'];
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        session_destroy();
        session_start();
    }
    
    public function register($data) {
        $required_fields = ['username', 'email', 'password', 'full_name', 'phone', 'address'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "فیلد $field الزامی است"];
            }
        }
        
        if (!is_valid_email($data['email'])) {
            return ['success' => false, 'message' => 'ایمیل معتبر نیست'];
        }
        
        if (!is_valid_phone($data['phone'])) {
            return ['success' => false, 'message' => 'شماره تماس معتبر نیست'];
        }
        
        if (strlen($data['password']) < 6) {
            return ['success' => false, 'message' => 'رمز عبور باید حداقل 6 کاراکتر باشد'];
        }
        
        $existing_user = $this->db->fetchRow(
            "SELECT id FROM users WHERE username = ? OR email = ?",
            [$data['username'], $data['email']]
        );
        
        if ($existing_user) {
            return ['success' => false, 'message' => 'نام کاربری یا ایمیل قبلاً ثبت شده است'];
        }
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $user_id = $this->db->insert('users', [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $hashed_password,
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'role' => 'user'
        ]);
        
        if ($user_id) {
            return ['success' => true, 'message' => 'ثبت نام با موفقیت انجام شد'];
        } else {
            return ['success' => false, 'message' => 'خطا در ثبت نام'];
        }
    }
    
    public function get_user($user_id) {
        return $this->db->fetchRow(
            "SELECT * FROM users WHERE id = ?",
            [$user_id]
        );
    }
    
    public function update_user($user_id, $data) {
        $allowed_fields = ['full_name', 'phone', 'address'];
        $update_data = [];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = $data[$field];
            }
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                return ['success' => false, 'message' => 'رمز عبور باید حداقل 6 کاراکتر باشد'];
            }
            $update_data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($update_data)) {
            return ['success' => false, 'message' => 'هیچ تغییری اعمال نشد'];
        }
        
        $result = $this->db->update('users', $update_data, 'id = ?', [$user_id]);
        
        if ($result > 0) {
            return ['success' => true, 'message' => 'اطلاعات با موفقیت به‌روزرسانی شد'];
        } else {
            return ['success' => false, 'message' => 'خطا در به‌روزرسانی اطلاعات'];
        }
    }
    
    public function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
    
/*************  ✨ Windsurf Command ⭐  *************/
/**
 * Returns the current user if the user is logged in, otherwise returns null.
 *
 * @return null|stdClass
 */
/*******  ae5b55fa-0c7e-48b3-892b-508bebcea1c7  *******/    public function current_user() {
        if ($this->is_logged_in()) {
            return $this->get_user($_SESSION['user_id']);
        }
        return null;
    }
    
    public function is_admin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    public function require_login() {
        if (!$this->is_logged_in()) {
            set_message('لطفاً ابتدا وارد حساب کاربری خود شوید', 'warning');
            redirect('login.php');
            exit();
        }
    }
    
    public function require_user() {
        if (!$this->is_logged_in()) {
            set_message('لطفاً ابتدا وارد حساب کاربری خود شوید', 'warning');
            redirect('login.php');
            exit();
        }
    }
    
    public function require_admin() {
        if (!$this->is_admin()) {
            set_message('شما به این بخش دسترسی ندارید', 'error');
            redirect('index.php');
            exit();
        }
    }
}

$auth = new Auth();
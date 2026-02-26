<?php
require_once 'config.php';
require_once 'includes/auth.php';

if ($auth->is_logged_in()) {
    $auth->logout();
    set_message('با موفقیت از حساب کاربری خود خارج شدید', 'success');
}

redirect('index.php');
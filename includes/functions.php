<?php

/**
 * Get current user IP address
 * 
 * @return string
 */
function get_user_ip() {
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    } else {
        return 'UNKNOWN';
    }
}

/**
 * Generate random string
 * 
 * @param int $length
 * @return string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $characters_length = strlen($characters);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)];
    }
    return $random_string;
}

/**
 * Check if string is valid Persian
 * 
 * @param string $string
 * @return bool
 */
function is_persian($string) {
    return preg_match('/\p{Arabic}/u', $string);
}

/**
 * Clean string for URL
 * 
 * @param string $string
 * @return string
 */
function clean_url($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

/**
 * Check if file is image
 * 
 * @param string $file_path
 * @return bool
 */
function is_image($file_path) {
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    return in_array($file_extension, $allowed_extensions);
}

/**
 * Resize image
 * 
 * @param string $source_path
 * @param string $destination_path
 * @param int $width
 * @param int $height
 * @param bool $crop
 * @return bool
 */
function resize_image($source_path, $destination_path, $width = 300, $height = 300, $crop = true) {
    if (!file_exists($source_path)) {
        return false;
    }

    $info = getimagesize($source_path);
    if (!$info) {
        return false;
    }

    list($src_width, $src_height, $src_type) = $info;

    switch ($src_type) {
        case IMAGETYPE_GIF:
            $src_image = imagecreatefromgif($source_path);
            break;
        case IMAGETYPE_JPEG:
            $src_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $src_image = imagecreatefrompng($source_path);
            break;
        default:
            return false;
    }

    if ($crop) {
        $src_x = ($src_width - $width) / 2;
        $src_y = ($src_height - $height) / 2;
        $dst_image = imagecreatetruecolor($width, $height);
        imagecopy($dst_image, $src_image, 0, 0, $src_x, $src_y, $width, $height);
    } else {
        $dst_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $width, $height, $src_width, $src_height);
    }

    switch ($src_type) {
        case IMAGETYPE_GIF:
            imagegif($dst_image, $destination_path);
            break;
        case IMAGETYPE_JPEG:
            imagejpeg($dst_image, $destination_path, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($dst_image, $destination_path, 9);
            break;
    }

    imagedestroy($src_image);
    imagedestroy($dst_image);

    return true;
}

/**
 * Send email
 * 
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param array $headers
 * @return bool
 */
function send_email($to, $subject, $message, $headers = []) {
    $default_headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . SITE_NAME . ' <' . SITE_EMAIL . '>'
    ];
    
    $all_headers = array_merge($default_headers, $headers);
    
    return mail($to, $subject, $message, implode("\r\n", $all_headers));
}

/**
 * Validate Iranian phone number
 * 
 * @param string $phone
 * @return bool
 */
function validate_iran_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^(09)[0-9]{9}$/', $phone);
}

/**
 * Validate Iranian national code
 * 
 * @param string $code
 * @return bool
 */
function validate_iran_national_code($code) {
    $code = preg_replace('/[^0-9]/', '', $code);
    if (strlen($code) != 10) {
        return false;
    }
    
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += (int)$code[$i] * (10 - $i);
    }
    
    $remainder = $sum % 11;
    if ($remainder < 2) {
        return (int)$code[9] == $remainder;
    } else {
        return (int)$code[9] == (11 - $remainder);
    }
}

/**
 * Get user browser info
 * 
 * @return array
 */
function get_user_browser() {
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version = '';

    // First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    } elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = 'MSIE';
    } elseif (preg_match('/Firefox/i', $u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub = 'Firefox';
    } elseif (preg_match('/Chrome/i', $u_agent)) {
        $bname = 'Google Chrome';
        $ub = 'Chrome';
    } elseif (preg_match('/Safari/i', $u_agent)) {
        $bname = 'Apple Safari';
        $ub = 'Safari';
    } elseif (preg_match('/Opera/i', $u_agent)) {
        $bname = 'Opera';
        $ub = 'Opera';
    } elseif (preg_match('/Netscape/i', $u_agent)) {
        $bname = 'Netscape';
        $ub = 'Netscape';
    }

    // finally get the correct version number
    $known = ['Version', $ub, 'other'];
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using the 'other' option
        //see if version is before or after the name
        if (strripos($u_agent, 'Version') < strripos($u_agent, $ub)) {
            $version = $matches['version'][0];
        } else {
            $version = $matches['version'][1];
        }
    } else {
        $version = $matches['version'][0];
    }

    // check if we have a number
    if ($version == null || $version == '') {
        $version = '?';
    }

    return [
        'userAgent' => $u_agent,
        'name' => $bname,
        'version' => $version,
        'platform' => $platform,
        'pattern' => $pattern
    ];
}

/**
 * Get user device type
 * 
 * @return string
 */
function get_device_type() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    if (preg_match('/tablet|ipad|playbook|silk/i', $user_agent)) {
        return 'tablet';
    }
    
    if (preg_match('/mobile|iphone|ipod|android|blackberry|opera|mini|windows\sce|palm|smartphone|iemobile/i', $user_agent)) {
        return 'mobile';
    }
    
    return 'desktop';
}

/**
 * Clean input data
 * 
 * @param string $data
 * @return string
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

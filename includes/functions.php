<?php
// includes/functions.php

// پاک‌سازی ورودی‌ها
function sanitize_input($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize_input($value);
        }
        return $data;
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// تولید هش رمز عبور
function generate_password_hash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// تأیید رمز عبور
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// تولید توکن بازیابی
function generate_reset_token() {
    return bin2hex(random_bytes(32));
}

// ارسال ایمیل بازیابی رمز عبور
function send_reset_email($email, $token, $username) {
    $reset_link = SITE_URL . "reset_password.php?token=" . $token;
    $subject = "بازیابی رمز عبور - سیستم فروش کیان ورنا";
    $message = "
سلام $username،

برای بازیابی رمز عبور خود روی لینک زیر کلیک کنید:
$reset_link

این لینک تا 24 ساعت معتبر است.

تیم فنی کیان ورنا
    ";
    
    $headers = "From: noreply@kianvarna.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    return mail($email, $subject, $message, $headers);
}

// هدایت کاربر
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit();
    }
}

// بررسی وضعیت ورود
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// دریافت نقش کاربر
function get_user_role() {
    return $_SESSION['user_role'] ?? null;
}

// دریافت نوع مشتری
function get_customer_type() {
    return $_SESSION['customer_type'] ?? null;
}

// بررسی دسترسی
function check_permission($required_role) {
    if (!is_logged_in()) {
        redirect(SITE_URL . 'login.php');
    }
    
    $user_role = get_user_role();
    $admin_roles = ['system_admin', 'warehouse_manager', 'financial_manager', 'sales_manager'];
    
    if ($required_role == 'admin' && !in_array($user_role, $admin_roles)) {
        redirect(SITE_URL . 'access_denied.php');
    }
    
    if ($required_role == 'system_admin' && $user_role != 'system_admin') {
        redirect(SITE_URL . 'access_denied.php');
    }
}

// استان‌های ایران
function get_provinces() {
    return [
        'آذربایجان شرقی', 'آذربایجان غربی', 'اردبیل', 'اصفهان', 'البرز', 'ایلام', 'بوشهر', 
        'تهران', 'چهارمحال و بختیاری', 'خراسان جنوبی', 'خراسان رضوی', 'خراسان شمالی', 
        'خوزستان', 'زنجان', 'سمنان', 'سیستان و بلوچستان', 'فارس', 'قزوین', 'قم', 
        'کردستان', 'کرمان', 'کرمانشاه', 'کهگیلویه و بویراحمد', 'گلستان', 'گیلان', 
        'لرستان', 'مازندران', 'مرکزی', 'هرمزگان', 'همدان', 'یزد'
    ];
}

// ترجمه نقش‌های کاربری
function translate_user_role($role) {
    $roles = [
        'customer' => 'مشتری',
        'system_admin' => 'مدیر سیستم',
        'warehouse_manager' => 'مدیر انبار',
        'financial_manager' => 'مدیر مالی',
        'sales_manager' => 'مدیر فروش'
    ];
    return $roles[$role] ?? $role;
}

// ترجمه نوع مشتری
function translate_customer_type($type) {
    $types = [
        'representative' => 'نماینده',
        'partner' => 'همکار',
        'expert' => 'کارشناس',
        'consumer' => 'مصرف کننده'
    ];
    return $types[$type] ?? $type;
}

// آپلود فایل
function upload_file($file, $directory, $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        return false;
    }

    $filename = uniqid() . '.' . $file_extension;
    $upload_path = UPLOAD_PATH . $directory . '/';
    
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }

    $full_path = $upload_path . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $full_path)) {
        return $directory . '/' . $filename;
    }
    
    return false;
}

// حذف فایل
function delete_file($file_path) {
    $full_path = UPLOAD_PATH . $file_path;
    if (file_exists($full_path)) {
        return unlink($full_path);
    }
    return true;
}

// فرمت کردن قیمت
function format_price($price) {
    return number_format($price, 0, '.', ',') . ' ریال';
}

// تولید پیغام موفقیت یا خطا
function show_message($message, $type = 'success') {
    $class = $type == 'success' ? 'alert-success' : 'alert-danger';
    return "<div class='alert $class alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}
?>
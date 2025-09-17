<?php
// admin/users/delete.php
require_once '../../config/config.php';

// بررسی دسترسی
check_permission('system_admin');

// بررسی متد درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$user = new User($db);
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

$response = ['success' => false, 'message' => ''];

if (!$user_id) {
    $response['message'] = 'شناسه کاربر نامعتبر است.';
} else {
    // بررسی وجود کاربر
    $user_data = $user->getById($user_id);
    if (!$user_data) {
        $response['message'] = 'کاربر یافت نشد.';
    } elseif ($user_id == $_SESSION['user_id']) {
        $response['message'] = 'نمی‌توانید خودتان را حذف کنید.';
    } elseif ($user_data['user_role'] == 'system_admin') {
        $response['message'] = 'نمی‌توانید مدیر سیستم را حذف کنید.';
    } else {
        if ($user->delete($user_id)) {
            $response['success'] = true;
            $response['message'] = 'کاربر با موفقیت حذف شد.';
        } else {
            $response['message'] = 'خطا در حذف کاربر.';
        }
    }
}

// بازگشت JSON برای AJAX یا هدایت برای درخواست معمولی
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    if ($response['success']) {
        $_SESSION['success_message'] = $response['message'];
    } else {
        $_SESSION['error_message'] = $response['message'];
    }
    redirect('index.php');
}
?>
<?php
// admin/products/delete.php
require_once '../../config/config.php';

// بررسی دسترسی
check_permission('admin');

// بررسی متد درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$product = new Product($db);
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

$response = ['success' => false, 'message' => ''];

if (!$product_id) {
    $response['message'] = 'شناسه محصول نامعتبر است.';
} else {
    // بررسی وجود محصول
    $product_data = $product->getById($product_id);
    if (!$product_data) {
        $response['message'] = 'محصول یافت نشد.';
    } else {
        if ($product->delete($product_id)) {
            $response['success'] = true;
            $response['message'] = 'محصول با موفقیت حذف شد.';
        } else {
            $response['message'] = 'خطا در حذف محصول.';
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
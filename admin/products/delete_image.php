<?php
// admin/products/delete_image.php
require_once '../../config/config.php';

// بررسی دسترسی
check_permission('admin');

// بررسی متد درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'روش درخواست نامعتبر است.']);
    exit;
}

$product = new Product($db);
$image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;

$response = ['success' => false, 'message' => ''];

if (!$image_id) {
    $response['message'] = 'شناسه تصویر نامعتبر است.';
} else {
    if ($product->deleteImage($image_id)) {
        $response['success'] = true;
        $response['message'] = 'تصویر با موفقیت حذف شد.';
    } else {
        $response['message'] = 'خطا در حذف تصویر.';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
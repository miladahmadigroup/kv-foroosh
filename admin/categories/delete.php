<?php
// admin/categories/delete.php
require_once '../../config/config.php';

// بررسی دسترسی
check_permission('admin');

// بررسی متد درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$category = new Category($db);
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

$response = ['success' => false, 'message' => ''];

if (!$category_id) {
    $response['message'] = 'شناسه دسته‌بندی نامعتبر است.';
} else {
    // بررسی وجود دسته‌بندی
    $category_data = $category->getById($category_id);
    if (!$category_data) {
        $response['message'] = 'دسته‌بندی یافت نشد.';
    } else {
        // بررسی وجود محصول در این دسته
        $product_count = $category->getProductCount($category_id);
        if ($product_count > 0) {
            $response['message'] = "نمی‌توان این دسته‌بندی را حذف کرد. $product_count محصول در این دسته وجود دارد.";
        } else {
            // بررسی وجود زیردسته
            $sub_categories = $category->getSubCategories($category_id);
            if (!empty($sub_categories)) {
                $response['message'] = 'نمی‌توان این دسته‌بندی را حذف کرد. زیردسته‌هایی در این دسته وجود دارد.';
            } else {
                if ($category->delete($category_id)) {
                    $response['success'] = true;
                    $response['message'] = 'دسته‌بندی با موفقیت حذف شد.';
                } else {
                    $response['message'] = 'خطا در حذف دسته‌بندی.';
                }
            }
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
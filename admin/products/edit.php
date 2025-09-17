<?php
require_once '../../config/config.php';
check_permission('admin');

$product = new Product($db);
$category = new Category($db);

$message = '';
$error = '';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$product_id) redirect('index.php');

$product_data = $product->getById($product_id);
if (!$product_data) redirect('index.php');

$form_data = $product_data;
$categories = $category->getAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_data = sanitize_input($_POST);
    
    $errors = [];
    
    if (empty($form_data['name'])) $errors[] = 'نام محصول الزامی است.';
    if (empty($form_data['code'])) $errors[] = 'کد محصول الزامی است.';
    if ($product->codeExists($form_data['code'], $product_id)) $errors[] = 'این کد قبلاً استفاده شده.';
    if (empty($form_data['category_id'])) $errors[] = 'انتخاب دسته‌بندی الزامی است.';
    
    $price_fields = ['price_representative', 'price_partner', 'price_expert', 'price_consumer'];
    foreach ($price_fields as $field) {
        if (!is_numeric($form_data[$field]) || $form_data[$field] < 0) {
            $form_data[$field] = 0;
        }
    }
    
    $new_main_image = '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        $new_main_image = upload_file($_FILES['main_image'], 'products');
        if (!$new_main_image) $errors[] = 'خطا در آپلود تصویر.';
    }
    
    if (empty($errors)) {
        if ($new_main_image) {
            if ($product_data['main_image']) delete_file($product_data['main_image']);
            $form_data['main_image'] = $new_main_image;
        } else {
            unset($form_data['main_image']);
        }
        
        if ($product->update($product_id, $form_data)) {
            $message = 'محصول با موفقیت بروزرسانی شد.';
            $product_data = $product->getById($product_id);
            $form_data = $product_data;
        } else {
            $error = 'خطا در بروزرسانی محصول.';
        }
    } else {
        $error = implode('<br>', $errors);
        $form_data = array_merge($product_data, $form_data);
    }
}

$page_title = 'ویرایش محصول';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">ویرایش محصول</h1>
                <a href="index.php" class="btn btn-outline-secondary">بازگشت</a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>اطلاعات محصول</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">نام محصول *</label>
                                        <input type="text" class="form-control" name="name" 
                                               value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">کد محصول *</label>
                                        <input type="text" class="form-control" name="code" 
                                               value="<?php echo htmlspecialchars($form_data['code'] ?? ''); ?>" required>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">دسته‌بندی *</label>
                                        <select class="form-select" name="category_id" required>
                                            <option value="">انتخاب دسته‌بندی</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" 
                                                        <?php echo ($form_data['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">توضیحات</label>
                                        <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">آدرس ویدئو</label>
                                        <input type="url" class="form-control" name="video_url" 
                                               value="<?php echo htmlspecialchars($form_data['video_url'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>قیمت‌گذاری</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">قیمت نماینده (ریال)</label>
                                        <input type="number" class="form-control" name="price_representative" 
                                               value="<?php echo $form_data['price_representative'] ?? 0; ?>" min="0">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">قیمت همکار (ریال)</label>
                                        <input type="number" class="form-control" name="price_partner" 
                                               value="<?php echo $form_data['price_partner'] ?? 0; ?>" min="0">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">قیمت کارشناس (ریال)</label>
                                        <input type="number" class="form-control" name="price_expert" 
                                               value="<?php echo $form_data['price_expert'] ?? 0; ?>" min="0">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">قیمت مصرف کننده (ریال)</label>
                                        <input type="number" class="form-control" name="price_consumer" 
                                               value="<?php echo $form_data['price_consumer'] ?? 0; ?>" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>تصویر فعلی</h5>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($form_data['main_image']): ?>
                                    <img src="<?php echo UPLOAD_URL . $form_data['main_image']; ?>" 
                                         class="img-fluid mb-3" style="max-height: 200px;">
                                <?php else: ?>
                                    <div class="bg-light p-4">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                        <p class="text-muted">تصویری وجود ندارد</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>تغییر تصویر</h5>
                            </div>
                            <div class="card-body">
                                <input type="file" class="form-control" name="main_image" accept="image/*" 
                                       onchange="previewImage(this, 'preview')">
                                <div class="text-center mt-3">
                                    <img id="preview" style="display: none; max-height: 150px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary me-2">ذخیره تغییرات</button>
                        <a href="index.php" class="btn btn-secondary">انصراف</a>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
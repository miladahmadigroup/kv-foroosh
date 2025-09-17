<?php
// admin/categories/edit.php
require_once '../../config/config.php';

// بررسی دسترسی
check_permission('admin');

$category = new Category($db);

$message = '';
$error = '';
$form_data = [];

// دریافت ID دسته‌بندی
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$category_id) {
    redirect('index.php');
}

// دریافت اطلاعات دسته‌بندی
$category_data = $category->getById($category_id);
if (!$category_data) {
    redirect('index.php');
}

$form_data = $category_data;

// دریافت دسته‌های اصلی برای انتخاب والد (به جز خود دسته‌بندی)
$main_categories = $category->getMainCategories();
$main_categories = array_filter($main_categories, function($cat) use ($category_id) {
    return $cat['id'] != $category_id;
});

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_data = sanitize_input($_POST);
    
    // اعتبارسنجی
    $errors = [];
    
    if (empty($form_data['name'])) {
        $errors[] = 'نام دسته‌بندی الزامی است.';
    }
    
    // تنظیم parent_id
    if (empty($form_data['parent_id'])) {
        $form_data['parent_id'] = null;
    }
    
    // بررسی اینکه دسته‌بندی والد خودش نباشد
    if ($form_data['parent_id'] == $category_id) {
        $errors[] = 'دسته‌بندی نمی‌تواند والد خودش باشد.';
    }
    
    // اگر خطایی نبود، دسته‌بندی را بروزرسانی کن
    if (empty($errors)) {
        if ($category->update($category_id, $form_data)) {
            $message = 'دسته‌بندی با موفقیت بروزرسانی شد.';
            // بروزرسانی اطلاعات نمایش
            $category_data = $category->getById($category_id);
            $form_data = $category_data;
        } else {
            $error = 'خطا در بروزرسانی دسته‌بندی. لطفاً مجدداً تلاش کنید.';
        }
    } else {
        $error = implode('<br>', $errors);
        // ترکیب داده‌های قدیمی با جدید
        $form_data = array_merge($category_data, $form_data);
    }
}

$page_title = 'ویرایش دسته‌بندی';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">ویرایش دسته‌بندی</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>بازگشت به لیست
                    </a>
                </div>
            </div>

            <!-- نمایش پیغام‌ها -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- فرم ویرایش دسته‌بندی -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">ویرایش اطلاعات دسته‌بندی</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="categoryForm" novalidate>
                                <div class="row">
                                    <!-- نام دسته‌بندی -->
                                    <div class="col-md-12 mb-3">
                                        <label for="name" class="form-label">
                                            نام دسته‌بندی <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" 
                                               required>
                                    </div>

                                    <!-- دسته والد -->
                                    <div class="col-md-12 mb-3">
                                        <label for="parent_id" class="form-label">دسته والد (اختیاری)</label>
                                        <select class="form-select" id="parent_id" name="parent_id">
                                            <option value="">دسته اصلی (بدون والد)</option>
                                            <?php foreach ($main_categories as $main_cat): ?>
                                                <option value="<?php echo $main_cat['id']; ?>" 
                                                        <?php echo (($form_data['parent_id'] ?? '') == $main_cat['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($main_cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">
                                            اگر این یک زیردسته است، دسته والد را انتخاب کنید
                                        </div>
                                    </div>

                                    <!-- توضیحات -->
                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label">توضیحات</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" 
                                                  placeholder="توضیحی در مورد این دسته‌بندی..."><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <!-- دکمه‌های عملیات -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-save me-2"></i>ذخیره تغییرات
                                        </button>
                                        <a href="index.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>انصراف
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- اطلاعات تکمیلی -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">اطلاعات دسته‌بندی</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>شناسه:</span>
                                    <strong><?php echo $category_data['id']; ?></strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>وضعیت:</span>
                                    <span class="badge <?php echo $category_data['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $category_data['is_active'] ? 'فعال' : 'غیرفعال'; ?>
                                    </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>تعداد محصولات:</span>
                                    <span class="badge bg-info">
                                        <?php echo $category->getProductCount($category_data['id']); ?> محصول
                                    </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>تاریخ ایجاد:</span>
                                    <small class="text-muted">
                                        <?php echo date('Y/m/d H:i', strtotime($category_data['created_at'])); ?>
                                    </small>
                                </div>
                                <?php if ($category_data['updated_at'] != $category_data['created_at']): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>آخرین بروزرسانی:</span>
                                    <small class="text-muted">
                                        <?php echo date('Y/m/d H:i', strtotime($category_data['updated_at'])); ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- زیردسته‌ها -->
                    <?php
                    $sub_categories = $category->getSubCategories($category_data['id']);
                    if (!empty($sub_categories)):
                    ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">زیردسته‌ها (<?php echo count($sub_categories); ?>)</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($sub_categories as $sub_cat): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><?php echo htmlspecialchars($sub_cat['name']); ?></span>
                                        <div>
                                            <span class="badge bg-info me-2">
                                                <?php echo $category->getProductCount($sub_cat['id']); ?>
                                            </span>
                                            <span class="badge <?php echo $sub_cat['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $sub_cat['is_active'] ? 'فعال' : 'غیرفعال'; ?>
                                            </span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- راهنما -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">نکات مهم</h6>
                        </div>
                        <div class="card-body">
                            <ul class="small text-muted">
                                <li>تغییر نام دسته‌بندی بر روی محصولات موجود تأثیر می‌گذارد</li>
                                <li>اگر این دسته‌بندی زیردسته دارد، نمی‌توان آن را والد دسته‌ای کرد</li>
                                <li>غیرفعال کردن دسته‌بندی باعث مخفی شدن آن از نمایش عمومی می‌شود</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// اعتبارسنجی فرم
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>

<?php include '../../includes/footer.php'; ?>
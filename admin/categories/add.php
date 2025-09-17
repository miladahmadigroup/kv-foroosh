<?php
// admin/categories/add.php
require_once '../../config/config.php';

// بررسی دسترسی
check_permission('admin');

$category = new Category($db);

$message = '';
$error = '';
$form_data = [];

// دریافت دسته‌های اصلی برای انتخاب والد
$main_categories = $category->getMainCategories();

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
    
    // اگر خطایی نبود، دسته‌بندی را ایجاد کن
    if (empty($errors)) {
        if ($category->create($form_data)) {
            $message = 'دسته‌بندی با موفقیت اضافه شد.';
            $form_data = []; // پاک کردن فرم
        } else {
            $error = 'خطا در ایجاد دسته‌بندی. لطفاً مجدداً تلاش کنید.';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

$page_title = 'افزودن دسته‌بندی جدید';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">افزودن دسته‌بندی جدید</h1>
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

            <!-- فرم افزودن دسته‌بندی -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">اطلاعات دسته‌بندی جدید</h5>
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
                                            <i class="fas fa-save me-2"></i>ذخیره دسته‌بندی
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

                <!-- راهنما -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">راهنمای ایجاد دسته‌بندی</h6>
                        </div>
                        <div class="card-body">
                            <h6>دسته اصلی:</h6>
                            <p class="small text-muted">
                                برای ایجاد دسته اصلی، فیلد "دسته والد" را خالی بگذارید.
                            </p>
                            
                            <h6>زیردسته:</h6>
                            <p class="small text-muted">
                                برای ایجاد زیردسته، یکی از دسته‌های اصلی را به عنوان والد انتخاب کنید.
                            </p>
                            
                            <h6>نکات مهم:</h6>
                            <ul class="small text-muted">
                                <li>نام دسته‌بندی باید منحصر به فرد باشد</li>
                                <li>توضیحات برای کمک به کاربران در درک دسته‌بندی مفید است</li>
                                <li>دسته‌بندی‌های جدید به صورت پیش‌فرض فعال هستند</li>
                            </ul>
                        </div>
                    </div>

                    <!-- نمایش دسته‌های موجود -->
                    <?php if (!empty($main_categories)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">دسته‌های اصلی موجود</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($main_categories as $main_cat): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><?php echo htmlspecialchars($main_cat['name']); ?></span>
                                        <span class="badge bg-info rounded-pill">
                                            <?php echo $category->getProductCount($main_cat['id']); ?>
                                        </span>
                                    </li>
                                    
                                    <?php
                                    // نمایش زیردسته‌ها
                                    $sub_categories = $category->getSubCategories($main_cat['id']);
                                    foreach ($sub_categories as $sub_cat):
                                    ?>
                                        <li class="list-group-item px-0">
                                            <small class="text-muted">
                                                └ <?php echo htmlspecialchars($sub_cat['name']); ?>
                                                <span class="badge bg-secondary badge-sm ms-2">
                                                    <?php echo $category->getProductCount($sub_cat['id']); ?>
                                                </span>
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
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

// فوکوس روی فیلد نام
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('name').focus();
});
</script>

<?php include '../../includes/footer.php'; ?>
<?php
// admin/users/edit.php
require_once '../../config/config.php';

// بررسی دسترسی
check_permission('admin');

$user = new User($db);

$message = '';
$error = '';
$form_data = [];

// دریافت ID کاربر
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    redirect('index.php');
}

// دریافت اطلاعات کاربر
$user_data = $user->getById($user_id);
if (!$user_data) {
    redirect('index.php');
}

// بررسی اینکه مدیر سیستم فقط بتواند مدیر سیستم دیگری را ویرایش کند
if ($user_data['user_role'] == 'system_admin' && get_user_role() != 'system_admin') {
    redirect('index.php');
}

$form_data = $user_data;

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_data = sanitize_input($_POST);
    
    // اعتبارسنجی
    $errors = [];
    
    if (empty($form_data['full_name'])) {
        $errors[] = 'نام و نام خانوادگی الزامی است.';
    }
    
    if (empty($form_data['username'])) {
        $errors[] = 'نام کاربری الزامی است.';
    } elseif ($user->usernameExists($form_data['username'], $user_id)) {
        $errors[] = 'این نام کاربری قبلاً استفاده شده است.';
    }
    
    if (empty($form_data['mobile'])) {
        $errors[] = 'شماره موبایل الزامی است.';
    }
    
    if (empty($form_data['country'])) {
        $errors[] = 'کشور الزامی است.';
    }
    
    if (empty($form_data['province'])) {
        $errors[] = 'استان الزامی است.';
    }
    
    if (empty($form_data['user_role'])) {
        $errors[] = 'نقش کاربری الزامی است.';
    }
    
    if ($form_data['user_role'] == 'customer' && empty($form_data['customer_type'])) {
        $errors[] = 'نوع مشتری برای کاربران مشتری الزامی است.';
    }
    
    // بررسی رمز عبور جدید (اختیاری)
    if (!empty($form_data['password'])) {
        if (strlen($form_data['password']) < 6) {
            $errors[] = 'رمز عبور باید حداقل 6 کاراکتر باشد.';
        }
        
        if ($form_data['password'] != $form_data['password_confirm']) {
            $errors[] = 'رمز عبور و تکرار آن یکسان نیستند.';
        }
    } else {
        // حذف فیلد رمز عبور اگر خالی باشد
        unset($form_data['password']);
    }
    
    // حذف فیلدهای اضافی
    unset($form_data['password_confirm']);
    
    // اگر خطایی نبود، کاربر را بروزرسانی کن
    if (empty($errors)) {
        // تنظیم نوع مشتری فقط برای مشتریان
        if ($form_data['user_role'] != 'customer') {
            $form_data['customer_type'] = null;
        }
        
        if ($user->update($user_id, $form_data)) {
            $message = 'اطلاعات کاربر با موفقیت بروزرسانی شد.';
            // بروزرسانی اطلاعات نمایش
            $user_data = $user->getById($user_id);
            $form_data = $user_data;
        } else {
            $error = 'خطا در بروزرسانی کاربر. لطفاً مجدداً تلاش کنید.';
        }
    } else {
        $error = implode('<br>', $errors);
        // ترکیب داده‌های قدیمی با جدید برای نمایش
        $form_data = array_merge($user_data, $form_data);
    }
}

$page_title = 'ویرایش کاربر';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">ویرایش کاربر</h1>
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

            <!-- فرم ویرایش کاربر -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">ویرایش اطلاعات کاربر</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="userForm" novalidate>
                        <div class="row">
                            <!-- نام و نام خانوادگی -->
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">
                                    نام و نام خانوادگی <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>" 
                                       required>
                            </div>

                            <!-- نام کاربری -->
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">
                                    نام کاربری <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>" 
                                       required>
                                <div class="form-text">نام کاربری باید منحصر به فرد باشد</div>
                            </div>

                            <!-- شماره موبایل -->
                            <div class="col-md-6 mb-3">
                                <label for="mobile" class="form-label">
                                    شماره موبایل <span class="text-danger">*</span>
                                </label>
                                <input type="tel" class="form-control" id="mobile" name="mobile" 
                                       value="<?php echo htmlspecialchars($form_data['mobile'] ?? ''); ?>" 
                                       required>
                            </div>

                            <!-- ایمیل -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">ایمیل</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                                <div class="form-text">برای بازیابی رمز عبور استفاده می‌شود</div>
                            </div>

                            <!-- کشور -->
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">
                                    کشور <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="country" name="country" 
                                       value="<?php echo htmlspecialchars($form_data['country'] ?? 'ایران'); ?>" 
                                       required>
                            </div>

                            <!-- استان -->
                            <div class="col-md-6 mb-3">
                                <label for="province" class="form-label">
                                    استان <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="province" name="province" required>
                                    <option value="">انتخاب استان</option>
                                    <?php foreach (get_provinces() as $province): ?>
                                        <option value="<?php echo $province; ?>" 
                                                <?php echo (($form_data['province'] ?? '') == $province) ? 'selected' : ''; ?>>
                                            <?php echo $province; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if (get_user_role() == 'system_admin'): ?>
                            <!-- نقش کاربری -->
                            <div class="col-md-6 mb-3">
                                <label for="user_role" class="form-label">
                                    نقش کاربری <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="user_role" name="user_role" required>
                                    <option value="">انتخاب نقش</option>
                                    <option value="customer" <?php echo (($form_data['user_role'] ?? '') == 'customer') ? 'selected' : ''; ?>>
                                        مشتری
                                    </option>
                                    <option value="sales_manager" <?php echo (($form_data['user_role'] ?? '') == 'sales_manager') ? 'selected' : ''; ?>>
                                        مدیر فروش
                                    </option>
                                    <option value="warehouse_manager" <?php echo (($form_data['user_role'] ?? '') == 'warehouse_manager') ? 'selected' : ''; ?>>
                                        مدیر انبار
                                    </option>
                                    <option value="financial_manager" <?php echo (($form_data['user_role'] ?? '') == 'financial_manager') ? 'selected' : ''; ?>>
                                        مدیر مالی
                                    </option>
                                    <option value="system_admin" <?php echo (($form_data['user_role'] ?? '') == 'system_admin') ? 'selected' : ''; ?>>
                                        مدیر سیستم
                                    </option>
                                </select>
                            </div>
                            <?php else: ?>
                                <input type="hidden" name="user_role" value="<?php echo htmlspecialchars($form_data['user_role']); ?>">
                            <?php endif; ?>

                            <!-- نوع مشتری -->
                            <div class="col-md-6 mb-3" id="customer_type_wrapper" style="<?php echo ($form_data['user_role'] != 'customer') ? 'display: none;' : ''; ?>">
                                <label for="customer_type" class="form-label">
                                    نوع مشتری <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="customer_type" name="customer_type">
                                    <option value="">انتخاب نوع مشتری</option>
                                    <option value="representative" <?php echo (($form_data['customer_type'] ?? '') == 'representative') ? 'selected' : ''; ?>>
                                        نماینده
                                    </option>
                                    <option value="partner" <?php echo (($form_data['customer_type'] ?? '') == 'partner') ? 'selected' : ''; ?>>
                                        همکار
                                    </option>
                                    <option value="expert" <?php echo (($form_data['customer_type'] ?? '') == 'expert') ? 'selected' : ''; ?>>
                                        کارشناس
                                    </option>
                                    <option value="consumer" <?php echo (($form_data['customer_type'] ?? '') == 'consumer') ? 'selected' : ''; ?>>
                                        مصرف کننده
                                    </option>
                                </select>
                            </div>

                            <!-- رمز عبور جدید -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">رمز عبور جدید (اختیاری)</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <div class="form-text">برای تغییر رمز عبور پر کنید، حداقل 6 کاراکتر</div>
                            </div>

                            <!-- تکرار رمز عبور -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">تکرار رمز عبور جدید</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm">
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
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userRoleSelect = document.getElementById('user_role');
    const customerTypeWrapper = document.getElementById('customer_type_wrapper');
    const customerTypeSelect = document.getElementById('customer_type');

    function toggleCustomerType() {
        if (userRoleSelect.value === 'customer') {
            customerTypeWrapper.style.display = 'block';
            customerTypeSelect.required = true;
        } else {
            customerTypeWrapper.style.display = 'none';
            customerTypeSelect.required = false;
            customerTypeSelect.value = '';
        }
    }

    // گوش دادن به تغییرات فقط اگر فیلد موجود باشد
    if (userRoleSelect) {
        userRoleSelect.addEventListener('change', toggleCustomerType);
    }

    // اعتبارسنجی رمز عبور
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirm');

    function validatePassword() {
        if (passwordField.value && passwordField.value !== confirmPasswordField.value) {
            confirmPasswordField.setCustomValidity('رمز عبور و تکرار آن یکسان نیستند');
        } else {
            confirmPasswordField.setCustomValidity('');
        }
    }

    passwordField.addEventListener('input', validatePassword);
    confirmPasswordField.addEventListener('input', validatePassword);
});
</script>

<?php include '../../includes/footer.php'; ?>
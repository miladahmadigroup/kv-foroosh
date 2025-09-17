<?php
// customer/profile.php
require_once '../config/config.php';

// بررسی ورود کاربر
if (!is_logged_in()) {
    redirect(SITE_URL . 'login.php');
}

// بررسی نقش مشتری
if (get_user_role() != 'customer') {
    redirect(SITE_URL . 'admin/');
}

$user = new User($db);
$message = '';
$error = '';

// دریافت اطلاعات کاربر فعلی
$user_data = $user->getById($_SESSION['user_id']);
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
    } elseif ($user->usernameExists($form_data['username'], $_SESSION['user_id'])) {
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
    
    // حذف فیلدهای اضافی که نباید بروزرسانی شوند
    unset($form_data['password_confirm']);
    unset($form_data['user_role']);
    unset($form_data['customer_type']);
    
    // اگر خطایی نبود، کاربر را بروزرسانی کن
    if (empty($errors)) {
        if ($user->update($_SESSION['user_id'], $form_data)) {
            $message = 'اطلاعات پروفایل با موفقیت بروزرسانی شد.';
            
            // بروزرسانی اطلاعات session
            $_SESSION['full_name'] = $form_data['full_name'];
            $_SESSION['username'] = $form_data['username'];
            
            // بروزرسانی اطلاعات نمایش
            $user_data = $user->getById($_SESSION['user_id']);
            $form_data = $user_data;
        } else {
            $error = 'خطا در بروزرسانی اطلاعات. لطفاً مجدداً تلاش کنید.';
        }
    } else {
        $error = implode('<br>', $errors);
        // ترکیب داده‌های قدیمی با جدید برای نمایش
        $form_data = array_merge($user_data, $form_data);
    }
}

$page_title = 'ویرایش پروفایل';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- محتوای اصلی -->
        <main class="col-12 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">ویرایش پروفایل</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>بازگشت به پنل
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

            <div class="row">
                <!-- فرم ویرایش پروفایل -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">اطلاعات شخصی</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="profileForm" novalidate>
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
                                        <div class="form-text">نام کاربری برای ورود به سیستم استفاده می‌شود</div>
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
                </div>

                <!-- اطلاعات حساب کاربری -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">اطلاعات حساب</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>شناسه کاربری:</span>
                                    <code><?php echo $user_data['id']; ?></code>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>نقش:</span>
                                    <span class="badge bg-info">
                                        <?php echo translate_user_role($user_data['user_role']); ?>
                                    </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>نوع مشتری:</span>
                                    <span class="badge bg-secondary">
                                        <?php echo translate_customer_type($user_data['customer_type']); ?>
                                    </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>وضعیت:</span>
                                    <span class="badge <?php echo $user_data['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $user_data['is_active'] ? 'فعال' : 'غیرفعال'; ?>
                                    </span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>تاریخ عضویت:</span>
                                    <small class="text-muted">
                                        <?php echo date('Y/m/d', strtotime($user_data['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- راهنما -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">راهنما</h6>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="helpAccordion">
                                <div class="accordion-item">
                                    <h6 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#help1">
                                            چگونه رمز عبور را تغییر دهم؟
                                        </button>
                                    </h6>
                                    <div id="help1" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                        <div class="accordion-body small">
                                            برای تغییر رمز عبور، فیلدهای "رمز عبور جدید" و "تکرار رمز عبور جدید" را پر کنید. اگر نمی‌خواهید رمز را تغییر دهید، این فیلدها را خالی بگذارید.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h6 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#help2">
                                            چرا ایمیل مهم است؟
                                        </button>
                                    </h6>
                                    <div id="help2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                        <div class="accordion-body small">
                                            ایمیل برای بازیابی رمز عبور استفاده می‌شود. اگر رمز عبور خود را فراموش کنید و ایمیل نداشته باشید، باید با مدیر سیستم تماس بگیرید.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h6 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#help3">
                                            نقش و نوع مشتری قابل تغییر است؟
                                        </button>
                                    </h6>
                                    <div id="help3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                        <div class="accordion-body small">
                                            نقش و نوع مشتری شما توسط مدیر سیستم تعیین می‌شود و قابل تغییر نیست. برای تغییر این موارد با مدیر سیستم تماس بگیرید.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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

    // اعتبارسنجی فرم
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });
});
</script>

<?php include '../includes/footer.php'; ?>
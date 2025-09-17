<?php
// admin/settings/index.php
require_once '../../config/config.php';

// بررسی دسترسی (فقط مدیر سیستم)
check_permission('system_admin');

$message = '';
$error = '';

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_data = sanitize_input($_POST);
    
    $success_count = 0;
    $error_count = 0;
    
    // لیست تنظیماتی که باید ذخیره شوند
    $settings_list = [
        'site_title' => 'عنوان سایت',
        'company_name' => 'نام شرکت',
        'login_text' => 'متن صفحه ورود',
        'login_video' => 'آدرس ویدئو صفحه ورود',
        'forgot_password_message' => 'پیام فراموشی رمز عبور',
        'contact_email' => 'ایمیل تماس',
        'contact_phone' => 'تلفن تماس',
        'company_address' => 'آدرس شرکت',
        'footer_text' => 'متن فوتر'
    ];
    
    foreach ($settings_list as $key => $label) {
        if (isset($form_data[$key])) {
            if ($settings->set($key, $form_data[$key], $label)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
    }
    
    if ($error_count == 0) {
        $message = 'تنظیمات با موفقیت ذخیره شد.';
    } else {
        $error = "خطا در ذخیره برخی تنظیمات. $success_count مورد ذخیره شد، $error_count مورد خطا داشت.";
    }
}

// دریافت تنظیمات فعلی
$current_settings = [];
$settings_data = $settings->getAll();
foreach ($settings_data as $setting) {
    $current_settings[$setting['setting_key']] = $setting['setting_value'];
}

$page_title = 'تنظیمات سیستم';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">تنظیمات سیستم</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-2"></i>عملیات
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="resetToDefaults()">
                                <i class="fas fa-undo me-2"></i>بازگشت به پیش‌فرض
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportSettings()">
                                <i class="fas fa-download me-2"></i>دریافت فایل تنظیمات
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- نمایش پیغام‌ها -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- فرم تنظیمات -->
            <form method="POST" id="settingsForm">
                <div class="row">
                    <!-- تنظیمات عمومی -->
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-globe me-2"></i>تنظیمات عمومی
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="site_title" class="form-label">عنوان سایت</label>
                                    <input type="text" class="form-control" id="site_title" name="site_title" 
                                           value="<?php echo htmlspecialchars($current_settings['site_title'] ?? 'سیستم فروش کیان ورنا'); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="company_name" class="form-label">نام شرکت</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" 
                                           value="<?php echo htmlspecialchars($current_settings['company_name'] ?? 'کیان ورنا'); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">ایمیل تماس</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                           value="<?php echo htmlspecialchars($current_settings['contact_email'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">تلفن تماس</label>
                                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                           value="<?php echo htmlspecialchars($current_settings['contact_phone'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="company_address" class="form-label">آدرس شرکت</label>
                                    <textarea class="form-control" id="company_address" name="company_address" rows="3"><?php echo htmlspecialchars($current_settings['company_address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- تنظیمات صفحه ورود -->
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-sign-in-alt me-2"></i>تنظیمات صفحه ورود
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="login_text" class="form-label">متن صفحه ورود</label>
                                    <textarea class="form-control" id="login_text" name="login_text" rows="3" 
                                              placeholder="متن خوشامدگویی که در صفحه ورود نمایش داده می‌شود"><?php echo htmlspecialchars($current_settings['login_text'] ?? 'به سیستم فروش کیان ورنا خوش آمدید'); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="login_video" class="form-label">آدرس ویدئو صفحه ورود</label>
                                    <input type="url" class="form-control" id="login_video" name="login_video" 
                                           value="<?php echo htmlspecialchars($current_settings['login_video'] ?? ''); ?>"
                                           placeholder="https://example.com/video.mp4">
                                    <div class="form-text">آدرس مستقیم فایل ویدئو که در صفحه ورود نمایش داده شود</div>
                                </div>

                                <div class="mb-3">
                                    <label for="forgot_password_message" class="form-label">پیام فراموشی رمز عبور</label>
                                    <textarea class="form-control" id="forgot_password_message" name="forgot_password_message" rows="4" 
                                              placeholder="پیامی که به کاربرانی که ایمیل ندارند در هنگام فراموشی رمز عبور نمایش داده می‌شود"><?php echo htmlspecialchars($current_settings['forgot_password_message'] ?? 'برای شما آدرس ایمیل ثبت نشده است، برای بازیابی رمز عبور با ادمین سامانه تماس بگیرید.'); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- تنظیمات ظاهری -->
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-palette me-2"></i>تنظیمات ظاهری
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="footer_text" class="form-label">متن فوتر</label>
                                    <textarea class="form-control" id="footer_text" name="footer_text" rows="2" 
                                              placeholder="متنی که در فوتر سایت نمایش داده می‌شود"><?php echo htmlspecialchars($current_settings['footer_text'] ?? ''); ?></textarea>
                                    <div class="form-text">اگر خالی باشد، متن پیش‌فرض نمایش داده می‌شود</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- دکمه‌های عملیات -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save me-2"></i>ذخیره تنظیمات
                                </button>
                                <button type="button" class="btn btn-outline-warning me-2" onclick="resetForm()">
                                    <i class="fas fa-undo me-2"></i>بازگشت به حالت قبل
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="previewLogin()">
                                    <i class="fas fa-eye me-2"></i>پیش‌نمایش صفحه ورود
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- اطلاعات سیستم -->
            <div class="row mt-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>اطلاعات سیستم
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>نسخه PHP:</strong><br>
                                    <span class="text-muted"><?php echo PHP_VERSION; ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>نسخه MySQL:</strong><br>
                                    <span class="text-muted">
                                        <?php
                                        try {
                                            $version = $db->query('SELECT VERSION() as version')->fetch();
                                            echo $version['version'];
                                        } catch (Exception $e) {
                                            echo 'نامشخص';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="col-sm-6 mt-2">
                                    <strong>سرور:</strong><br>
                                    <span class="text-muted"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'نامشخص'; ?></span>
                                </div>
                                <div class="col-sm-6 mt-2">
                                    <strong>آخرین بروزرسانی:</strong><br>
                                    <span class="text-muted"><?php echo date('Y/m/d H:i'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>آمار سیستم
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // آمار کلی سیستم
                            $user = new User($db);
                            $product = new Product($db);
                            $category = new Category($db);
                            
                            $total_users = $user->count();
                            $total_products = $product->count();
                            $total_categories = count($category->getAll());
                            ?>
                            <div class="row text-center">
                                <div class="col-4">
                                    <h5 class="text-primary"><?php echo number_format($total_users); ?></h5>
                                    <small class="text-muted">کاربران</small>
                                </div>
                                <div class="col-4">
                                    <h5 class="text-success"><?php echo number_format($total_products); ?></h5>
                                    <small class="text-muted">محصولات</small>
                                </div>
                                <div class="col-4">
                                    <h5 class="text-info"><?php echo number_format($total_categories); ?></h5>
                                    <small class="text-muted">دسته‌ها</small>
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
// بازگشت به حالت قبل از تغییرات
function resetForm() {
    if (confirm('آیا از بازگشت به حالت قبل اطمینان دارید؟')) {
        location.reload();
    }
}

// پیش‌نمایش صفحه ورود
function previewLogin() {
    window.open('<?php echo SITE_URL; ?>login.php', '_blank');
}

// بازگشت به تنظیمات پیش‌فرض
function resetToDefaults() {
    if (confirm('آیا از بازگشت به تنظیمات پیش‌فرض اطمینان دارید؟ این عمل قابل برگشت نیست.')) {
        // ارسال درخواست AJAX برای بازگشت به پیش‌فرض
        fetch('reset_defaults.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=reset_defaults'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('تنظیمات به حالت پیش‌فرض بازگشت داده شد.', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('خطا در بازگشت به تنظیمات پیش‌فرض.', 'error');
            }
        })
        .catch(error => {
            showNotification('خطا در ارتباط با سرور.', 'error');
        });
    }
}

// خروجی گرفتن از تنظیمات
function exportSettings() {
    window.open('export_settings.php', '_blank');
}

// اعتبارسنجی فرم
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    const submitBtn = e.target.querySelector('button[type="submit"]');
    showLoading(submitBtn);
    
    // رفع loading پس از ارسال
    setTimeout(() => hideLoading(submitBtn), 2000);
});

// ذخیره خودکار (هر 5 دقیقه)
setInterval(function() {
    const form = document.getElementById('settingsForm');
    const formData = new FormData(form);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('alert-success')) {
            showNotification('تنظیمات به صورت خودکار ذخیره شد.', 'info');
        }
    })
    .catch(error => {
        console.log('Auto-save failed:', error);
    });
}, 300000); // 5 دقیقه
</script>

<?php include '../../includes/footer.php'; ?>
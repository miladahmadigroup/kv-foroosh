<?php
// reset_password.php
require_once 'config/config.php';

// اگر کاربر وارد شده باشد، هدایت به پنل مربوطه
if (is_logged_in()) {
    redirect(SITE_URL . 'admin/');
}

$message = '';
$error = '';
$token_valid = false;

// بررسی وجود توکن
$token = isset($_GET['token']) ? sanitize_input($_GET['token']) : '';

if (empty($token)) {
    $error = 'لینک بازیابی نامعتبر است.';
} else {
    // بررسی اعتبار توکن
    $user = new User($db);
    $query = "SELECT * FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        $token_valid = true;
    } else {
        $error = 'لینک بازیابی منقضی یا نامعتبر است.';
    }
}

// پردازش فرم تغییر رمز عبور
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid) {
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['password_confirm'] ?? '';
    
    if (empty($new_password)) {
        $error = 'رمز عبور جدید الزامی است.';
    } elseif (strlen($new_password) < 6) {
        $error = 'رمز عبور باید حداقل 6 کاراکتر باشد.';
    } elseif ($new_password != $confirm_password) {
        $error = 'رمز عبور و تکرار آن یکسان نیستند.';
    } else {
        $user = new User($db);
        if ($user->resetPassword($token, $new_password)) {
            $message = 'رمز عبور با موفقیت تغییر کرد. می‌توانید وارد شوید.';
            $token_valid = false;
        } else {
            $error = 'خطا در تغییر رمز عبور. لطفاً مجدداً تلاش کنید.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بازیابی رمز عبور - <?php echo $settings->get('site_title'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Vazir Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card login-card shadow">
                        <div class="card-body p-5">
                            <div class="login-header">
                                <h1><?php echo $settings->get('company_name'); ?></h1>
                                <p class="text-muted">تغییر رمز عبور</p>
                            </div>

                            <!-- نمایش پیغام خطا -->
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <!-- نمایش پیغام موفقیت -->
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo htmlspecialchars($message); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($token_valid && empty($message)): ?>
                                <!-- فرم تغییر رمز عبور -->
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">رمز عبور جدید</label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               required minlength="6">
                                        <div class="form-text">حداقل 6 کاراکتر</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password_confirm" class="form-label">تکرار رمز عبور جدید</label>
                                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                               required>
                                    </div>
                                    
                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary">تغییر رمز عبور</button>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <!-- بازگشت به صفحه ورود -->
                            <div class="text-center">
                                <a href="login.php" class="text-muted">
                                    <small>بازگشت به صفحه ورود</small>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- کپی‌رایت -->
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            © <?php echo date('Y'); ?> <?php echo $settings->get('company_name'); ?>. 
                            تمامی حقوق محفوظ است.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // اعتبارسنجی رمز عبور
    document.addEventListener('DOMContentLoaded', function() {
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('password_confirm');
        
        if (passwordField && confirmPasswordField) {
            function validatePassword() {
                if (passwordField.value !== confirmPasswordField.value) {
                    confirmPasswordField.setCustomValidity('رمز عبور و تکرار آن یکسان نیستند');
                } else {
                    confirmPasswordField.setCustomValidity('');
                }
            }
            
            passwordField.addEventListener('input', validatePassword);
            confirmPasswordField.addEventListener('input', validatePassword);
        }
    });
    </script>
</body>
</html>
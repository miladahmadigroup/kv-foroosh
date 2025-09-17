<?php
require_once 'config/config.php';

if (is_logged_in()) {
    redirect(SITE_URL . 'admin/');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    
    if (empty($username)) {
        $error = 'لطفاً نام کاربری را وارد کنید.';
    } else {
        $user = new User($db);
        $user_data = $user->getByUsername($username);
        
        if ($user_data) {
            if (!empty($user_data['email'])) {
                $token = $user->setResetToken($user_data['email']);
                if ($token && send_reset_email($user_data['email'], $token, $user_data['full_name'])) {
                    $message = 'لینک بازیابی رمز عبور به ایمیل شما ارسال شد.';
                } else {
                    $error = 'خطا در ارسال ایمیل.';
                }
            } else {
                $forgot_message = $settings->get('forgot_password_message');
                $error = $forgot_message ?: 'برای شما آدرس ایمیل ثبت نشده است، برای بازیابی رمز عبور با ادمین سامانه تماس بگیرید.';
            }
        } else {
            $error = 'نام کاربری یافت نشد.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بازیابی رمز عبور</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body p-5">
                            <h1 class="text-center mb-4">بازیابی رمز عبور</h1>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($message)): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                            <?php endif; ?>

                            <?php if (empty($message)): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">نام کاربری</label>
                                    <input type="text" class="form-control" name="username" required autofocus>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">ارسال لینک بازیابی</button>
                            </form>
                            <?php endif; ?>

                            <div class="text-center mt-3">
                                <a href="login.php">بازگشت به ورود</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
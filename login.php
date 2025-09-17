<?php
require_once 'config/config.php';

if (is_logged_in()) {
    $user_role = get_user_role();
    $admin_roles = ['system_admin', 'warehouse_manager', 'financial_manager', 'sales_manager'];
    
    if (in_array($user_role, $admin_roles)) {
        redirect(SITE_URL . 'admin/');
    } else {
        redirect(SITE_URL . 'customer/');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'لطفاً نام کاربری و رمز عبور را وارد کنید.';
    } else {
        $user = new User($db);
        if ($user->login($username, $password)) {
            $user_role = get_user_role();
            $admin_roles = ['system_admin', 'warehouse_manager', 'financial_manager', 'sales_manager'];
            
            if (in_array($user_role, $admin_roles)) {
                redirect(SITE_URL . 'admin/');
            } else {
                redirect(SITE_URL . 'customer/');
            }
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است.';
        }
    }
}

$login_text = $settings->get('login_text') ?: 'به سیستم فروش کیان ورنا خوش آمدید';
$login_video = $settings->get('login_video');
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم</title>
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
                            <h1 class="text-center mb-2">کیان ورنا</h1>
                            <p class="text-center text-muted mb-4"><?php echo htmlspecialchars($login_text); ?></p>

                            <?php if (!empty($login_video)): ?>
                            <video class="w-100 mb-4" controls style="max-height: 200px;">
                                <source src="<?php echo htmlspecialchars($login_video); ?>" type="video/mp4">
                            </video>
                            <?php endif; ?>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">نام کاربری</label>
                                    <input type="text" class="form-control" name="username" 
                                           value="<?php echo htmlspecialchars($username ?? ''); ?>" required autofocus>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">رمز عبور</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 mb-3">ورود</button>
                            </form>

                            <div class="text-center">
                                <a href="forgot_password.php" class="text-muted">
                                    <small>رمز عبور را فراموش کرده‌اید؟</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
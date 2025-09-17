<?php
// access_denied.php
require_once 'config/config.php';

$page_title = 'دسترسی غیرمجاز';
$show_navbar = is_logged_in(); // نمایش نوار ناوبری فقط اگر کاربر وارد شده باشد
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - ' . $settings->get('site_title'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Vazir Font -->
    <link href="<?php echo SITE_URL; ?>assets/css/vazir.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">
    
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
        }
        
        .error-card {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 2rem;
        }
        
        .error-code {
            font-size: 3rem;
            font-weight: 700;
            color: #dc3545;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php if ($show_navbar): ?>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark position-fixed w-100" style="top: 0; z-index: 1000; background-color: var(--primary-color) !important;">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                    <i class="fas fa-store me-2"></i>
                    <?php echo $settings->get('company_name'); ?>
                </a>
                
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>خروج
                    </a>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <div class="error-container" style="<?php echo $show_navbar ? 'padding-top: 76px;' : ''; ?>">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card error-card">
                        <div class="card-body text-center p-5">
                            <div class="error-icon">
                                <i class="fas fa-ban"></i>
                            </div>
                            
                            <div class="error-code">403</div>
                            
                            <h2 class="mb-4">دسترسی غیرمجاز</h2>
                            
                            <p class="lead mb-4">
                                شما مجاز به دسترسی به این صفحه نیستید.
                            </p>
                            
                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>دلایل احتمالی:</strong>
                                <ul class="list-unstyled mt-2 mb-0">
                                    <li>• سطح دسترسی شما کافی نیست</li>
                                    <li>• نیاز به ورود مجدد دارید</li>
                                    <li>• حساب کاربری شما غیرفعال شده</li>
                                </ul>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-block">
                                <?php if (is_logged_in()): ?>
                                    <?php 
                                    $user_role = get_user_role();
                                    $admin_roles = ['system_admin', 'warehouse_manager', 'financial_manager', 'sales_manager'];
                                    ?>
                                    
                                    <?php if (in_array($user_role, $admin_roles)): ?>
                                        <a href="<?php echo SITE_URL; ?>admin/" class="btn btn-primary btn-lg">
                                            <i class="fas fa-tachometer-alt me-2"></i>پنل مدیریت
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo SITE_URL; ?>customer/" class="btn btn-primary btn-lg">
                                            <i class="fas fa-home me-2"></i>پنل مشتری
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo SITE_URL; ?>logout.php" class="btn btn-outline-danger btn-lg">
                                        <i class="fas fa-sign-out-alt me-2"></i>خروج
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo SITE_URL; ?>login.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>ورود به سیستم
                                    </a>
                                <?php endif; ?>
                                
                                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-arrow-right me-2"></i>بازگشت
                                </a>
                            </div>
                            
                            <?php if (is_logged_in()): ?>
                            <div class="mt-4 pt-4 border-top">
                                <small class="text-muted">
                                    <strong>اطلاعات شما:</strong><br>
                                    نام: <?php echo htmlspecialchars($_SESSION['full_name']); ?><br>
                                    نقش: <?php echo translate_user_role(get_user_role()); ?><br>
                                    <?php if (get_customer_type()): ?>
                                        نوع: <?php echo translate_customer_type(get_customer_type()); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- تماس با پشتیبانی -->
                    <div class="text-center mt-4">
                        <div class="card error-card">
                            <div class="card-body p-3">
                                <h6 class="mb-2">
                                    <i class="fas fa-headset me-2"></i>نیاز به کمک دارید؟
                                </h6>
                                <small class="text-muted">
                                    در صورت تداوم مشکل، با مدیر سیستم تماس بگیرید.
                                    <br>
                                    <?php 
                                    $contact_email = $settings->get('contact_email');
                                    $contact_phone = $settings->get('contact_phone');
                                    ?>
                                    
                                    <?php if ($contact_email): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>" class="text-decoration-none">
                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($contact_email); ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($contact_phone): ?>
                                        <?php if ($contact_email): ?> | <?php endif; ?>
                                        <a href="tel:<?php echo htmlspecialchars($contact_phone); ?>" class="text-decoration-none">
                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($contact_phone); ?>
                                        </a>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto redirect after 30 seconds if logged in
        <?php if (is_logged_in()): ?>
        let countdown = 30;
        const countdownElement = document.createElement('div');
        countdownElement.className = 'mt-3 small text-muted';
        countdownElement.innerHTML = `<i class="fas fa-clock me-1"></i>هدایت خودکار تا ${countdown} ثانیه دیگر...`;
        document.querySelector('.error-card .card-body').appendChild(countdownElement);
        
        const timer = setInterval(function() {
            countdown--;
            countdownElement.innerHTML = `<i class="fas fa-clock me-1"></i>هدایت خودکار تا ${countdown} ثانیه دیگر...`;
            
            if (countdown <= 0) {
                clearInterval(timer);
                <?php 
                $user_role = get_user_role();
                $admin_roles = ['system_admin', 'warehouse_manager', 'financial_manager', 'sales_manager'];
                
                if (in_array($user_role, $admin_roles)): ?>
                    window.location.href = '<?php echo SITE_URL; ?>admin/';
                <?php else: ?>
                    window.location.href = '<?php echo SITE_URL; ?>customer/';
                <?php endif; ?>
            }
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>
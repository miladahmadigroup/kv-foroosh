<?php
if (!defined('SITE_PATH')) {
    die('Direct access not allowed');
}

$page_title = $page_title ?? 'سیستم فروش کیان ورنا';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title . ' - ' . $settings->get('site_title')); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <i class="fas fa-store me-2"></i>
                <?php echo $settings->get('company_name'); ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (is_logged_in()): ?>
                        <?php 
                        $user_role = get_user_role();
                        $admin_roles = ['system_admin', 'warehouse_manager', 'financial_manager', 'sales_manager'];
                        ?>
                        
                        <?php if (in_array($user_role, $admin_roles)): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>admin/">
                                    <i class="fas fa-tachometer-alt me-1"></i>داشبورد
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                    <i class="fas fa-users me-1"></i>کاربران
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/users/">لیست کاربران</a></li>
                                    <?php if ($user_role == 'system_admin'): ?>
                                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/users/add.php">افزودن کاربر</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                    <i class="fas fa-box me-1"></i>محصولات
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/products/">لیست محصولات</a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/products/add.php">افزودن محصول</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/categories/">دسته‌بندی‌ها</a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/categories/add.php">افزودن دسته‌بندی</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>customer/">
                                    <i class="fas fa-home me-1"></i>پنل مشتری
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>customer/products.php">
                                    <i class="fas fa-shopping-bag me-1"></i>محصولات
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <h6 class="dropdown-header">
                                        <?php echo translate_user_role(get_user_role()); ?>
                                        <?php if (get_customer_type()): ?>
                                            - <?php echo translate_customer_type(get_customer_type()); ?>
                                        <?php endif; ?>
                                    </h6>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                
                                <?php if (get_user_role() == 'customer'): ?>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>customer/profile.php">
                                        <i class="fas fa-user-edit me-2"></i>ویرایش پروفایل
                                    </a></li>
                                <?php endif; ?>
                                
                                <?php if (get_user_role() == 'system_admin'): ?>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/settings/">
                                        <i class="fas fa-cog me-2"></i>تنظیمات سیستم
                                    </a></li>
                                <?php endif; ?>
                                
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>خروج
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>ورود
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-wrapper" style="margin-top: 76px;">
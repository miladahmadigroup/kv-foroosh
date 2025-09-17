<?php
// index.php
require_once 'config/config.php';

// هدایت کاربران بر اساس وضعیت ورود و نقش
if (is_logged_in()) {
    $user_role = get_user_role();
    $admin_roles = ['system_admin', 'warehouse_manager', 'financial_manager', 'sales_manager'];
    
    if (in_array($user_role, $admin_roles)) {
        redirect(SITE_URL . 'admin/');
    } else {
        redirect(SITE_URL . 'customer/');
    }
} else {
    redirect(SITE_URL . 'login.php');
}
?>
<?php
// logout.php
require_once 'config/config.php';

// خروج کاربر
if (is_logged_in()) {
    $user = new User($db);
    $user->logout();
}

// هدایت به صفحه ورود
redirect(SITE_URL . 'login.php');
?>
<?php
// config/config.php
session_start();

// تنظیمات اصلی
define('SITE_URL', 'http://localhost/kv-foroosh/');
define('SITE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/kv-foroosh/');
define('UPLOAD_PATH', SITE_PATH . 'assets/images/uploads/');
define('UPLOAD_URL', SITE_URL . 'assets/images/uploads/');

// اتصال به پایگاه داده
require_once SITE_PATH . 'config/database.php';
$database = new Database();
$db = $database->connect();

// بارگذاری کلاس‌ها و توابع
require_once SITE_PATH . 'includes/functions.php';
require_once SITE_PATH . 'classes/Settings.php';
require_once SITE_PATH . 'classes/User.php';
require_once SITE_PATH . 'classes/Product.php';
require_once SITE_PATH . 'classes/Category.php';

// ایجاد نمونه از کلاس تنظیمات
$settings = new Settings($db);

// تنظیم منطقه زمانی
date_default_timezone_set('Asia/Tehran');

// تنظیمات خطا
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
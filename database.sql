-- database.sql
CREATE DATABASE IF NOT EXISTS kian_varna_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kian_varna_db;

-- جدول کاربران
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    country VARCHAR(100) NOT NULL DEFAULT 'ایران',
    province VARCHAR(100) NOT NULL,
    user_role ENUM('customer', 'system_admin', 'warehouse_manager', 'financial_manager', 'sales_manager') NOT NULL,
    customer_type ENUM('representative', 'partner', 'expert', 'consumer') NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    reset_token VARCHAR(64) NULL,
    reset_token_expiry TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول دسته‌بندی محصولات
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    parent_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- جدول محصولات
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) UNIQUE NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    main_image VARCHAR(255),
    video_url VARCHAR(255),
    price_representative DECIMAL(10,2) DEFAULT 0,
    price_partner DECIMAL(10,2) DEFAULT 0,
    price_expert DECIMAL(10,2) DEFAULT 0,
    price_consumer DECIMAL(10,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- جدول گالری تصاویر محصولات
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- جدول تنظیمات سیستم
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- درج کاربر مدیر سیستم اولیه
INSERT INTO users (full_name, username, mobile, email, country, province, user_role, password_hash) 
VALUES ('مدیر سیستم', 'admin', '09123456789', 'admin@kianvarna.com', 'ایران', 'تهران', 'system_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- درج تنظیمات اولیه
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('login_text', 'به سیستم فروش کیان ورنا خوش آمدید', 'متن نمایش در صفحه ورود'),
('login_video', '', 'آدرس ویدئو صفحه ورود'),
('forgot_password_message', 'برای شما آدرس ایمیل ثبت نشده است، برای بازیابی رمز عبور با ادمین سامانه تماس بگیرید.', 'پیام فراموشی رمز عبور بدون ایمیل'),
('site_title', 'سیستم فروش کیان ورنا', 'عنوان سایت'),
('company_name', 'کیان ورنا', 'نام شرکت');
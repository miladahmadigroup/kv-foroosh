<?php
// admin/products/add.php
require_once '../../config/config.php';

// بررسی دسترسی
check_permission('admin');

$product = new Product($db);
$category = new Category($db);

$message = '';
$error = '';
$form_data = [];

// دریافت دسته‌بندی‌ها
$categories = $category->getAll();

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_data = sanitize_input($_POST);
    
    // اعتبارسنجی
    $errors = [];
    
    if (empty($form_data['name'])) {
        $errors[] = 'نام محصول الزامی است.';
    }
    
    if (empty($form_data['code'])) {
        $errors[] = 'کد محصول الزامی است.';
    } elseif ($product->codeExists($form_data['code'])) {
        $errors[] = 'این کد محصول قبلاً استفاده شده است.';
    }
    
    if (empty($form_data['category_id']) || !is_numeric($form_data['category_id'])) {
        $errors[] = 'انتخاب دسته‌بندی الزامی است.';
    }
    
    // اعتبارسنجی قیمت‌ها
    $price_fields = ['price_representative', 'price_partner', 'price_expert', 'price_consumer'];
    foreach ($price_fields as $field) {
        if (!isset($form_data[$field]) || !is_numeric($form_data[$field]) || $form_data[$field] < 0) {
            $form_data[$field] = 0;
        }
    }
    
    // پردازش آپلود تصویر اصلی
    $main_image_path = '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        $main_image_path = upload_file($_FILES['main_image'], 'products');
        if (!$main_image_path) {
            $errors[] = 'خطا در آپلود تصویر اصلی.';
        }
    }
    
    // پردازش آپلود تصاویر گالری
    $gallery_images = [];
    if (isset($_FILES['gallery_images'])) {
        for ($i = 0; $i < count($_FILES['gallery_images']['name']); $i++) {
            if ($_FILES['gallery_images']['error'][$i] == UPLOAD_ERR_OK) {
                $gallery_file = [
                    'name' => $_FILES['gallery_images']['name'][$i],
                    'type' => $_FILES['gallery_images']['type'][$i],
                    'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                    'error' => $_FILES['gallery_images']['error'][$i],
                    'size' => $_FILES['gallery_images']['size'][$i]
                ];
                
                $image_path = upload_file($gallery_file, 'products');
                if ($image_path) {
                    $gallery_images[] = $image_path;
                }
            }
        }
    }
    
    // اگر خطایی نبود، محصول را ایجاد کن
    if (empty($errors)) {
        $form_data['main_image'] = $main_image_path;
        
        $product_id = $product->create($form_data, $gallery_images);
        if ($product_id) {
            $message = 'محصول با موفقیت اضافه شد.';
            $form_data = []; // پاک کردن فرم
        } else {
            $error = 'خطا در ایجاد محصول. لطفاً مجدداً تلاش کنید.';
            
            // حذف تصاویر آپلود شده در صورت خطا
            if ($main_image_path) {
                delete_file($main_image_path);
            }
            foreach ($gallery_images as $img_path) {
                delete_file($img_path);
            }
        }
    } else {
        $error = implode('<br>', $errors);
        
        // حذف تصاویر آپلود شده در صورت خطا
        if ($main_image_path) {
            delete_file($main_image_path);
        }
        foreach ($gallery_images as $img_path) {
            delete_file($img_path);
        }
    }
}

$page_title = 'افزودن محصول جدید';
$additional_css = ['https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css'];
$additional_js = ['https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js'];
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">افزودن محصول جدید</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>بازگشت به لیست
                    </a>
                </div>
            </div>

            <!-- نمایش پیغام‌ها -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- فرم افزودن محصول -->
            <form method="POST" enctype="multipart/form-data" id="productForm" novalidate>
                <div class="row">
                    <!-- اطلاعات اصلی محصول -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">اطلاعات اصلی محصول</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- نام محصول -->
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">
                                            نام محصول <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" 
                                               required>
                                    </div>

                                    <!-- کد محصول -->
                                    <div class="col-md-6 mb-3">
                                        <label for="code" class="form-label">
                                            کد محصول <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="code" name="code" 
                                               value="<?php echo htmlspecialchars($form_data['code'] ?? ''); ?>" 
                                               required>
                                        <div class="form-text">کد محصول باید منحصر به فرد باشد</div>
                                    </div>

                                    <!-- دسته‌بندی -->
                                    <div class="col-md-12 mb-3">
                                        <label for="category_id" class="form-label">
                                            دسته‌بندی <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">انتخاب دسته‌بندی</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" 
                                                        <?php echo (($form_data['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                                    <?php 
                                                    echo htmlspecialchars($cat['name']);
                                                    if ($cat['parent_name']) {
                                                        echo ' - ' . htmlspecialchars($cat['parent_name']);
                                                    }
                                                    ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- توضیحات -->
                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label">توضیحات</label>
                                        <textarea class="form-control" id="description" name="description" rows="4">
                                            <?php echo htmlspecialchars($form_data['description'] ?? ''); ?>
                                        </textarea>
                                    </div>

                                    <!-- ویدئو -->
                                    <div class="col-md-12 mb-3">
                                        <label for="video_url" class="form-label">آدرس ویدئو</label>
                                        <input type="url" class="form-control" id="video_url" name="video_url" 
                                               value="<?php echo htmlspecialchars($form_data['video_url'] ?? ''); ?>" 
                                               placeholder="https://example.com/video.mp4">
                                        <div class="form-text">آدرس مستقیم فایل ویدئو یا لینک یوتیوب</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- قیمت‌ها -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">قیمت‌گذاری</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="price_representative" class="form-label">قیمت نماینده (ریال)</label>
                                        <input type="number" class="form-control" id="price_representative" 
                                               name="price_representative" min="0" step="1000"
                                               value="<?php echo $form_data['price_representative'] ?? 0; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="price_partner" class="form-label">قیمت همکار (ریال)</label>
                                        <input type="number" class="form-control" id="price_partner" 
                                               name="price_partner" min="0" step="1000"
                                               value="<?php echo $form_data['price_partner'] ?? 0; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="price_expert" class="form-label">قیمت کارشناس (ریال)</label>
                                        <input type="number" class="form-control" id="price_expert" 
                                               name="price_expert" min="0" step="1000"
                                               value="<?php echo $form_data['price_expert'] ?? 0; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="price_consumer" class="form-label">قیمت مصرف کننده (ریال)</label>
                                        <input type="number" class="form-control" id="price_consumer" 
                                               name="price_consumer" min="0" step="1000"
                                               value="<?php echo $form_data['price_consumer'] ?? 0; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- تصاویر -->
                    <div class="col-lg-4">
                        <!-- تصویر اصلی -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">تصویر شاخص</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="main_image" class="form-label">انتخاب تصویر شاخص</label>
                                    <input type="file" class="form-control" id="main_image" name="main_image" 
                                           accept="image/*" onchange="previewImage(this, 'main_image_preview')">
                                    <div class="form-text">فرمت‌های مجاز: JPG, PNG, GIF (حداکثر 2MB)</div>
                                </div>
                                
                                <div class="text-center">
                                    <img id="main_image_preview" class="image-preview" style="display: none;">
                                </div>
                            </div>
                        </div>

                        <!-- گالری تصاویر -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">گالری تصاویر</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="gallery_images" class="form-label">انتخاب تصاویر گالری</label>
                                    <input type="file" class="form-control" id="gallery_images" 
                                           name="gallery_images[]" accept="image/*" multiple
                                           onchange="previewGalleryImages(this)">
                                    <div class="form-text">می‌توانید چندین تصویر انتخاب کنید</div>
                                </div>
                                
                                <div id="gallery_preview" class="image-gallery"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- دکمه‌های عملیات -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save me-2"></i>ذخیره محصول
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>انصراف
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
// پیش‌نمایش تصاویر گالری
function previewGalleryImages(input) {
    const preview = document.getElementById('gallery_preview');
    preview.innerHTML = '';
    
    if (input.files) {
        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-item';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'image-preview';
                
                div.appendChild(img);
                preview.appendChild(div);
            }
            
            reader.readAsDataURL(file);
        }
    }
}

// فرمت کردن قیمت‌ها هنگام تایپ
document.querySelectorAll('input[type="number"][name^="price_"]').forEach(function(input) {
    input.addEventListener('input', function() {
        // حذف کاراکترهای غیرعددی
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});

// اعتبارسنجی فرم
document.getElementById('productForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>

<?php include '../../includes/footer.php'; ?>
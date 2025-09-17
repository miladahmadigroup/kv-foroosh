<?php
require_once '../../config/config.php';
check_permission('admin');

$product = new Product($db);
$category = new Category($db);

$message = '';
$error = '';
$form_data = [];

// دریافت دسته‌بندی‌های فعال (شامل همه دسته‌ها)
$categories = $category->getAll(false); // false = فقط فعال‌ها

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_data = sanitize_input($_POST);
    
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
    
    // اعتبارسنجی ویدئو
    if (!empty($form_data['video_url'])) {
        $video_url = $form_data['video_url'];
        // بررسی فرمت URL
        if (!filter_var($video_url, FILTER_VALIDATE_URL)) {
            $errors[] = 'آدرس ویدئو معتبر نیست.';
        }
    }
    
    // پردازش تصویر اصلی
    $main_image_path = '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        $main_image_path = upload_file($_FILES['main_image'], 'products');
        if (!$main_image_path) {
            $errors[] = 'خطا در آپلود تصویر اصلی.';
        }
    }
    
    // پردازش تصاویر گالری
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
    
    if (empty($errors)) {
        $form_data['main_image'] = $main_image_path;
        
        $product_id = $product->create($form_data, $gallery_images);
        if ($product_id) {
            $message = 'محصول با موفقیت اضافه شد.';
            $form_data = [];
        } else {
            $error = 'خطا در ایجاد محصول.';
            if ($main_image_path) delete_file($main_image_path);
            foreach ($gallery_images as $img) delete_file($img);
        }
    } else {
        $error = implode('<br>', $errors);
        if ($main_image_path) delete_file($main_image_path);
        foreach ($gallery_images as $img) delete_file($img);
    }
}

$page_title = 'افزودن محصول جدید';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">افزودن محصول جدید</h1>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-2"></i>بازگشت به لیست
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">اطلاعات اصلی محصول</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">نام محصول <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" 
                                               value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">کد محصول <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="code" 
                                               value="<?php echo htmlspecialchars($form_data['code'] ?? ''); ?>" required>
                                        <div class="form-text">کد محصول باید منحصر به فرد باشد</div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">دسته‌بندی <span class="text-danger">*</span></label>
                                        <select class="form-select" name="category_id" required>
                                            <option value="">انتخاب دسته‌بندی</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" 
                                                        <?php echo (($form_data['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                                    <?php 
                                                    // نمایش ساختار درختی دسته‌ها
                                                    if ($cat['parent_name']) {
                                                        echo htmlspecialchars($cat['parent_name']) . ' > ' . htmlspecialchars($cat['name']);
                                                    } else {
                                                        echo htmlspecialchars($cat['name']);
                                                    }
                                                    ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">توضیحات</label>
                                        <textarea class="form-control" id="description" name="description" rows="6">
                                            <?php echo htmlspecialchars($form_data['description'] ?? ''); ?>
                                        </textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">آدرس ویدئو</label>
                                        <input type="url" class="form-control" name="video_url" 
                                               value="<?php echo htmlspecialchars($form_data['video_url'] ?? ''); ?>" 
                                               placeholder="https://youtube.com/watch?v=... یا آدرس مستقیم ویدئو">
                                        <div class="form-text">
                                            می‌تواند لینک یوتیوب، آپارات، ویمیو یا آدرس مستقیم فایل ویدئو باشد
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">قیمت‌گذاری</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">قیمت نماینده (ریال)</label>
                                        <input type="number" class="form-control" name="price_representative" 
                                               min="0" step="1000" value="<?php echo $form_data['price_representative'] ?? 0; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">قیمت همکار (ریال)</label>
                                        <input type="number" class="form-control" name="price_partner" 
                                               min="0" step="1000" value="<?php echo $form_data['price_partner'] ?? 0; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">قیمت کارشناس (ریال)</label>
                                        <input type="number" class="form-control" name="price_expert" 
                                               min="0" step="1000" value="<?php echo $form_data['price_expert'] ?? 0; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">قیمت مصرف کننده (ریال)</label>
                                        <input type="number" class="form-control" name="price_consumer" 
                                               min="0" step="1000" value="<?php echo $form_data['price_consumer'] ?? 0; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">تصویر شاخص</h5>
                            </div>
                            <div class="card-body">
                                <input type="file" class="form-control" name="main_image" accept="image/*" 
                                       onchange="previewImage(this, 'main_image_preview')">
                                <div class="form-text">فرمت‌های مجاز: JPG, PNG, GIF (حداکثر 2MB)</div>
                                
                                <div class="text-center mt-3">
                                    <img id="main_image_preview" class="image-preview" style="display: none;">
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">گالری تصاویر</h5>
                            </div>
                            <div class="card-body">
                                <input type="file" class="form-control" name="gallery_images[]" 
                                       accept="image/*" multiple onchange="previewGalleryImages(this)">
                                <div class="form-text">می‌توانید چندین تصویر انتخاب کنید</div>
                                
                                <div id="gallery_preview" class="row mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

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
            </form>
        </main>
    </div>
</div>

<!-- TinyMCE Editor -->
<script src="https://cdn.tiny.cloud/1/u91ty2sy0tkkig9umbwybvtm6yohdhras5soe40sdofnryuf/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
// تنظیم TinyMCE برای فارسی
tinymce.init({
    selector: '#description',
    height: 300,
    language: 'fa',
    directionality: 'rtl',
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | ' +
    'bold italic forecolor | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat | help',
    content_style: 'body { font-family: Vazir, Arial, sans-serif; font-size: 14px; direction: rtl; }',
    setup: function (editor) {
        editor.on('change', function () {
            editor.save();
        });
    }
});

// پیش‌نمایش تصاویر گالری
function previewGalleryImages(input) {
    const preview = document.getElementById('gallery_preview');
    preview.innerHTML = '';
    
    if (input.files) {
        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-6 mb-2';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-fluid rounded';
                img.style.height = '80px';
                img.style.objectFit = 'cover';
                
                col.appendChild(img);
                preview.appendChild(col);
            }
            
            reader.readAsDataURL(file);
        }
    }
}

// فرمت کردن قیمت‌ها
document.querySelectorAll('input[type="number"][name^="price_"]').forEach(function(input) {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>

<?php include '../../includes/footer.php'; ?>

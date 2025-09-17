<?php
// admin/products/index.php
require_once '../../config/config.php';

// بررسی دسترسی
check_permission('admin');

$product = new Product($db);
$category = new Category($db);

// پردازش عملیات
$message = '';
$error = '';

// حذف محصول
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    if ($product->delete($delete_id)) {
        $message = 'محصول با موفقیت حذف شد.';
    } else {
        $error = 'خطا در حذف محصول.';
    }
}

// تغییر وضعیت محصول
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $toggle_id = (int)$_GET['toggle'];
    
    if ($product->toggleStatus($toggle_id)) {
        $message = 'وضعیت محصول تغییر کرد.';
    } else {
        $error = 'خطا در تغییر وضعیت محصول.';
    }
}

// تنظیمات صفحه‌بندی
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// فیلترها
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

// دریافت محصولات
$products = $product->getAll($per_page, $offset, $search, $category_id);
$total_products = $product->count($search, $category_id);
$total_pages = ceil($total_products / $per_page);

// دریافت دسته‌بندی‌ها برای فیلتر
$categories = $category->getAll();

$page_title = 'مدیریت محصولات';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">مدیریت محصولات</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="add.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>افزودن محصول جدید
                        </a>
                        <a href="../categories/" class="btn btn-outline-secondary">
                            <i class="fas fa-tags me-2"></i>مدیریت دسته‌بندی‌ها
                        </a>
                    </div>
                </div>
            </div>

            <!-- نمایش پیغام‌ها -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- جستجو و فیلتر -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">جستجو</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="جستجو در نام، کد، توضیحات...">
                        </div>
                        <div class="col-md-4">
                            <label for="category" class="form-label">دسته‌بندی</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">همه دسته‌بندی‌ها</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php 
                                        echo htmlspecialchars($cat['name']);
                                        if ($cat['parent_name']) {
                                            echo ' (' . htmlspecialchars($cat['parent_name']) . ')';
                                        }
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>جستجو
                            </button>
                            <?php if (!empty($search) || $category_id): ?>
                                <a href="?" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>پاک
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- آمار سریع -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-sm-3">
                                    <h5 class="text-primary"><?php echo number_format($total_products); ?></h5>
                                    <small class="text-muted">کل محصولات</small>
                                </div>
                                <div class="col-sm-3">
                                    <?php
                                    $active_products = $product->count();
                                    echo "<h5 class='text-success'>" . number_format($active_products) . "</h5>";
                                    echo "<small class='text-muted'>محصولات فعال</small>";
                                    ?>
                                </div>
                                <div class="col-sm-3">
                                    <h5 class="text-info"><?php echo number_format(count($categories)); ?></h5>
                                    <small class="text-muted">دسته‌بندی‌ها</small>
                                </div>
                                <div class="col-sm-3">
                                    <h5 class="text-warning"><?php echo number_format($per_page); ?></h5>
                                    <small class="text-muted">در هر صفحه</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- لیست محصولات -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">لیست محصولات</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($products)): ?>
                        <!-- نمایش کارتی در موبایل و تبلت -->
                        <div class="d-md-none">
                            <div class="row">
                                <?php foreach ($products as $product_item): ?>
                                    <div class="col-12 mb-3">
                                        <div class="card">
                                            <div class="row g-0">
                                                <div class="col-4">
                                                    <?php if (!empty($product_item['main_image'])): ?>
                                                        <img src="<?php echo UPLOAD_URL . $product_item['main_image']; ?>" 
                                                             class="img-fluid rounded-start h-100 object-fit-cover" 
                                                             alt="<?php echo htmlspecialchars($product_item['name']); ?>"
                                                             style="max-height: 120px;">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center h-100 rounded-start" 
                                                             style="min-height: 120px;">
                                                            <i class="fas fa-image fa-2x text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-8">
                                                    <div class="card-body p-3">
                                                        <h6 class="card-title mb-1">
                                                            <?php echo htmlspecialchars($product_item['name']); ?>
                                                        </h6>
                                                        <p class="card-text">
                                                            <small class="text-muted">
                                                                کد: <?php echo htmlspecialchars($product_item['code']); ?>
                                                            </small><br>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars($product_item['category_name']); ?>
                                                            </small>
                                                        </p>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="edit.php?id=<?php echo $product_item['id']; ?>" 
                                                               class="btn btn-outline-primary btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="?toggle=<?php echo $product_item['id']; ?>" 
                                                               class="btn btn-outline-warning btn-sm">
                                                                <i class="fas fa-toggle-<?php echo $product_item['is_active'] ? 'on' : 'off'; ?>"></i>
                                                            </a>
                                                            <a href="?delete=<?php echo $product_item['id']; ?>" 
                                                               class="btn btn-outline-danger btn-sm"
                                                               onclick="return confirm('آیا از حذف این محصول اطمینان دارید؟')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- نمایش جدولی در دسکتاپ -->
                        <div class="d-none d-md-block">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>تصویر</th>
                                            <th>نام محصول</th>
                                            <th>کد</th>
                                            <th>دسته‌بندی</th>
                                            <th>قیمت‌ها (ریال)</th>
                                            <th>وضعیت</th>
                                            <th>تاریخ ایجاد</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product_item): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($product_item['main_image'])): ?>
                                                        <img src="<?php echo UPLOAD_URL . $product_item['main_image']; ?>" 
                                                             class="image-preview" 
                                                             alt="<?php echo htmlspecialchars($product_item['name']); ?>"
                                                             style="width: 50px; height: 50px;">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                                             style="width: 50px; height: 50px; border-radius: 4px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($product_item['name']); ?></strong>
                                                    <?php if (!empty($product_item['description'])): ?>
                                                        <br><small class="text-muted">
                                                            <?php echo substr(htmlspecialchars($product_item['description']), 0, 50) . '...'; ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <code><?php echo htmlspecialchars($product_item['code']); ?></code>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars($product_item['category_name']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="d-block">نماینده: <?php echo format_price($product_item['price_representative']); ?></small>
                                                    <small class="d-block">همکار: <?php echo format_price($product_item['price_partner']); ?></small>
                                                    <small class="d-block">کارشناس: <?php echo format_price($product_item['price_expert']); ?></small>
                                                    <small class="d-block">مصرف‌کننده: <?php echo format_price($product_item['price_consumer']); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($product_item['is_active']): ?>
                                                        <span class="badge bg-success">فعال</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">غیرفعال</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('Y/m/d', strtotime($product_item['created_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="edit.php?id=<?php echo $product_item['id']; ?>" 
                                                           class="btn btn-outline-primary" title="ویرایش">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <a href="?toggle=<?php echo $product_item['id']; ?>" 
                                                           class="btn btn-outline-warning" 
                                                           title="تغییر وضعیت"
                                                           onclick="return confirm('آیا از تغییر وضعیت این محصول اطمینان دارید؟')">
                                                            <i class="fas fa-toggle-<?php echo $product_item['is_active'] ? 'on' : 'off'; ?>"></i>
                                                        </a>
                                                        
                                                        <a href="?delete=<?php echo $product_item['id']; ?>" 
                                                           class="btn btn-outline-danger" 
                                                           title="حذف"
                                                           onclick="return confirm('آیا از حذف این محصول اطمینان دارید؟ این عمل قابل برگشت نیست.')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- صفحه‌بندی -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="صفحه‌بندی محصولات" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>">قبلی</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $start = max(1, $page - 2);
                                    $end = min($total_pages, $page + 2);
                                    
                                    for ($i = $start; $i <= $end; $i++):
                                    ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>">بعدی</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">هیچ محصولی یافت نشد</h5>
                            <?php if (!empty($search) || $category_id): ?>
                                <p class="text-muted">
                                    نتیجه‌ای برای 
                                    <?php if (!empty($search)): ?>
                                        جستجوی "<?php echo htmlspecialchars($search); ?>"
                                    <?php endif; ?>
                                    <?php if ($category_id): ?>
                                        در دسته‌بندی انتخاب شده
                                    <?php endif; ?>
                                    یافت نشد.
                                </p>
                                <a href="?" class="btn btn-outline-primary">نمایش همه محصولات</a>
                            <?php else: ?>
                                <p class="text-muted">هنوز محصولی اضافه نشده است.</p>
                                <a href="add.php" class="btn btn-primary">افزودن اولین محصول</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
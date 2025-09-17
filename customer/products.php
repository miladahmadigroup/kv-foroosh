<?php
// customer/products.php
require_once '../config/config.php';

// بررسی ورود کاربر
if (!is_logged_in()) {
    redirect(SITE_URL . 'login.php');
}

// بررسی نقش مشتری
if (get_user_role() != 'customer') {
    redirect(SITE_URL . 'admin/');
}

$product = new Product($db);
$category = new Category($db);

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

$customer_type = get_customer_type();

$page_title = 'محصولات';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- سایدبار دسته‌بندی‌ها -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="position-sticky pt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-muted">
                    <span>دسته‌بندی‌ها</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link <?php echo empty($category_id) ? 'active' : ''; ?>" 
                           href="products.php">
                            <i class="fas fa-list"></i>
                            همه محصولات
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($category_id == $cat['id']) ? 'active' : ''; ?>" 
                               href="products.php?category=<?php echo $cat['id']; ?>">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($cat['name']); ?>
                                <?php if ($cat['parent_name']): ?>
                                    <br><small class="text-muted ps-3"><?php echo htmlspecialchars($cat['parent_name']); ?></small>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- اطلاعات مشتری -->
                <div class="sidebar-sticky-bottom mt-4 p-3 bg-light rounded">
                    <div class="text-center">
                        <small class="text-muted d-block">نوع مشتری شما:</small>
                        <strong class="text-primary">
                            <?php echo translate_customer_type($customer_type); ?>
                        </strong>
                    </div>
                    <hr class="my-2">
                    <div class="text-center">
                        <small class="text-muted">قیمت‌ها بر اساس نوع مشتری شما محاسبه می‌شود</small>
                    </div>
                </div>
            </div>
        </nav>

        <!-- محتوای اصلی -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">محصولات</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <small class="text-muted">
                        <?php echo number_format($total_products); ?> محصول یافت شد
                    </small>
                </div>
            </div>

            <!-- جستجو -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <?php if ($category_id): ?>
                            <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                        <?php endif; ?>
                        
                        <div class="col-md-10">
                            <label for="search" class="form-label">جستجو در محصولات</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="جستجو در نام، کد یا توضیحات محصولات...">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>جستجو
                            </button>
                        </div>
                        
                        <?php if (!empty($search)): ?>
                        <div class="col-12">
                            <a href="products.php<?php echo $category_id ? '?category=' . $category_id : ''; ?>" 
                               class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>پاک کردن جستجو
                            </a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- لیست محصولات -->
            <?php if (!empty($products)): ?>
                <div class="row">
                    <?php foreach ($products as $product_item): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 product-card">
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center position-relative" 
                                     style="height: 250px; overflow: hidden;">
                                    <?php if (!empty($product_item['main_image'])): ?>
                                        <img src="<?php echo UPLOAD_URL . $product_item['main_image']; ?>" 
                                             class="img-fluid" 
                                             alt="<?php echo htmlspecialchars($product_item['name']); ?>"
                                             style="max-height: 100%; max-width: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                    <?php else: ?>
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    <?php endif; ?>
                                    
                                    <!-- بج دسته‌بندی -->
                                    <span class="badge bg-secondary position-absolute top-0 start-0 m-2">
                                        <?php echo htmlspecialchars($product_item['category_name']); ?>
                                    </span>
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title mb-2">
                                        <?php echo htmlspecialchars($product_item['name']); ?>
                                    </h5>
                                    
                                    <p class="card-text">
                                        <small class="text-muted">
                                            کد محصول: <code><?php echo htmlspecialchars($product_item['code']); ?></code>
                                        </small>
                                    </p>
                                    
                                    <?php if (!empty($product_item['description'])): ?>
                                        <p class="card-text flex-grow-1">
                                            <?php echo substr(strip_tags($product_item['description']), 0, 100) . '...'; ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <!-- قیمت -->
                                    <div class="price-display mb-3">
                                        <?php
                                        $price = $product->getPrice($product_item['id'], $customer_type);
                                        if ($price > 0) {
                                            echo '<strong>' . format_price($price) . '</strong>';
                                        } else {
                                            echo '<small class="text-muted">قیمت تماس بگیرید</small>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <?php echo date('Y/m/d', strtotime($product_item['created_at'])); ?>
                                        </small>
                                        
                                        <?php if (!empty($product_item['video_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($product_item['video_url']); ?>" 
                                               target="_blank" class="btn btn-outline-info btn-sm" title="مشاهده ویدئو">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
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
                        <a href="products.php" class="btn btn-primary">مشاهده همه محصولات</a>
                    <?php else: ?>
                        <p class="text-muted">هنوز محصولی اضافه نشده است.</p>
                        <a href="index.php" class="btn btn-primary">بازگشت به پنل</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Mobile sidebar toggle button -->
<div class="d-md-none">
    <button class="btn btn-primary sidebar-toggle position-fixed" 
            style="top: 80px; right: 15px; z-index: 1000;" 
            onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
</div>

<style>
.product-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.product-card:hover img {
    transform: scale(1.05);
}

.sidebar {
    background-color: #f8f9fa;
    border-left: 1px solid #e9ecef;
    min-height: calc(100vh - 76px);
}

.sidebar .nav-link {
    color: #495057;
    padding: 0.75rem 1rem;
    margin-bottom: 0.25rem;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

.sidebar .nav-link:hover {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.sidebar .nav-link.active {
    background-color: #28a745;
    color: white;
}

.sidebar .nav-link i {
    width: 1.25rem;
    margin-left: 0.75rem;
    text-align: center;
}

@media (max-width: 767.98px) {
    .sidebar {
        position: fixed;
        top: 76px;
        right: -100%;
        width: 280px;
        height: calc(100vh - 76px);
        transition: right 0.3s ease;
        z-index: 999;
        overflow-y: auto;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .sidebar.show {
        right: 0;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
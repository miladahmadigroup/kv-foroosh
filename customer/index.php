<?php
require_once '../config/config.php';

if (!is_logged_in()) {
    redirect(SITE_URL . 'login.php');
}

if (get_user_role() != 'customer') {
    redirect(SITE_URL . 'admin/');
}

$product = new Product($db);
$category = new Category($db);

$total_products = $product->count();
$categories_list = $category->getMainCategories();
$recent_products = $product->getAll(6, 0);

$customer_type = get_customer_type();
$customer_type_fa = translate_customer_type($customer_type);

$page_title = 'پنل مشتری';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-12 px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">پنل مشتری</h1>
                <div class="btn-toolbar">
                    <small class="text-muted">
                        خوش آمدید، <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        <br>
                        <span class="badge bg-info"><?php echo $customer_type_fa; ?></span>
                    </small>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col mr-2">
                                    <div class="font-weight-bold text-uppercase mb-1">کل محصولات</div>
                                    <div class="h5 mb-0"><?php echo number_format($total_products); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-box fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col mr-2">
                                    <div class="font-weight-bold text-uppercase mb-1">دسته‌بندی‌ها</div>
                                    <div class="h5 mb-0"><?php echo number_format(count($categories_list)); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tags fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col mr-2">
                                    <div class="font-weight-bold text-uppercase mb-1">نوع مشتری</div>
                                    <div class="h6 mb-0"><?php echo $customer_type_fa; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col mr-2">
                                    <div class="font-weight-bold text-uppercase mb-1">امروز</div>
                                    <div class="h5 mb-0"><?php echo date('Y/m/d'); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">دسترسی‌های سریع</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="products.php" class="btn btn-outline-primary btn-lg w-100">
                                        <i class="fas fa-shopping-bag fa-2x mb-2"></i>
                                        <br>مشاهده محصولات
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="profile.php" class="btn btn-outline-success btn-lg w-100">
                                        <i class="fas fa-user-edit fa-2x mb-2"></i>
                                        <br>ویرایش پروفایل
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">اطلاعات شما</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <span>نام:</span>
                                    <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <span>نام کاربری:</span>
                                    <code><?php echo htmlspecialchars($_SESSION['username']); ?></code>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <span>نوع مشتری:</span>
                                    <span class="badge bg-info"><?php echo $customer_type_fa; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($categories_list)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">دسته‌بندی‌های محصولات</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($categories_list as $cat): ?>
                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                        <a href="products.php?category=<?php echo $cat['id']; ?>" 
                                           class="btn btn-outline-secondary w-100 p-3">
                                            <i class="fas fa-tag fa-lg mb-2"></i>
                                            <br>
                                            <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($recent_products)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="mb-0">جدیدترین محصولات</h5>
                            <a href="products.php" class="btn btn-sm btn-outline-primary">
                                مشاهده همه
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($recent_products as $product_item): ?>
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                                 style="height: 200px;">
                                                <?php if ($product_item['main_image']): ?>
                                                    <img src="<?php echo UPLOAD_URL . $product_item['main_image']; ?>" 
                                                         class="img-fluid" style="max-height: 100%; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fas fa-image fa-3x text-muted"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($product_item['name']); ?></h6>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        کد: <?php echo htmlspecialchars($product_item['code']); ?>
                                                    </small>
                                                </p>
                                                
                                                <div class="price-display">
                                                    <?php
                                                    $price = $product->getPrice($product_item['id'], $customer_type);
                                                    echo format_price($price);
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <small class="text-muted">
                                                    دسته‌بندی: <?php echo htmlspecialchars($product_item['category_name']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
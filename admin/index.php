<?php
require_once '../config/config.php';
check_permission('admin');

$user = new User($db);
$product = new Product($db);
$category = new Category($db);

$total_users = $user->count();
$total_products = $product->count();
$total_categories = count($category->getAll());

$page_title = 'داشبورد مدیریت';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">داشبورد مدیریت</h1>
                <div class="btn-toolbar">
                    <small class="text-muted">
                        خوش آمدید، <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </small>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col mr-2">
                                    <div class="font-weight-bold text-uppercase mb-1">کل کاربران</div>
                                    <div class="h5 mb-0"><?php echo number_format($total_users); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x"></i>
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
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col mr-2">
                                    <div class="font-weight-bold text-uppercase mb-1">دسته‌بندی‌ها</div>
                                    <div class="h5 mb-0"><?php echo number_format($total_categories); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tags fa-2x"></i>
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

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">دسترسی‌های سریع</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="users/add.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-user-plus me-2"></i>افزودن کاربر جدید
                                </a>
                                <a href="products/add.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-plus me-2"></i>افزودن محصول جدید
                                </a>
                                <a href="categories/add.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-tag me-2"></i>افزودن دسته‌بندی جدید
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">آخرین کاربران</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $recent_users = $user->getAll(5, 0);
                            if (!empty($recent_users)):
                            ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>نام</th>
                                                <th>نقش</th>
                                                <th>تاریخ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_users as $recent_user): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($recent_user['full_name']); ?></td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <?php echo translate_user_role($recent_user['user_role']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small><?php echo date('Y/m/d', strtotime($recent_user['created_at'])); ?></small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">هیچ کاربری یافت نشد.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
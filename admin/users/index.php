<?php
// admin/users/index.php
require_once '../../config/config.php';

// بررسی دسترسی
check_permission('admin');

$user = new User($db);

// پردازش عملیات
$message = '';
$error = '';

// حذف کاربر
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // بررسی عدم حذف خودی
    if ($delete_id == $_SESSION['user_id']) {
        $error = 'نمی‌توانید خودتان را حذف کنید.';
    } else {
        if ($user->delete($delete_id)) {
            $message = 'کاربر با موفقیت حذف شد.';
        } else {
            $error = 'خطا در حذف کاربر.';
        }
    }
}

// تغییر وضعیت کاربر
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $toggle_id = (int)$_GET['toggle'];
    
    if ($toggle_id == $_SESSION['user_id']) {
        $error = 'نمی‌توانید وضعیت خودتان را تغییر دهید.';
    } else {
        if ($user->toggleStatus($toggle_id)) {
            $message = 'وضعیت کاربر تغییر کرد.';
        } else {
            $error = 'خطا در تغییر وضعیت کاربر.';
        }
    }
}

// تنظیمات صفحه‌بندی
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// جستجو
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// دریافت کاربران
$users = $user->getAll($per_page, $offset, $search);
$total_users = $user->count($search);
$total_pages = ceil($total_users / $per_page);

$page_title = 'مدیریت کاربران';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">مدیریت کاربران</h1>
                <?php if (get_user_role() == 'system_admin'): ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>افزودن کاربر جدید
                        </a>
                    </div>
                <?php endif; ?>
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
                        <div class="col-md-8">
                            <label for="search" class="form-label">جستجو</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="جستجو در نام، نام کاربری، موبایل...">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>جستجو
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="?" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>پاک کردن
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
                                    <h5 class="text-primary"><?php echo number_format($total_users); ?></h5>
                                    <small class="text-muted">کل کاربران</small>
                                </div>
                                <div class="col-sm-3">
                                    <?php
                                    $active_count = $user->count('', true); // فعال
                                    echo "<h5 class='text-success'>" . number_format($active_count) . "</h5>";
                                    echo "<small class='text-muted'>کاربران فعال</small>";
                                    ?>
                                </div>
                                <div class="col-sm-3">
                                    <?php
                                    $customer_query = "SELECT COUNT(*) as count FROM users WHERE user_role = 'customer' AND is_active = 1";
                                    $customer_stmt = $db->prepare($customer_query);
                                    $customer_stmt->execute();
                                    $customer_count = $customer_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                    echo "<h5 class='text-info'>" . number_format($customer_count) . "</h5>";
                                    echo "<small class='text-muted'>مشتریان</small>";
                                    ?>
                                </div>
                                <div class="col-sm-3">
                                    <?php
                                    $admin_query = "SELECT COUNT(*) as count FROM users WHERE user_role IN ('system_admin', 'warehouse_manager', 'financial_manager', 'sales_manager') AND is_active = 1";
                                    $admin_stmt = $db->prepare($admin_query);
                                    $admin_stmt->execute();
                                    $admin_count = $admin_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                    echo "<h5 class='text-warning'>" . number_format($admin_count) . "</h5>";
                                    echo "<small class='text-muted'>مدیران</small>";
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول کاربران -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">لیست کاربران</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($users)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>شناسه</th>
                                        <th>نام و نام خانوادگی</th>
                                        <th>نام کاربری</th>
                                        <th>موبایل</th>
                                        <th>ایمیل</th>
                                        <th>نقش</th>
                                        <th>نوع مشتری</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ ثبت‌نام</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user_item): ?>
                                        <tr>
                                            <td><?php echo $user_item['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($user_item['full_name']); ?></strong>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($user_item['username']); ?></code>
                                            </td>
                                            <td>
                                                <a href="tel:<?php echo $user_item['mobile']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($user_item['mobile']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if (!empty($user_item['email'])): ?>
                                                    <a href="mailto:<?php echo $user_item['email']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($user_item['email']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <small class="text-muted">ندارد</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo translate_user_role($user_item['user_role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user_item['customer_type']): ?>
                                                    <span class="badge bg-secondary">
                                                        <?php echo translate_customer_type($user_item['customer_type']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <small class="text-muted">-</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user_item['is_active']): ?>
                                                    <span class="badge bg-success">فعال</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">غیرفعال</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('Y/m/d', strtotime($user_item['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="edit.php?id=<?php echo $user_item['id']; ?>" 
                                                       class="btn btn-outline-primary" title="ویرایش">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if ($user_item['id'] != $_SESSION['user_id']): ?>
                                                        <a href="?toggle=<?php echo $user_item['id']; ?>" 
                                                           class="btn btn-outline-warning" 
                                                           title="تغییر وضعیت"
                                                           onclick="return confirm('آیا از تغییر وضعیت این کاربر اطمینان دارید؟')">
                                                            <i class="fas fa-toggle-<?php echo $user_item['is_active'] ? 'on' : 'off'; ?>"></i>
                                                        </a>
                                                        
                                                        <?php if (get_user_role() == 'system_admin' && $user_item['user_role'] != 'system_admin'): ?>
                                                            <a href="?delete=<?php echo $user_item['id']; ?>" 
                                                               class="btn btn-outline-danger" 
                                                               title="حذف"
                                                               onclick="return confirm('آیا از حذف این کاربر اطمینان دارید؟ این عمل قابل برگشت نیست.')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- صفحه‌بندی -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="صفحه‌بندی کاربران" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">قبلی</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $start = max(1, $page - 2);
                                    $end = min($total_pages, $page + 2);
                                    
                                    for ($i = $start; $i <= $end; $i++):
                                    ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">بعدی</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">هیچ کاربری یافت نشد</h5>
                            <?php if (!empty($search)): ?>
                                <p class="text-muted">نتیجه‌ای برای جستجوی "<?php echo htmlspecialchars($search); ?>" یافت نشد.</p>
                                <a href="?" class="btn btn-outline-primary">نمایش همه کاربران</a>
                            <?php else: ?>
                                <p class="text-muted">هنوز کاربری ثبت‌نام نکرده است.</p>
                                <?php if (get_user_role() == 'system_admin'): ?>
                                    <a href="add.php" class="btn btn-primary">افزودن اولین کاربر</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<?php
require_once '../../config/config.php';
check_permission('admin');

$category = new Category($db);

$message = '';
$error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    if ($category->delete($delete_id)) {
        $message = 'دسته‌بندی با موفقیت حذف شد.';
    } else {
        $error = 'خطا در حذف دسته‌بندی. ممکن است محصول در این دسته وجود داشته باشد.';
    }
}

$categories = $category->getAll(true);

$page_title = 'مدیریت دسته‌بندی‌ها';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">مدیریت دسته‌بندی‌ها</h1>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>افزودن دسته‌بندی جدید
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-sm-4">
                                    <h5 class="text-primary"><?php echo count($categories); ?></h5>
                                    <small class="text-muted">کل دسته‌بندی‌ها</small>
                                </div>
                                <div class="col-sm-4">
                                    <?php
                                    $active_categories = array_filter($categories, function($cat) { 
                                        return $cat['is_active']; 
                                    });
                                    echo "<h5 class='text-success'>" . count($active_categories) . "</h5>";
                                    echo "<small class='text-muted'>دسته‌های فعال</small>";
                                    ?>
                                </div>
                                <div class="col-sm-4">
                                    <?php
                                    $main_categories = array_filter($categories, function($cat) { 
                                        return is_null($cat['parent_id']); 
                                    });
                                    echo "<h5 class='text-info'>" . count($main_categories) . "</h5>";
                                    echo "<small class='text-muted'>دسته‌های اصلی</small>";
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">لیست دسته‌بندی‌ها</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($categories)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>شناسه</th>
                                        <th>نام دسته‌بندی</th>
                                        <th>دسته والد</th>
                                        <th>توضیحات</th>
                                        <th>تعداد محصولات</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ ایجاد</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td><?php echo $cat['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                                <?php if (!is_null($cat['parent_id'])): ?>
                                                    <br><small class="text-muted">└ زیردسته</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($cat['parent_name']): ?>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars($cat['parent_name']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <small class="text-muted">دسته اصلی</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($cat['description']): ?>
                                                    <small>
                                                        <?php echo substr(htmlspecialchars($cat['description']), 0, 50); ?>
                                                        <?php if (strlen($cat['description']) > 50) echo '...'; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">ندارد</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $category->getProductCount($cat['id']); ?> محصول
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($cat['is_active']): ?>
                                                    <span class="badge bg-success">فعال</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">غیرفعال</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('Y/m/d', strtotime($cat['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit.php?id=<?php echo $cat['id']; ?>" 
                                                       class="btn btn-outline-primary" title="ویرایش">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <a href="?delete=<?php echo $cat['id']; ?>" 
                                                       class="btn btn-outline-danger" 
                                                       title="حذف"
                                                       onclick="return confirm('آیا از حذف این دسته‌بندی اطمینان دارید؟')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">هیچ دسته‌بندی‌ای یافت نشد</h5>
                            <p class="text-muted">هنوز دسته‌بندی‌ای اضافه نشده است.</p>
                            <a href="add.php" class="btn btn-primary">افزودن اولین دسته‌بندی</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
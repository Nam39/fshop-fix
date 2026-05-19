<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['roleId'] ?? 2;
$backLink = ($role == 1) ? './admin.php' : './index.php';
?>
<div class="container py-5">
    <h2 class="text-center mb-4 mt-5 pt-5">Thông tin tài khoản</h2>
    
    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (isset($error) && $error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="row g-4">
        <div class="col-md-4 text-center">
            <img src="./assets/img/<?= htmlspecialchars($user['Anh_user']) ?>" alt="Avatar" class="rounded-circle avatar-img-info mb-3">
            <div>
                <label class="form-label">Thay ảnh đại diện:</label>
                <input type="file" name="anh_user" class="form-control input-cus">
            </div>
        </div>

        <div class="col-md-8">
            <div class="mb-3">
                <label class="form-label">Họ tên</label>
                <input type="text" name="ten_user" value="<?= htmlspecialchars($user['Ten_user']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="sdt" value="<?= htmlspecialchars($user['sdt']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">email</label>
                <input type="text" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Địa chỉ</label>
                <input type="text" name="diachi" value="<?= htmlspecialchars($user['diachi']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày sinh</label>
                <input type="text" name="ngaysinh" value="<?= htmlspecialchars($user['ngaysinh']) ?>" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Lưu thay đổi</button>
            <a href="<?= $backLink ?>" class="btn btn-primary">Quay lại</a>
        </div>
    </form>
</div> 
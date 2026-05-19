<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['roleId'] ?? 2;
$backLink = ($role == 1) ? './admin.php' : './index.php';
?>

<div class="w-100">
    <h3 class="fw-extrabold text-dark mb-4 pb-2 border-bottom text-center text-lg-start d-flex align-items-center justify-content-center justify-content-lg-start">
        <i class="fa-solid fa-circle-user text-primary me-2"></i>Thông tin tài khoản
    </h3>
    
    <!-- PHP ALERTS -->
    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success border-0 rounded-4 shadow-sm p-3 mb-4 fw-semibold">
            <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($success) ?>
        </div>
    <?php elseif (isset($error) && $error): ?>
        <div class="alert alert-danger border-0 rounded-4 shadow-sm p-3 mb-4 fw-semibold">
            <i class="fa-solid fa-circle-xmark me-2"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="row g-4 pt-2">
        
        <!-- LEFT: AVATAR DISPLAY & UPLOAD -->
        <div class="col-lg-4 text-center border-end border-slate-100 pb-3 pb-lg-0">
            <div class="position-relative d-inline-block mb-3">
                <img src="./assets/img/<?= htmlspecialchars($user['Anh_user']) ?>" 
                     alt="Avatar" 
                     class="rounded-circle avatar-img-info">
            </div>
            
            <div class="px-2">
                <label class="form-label form-label-custom justify-content-center mb-2">
                    <i class="fa-solid fa-camera"></i> Thay ảnh đại diện
                </label>
                <input type="file" name="anh_user" class="form-control form-control-custom bg-white">
                <div class="form-text text-muted mt-2 small">
                    Khuyên dùng ảnh tỷ lệ 1:1, định dạng PNG hoặc JPG dưới 2MB.
                </div>
            </div>
        </div>

        <!-- RIGHT: EDITABLE PROFILE FORM -->
        <div class="col-lg-8">
            <div class="row g-3">
                
                <div class="col-md-6">
                    <div class="mb-1">
                        <label class="form-label form-label-custom">
                            <i class="fa-solid fa-user"></i> Họ tên
                        </label>
                        <input type="text" 
                               name="ten_user" 
                               value="<?= htmlspecialchars($user['Ten_user']) ?>" 
                               class="form-control form-control-custom" 
                               placeholder="Nhập họ tên của bạn..."
                               required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-1">
                        <label class="form-label form-label-custom">
                            <i class="fa-solid fa-phone"></i> Số điện thoại
                        </label>
                        <input type="tel" 
                               name="sdt" 
                               value="<?= htmlspecialchars($user['sdt']) ?>" 
                               class="form-control form-control-custom" 
                               placeholder="Nhập số điện thoại..."
                               required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-1">
                        <label class="form-label form-label-custom">
                            <i class="fa-solid fa-envelope"></i> Địa chỉ Email
                        </label>
                        <input type="email" 
                               name="email" 
                               value="<?= htmlspecialchars($user['email']) ?>" 
                               class="form-control form-control-custom" 
                               placeholder="name@example.com"
                               required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-1">
                        <label class="form-label form-label-custom">
                            <i class="fa-solid fa-calendar-days"></i> Ngày sinh
                        </label>
                        <input type="text" 
                               name="ngaysinh" 
                               value="<?= htmlspecialchars($user['ngaysinh']) ?>" 
                               class="form-control form-control-custom" 
                               placeholder="Ví dụ: DD/MM/YYYY"
                               required>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-2">
                        <label class="form-label form-label-custom">
                            <i class="fa-solid fa-map-location-dot"></i> Địa chỉ nhận hàng
                        </label>
                        <input type="text" 
                               name="diachi" 
                               value="<?= htmlspecialchars($user['diachi']) ?>" 
                               class="form-control form-control-custom" 
                               placeholder="Số nhà, tên đường, phường/xã..."
                               required>
                    </div>
                </div>

                <!-- ACTIONS BUTTON CAPSULES -->
                <div class="col-12 mt-4 pt-2 d-flex gap-2 justify-content-center justify-content-lg-start flex-wrap">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                    </button>
                    <a href="<?= $backLink ?>" class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-arrow-left-long"></i> Quay lại
                    </a>
                </div>

            </div>
        </div>

    </form>
</div>
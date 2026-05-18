<?php 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$success = "";
$error = "";

$role = $_SESSION['role'] ?? 2;
$backLink = ($role == 1) ? './admin.php' : './index.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin</title>
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .avatar-img-info {
            width: 300px;
            height: 300px;
            object-fit: cover;
            border: 1px solid rgba(0, 0, 0, 0.6);
        }
        .input-cus{
            background: #ccc;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h2 class="text-center mb-4 mt-5 pt-5">Thông tin tài khoản</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
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
    
    
</head>
<body>
    
</body>
</html>
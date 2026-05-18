<?php
session_start();
require_once "./connect_DB/connect_db.php";
$conn = connectData();

if (!isset($_SESSION['idtk'])) {
    header("Location: login.php");
    exit();
}

$thongbao = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idtk = $_SESSION['idtk'];
    $matkhaucu = trim($_POST['matkhaucu']);
    $matkhaumoi = trim($_POST['matkhaumoi']);
    $xacnhan = trim($_POST['xacnhan']);

    $sql = "SELECT password FROM taikhoan WHERE idtk = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $idtk);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && trim($matkhaucu) === trim($row['password'])) {
                if ($matkhaumoi === $xacnhan) {
                    $update = "UPDATE taikhoan SET password = ? WHERE idtk = ?";
                    $stmt2 = $conn->prepare($update);
                    $stmt2->bind_param("si", $matkhaumoi, $idtk);
                    if ($stmt2->execute()) {
                        $thongbao = "<div class='alert alert-success'>✅ Đổi mật khẩu thành công.</div>";
                    } else {
                        $thongbao = "<div class='alert alert-danger'>❌ Lỗi khi cập nhật mật khẩu.</div>";
                    }
                } else {
                    $thongbao = "<div class='alert alert-warning'>⚠️ Mật khẩu mới không khớp với xác nhận.</div>";
                }
            } else {
                $thongbao = "<div class='alert alert-danger'>❌ Mật khẩu cũ không đúng.</div>";
            }
    } else {
        $thongbao = "<div class='alert alert-danger'>❌ Không thể chuẩn bị truy vấn.</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu</title>
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>

        .change-password-card {
            background: #ffffff;
            padding: 35px;
            max-width: 420px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
        }

        .in{
            border-color: rgba(0, 0, 0, 0.25);
        }
        
    </style>
</head>
<body>
<div class="container mt-5 change-password-card" style="max-width: 500px;">
    <h3 class="mb-4 text-center text-primary">Đổi mật khẩu</h3>
    <?= $thongbao ?>
    <form method="POST">
        <div class="mb-3">
            <label for="matkhaucu" class="form-label">Mật khẩu cũ</label>
            <input type="password" class="form-control in" id="matkhaucu" name="matkhaucu" required>
        </div>
        <div class="mb-3">
            <label for="matkhaumoi" class="form-label">Mật khẩu mới</label>
            <input type="password" class="form-control in" id="matkhaumoi" name="matkhaumoi" required>
        </div>
        <div class="mb-3">
            <label for="xacnhan" class="form-label">Xác nhận mật khẩu mới</label>
            <input type="password" class="form-control in" id="xacnhan" name="xacnhan" required>
        </div>
        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">⬅ Quay lại</a>
            <button type="submit" class="btn btn-success">Cập nhật mật khẩu</button>
        </div>
    </form>
</div>
</body>
</html>


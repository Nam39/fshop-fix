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

    if (empty($matkhaucu) || empty($matkhaumoi) || empty($xacnhan)) {
        $thongbao = "<div class='alert alert-warning border-0' style='background: rgba(255, 193, 7, 0.25); color: #ffe28c; backdrop-filter: blur(5px); border-radius: 10px;'><i class='fa-solid fa-triangle-exclamation me-2'></i> Vui lòng điền đầy đủ thông tin!</div>";
    } else {
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
                        $thongbao = "<div class='alert alert-success border-0' style='background: rgba(40, 167, 69, 0.25); color: #a3ffb4; backdrop-filter: blur(5px); border-radius: 10px;'><i class='fa-solid fa-circle-check me-2'></i> Đổi mật khẩu thành công! Đang chuyển hướng đăng nhập...</div>";
                        echo "<script>
                            setTimeout(function() {
                                window.location.href = 'logout.php';
                            }, 2000);
                        </script>";
                    } else {
                        $thongbao = "<div class='alert alert-danger border-0' style='background: rgba(220, 53, 69, 0.25); color: #ffccd0; backdrop-filter: blur(5px); border-radius: 10px;'><i class='fa-solid fa-triangle-exclamation me-2'></i> Lỗi khi cập nhật mật khẩu.</div>";
                    }
                    $stmt2->close();
                } else {
                    $thongbao = "<div class='alert alert-warning border-0' style='background: rgba(255, 193, 7, 0.25); color: #ffe28c; backdrop-filter: blur(5px); border-radius: 10px;'><i class='fa-solid fa-triangle-exclamation me-2'></i> Mật khẩu mới không khớp với xác nhận.</div>";
                }
            } else {
                $thongbao = "<div class='alert alert-danger border-0' style='background: rgba(220, 53, 69, 0.25); color: #ffccd0; backdrop-filter: blur(5px); border-radius: 10px;'><i class='fa-solid fa-triangle-exclamation me-2'></i> Mật khẩu cũ không chính xác.</div>";
            }
            $stmt->close();
        } else {
            $thongbao = "<div class='alert alert-danger border-0' style='background: rgba(220, 53, 69, 0.25); color: #ffccd0; backdrop-filter: blur(5px); border-radius: 10px;'><i class='fa-solid fa-triangle-exclamation me-2'></i> Lỗi truy cập cơ sở dữ liệu.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đổi mật khẩu</title>
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('./assets/img/bg-newsletter.jpg') no-repeat center center/cover;
        }

        .change-password-card {
            background: rgba(255, 255, 255, 0.15);
            padding: 35px;
            max-width: 440px;
            width: 100%;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            color: white;
        }

        .change-password-card input {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
        }

        .change-password-card input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .change-password-card input:focus {
            background: rgba(255, 255, 255, 0.5);
            color: white;
            box-shadow: none;
        }

        .btn-custom-success {
            background: rgba(40, 167, 69, 0.4);
            border: none;
            color: white;
            font-weight: bold;
            transition: all 0.2s;
        }

        .btn-custom-success:hover {
            background: rgba(40, 167, 69, 0.7);
            color: white;
        }

        .btn-custom-secondary {
            background: rgba(255, 255, 255, 0.25);
            border: none;
            color: white;
            font-weight: bold;
            transition: all 0.2s;
        }

        .btn-custom-secondary:hover {
            background: rgba(255, 255, 255, 0.45);
            color: white;
        }
    </style>
</head>
<body>
<div class="change-password-card">
    <div class="text-center mb-4">
        <i class="fa-solid fa-key fa-3x mb-3 text-warning"></i>
        <h3 class="fw-bold">Đổi mật khẩu</h3>
        <p class="text-white-50 small">Cập nhật mật khẩu mới bảo mật cho tài khoản của bạn</p>
    </div>

    <?= $thongbao ?>

    <form method="POST">
        <div class="mb-3">
            <label for="matkhaucu" class="form-label small text-white-50">Mật khẩu hiện tại</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent text-white border-end-0">
                    <i class="fas fa-lock-open"></i>
                </span>
                <input type="password" class="form-control border-start-0" id="matkhaucu" name="matkhaucu" placeholder="Nhập mật khẩu cũ" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="matkhaumoi" class="form-label small text-white-50">Mật khẩu mới</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent text-white border-end-0">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" class="form-control border-start-0" id="matkhaumoi" name="matkhaumoi" placeholder="Nhập mật khẩu mới" required>
            </div>
        </div>
        <div class="mb-4">
            <label for="xacnhan" class="form-label small text-white-50">Xác nhận mật khẩu mới</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent text-white border-end-0">
                    <i class="fas fa-check-double"></i>
                </span>
                <input type="password" class="form-control border-start-0" id="xacnhan" name="xacnhan" placeholder="Xác nhận mật khẩu mới" required>
            </div>
        </div>
        <div class="d-flex justify-content-between gap-2 mt-4">
            <a href="index.php" class="btn btn-custom-secondary px-4 py-2 rounded-pill"><i class="fa-solid fa-arrow-left me-1"></i> Quay lại</a>
            <button type="submit" class="btn btn-custom-success px-4 py-2 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i> Cập nhật</button>
        </div>
    </form>
</div>
</body>
</html>

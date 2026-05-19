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
        $thongbao = "<div class='alert border-0 text-center py-2.5 px-3 rounded-3 fw-semibold mb-4 animate-shake' role='alert' style='background: rgba(245, 158, 11, 0.12); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.25);'><i class='fa-solid fa-circle-exclamation me-2'></i> Vui lòng điền đầy đủ thông tin!</div>";
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
                        $thongbao = "<div class='alert border-0 text-center py-2.5 px-3 rounded-3 fw-semibold mb-4' role='alert' style='background: rgba(16, 185, 129, 0.12); color: #a7f3d0; border: 1px solid rgba(16, 185, 129, 0.25);'><i class='fa-solid fa-circle-check me-2'></i> Đổi mật khẩu thành công! Đang chuyển hướng đăng xuất...</div>";
                        echo "<script>
                            setTimeout(function() {
                                window.location.href = 'logout.php';
                            }, 2000);
                        </script>";
                    } else {
                        $thongbao = "<div class='alert border-0 text-center py-2.5 px-3 rounded-3 fw-semibold mb-4 animate-shake' role='alert' style='background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25);'><i class='fa-solid fa-circle-exclamation me-2'></i> Lỗi khi cập nhật mật khẩu mới.</div>";
                    }
                    $stmt2->close();
                } else {
                    $thongbao = "<div class='alert border-0 text-center py-2.5 px-3 rounded-3 fw-semibold mb-4 animate-shake' role='alert' style='background: rgba(245, 158, 11, 0.12); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.25);'><i class='fa-solid fa-circle-exclamation me-2'></i> Mật khẩu mới không khớp với xác nhận.</div>";
                }
            } else {
                $thongbao = "<div class='alert border-0 text-center py-2.5 px-3 rounded-3 fw-semibold mb-4 animate-shake' role='alert' style='background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25);'><i class='fa-solid fa-circle-exclamation me-2'></i> Mật khẩu hiện tại không chính xác.</div>";
            }
            $stmt->close();
        } else {
            $thongbao = "<div class='alert border-0 text-center py-2.5 px-3 rounded-3 fw-semibold mb-4 animate-shake' role='alert' style='background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25);'><i class='fa-solid fa-circle-exclamation me-2'></i> Lỗi truy cập cơ sở dữ liệu.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đổi mật khẩu | UNIQ</title>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    
    <style>
        :root {
            --primary-blue: #3b82f6;
            --dark-bg: #090d16;
            --glass-card: rgba(15, 23, 42, 0.45);
        }

        body {
            font-family: 'Outfit', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(9, 13, 22, 0.7), rgba(9, 13, 22, 0.85)), url('./assets/img/bg-newsletter.jpg') no-repeat center center/cover;
            color: #f8fafc;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .change-password-card {
            background: var(--glass-card);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 440px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .change-password-title {
            font-weight: 800;
            font-size: 2.2rem;
            letter-spacing: 0.1em;
            color: #ffffff;
            margin-bottom: 5px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 16px;
        }

        .input-group-custom i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 1.05rem;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .form-control-pass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 12px 16px 12px 46px;
            color: #ffffff !important;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-pass::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .form-control-pass:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .form-control-pass:focus + i {
            color: var(--primary-blue);
        }

        .btn-update {
            background: #ffffff;
            color: #0f172a;
            font-weight: 700;
            border-radius: 50px;
            border: none;
            padding: 11px 24px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1);
        }

        .btn-update:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.2);
            color: #0f172a;
        }

        .btn-cancel {
            background: transparent;
            color: #ffffff;
            font-weight: 600;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 11px 24px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: #ffffff;
            color: #ffffff;
        }

        @media (max-width: 480px) {
            .change-password-card {
                padding: 30px 20px;
                max-width: 90%;
            }
            .change-password-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
<div class="change-password-card animate-fade-in">
    
    <!-- BRAND HEADING -->
    <h1 class="change-password-title">UNIQ<span style="color: var(--primary-blue);">.</span></h1>
    <p class="text-white-50 small mb-4 pb-2">Đổi mật khẩu để bảo vệ tài khoản</p>

    <!-- PHP Alerts -->
    <?= $thongbao ?>

    <form method="POST">
        
        <!-- Current Password -->
        <div class="input-group-custom text-start">
            <input type="password" class="form-control form-control-pass" id="matkhaucu" name="matkhaucu" placeholder="Nhập mật khẩu cũ..." required>
            <i class="fas fa-lock-open"></i>
        </div>

        <!-- New Password -->
        <div class="input-group-custom text-start">
            <input type="password" class="form-control form-control-pass" id="matkhaumoi" name="matkhaumoi" placeholder="Nhập mật khẩu mới..." required>
            <i class="fas fa-key"></i>
        </div>

        <!-- Confirm Password -->
        <div class="input-group-custom text-start mb-4">
            <input type="password" class="form-control form-control-pass" id="xacnhan" name="xacnhan" placeholder="Xác nhận mật khẩu mới..." required>
            <i class="fas fa-shield-halved"></i>
        </div>

        <!-- Action capsules -->
        <div class="d-flex justify-content-between gap-3 mt-4">
            <a href="index.php" class="btn btn-cancel flex-grow-1"><i class="fa-solid fa-arrow-left me-2"></i> Quay lại</a>
            <button type="submit" class="btn btn-update flex-grow-1"><i class="fa-solid fa-circle-check me-2"></i> Cập nhật</button>
        </div>

    </form>
</div>

<script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

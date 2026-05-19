<?php
session_start();
include "./connect_DB/connect_db.php";
$conn = connectData();
$mess = "";
$step = 1;

if (isset($_GET['cancel'])) {
    unset($_SESSION['reset_idtk']);
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['reset_idtk'])) {
    $step = 2;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $sdt = trim($_POST['sdt']);

        if (empty($username) || empty($email) || empty($sdt)) {
            $mess = "Vui lòng điền đầy đủ thông tin!";
        } else {
            $stmt = $conn->prepare("
                SELECT tk.idtk 
                FROM taikhoan tk
                JOIN users u ON tk.idtk = u.idtk
                WHERE tk.username = ? AND u.email = ? AND u.sdt = ?
            ");
            $stmt->bind_param("sss", $username, $email, $sdt);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $_SESSION['reset_idtk'] = $row['idtk'];
                $step = 2;
                $mess = "";
            } else {
                $mess = "Thông tin xác thực tài khoản không chính xác!";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['reset'])) {
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (empty($new_pass) || empty($confirm_pass)) {
            $mess = "Vui lòng nhập đầy đủ thông tin mật khẩu!";
        } elseif ($new_pass !== $confirm_pass) {
            $mess = "Mật khẩu xác nhận không khớp!";
        } else {
            $idtk = $_SESSION['reset_idtk'];
            $stmt = $conn->prepare("UPDATE taikhoan SET password = ? WHERE idtk = ?");
            $stmt->bind_param("si", $new_pass, $idtk);
            if ($stmt->execute()) {
                unset($_SESSION['reset_idtk']);
                echo "<script>alert('Đặt lại mật khẩu thành công! Vui lòng đăng nhập lại.'); window.location.href='login.php';</script>";
                exit();
            } else {
                $mess = "Đã xảy ra lỗi hệ thống, vui lòng thử lại!";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quên mật khẩu | UNIQ</title>
    
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

        .forgot-password-card {
            background: var(--glass-card);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 420px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .forgot-password-title {
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

        .form-control-forgot {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 12px 16px 12px 46px;
            color: #ffffff !important;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-forgot::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .form-control-forgot:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .form-control-forgot:focus + i {
            color: var(--primary-blue);
        }

        .btn-action {
            background: #ffffff;
            color: #0f172a;
            font-weight: 700;
            border-radius: 50px;
            border: none;
            padding: 12px;
            font-size: 1rem;
            letter-spacing: 0.02em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1);
        }

        .btn-action:hover {
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
            padding: 11px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: #ffffff;
            color: #ffffff;
        }

        .forgot-alert {
            background: rgba(239, 68, 68, 0.12) !important;
            border: 1px solid rgba(239, 68, 68, 0.25) !important;
            color: #fca5a5 !important;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 500;
        }

        @media (max-width: 480px) {
            .forgot-password-card {
                padding: 30px 20px;
                max-width: 90%;
            }
            .forgot-password-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<div class="forgot-password-card animate-fade-in">
    
    <!-- BRAND HEADING -->
    <h1 class="forgot-password-title">UNIQ<span style="color: var(--primary-blue);">.</span></h1>

    <?php if ($step == 1): ?>
        <p class="text-white-50 small mb-4 pb-2">Nhập thông tin xác thực để lấy lại quyền truy cập</p>
        
        <form method="POST">
            <!-- Username Input -->
            <div class="input-group-custom">
                <input type="text" name="username" class="form-control form-control-forgot" placeholder="Nhập tên tài khoản..." required>
                <i class="fas fa-user"></i>
            </div>
            
            <!-- Email Input -->
            <div class="input-group-custom">
                <input type="email" name="email" class="form-control form-control-forgot" placeholder="Nhập Email đăng ký..." required>
                <i class="fas fa-envelope"></i>
            </div>
            
            <!-- Phone Input -->
            <div class="input-group-custom mb-4">
                <input type="text" name="sdt" class="form-control form-control-forgot" placeholder="Nhập Số điện thoại..." required>
                <i class="fas fa-phone"></i>
            </div>
            
            <!-- Actions -->
            <button type="submit" name="verify" class="btn btn-action w-100 mb-3"><i class="fa-solid fa-shield-halved me-2"></i> Xác thực tài khoản</button>
            <a href="?cancel=1" class="btn btn-cancel w-100 d-block"><i class="fa-solid fa-arrow-left me-2"></i> Quay lại đăng nhập</a>
        </form>

    <?php else: ?>
        <p class="text-white-50 small mb-4 pb-2">Xác thực thành công! Nhập mật khẩu mới bên dưới</p>
        
        <form method="POST">
            <!-- New Password -->
            <div class="input-group-custom">
                <input type="password" name="new_password" class="form-control form-control-forgot" placeholder="Nhập mật khẩu mới..." required>
                <i class="fas fa-lock"></i>
            </div>
            
            <!-- Confirm Password -->
            <div class="input-group-custom mb-4">
                <input type="password" name="confirm_password" class="form-control form-control-forgot" placeholder="Xác nhận mật khẩu mới..." required>
                <i class="fas fa-shield-halved"></i>
            </div>
            
            <!-- Actions -->
            <button type="submit" name="reset" class="btn btn-action w-100 mb-3"><i class="fa-solid fa-circle-check me-2"></i> Đặt lại mật khẩu</button>
            <a href="?cancel=1" class="btn btn-cancel w-100 d-block"><i class="fa-solid fa-xmark me-2"></i> Hủy bỏ</a>
        </form>
    <?php endif; ?>

    <!-- Alert error notifications -->
    <?php if (!empty($mess)): ?>
        <div class="alert forgot-alert mt-4 py-2.5 px-3 border-0 text-center animate-shake" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($mess) ?>
        </div>
    <?php endif; ?>
    
</div>

<script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

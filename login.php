<?php
session_start();
include "./connect_DB/connect_db.php";

$conn = connectData();
$mess = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $mess = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Lấy tài khoản theo username
        $stmt = $conn->prepare("
            SELECT * FROM taikhoan 
            WHERE username = ?
        ");

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Kiểm tra mật khẩu
            if ($password == $user['password']) {
                if ($user['trangthai'] == 0) {
                    $mess = "Tài khoản của bạn đã bị khóa! Vui lòng liên hệ quản trị viên để được hỗ trợ.";
                } else {
                    // Đăng nhập thành công
                    $_SESSION['idtk'] = $user['idtk'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['roleId'] = $user['roleId'];
                    if ($_SESSION['roleId'] != 1) {
                        header("Location: index.php");
                        exit();
                    }
                    header("Location: admin.php");
                    exit();
                }
            } else {
                $mess = "Mật khẩu không chính xác!";
            }
        } else {
            $mess = "Tài khoản không tồn tại trên hệ thống!";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập | UNIQ</title>

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

        .login-card {
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

        .login-title {
            font-weight: 800;
            font-size: 2.2rem;
            letter-spacing: 0.1em;
            color: #ffffff;
            margin-bottom: 5px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
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

        .form-control-login {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 12px 16px 12px 46px;
            color: #ffffff !important;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-login::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .form-control-login:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .form-control-login:focus + i {
            color: var(--primary-blue);
        }

        .btn-login {
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

        .btn-login:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.2);
            color: #0f172a;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-links {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .login-links a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .login-links a:hover {
            color: var(--primary-blue);
            text-decoration: underline;
        }

        .login-alert {
            background: rgba(239, 68, 68, 0.12) !important;
            border: 1px solid rgba(239, 68, 68, 0.25) !important;
            color: #fca5a5 !important;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 500;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
                max-width: 90%;
            }
            .login-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<div class="login-card shadow-2xl animate-fade-in">
    
    <!-- BRAND HEADING -->
    <h1 class="login-title">UNIQ<span style="color: var(--primary-blue);">.</span></h1>
    <p class="text-white-50 small mb-4 pb-2">Đăng nhập để trải nghiệm thời trang cao cấp</p>

    <form method="POST">
        <!-- Username input -->
        <div class="input-group-custom">
            <input type="text" name="username" class="form-control form-control-login" placeholder="Nhập tên đăng nhập..." required>
            <i class="fa-solid fa-user"></i>
        </div>

        <!-- Password input -->
        <div class="input-group-custom">
            <input type="password" name="password" class="form-control form-control-login" placeholder="Nhập mật khẩu..." required>
            <i class="fa-solid fa-lock"></i>
        </div>

        <!-- Forgot password -->
        <div class="d-flex justify-content-end mb-4 login-links">
            <a href="./quenmatkhau.php">Quên mật khẩu?</a>
        </div>

        <!-- Submit btn -->
        <button type="submit" class="btn btn-login w-100 mb-3">Đăng nhập</button>
    </form>

    <!-- Sign up redirect -->
    <div class="login-links mt-3">
        Không có tài khoản? <a href="./signup.php" class="ms-1">Đăng ký ngay</a>
    </div>

    <!-- Error message alert -->
    <?php if (!empty($mess)): ?>
        <div class="alert login-alert mt-4 py-2.5 px-3 border-0 text-center animate-shake" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($mess) ?>
        </div>
    <?php endif; ?>
    
</div>

<script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>

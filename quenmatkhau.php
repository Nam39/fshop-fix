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
                $mess = "Tên đăng nhập, Email hoặc Số điện thoại không chính xác!";
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
    <title>Quên mật khẩu</title>
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

        .glassmorphism {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
            color: white;
            text-align: center;
        }

        .glassmorphism input {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
        }

        .glassmorphism input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .glassmorphism input:focus {
            background: rgba(255, 255, 255, 0.6);
            outline: none;
            box-shadow: none;
            color: white;
        }

        .btn-custom {
            background: rgba(255, 255, 255, 0.3);
            border: none;
            color: white;
            font-weight: bold;
            padding: 10px;
        }

        .btn-custom:hover {
            background: rgba(255, 255, 255, 0.5);
            color: white;
        }

        @media (max-width: 768px) {
            .glassmorphism {
                padding: 20px;
                max-width: 90%;
            }
        }
    </style>
</head>
<body>

<div class="glassmorphism">
    <h2 class="fw-bold mb-2">Quên mật khẩu</h2>
    
    <?php if ($step == 1): ?>
        <p class="text-white-50 mb-4 small">Nhập thông tin đăng ký để khôi phục mật khẩu</p>
        <form method="POST">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-white border-end-0">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="username" class="form-control border-start-0" placeholder="Tên đăng nhập" required>
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-white border-end-0">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email" class="form-control border-start-0" placeholder="Email đăng ký" required>
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-white border-end-0">
                        <i class="fas fa-phone"></i>
                    </span>
                    <input type="text" name="sdt" class="form-control border-start-0" placeholder="Số điện thoại đăng ký" required>
                </div>
            </div>
            
            <button type="submit" name="verify" class="btn btn-custom w-100 mt-3 rounded-pill">Xác thực tài khoản</button>
            <a href="?cancel=1" class="btn btn-outline-light w-100 mt-2 rounded-pill small">Quay lại đăng nhập</a>
        </form>
    <?php else: ?>
        <p class="text-white-50 mb-4 small">Xác thực thành công! Hãy nhập mật khẩu mới của bạn.</p>
        <form method="POST">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-white border-end-0">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="new_password" class="form-control border-start-0" placeholder="Mật khẩu mới" required>
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-white border-end-0">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="confirm_password" class="form-control border-start-0" placeholder="Xác nhận mật khẩu mới" required>
                </div>
            </div>
            
            <button type="submit" name="reset" class="btn btn-custom w-100 mt-3 rounded-pill">Đặt lại mật khẩu</button>
            <a href="?cancel=1" class="btn btn-outline-light w-100 mt-2 rounded-pill">Hủy bỏ</a>
        </form>
    <?php endif; ?>

    <?php if (!empty($mess)): ?>
        <div class="alert alert-danger mt-3 py-2 border-0 text-center" role="alert" style="background: rgba(220, 53, 69, 0.25); color: #ffccd0; backdrop-filter: blur(5px); border-radius: 10px;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($mess) ?>
        </div>
    <?php endif; ?>
</div>

<script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include "./connect_DB/connect_db.php";

$conn = connectData();
$mess = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    date_default_timezone_set("Asia/Ho_Chi_Minh");
    $thoigiantao = date("Y-m-d H:i:s");

    // Lấy dữ liệu từ form
    $ten = trim($_POST['Ten_user']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $sdt = trim($_POST['sdt']);
    $diachi = trim($_POST['diachi']);
    $ngaysinh = $_POST['ngaysinh'];

    // Xóa khoảng trắng username
    $ten = str_replace(" ", "", $ten);

    // Giá trị mặc định
    $roleId = 2;
    $anh_user = "user.jpg";
    $trangthai = 1;

    // Kiểm tra dữ liệu
    if (empty($ten) || empty($password) || empty($email)) {
        $mess = "<div class='alert alert-danger py-2.5 px-3 border-0 text-center rounded-3 fw-semibold' role='alert' style='background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25);'>
                    <i class='fa-solid fa-circle-exclamation me-2'></i> Vui lòng điền đầy đủ các thông tin bắt buộc!
                 </div>";
    } else {
        // Kiểm tra trùng Email
        $stmt_email = $conn->prepare("SELECT iduser FROM users WHERE email = ?");
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        $res_email = $stmt_email->get_result();

        // Kiểm tra trùng Username
        $stmt_user = $conn->prepare("SELECT idtk FROM taikhoan WHERE username = ?");
        $stmt_user->bind_param("s", $ten);
        $stmt_user->execute();
        $res_user = $stmt_user->get_result();

        if ($res_email->num_rows > 0) {
            $mess = "<div class='alert alert-danger py-2.5 px-3 border-0 text-center rounded-3 fw-semibold' role='alert' style='background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25);'>
                        <i class='fa-solid fa-circle-exclamation me-2'></i> Địa chỉ Email này đã được đăng ký tài khoản khác!
                     </div>";
            $stmt_email->close();
            $stmt_user->close();
        } elseif ($res_user->num_rows > 0) {
            $mess = "<div class='alert alert-danger py-2.5 px-3 border-0 text-center rounded-3 fw-semibold' role='alert' style='background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25);'>
                        <i class='fa-solid fa-circle-exclamation me-2'></i> Tên tài khoản đã tồn tại! Vui lòng chọn tên khác.
                     </div>";
            $stmt_email->close();
            $stmt_user->close();
        } else {
            $stmt_email->close();
            $stmt_user->close();

            // Thêm tài khoản
            $stmt1 = $conn->prepare("
                INSERT INTO taikhoan 
                (username, password, roleId, trangthai, thoigiantao) 
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt1->bind_param(
                "ssiis",
                $ten,
                $password,
                $roleId,
                $trangthai,
                $thoigiantao
            );

            if ($stmt1->execute()) {
                $idtk = $conn->insert_id;

                // Thêm users
                $stmt2 = $conn->prepare("
                    INSERT INTO users
                    (idtk, Ten_user, Anh_user, sdt, email, diachi, ngaysinh)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt2->bind_param(
                    "issssss",
                    $idtk,
                    $ten,
                    $anh_user,
                    $sdt,
                    $email,
                    $diachi,
                    $ngaysinh
                );

                if ($stmt2->execute()) {
                    echo "<script>
                            alert('Đăng ký tài khoản thành công!');
                            window.location.href='login.php';
                          </script>";
                    exit();
                } else {
                    $mess = "<div class='alert alert-danger py-2.5 px-3 border-0 text-center rounded-3 fw-semibold' role='alert' style='background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25);'>
                                <i class='fa-solid fa-circle-exclamation me-2'></i> Lỗi tạo thông tin người dùng chi tiết!
                             </div>";
                }
                $stmt2->close();
            } else {
                $mess = "<div class='alert alert-danger py-2.5 px-3 border-0 text-center rounded-3 fw-semibold' role='alert' style='background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25);'>
                            <i class='fa-solid fa-circle-exclamation me-2'></i> Gặp sự cố trong quá trình khởi tạo tài khoản!
                         </div>";
            }
            $stmt1->close();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký tài khoản | UNIQ</title>

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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(9, 13, 22, 0.72), rgba(9, 13, 22, 0.85)), url('./assets/img/bg-newsletter.jpg') no-repeat center center/cover;
            color: #f8fafc;
            padding: 40px 10px;
        }

        .signup-card {
            background: var(--glass-card);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 520px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .signup-title {
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

        .form-control-signup {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 12px 16px 12px 46px;
            color: #ffffff !important;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-signup::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .form-control-signup:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .form-control-signup:focus + i {
            color: var(--primary-blue);
        }

        .btn-signup {
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

        .btn-signup:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.2);
            color: #0f172a;
        }

        .btn-signup:active {
            transform: translateY(0);
        }

        .signup-links {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .signup-links a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .signup-links a:hover {
            color: var(--primary-blue);
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .signup-card {
                padding: 30px 20px;
            }
            .signup-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>

<div class="signup-card shadow-2xl animate-fade-in">

    <!-- BRAND HEADING -->
    <h1 class="signup-title">UNIQ<span style="color: var(--primary-blue);">.</span></h1>
    <p class="text-white-50 small mb-4 pb-2">Tạo tài khoản mới để tiếp tục mua sắm</p>

    <!-- PHP Server Alerts -->
    <?php if (!empty($mess)): ?>
        <div class="mb-4">
            <?= $mess ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">

        <!-- Email input -->
        <div class="input-group-custom">
            <input type="email" name="email" class="form-control form-control-signup" placeholder="Nhập địa chỉ Email (*)..." required>
            <i class="fa-solid fa-envelope"></i>
        </div>

        <!-- Ten_user input -->
        <div class="input-group-custom">
            <input type="text" name="Ten_user" class="form-control form-control-signup" placeholder="Tên tài khoản (viết liền, *)..." required>
            <i class="fa-solid fa-user"></i>
        </div>

        <!-- Password input -->
        <div class="input-group-custom">
            <input type="password" name="password" class="form-control form-control-signup" placeholder="Nhập mật khẩu tài khoản (*)..." required>
            <i class="fa-solid fa-lock"></i>
        </div>

        <!-- sdt input -->
        <div class="input-group-custom">
            <input type="tel" name="sdt" class="form-control form-control-signup" placeholder="Nhập số điện thoại liên hệ...">
            <i class="fa-solid fa-phone"></i>
        </div>

        <!-- diachi input -->
        <div class="input-group-custom">
            <input type="text" name="diachi" class="form-control form-control-signup" placeholder="Nhập địa chỉ giao hàng...">
            <i class="fa-solid fa-map-location-dot"></i>
        </div>

        <!-- ngaysinh input -->
        <div class="input-group-custom">
            <input type="text" name="ngaysinh" class="form-control form-control-signup" placeholder="Nhập ngày sinh (ví dụ: DD/MM/YYYY)...">
            <i class="fa-solid fa-calendar-days"></i>
        </div>

        <!-- Submit btn -->
        <button type="submit" class="btn btn-signup w-100 mt-2">Đăng ký tài khoản</button>

    </form>

    <!-- Sign in redirect -->
    <div class="signup-links mt-4">
        Đã có tài khoản? <a href="./login.php" class="ms-1">Đăng nhập ngay</a>
    </div>

</div>

<script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>

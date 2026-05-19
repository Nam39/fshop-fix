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
        $mess = "<div class='alert alert-danger py-2 border-0 text-center' role='alert' style='background: rgba(220, 53, 69, 0.25); color: #ffccd0; backdrop-filter: blur(5px); border-radius: 10px;'>
                    <i class='fa-solid fa-triangle-exclamation me-2'></i> Vui lòng không để trống các trường bắt buộc!
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
            $mess = "<div class='alert alert-danger py-2 border-0 text-center' role='alert' style='background: rgba(220, 53, 69, 0.25); color: #ffccd0; backdrop-filter: blur(5px); border-radius: 10px;'>
                        <i class='fa-solid fa-triangle-exclamation me-2'></i> Email này đã được đăng ký cho tài khoản khác!
                     </div>";
            $stmt_email->close();
            $stmt_user->close();
        } elseif ($res_user->num_rows > 0) {
            $mess = "<div class='alert alert-danger py-2 border-0 text-center' role='alert' style='background: rgba(220, 53, 69, 0.25); color: #ffccd0; backdrop-filter: blur(5px); border-radius: 10px;'>
                        <i class='fa-solid fa-triangle-exclamation me-2'></i> Tên người dùng đã tồn tại! Vui lòng chọn tên khác.
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
                            alert('Đăng ký thành công!');
                            window.location.href='login.php';
                          </script>";
                    exit();
                } else {
                    $mess = "<div class='alert alert-danger py-2 border-0 text-center' role='alert' style='background: rgba(220, 53, 69, 0.25); color: #ffccd0; backdrop-filter: blur(5px); border-radius: 10px;'>
                                <i class='fa-solid fa-triangle-exclamation me-2'></i> Lỗi thêm thông tin người dùng!
                             </div>";
                }
                $stmt2->close();
            } else {
                $mess = "<div class='alert alert-danger py-2 border-0 text-center' role='alert' style='background: rgba(220, 53, 69, 0.25); color: #ffccd0; backdrop-filter: blur(5px); border-radius: 10px;'>
                            <i class='fa-solid fa-triangle-exclamation me-2'></i> Lỗi tạo tài khoản!
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
    <title>Đăng ký tài khoản</title>
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            max-width: 500px;
            color: white;
            text-align: center;
        }

        .glassmorphism input,
        .glassmorphism select {
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
        }

        .btn-custom {
            background: rgba(255, 255, 255, 0.3);
            border: none;
            color: white;
        }

        .btn-custom:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        @media (max-width: 768px) {
            .glassmorphism {
                padding: 20px;
                max-width: 90%;
            }
        }

        @media (max-width: 480px) {
            .glassmorphism {
                padding: 15px;
            }

            .glassmorphism h2 {
                font-size: 22px;
            }

            .btn-custom {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>

<body>

<div class="glassmorphism">

<div class="text-center mb-4">
    <i class="fas fa-user-circle fa-4x mb-3"></i>
    <h2 class="fw-bold">Đăng ký tài khoản</h2>
    <p class="text-light">Tạo tài khoản mới để tiếp tục</p>
</div>

<?= $mess ?>

<form action="" method="POST">

    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-transparent text-white border-end-0">
                <i class="fas fa-envelope"></i>
            </span>
            <input 
                type="email" 
                name="email" 
                class="form-control border-start-0" 
                placeholder="Nhập email"
                required
            >
        </div>
    </div>

    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-transparent text-white border-end-0">
                <i class="fas fa-user"></i>
            </span>
            <input 
                type="text" 
                name="Ten_user" 
                class="form-control border-start-0" 
                placeholder="Tên người dùng"
                required
            >
        </div>
    </div>

    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-transparent text-white border-end-0">
                <i class="fas fa-lock"></i>
            </span>
            <input 
                type="password" 
                name="password" 
                class="form-control border-start-0" 
                placeholder="Mật khẩu"
                required
            >
        </div>
    </div>

    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-transparent text-white border-end-0">
                <i class="fas fa-phone"></i>
            </span>
            <input 
                type="text" 
                name="sdt" 
                class="form-control border-start-0" 
                placeholder="Số điện thoại"
            >
        </div>
    </div>

    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-transparent text-white border-end-0">
                <i class="fas fa-map-marker-alt"></i>
            </span>
            <input 
                type="text" 
                name="diachi" 
                class="form-control border-start-0" 
                placeholder="Địa chỉ"
            >
        </div>
    </div>

    <div class="mb-4">
        <div class="input-group">
            <span class="input-group-text bg-transparent text-white border-end-0">
                <i class="fas fa-calendar"></i>
            </span>
            <input 
                type="date" 
                name="ngaysinh" 
                class="form-control border-start-0"
            >
        </div>
    </div>

    <button type="submit" class="btn btn-custom w-100 py-2 fw-bold">
        <i class="fas fa-user-plus me-2"></i>
        Đăng ký
    </button>

</form>

<div class="text-center mt-4">
    <p class="mb-0">
        Đã có tài khoản?
        <a href="./login.php" class="text-warning fw-bold text-decoration-none">
            Đăng nhập
        </a>
    </p>
</div>

</div>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    


</body>

</html>

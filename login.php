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
            if ($password==$user['password']) {

                // Đăng nhập thành công
                $_SESSION['idtk'] = $user['idtk'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['roleId'] = $user['roleId'];
                if($_SESSION['roleId'] != 1){
                    header("Location: index.php");
                exit();
                }
                header("Location: admin.php");
                exit();

            } else {

                $mess = "Sai mật khẩu!";
            }

        } else {

            $mess = "Tài khoản không tồn tại!";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
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
            max-width: 400px;
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
    <h2 class="fw-bold mb-4">Đăng nhập</h2>

    <form method="POST">
        <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Nhập tên" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
        </div>
        <div class="d-flex justify-content-end">
            <a href="#" class="text-white">Quên mật khẩu?</a>
        </div>
        <button type="submit" class="btn btn-custom w-100 mt-3">Đăng nhập</button>
    </form>
    <p class="mt-3">Không có tài khoản? <a href="./signup.php" class="text-white fw-bold">Đăng ký</a></p>

    <?= $mess ?>
</div>

<script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>

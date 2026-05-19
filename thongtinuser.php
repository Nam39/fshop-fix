<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "./connect_DB/connect_db.php";

$success = "";
$error = "";

$conn = connectData();

if (!isset($_SESSION['idtk'])) {
    header("Location: login.php");
    exit;
}

$idtk = $_SESSION['idtk'];
$sql = "SELECT * FROM users WHERE idtk = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idtk);
$stmt->execute();
$userResult = $stmt->get_result();
if ($userResult->num_rows > 0) {
    $user = $userResult->fetch_assoc();
} else {
    die("Không tìm thấy thông tin người dùng.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_user = trim($_POST['ten_user']);
    $anh_user = $user['Anh_user'];
    $sdt = $_POST['sdt'];
    $email = $_POST['email'];
    $diachi = $_POST['diachi'];
    $ngaysinh = $_POST['ngaysinh'];

    if (!empty($_FILES['anh_user']['name'])) {
        $file_name = basename($_FILES["anh_user"]["name"]);
        $target_dir = "assets/img/";
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["anh_user"]["tmp_name"], $target_file)) {
            $anh_user = $file_name;
        } else {
            $error = "Tải ảnh thất bại.";
        }
    }

    if ($error === "") {
        $updateSql = "UPDATE users SET Ten_user = ?, Anh_user = ?, sdt = ?, email = ?, diachi = ?, ngaysinh = ? WHERE idtk = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssisssi", $ten_user, $anh_user, $sdt, $email, $diachi, $ngaysinh, $idtk);

        if ($updateStmt->execute()) {
            $_SESSION['Ten_user'] = $ten_user;
            $_SESSION['Anh_user'] = $anh_user;
            $_SESSION['sdt'] = $sdt;
            $_SESSION['email'] = $email;
            $_SESSION['diachi'] = $diachi;
            $_SESSION['ngaysinh'] = $ngaysinh;

            // Load lại dữ liệu user sau khi cập nhật
            $stmt = $conn->prepare("SELECT * FROM users WHERE idtk = ?");
            $stmt->bind_param("i", $idtk);
            $stmt->execute();
            $userResult = $stmt->get_result();
            $user = $userResult->fetch_assoc();

            $success = "Cập nhật thông tin thành công!";
        }
        else {
            $error = "Cập nhật thông tin thất bại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin tài khoản | UNIQ</title>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/index.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --dark-color: #0f172a;
            --card-radius: 24px;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #334155;
        }

        .profile-container {
            margin-top: 130px;
            margin-bottom: 80px;
        }

        .profile-card {
            background-color: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--card-radius);
            padding: 40px;
        }

        .avatar-img-info {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 4px solid #ffffff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .avatar-img-info:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 30px rgba(13, 110, 253, 0.18);
        }

        .form-control-custom {
            border-radius: 12px;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08);
        }

        .form-label-custom {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label-custom i {
            color: var(--primary-color);
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .profile-container {
                margin-top: 100px;
                margin-bottom: 40px;
            }
            .profile-card {
                padding: 24px 16px;
            }
        }
    </style>
</head>
<body>

    <!-- HEADER LAYOUT -->
    <?php include "./assets/layout/header/index.php"; ?>

    <!-- PROFILE BODY -->
    <div class="container profile-container">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-10">
                <div class="profile-card shadow-sm">
                    <?php include "./assets/layout/info/index.php"; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER LAYOUT -->
    <?php include "./assets/layout/footer/index.php"; ?>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

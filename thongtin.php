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

            $success = "Cập nhật thành công!";
        }
        else {
            $error = "Cập nhật thất bại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thay đổi thông tin</title>
    
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .avatar-img-info {
            width: 300px;
            height: 300px;
            object-fit: cover;
            border: 1px solid rgba(0, 0, 0, 0.6);
        }

        .input-cus {
            background: #ccc;
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php include "./assets/layout/header/index.php"; ?>

<?php include "./assets/layout/info/index.php"; ?>

<?php include "./assets/layout/footer/index.php"; ?>
<script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
include_once 'connect_DB/connect_db.php';
if (!isset($_SESSION['idtk'])) {
    echo "Bạn chưa đăng nhập.";
    exit;
}
$conn = connectData();


$idtk = $_SESSION['idtk'];
$sql = "SELECT * FROM users WHERE idtk = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idtk);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<?php include "./assets/layout/info/index.php"; ?>

<?php
// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_user = $_POST['ten_user'];

    // Xử lý ảnh nếu có
    $anh_user = $user['Anh_user'];
    if (isset($_FILES['anh_user']) && $_FILES['anh_user']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['anh_user']['tmp_name'];
        $filename = basename($_FILES['anh_user']['name']);
        move_uploaded_file($tmp_name, "./assets/img/" . $filename);
        $anh_user = $filename;
    }

    $update_sql = "UPDATE users SET Ten_user = ?, Anh_user = ? WHERE idtk = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $ten_user, $anh_user, $idtk);
    $update_stmt->execute();

    $_SESSION['Ten_user'] = $ten_user;
    $_SESSION['Anh_user'] = $anh_user;

    echo "<script>alert('Cập nhật thành công!'); window.location.href='?page=thongtin';</script>";
}
?>
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

$id = $_SESSION['idtk'];
$sql = "SELECT * FROM users WHERE iduser = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();


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
        $updateSql = "UPDATE users SET Ten_user = ?, Ten_user = ?, Anh_user = ?, sdt = ?, email = ?, diachi = ?, ngaysinh = ? WHERE iduser = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssisssi", $username, $ten_user, $anh_user, $sdt, $email, $diachi, $ngaysinh, $id);
        if ($updateStmt->execute()) {
            $success = "Cập nhật thành công!";
            $_SESSION['username'] = $username;
            $_SESSION['Ten_user'] = $ten_user;
            $_SESSION['Anh_user'] = $anh_user;
            $_SESSION['sdt'] = $sdt;
            $_SESSION['email'] = $email;
            $_SESSION['diachi'] = $diachi;
            $_SESSION['ngaysinh'] = $ngaysinh;
            
            $user['username'] = $username;
            $user['Ten_user'] = $ten_user;
            $user['Anh_user'] = $anh_user;
            $user['sdt'] = $sdt;
            $user['email'] = $email;
            $user['diachi'] = $diachi;
            $user['ngaysinh'] = $ngaysinh;
        } else {
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

    <div class="container py-5">
        <h2 class="text-center mb-4 mt-5 pt-5">Thông tin tài khoản</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="row g-4">
            <div class="col-md-4 text-center">
                <img src="./assets/img/<?= htmlspecialchars($user['Anh_user']) ?>" alt="Avatar" class="rounded-circle avatar-img-info mb-3">
                <div>
                    <label class="form-label">Thay ảnh đại diện:</label>
                    <input type="file" name="anh_user" class="form-control input-cus">
                </div>
            </div>

            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label">Họ tên</label>
                    <input type="text" name="ten_user" value="<?= htmlspecialchars($user['Ten_user']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="sdt" value="<?= htmlspecialchars($user['sdt']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">email</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="diachi" value="<?= htmlspecialchars($user['diachi']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ngày sinh</label>
                    <input type="text" name="ngaysinh" value="<?= htmlspecialchars($user['ngaysinh']) ?>" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Lưu thay đổi</button>
                <a  href="<?= ($_SESSION['roleId'] == 1) ? './admin.php' : './index.php' ?>" 
   class="btn btn-primary">Quay lại</a>
            </div>
        </form>
    </div>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM users WHERE iduser = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if (!$row) {
        echo "Người dùng không tồn tại!";
        exit();
    }
} else {
    echo "ID không hợp lệ!";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_user = trim($_POST['Ten_user']);
    $sdt = trim($_POST['sdt']);
    $email = trim($_POST['email']);
    $diachi = trim($_POST['diachi']);
    $ngaysinh = trim($_POST['ngaysinh']);

    $anh_cu = $row['Anh_user'];
    $anh_moi = $_FILES['Anh_user']['name'];
    $anh = $anh_cu;

    if (!empty($anh_moi)) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($anh_moi, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('avatar_') . '.' . $ext;
            $target_dir = "../assets/img/";
            if (move_uploaded_file($_FILES['Anh_user']['tmp_name'], $target_dir . $new_filename)) {
                $anh = $new_filename;
            }
        }
    }

    if (!empty($ten_user) && !empty($email)) {
        // Check email duplication
        $stmt_check = $conn->prepare("SELECT iduser FROM users WHERE email = ? AND iduser != ?");
        $stmt_check->bind_param("si", $email, $id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $error = "Email đã tồn tại!";
        } else {
            $sql_update = "UPDATE users SET Ten_user = ?, Anh_user = ?, sdt = ?, email = ?, diachi = ?, ngaysinh = ? WHERE iduser = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssssssi", $ten_user, $anh, $sdt, $email, $diachi, $ngaysinh, $id);
            if ($stmt_update->execute()) {
                header("Location: ../admin.php?page=qlnd");
                exit();
            } else {
                $error = "Lỗi cập nhật: " . $conn->error;
            }
        }
    } else {
        $error = "Vui lòng điền đầy đủ Tên và Email!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa người dùng</title>
    <link href=".././assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .avatar-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 2px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5" style="max-width: 700px;">
        <div class="card bg-body-secondary shadow">
            <div class="card-header bg-dark text-white text-center">
                <h2>Sửa thông tin người dùng</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <img src="../assets/img/<?= htmlspecialchars($row['Anh_user']) ?>" alt="Avatar" class="rounded-circle avatar-preview mb-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Thay ảnh đại diện</label>
                                <input type="file" name="Anh_user" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID người dùng</label>
                                <input type="text" class="form-control" value="<?= $row['iduser'] ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID tài khoản liên kết</label>
                                <input type="text" class="form-control" value="<?= $row['idtk'] ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Họ và tên</label>
                                <input type="text" name="Ten_user" class="form-control" value="<?= htmlspecialchars($row['Ten_user']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($row['sdt']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Địa chỉ</label>
                                <input type="text" name="diachi" class="form-control" value="<?= htmlspecialchars($row['diachi']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày sinh</label>
                                <input type="text" name="ngaysinh" class="form-control" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars($row['ngaysinh']) ?>">
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="submit" class="btn btn-success px-4">Lưu thay đổi</button>
                                <a href=".././admin.php?page=qlnd" class="btn btn-primary px-4">Quay lại</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

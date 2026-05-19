<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM taikhoan WHERE idtk = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if (!$row) {
        echo "Tài khoản không tồn tại!";
        exit();
    }
} else {
    echo "ID không hợp lệ!";
    exit();
}

$sql_role = "SELECT * FROM role";
$result_role = $conn->query($sql_role);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $roleId = (int)$_POST['roleId'];
    $trangthai = (int)$_POST['trangthai'];

    if (!empty($username) && !empty($password)) {
        // Check if username is taken by another account
        $stmt_check = $conn->prepare("SELECT idtk FROM taikhoan WHERE username = ? AND idtk != ?");
        $stmt_check->bind_param("si", $username, $id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $error = "Username đã tồn tại!";
        } else {
            $sql_update = "UPDATE taikhoan SET username = ?, password = ?, roleId = ?, trangthai = ? WHERE idtk = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssiii", $username, $password, $roleId, $trangthai, $id);
            if ($stmt_update->execute()) {
                header("Location: ../admin.php?page=qltk");
                exit();
            } else {
                $error = "Lỗi cập nhật: " . $conn->error;
            }
        }
    } else {
        $error = "Vui lòng điền đầy đủ thông tin!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa tài khoản</title>
    <link href=".././assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card bg-body-secondary">
            <div class="card-header bg-dark text-white text-center">
                <h2>Sửa tài khoản</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">ID tài khoản</label>
                        <input type="text" class="form-control" value="<?= $row['idtk'] ?>" disabled>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($row['username']) ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Mật khẩu</label>
                        <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($row['password']) ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Vai trò</label>
                        <select name="roleId" class="form-select" required>
                            <?php while ($role = $result_role->fetch_assoc()) { ?>
                                <option value="<?= $role['roleId'] ?>" <?= ($role['roleId'] == $row['roleId']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['Ten']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Trạng thái</label>
                        <select name="trangthai" class="form-select" required>
                            <option value="1" <?= ($row['trangthai'] == 1) ? 'selected' : '' ?>>✅ Hoạt động</option>
                            <option value="0" <?= ($row['trangthai'] == 0) ? 'selected' : '' ?>>❌ Bị khóa</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success px-4">Lưu thay đổi</button>
                        <a href=".././admin.php?page=qltk" class="btn btn-primary px-4">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

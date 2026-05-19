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
            $error = "Username đã tồn tại trên hệ thống!";
        } else {
            $sql_update = "UPDATE taikhoan SET username = ?, password = ?, roleId = ?, trangthai = ? WHERE idtk = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssiii", $username, $password, $roleId, $trangthai, $id);
            if ($stmt_update->execute()) {
                header("Location: ../admin.php?page=qltk");
                exit();
            } else {
                $error = "Gặp sự cố khi cập nhật cơ sở dữ liệu: " . $conn->error;
            }
        }
    } else {
        $error = "Vui lòng điền đầy đủ các thông tin bắt buộc!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa tài khoản | UNIQ</title>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href=".././assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=".././assets/fonts/css/all.min.css">
    
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
            padding: 60px 10px;
        }

        .edit-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--card-radius);
            padding: 40px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
            width: 100%;
            max-width: 580px;
            margin: 0 auto;
        }

        .edit-title {
            font-weight: 800;
            color: var(--dark-color);
            font-size: 1.6rem;
        }

        .form-label-custom {
            font-weight: 700;
            color: #334155;
            font-size: 0.95rem;
            margin-bottom: 6px;
        }

        .form-control-custom {
            border-radius: 12px;
            padding: 11px 16px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08);
        }

        .form-control-custom:disabled {
            background-color: #f1f5f9;
            color: #64748b;
            font-weight: 600;
        }

        .btn-save {
            background-color: var(--primary-color);
            color: #ffffff;
            font-weight: 700;
            border-radius: 50px;
            border: none;
            padding: 11px 24px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }

        .btn-save:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.25);
            color: #ffffff;
        }

        .btn-back {
            background: transparent;
            color: #64748b;
            font-weight: 600;
            border-radius: 50px;
            border: 1px solid #cbd5e1;
            padding: 11px 24px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: #f1f5f9;
            color: var(--dark-color);
            border-color: #94a3b8;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 12px 16px;
        }

        @media (max-width: 480px) {
            .edit-card {
                padding: 30px 20px;
            }
            .edit-title {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body>

    <div class="edit-card animate-fade-in">
        
        <!-- HEADER BLOCK -->
        <div class="mb-4">
            <h3 class="edit-title mb-1 d-flex align-items-center">
                <i class="fa-solid fa-user-pen text-primary me-2"></i>Sửa tài khoản
            </h3>
            <p class="text-secondary small mb-0">Cập nhật thông tin chi tiết và quyền truy cập của tài khoản</p>
        </div>

        <!-- SERVER ERRORS -->
        <?php if (isset($error)): ?>
            <div class="alert alert-error mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- FORM DETAILS -->
        <form method="POST">
            
            <div class="mb-3">
                <label class="form-label form-label-custom">ID tài khoản</label>
                <input type="text" class="form-control form-control-custom" value="#<?= $row['idtk'] ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-custom">Username</label>
                <input type="text" name="username" class="form-control form-control-custom" value="<?= htmlspecialchars($row['username']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-custom">Mật khẩu</label>
                <input type="text" name="password" class="form-control form-control-custom" value="<?= htmlspecialchars($row['password']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-custom">Vai trò</label>
                <select name="roleId" class="form-select form-control-custom" required>
                    <?php while ($role = $result_role->fetch_assoc()) { ?>
                        <option value="<?= $role['roleId'] ?>" <?= ($role['roleId'] == $row['roleId']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['Ten']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label form-label-custom">Trạng thái</label>
                <select name="trangthai" class="form-select form-control-custom" required>
                    <option value="1" <?= ($row['trangthai'] == 1) ? 'selected' : '' ?>>✅ Hoạt động</option>
                    <option value="0" <?= ($row['trangthai'] == 0) ? 'selected' : '' ?>>❌ Bị khóa</option>
                </select>
            </div>

            <!-- BUTTON ACTIONS -->
            <div class="d-flex justify-content-between gap-3 mt-4">
                <a href=".././admin.php?page=qltk" class="btn btn-back flex-grow-1 text-center"><i class="fa-solid fa-arrow-left me-2"></i> Quay lại</a>
                <button type="submit" class="btn btn-save flex-grow-1"><i class="fa-solid fa-circle-check me-2"></i> Lưu thay đổi</button>
            </div>

        </form>

    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

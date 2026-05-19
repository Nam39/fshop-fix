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
        } else {
            $error = "Định dạng ảnh không hợp lệ (Chỉ chấp nhận jpg, jpeg, png, gif, webp)!";
        }
    }

    if (!$error) {
        if (!empty($ten_user) && !empty($email)) {
            // Check email duplication
            $stmt_check = $conn->prepare("SELECT iduser FROM users WHERE email = ? AND iduser != ?");
            $stmt_check->bind_param("si", $email, $id);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $error = "Email đã được sử dụng bởi người dùng khác!";
            } else {
                $sql_update = "UPDATE users SET Ten_user = ?, Anh_user = ?, sdt = ?, email = ?, diachi = ?, ngaysinh = ? WHERE iduser = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssssssi", $ten_user, $anh, $sdt, $email, $diachi, $ngaysinh, $id);
                if ($stmt_update->execute()) {
                    header("Location: ../admin.php?page=qlnd");
                    exit();
                } else {
                    $error = "Gặp sự cố khi cập nhật cơ sở dữ liệu: " . $conn->error;
                }
            }
        } else {
            $error = "Vui lòng điền đầy đủ Họ tên và Địa chỉ Email!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thông tin người dùng | UNIQ</title>
    
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
            max-width: 720px;
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

        .avatar-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e2e8f0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }

        .avatar-preview:hover {
            transform: scale(1.03);
            border-color: var(--primary-color);
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

        @media (max-width: 576px) {
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
                <i class="fa-solid fa-user-gear text-primary me-2"></i>Sửa hồ sơ người dùng
            </h3>
            <p class="text-secondary small mb-0">Cập nhật thông tin chi tiết và ảnh đại diện của khách hàng</p>
        </div>

        <!-- SERVER VALIDATION ERRORS -->
        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert alert-error mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- FORM DETAILS -->
        <form method="POST" enctype="multipart/form-data">
            
            <div class="row">
                
                <!-- Avatar Column -->
                <div class="col-md-4 text-center mb-4 border-end border-slate-100 pe-md-4">
                    <div class="p-3 bg-light rounded-3 d-inline-block w-100">
                        <img src="../assets/img/<?= htmlspecialchars($row['Anh_user']) ?>" alt="Avatar Preview" class="avatar-preview mb-3">
                        <div class="text-start">
                            <label class="form-label form-label-custom d-block text-center mb-2">Thay ảnh đại diện</label>
                            <input type="file" name="Anh_user" class="form-control form-control-custom bg-white" accept="image/*">
                            <div class="form-text text-muted text-center mt-1 small">jpg, png, webp</div>
                        </div>
                    </div>
                </div>

                <!-- Input Fields Column -->
                <div class="col-md-8 ps-md-4">
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label form-label-custom">ID người dùng</label>
                            <input type="text" class="form-control form-control-custom" value="#<?= $row['iduser'] ?>" disabled>
                        </div>
                        <div class="col-6">
                            <label class="form-label form-label-custom">ID tài khoản liên kết</label>
                            <input type="text" class="form-control form-control-custom" value="#<?= $row['idtk'] ?>" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-custom">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="Ten_user" class="form-control form-control-custom" value="<?= htmlspecialchars($row['Ten_user']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-custom">Số điện thoại</label>
                        <input type="text" name="sdt" class="form-control form-control-custom" value="<?= htmlspecialchars($row['sdt']) ?>" placeholder="Ví dụ: 0987654321">
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-custom">Địa chỉ Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-custom" value="<?= htmlspecialchars($row['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-custom">Địa chỉ thường trú</label>
                        <input type="text" name="diachi" class="form-control form-control-custom" value="<?= htmlspecialchars($row['diachi']) ?>" placeholder="Số nhà, Tên đường, Quận, Thành phố...">
                    </div>

                    <div class="mb-4">
                        <label class="form-label form-label-custom">Ngày sinh</label>
                        <input type="text" name="ngaysinh" class="form-control form-control-custom" placeholder="Định dạng: YYYY-MM-DD" value="<?= htmlspecialchars($row['ngaysinh']) ?>">
                    </div>

                    <!-- BUTTON ACTIONS -->
                    <div class="d-flex justify-content-between gap-3 mt-4">
                        <a href=".././admin.php?page=qlnd" class="btn btn-back flex-grow-1 text-center"><i class="fa-solid fa-arrow-left me-2"></i> Quay lại</a>
                        <button type="submit" class="btn btn-save flex-grow-1"><i class="fa-solid fa-circle-check me-2"></i> Lưu thay đổi</button>
                    </div>

                </div>

            </div>

        </form>

    </div>

</body>
</html>

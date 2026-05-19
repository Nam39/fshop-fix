<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

$sql_danhmuc = "SELECT * FROM danhmucsanpham";
$result_danhmuc = $conn->query($sql_danhmuc);

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Ten = trim($_POST['Ten'] ?? '');
    $MoTa = trim($_POST['MoTa'] ?? '');
    $Gia = $_POST['Gia'] ?? 0;
    $SoLuong = $_POST['soluong'] ?? 0;
    $id_DanhMuc = $_POST['id_DanhMuc'] ?? 0;

    $Anh = "";
    if (isset($_FILES["Anh"]) && $_FILES["Anh"]["error"] == 0) {
        $target_dir = ".././assets/img/";
        $target_file = $target_dir . basename($_FILES["Anh"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "png", "jpeg", "gif", "webp"];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["Anh"]["tmp_name"], $target_file)) {
                $Anh = basename($_FILES["Anh"]["name"]);
            } else {
                $error = "Lỗi tải ảnh lên thư mục lưu trữ!";
            }
        } else {
            $error = "Định dạng ảnh không hợp lệ (Chỉ chấp nhận jpg, png, jpeg, gif, webp)!";
        }
    } else {
        $error = "Vui lòng chọn ảnh đại diện cho sản phẩm!";
    }

    if (!$error) {
        $result_count = $conn->query("SELECT COUNT(*) as total FROM sanpham");
        $row_count = $result_count->fetch_assoc();
        $total = $row_count['total'];

        if ($total == 0) {
            $conn->query("ALTER TABLE sanpham AUTO_INCREMENT = 1");
        }

        // Check if Gia and SoLuong are greater than zero
        if ($Gia <= 0) {
            $error = "Giá sản phẩm phải lớn hơn 0 VNĐ!";
        } else if ($SoLuong <= 0) {
            $error = "Số lượng sản phẩm phải lớn hơn 0!";
        } else if (empty($Ten) || empty($MoTa) || empty($Anh) || $id_DanhMuc <= 0) {
            $error = "Vui lòng điền đầy đủ tất cả các thông tin bắt buộc!";
        } else {
            $sql = "INSERT INTO sanpham (`Ten`, `MoTa`, `Gia`, `soluong`, `Anh`, `id_DanhMuc`)
                    VALUES ('$Ten', '$MoTa', '$Gia', '$SoLuong', '$Anh', '$id_DanhMuc')";

            if ($conn->query($sql) === TRUE) {
                header("Location: .././admin.php?page=qLsp");
                exit();
            } else {
                $error = "Lỗi hệ thống khi thêm sản phẩm: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm mới | UNIQ</title>
    
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
            max-width: 640px;
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
                <i class="fa-solid fa-circle-plus text-primary me-2"></i>Thêm sản phẩm mới
            </h3>
            <p class="text-secondary small mb-0">Điền thông tin chi tiết bên dưới để bổ sung sản phẩm vào catalog cửa hàng</p>
        </div>

        <!-- SERVER VALIDATION ERRORS -->
        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert alert-error mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- FORM DETAILS -->
        <form method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label class="form-label form-label-custom">Tên sản phẩm <span class="text-danger">*</span></label>
                <input type="text" name="Ten" class="form-control form-control-custom" placeholder="Nhập tên sản phẩm thời trang..." required>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-custom">Mô tả sản phẩm <span class="text-danger">*</span></label>
                <textarea name="MoTa" class="form-control form-control-custom" rows="3" placeholder="Mô tả thông tin chi tiết, kích cỡ, chất liệu..." required></textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label form-label-custom">Giá sản phẩm (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" name="Gia" class="form-control form-control-custom" placeholder="Ví dụ: 250000" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-custom">Số lượng nhập kho <span class="text-danger">*</span></label>
                    <input type="number" name="soluong" class="form-control form-control-custom" placeholder="Số lượng sản phẩm..." required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-custom">Ảnh đại diện sản phẩm <span class="text-danger">*</span></label>
                <input type="file" name="Anh" class="form-control form-control-custom" accept="image/*" required>
                <div class="form-text text-muted mt-1 small">Chỉ chấp nhận các định dạng tệp ảnh thông dụng: jpg, png, jpeg, gif, webp</div>
            </div>

            <div class="mb-4">
                <label class="form-label form-label-custom">Loại sản phẩm (Danh mục) <span class="text-danger">*</span></label>
                <select name="id_DanhMuc" class="form-select form-control-custom" required>
                    <option value="" disabled selected>-- Chọn một danh mục --</option>
                    <?php if ($result_danhmuc && $result_danhmuc->num_rows > 0): ?>
                        <?php while ($row = $result_danhmuc->fetch_assoc()): ?>
                            <option value="<?= $row['id_DanhMuc'] ?>"><?= htmlspecialchars($row['Ten_DanhMuc']) ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- BUTTON ACTIONS -->
            <div class="d-flex justify-content-between gap-3 mt-4">
                <a href=".././admin.php?page=qLsp" class="btn btn-back flex-grow-1 text-center"><i class="fa-solid fa-arrow-left me-2"></i> Quay lại</a>
                <button type="submit" class="btn btn-save flex-grow-1"><i class="fa-solid fa-plus me-2"></i> Thêm sản phẩm</button>
            </div>

        </form>

    </div>

</body>
</html>
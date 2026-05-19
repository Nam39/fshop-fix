<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM sanpham WHERE id = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if (!$row) {
        echo "Sản phẩm không tồn tại!";
        exit();
    }
} else {
    echo "ID không hợp lệ!";
    exit();
}

$sql_danhmuc = "SELECT * FROM danhmucsanpham";
$result_danhmuc = $conn->query($sql_danhmuc);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten = $_POST['Ten'];
    $mota = $_POST['MoTa'];
    $soluong = $_POST['SoLuong'];
    $gia = $_POST['Gia'];
    $danhmuc = $_POST['id_DanhMuc'];

    $anhMoi = $_FILES['Anh']['name'];
    $anhCu = $row['Anh'];

    if (!empty($anhMoi)) {
        $target_dir = "../assets/img/";
        $target_file = $target_dir . basename($anhMoi);
        move_uploaded_file($_FILES["Anh"]["tmp_name"], $target_file);
        $anh = $anhMoi;
    } else {
        $anh = $anhCu;
    }

    $sql = "UPDATE sanpham SET Ten='$ten', MoTa='$mota', SoLuong='$soluong', Gia='$gia', Anh='$anh', id_DanhMuc='$danhmuc' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../admin.php?page=qLsp");
        exit();
    } else {
        $error = "Gặp sự cố khi cập nhật cơ sở dữ liệu: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa sản phẩm | UNIQ</title>
    
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

        .current-img-preview {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
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
                <i class="fa-solid fa-shirt text-primary me-2"></i>Sửa sản phẩm
            </h3>
            <p class="text-secondary small mb-0">Cập nhật thông tin chi tiết và đơn giá sản phẩm</p>
        </div>

        <!-- SERVER ERRORS -->
        <?php if (isset($error)): ?>
            <div class="alert alert-error mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- FORM DETAILS -->
        <form method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label class="form-label form-label-custom">ID sản phẩm</label>
                <input type="text" class="form-control form-control-custom" value="#<?= $row['id'] ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-custom">Tên sản phẩm</label>
                <input type="text" name="Ten" class="form-control form-control-custom" value="<?= htmlspecialchars($row['Ten']) ?>" required>
            </div>

            <!-- Product Image Preview Block -->
            <div class="mb-3 p-3 bg-light rounded-3 d-flex align-items-center gap-3">
                <div>
                    <label class="form-label form-label-custom d-block mb-1">Ảnh hiện tại</label>
                    <img src="../assets/img/<?= htmlspecialchars($row['Anh']) ?>" alt="Product Thumbnail" class="current-img-preview">
                </div>
                <div class="flex-grow-1">
                    <label class="form-label form-label-custom">Ảnh mới (bỏ trống nếu không thay đổi)</label>
                    <input type="file" name="Anh" class="form-control form-control-custom bg-white">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-custom">Mô tả chi tiết sản phẩm</label>
                <textarea name="MoTa" class="form-control form-control-custom" rows="3" required><?= htmlspecialchars($row['MoTa']) ?></textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label form-label-custom">Giá bán (VNĐ)</label>
                    <input type="number" name="Gia" class="form-control form-control-custom" value="<?= $row['Gia'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-custom">Số lượng kho hàng</label>
                    <input type="number" name="SoLuong" class="form-control form-control-custom" value="<?= $row['soluong'] ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label form-label-custom">Loại sản phẩm (Danh mục)</label>
                <select name="id_DanhMuc" class="form-select form-control-custom">
                    <?php while ($danhmuc = $result_danhmuc->fetch_assoc()) { ?>
                        <option value="<?php echo $danhmuc['id_DanhMuc']; ?>"
                            <?php echo ($danhmuc['id_DanhMuc'] == $row['id_DanhMuc']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($danhmuc['Ten_DanhMuc']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- BUTTON ACTIONS -->
            <div class="d-flex justify-content-between gap-3 mt-4">
                <a href=".././admin.php?page=qLsp" class="btn btn-back flex-grow-1 text-center"><i class="fa-solid fa-arrow-left me-2"></i> Quay lại</a>
                <button type="submit" class="btn btn-save flex-grow-1"><i class="fa-solid fa-circle-check me-2"></i> Lưu thay đổi</button>
            </div>

        </form>

    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
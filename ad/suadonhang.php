<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM donhang WHERE idDonHang = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if (!$row) {
        echo "Đơn hàng không tồn tại!";
        exit();
    }

    // Fetch order details (items)
    $sql_items = "
        SELECT ct.*, sp.Ten, sp.Anh, sp.Gia
        FROM chitietdonhang ct
        JOIN sanpham sp ON ct.idsanpham = sp.id
        WHERE ct.idDonHang = $id
    ";
    $result_items = $conn->query($sql_items);
} else {
    echo "ID không hợp lệ!";
    exit();
}

$statuses = [
    0 => "⏳ Chờ xử lý",
    1 => "✅ Đã xác nhận",
    2 => "🚚 Đang giao hàng",
    3 => "🎉 Đã hoàn thành",
    4 => "❌ Đã hủy bỏ"
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trangthai = (int)$_POST['trangthai'];

    if (array_key_exists($trangthai, $statuses)) {
        $sql_update = "UPDATE donhang SET trangthai = ? WHERE idDonHang = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $trangthai, $id);
        if ($stmt_update->execute()) {
            header("Location: ../admin.php?page=qldh");
            exit();
        } else {
            $error = "Gặp sự cố khi cập nhật cơ sở dữ liệu: " . $conn->error;
        }
    } else {
        $error = "Trạng thái vận đơn chọn không hợp lệ!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết và Sửa đơn hàng | UNIQ</title>
    
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
            max-width: 780px;
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

        .mono-badge {
            font-family: monospace;
            font-weight: 700;
            font-size: 0.9rem;
            color: #475569;
            background-color: #f1f5f9;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .product-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
        }

        /* Order Table styling */
        .order-table th {
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 10px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background-color: #f8fafc;
        }

        .order-table td {
            padding: 14px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
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
                <i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Chi tiết đơn hàng
            </h3>
            <p class="text-secondary small mb-0">Xem danh sách sản phẩm và cập nhật trạng thái xử lý vận đơn</p>
        </div>

        <!-- SERVER VALIDATION ERRORS -->
        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert alert-error mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- META DETAILS BLOCK -->
        <div class="row g-3 mb-4 p-3 bg-light rounded-3">
            <div class="col-sm-6 col-6">
                <div class="mb-2"><strong class="text-secondary small">MÃ ĐƠN HÀNG</strong></div>
                <span class="mono-badge">#<?= $row['idDonHang'] ?></span>
            </div>
            <div class="col-sm-6 col-6 text-sm-end text-end">
                <div class="mb-2"><strong class="text-secondary small">MÃ KHÁCH HÀNG</strong></div>
                <span class="mono-badge">#<?= $row['idKhach'] ?></span>
            </div>
            <div class="col-12 mt-2 pt-2 border-top border-slate-200 d-flex justify-content-between align-items-center">
                <strong class="text-secondary small">NGÀY ĐẶT HÀNG</strong>
                <span class="text-dark fw-semibold"><?= date("d/m/Y H:i", strtotime($row['ngaydathang'])) ?></span>
            </div>
        </div>

        <!-- ORDERED PRODUCTS TABLE -->
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-cart-shopping text-secondary me-2"></i>Sản phẩm đã đặt</h5>
        
        <div class="table-responsive mb-4 rounded-3 border">
            <table class="table order-table text-center align-middle m-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">Ảnh</th>
                        <th style="text-align: left;">Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>SL</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $tongTien = 0;
                    if ($result_items && $result_items->num_rows > 0): 
                        while ($item = $result_items->fetch_assoc()): 
                            $thanhTien = $item['soluong'] * $item['Gia'];
                            $tongTien += $thanhTien;
                    ?>
                        <tr>
                            <td>
                                <img src="../assets/img/<?= htmlspecialchars($item['Anh']) ?>" class="product-thumb" alt="Product image">
                            </td>
                            <td class="text-start fw-semibold text-dark"><?= htmlspecialchars($item['Ten']) ?></td>
                            <td class="text-secondary"><?= number_format($item['Gia'], 0, ',', '.') ?> đ</td>
                            <td class="fw-bold"><?= $item['soluong'] ?></td>
                            <td class="fw-bold text-dark"><?= number_format($thanhTien, 0, ',', '.') ?> đ</td>
                        </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="5" class="text-muted py-4">Không tìm thấy chi tiết sản phẩm nào.</td>
                        </tr>
                    <?php endif; ?>
                    <tr class="bg-light">
                        <td colspan="4" class="text-end fw-bold py-3 text-secondary">Tổng cộng:</td>
                        <td class="fw-bold text-primary fs-5 py-3"><?= number_format($tongTien, 0, ',', '.') ?> VNĐ</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- FORM DETAILS -->
        <form method="POST">
            
            <div class="mb-4">
                <label class="form-label form-label-custom">Cập nhật trạng thái đơn hàng</label>
                <select name="trangthai" class="form-select form-control-custom fw-semibold text-dark">
                    <?php foreach ($statuses as $val => $label) { ?>
                        <option value="<?= $val ?>" <?= ($val == $row['trangthai']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- BUTTON ACTIONS -->
            <div class="d-flex justify-content-between gap-3 mt-4">
                <a href=".././admin.php?page=qldh" class="btn btn-back flex-grow-1 text-center"><i class="fa-solid fa-arrow-left me-2"></i> Quay lại</a>
                <button type="submit" class="btn btn-save flex-grow-1"><i class="fa-solid fa-circle-check me-2"></i> Lưu thay đổi</button>
            </div>

        </form>

    </div>

</body>
</html>

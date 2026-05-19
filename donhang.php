<?php
session_start();
require_once "./connect_DB/connect_db.php";

$conn = connectData();

if (!isset($_SESSION['idtk'])) {
    header("Location: login.php");
    exit();
}

/* ================= LẤY ID USER ================= */

$idtk = $_SESSION['idtk'];

$sqlUser = "
    SELECT iduser
    FROM users
    WHERE idtk = ?
";

$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $idtk);
$stmtUser->execute();

$userData = $stmtUser->get_result()->fetch_assoc();

if (!$userData) {
    die("Không tìm thấy người dùng.");
}

$iduser = $userData['iduser'];
$lastOrderId = isset($_SESSION['last_order_id']) ? (int)$_SESSION['last_order_id'] : 0;

/* ================= LẤY DANH SÁCH ĐƠN HÀNG ================= */

$sql = "
    SELECT
        idDonHang,
        ngaydathang,
        trangthai,
        tongtien
    FROM donhang
    WHERE idKhach = ?
";

$types = "i";
$params = [$iduser];

// Tương thích dữ liệu cũ nếu idKhach từng lưu nhầm id tài khoản thay vì id user.
if ($idtk !== $iduser) {
    $sql .= " OR idKhach = ?";
    $types .= "i";
    $params[] = $idtk;
}

// Bảo đảm đơn vừa đặt trong session hiện tại vẫn hiện ngay sau khi thanh toán.
if ($lastOrderId > 0) {
    $sql .= " OR idDonHang = ?";
    $types .= "i";
    $params[] = $lastOrderId;
}

$sql .= " ORDER BY ngaydathang DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

$orderResult = $stmt->get_result();
$orders = $orderResult ? $orderResult->fetch_all(MYSQLI_ASSOC) : [];

/* ================= HÀM HIỂN THỊ TRẠNG THÁI (PASTEL LUXURY STYLE) ================= */

function hienThiTrangThai($status)
{
    switch ($status) {
        case 0:
            return '<span class="badge rounded-pill px-3 py-2 fw-bold" style="background-color: #fef3c7; color: #d97706; border: 1px solid #fde68a;">Đã đặt hàng</span>';
        case 1:
            return '<span class="badge rounded-pill px-3 py-2 fw-bold" style="background-color: #e0e7ff; color: #4f46e5; border: 1px solid #c7d2fe;">Đã xác nhận</span>';
        case 2:
            return '<span class="badge rounded-pill px-3 py-2 fw-bold" style="background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;">Đang giao</span>';
        case 3:
            return '<span class="badge rounded-pill px-3 py-2 fw-bold" style="background-color: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0;">Hoàn thành</span>';
        case 4:
            return '<span class="badge rounded-pill px-3 py-2 fw-bold" style="background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca;">Đã hủy</span>';
        default:
            return '<span class="badge rounded-pill px-3 py-2 fw-bold" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">Không rõ</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi | UNIQ</title>

    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
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

        .orders-container {
            margin-top: 130px;
            margin-bottom: 80px;
        }

        .orders-card {
            background: #ffffff;
            padding: 40px;
            border-radius: var(--card-radius);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .orders-title {
            font-weight: 800;
            color: var(--dark-color);
            font-size: 1.5rem;
        }

        .orders-table th {
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            padding: 14px 10px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .orders-table td {
            padding: 16px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .order-id-badge {
            font-family: monospace;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--primary-color);
            background-color: rgba(13, 110, 253, 0.06);
            padding: 6px 12px;
            border-radius: 8px;
        }

        /* Order item capsule */
        .order-item-capsule {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .order-item-capsule:hover {
            border-color: #cbd5e1;
            background-color: #f1f5f9;
        }

        .order-item-img {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        @media (max-width: 768px) {
            .orders-container {
                margin-top: 100px;
                margin-bottom: 40px;
            }
            .orders-card {
                padding: 24px 16px;
            }
        }
    </style>
</head>

<body>

<?php include "./assets/layout/header/index.php"; ?>

<div class="container orders-container">

    <div class="orders-card shadow-sm">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">

            <h2 class="orders-title mb-0 d-flex align-items-center">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Lịch sử mua hàng
            </h2>

            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại mua sắm
            </a>

        </div>

        <?php if (!empty($orders)): ?>

            <div class="table-responsive">

                <table class="table orders-table align-middle text-center m-0">

                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th class="text-start" style="min-width: 320px;">Sản phẩm</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($orders as $row): ?>

                            <?php
                            $idDonHang = $row['idDonHang'];

                            $sqlDetail = "
                                SELECT
                                    chitietdonhang.soluong,
                                    chitietdonhang.gia,
                                    sanpham.Ten,
                                    sanpham.Anh,
                                    danhmucsanpham.Ten_DanhMuc
                                FROM chitietdonhang
                                JOIN sanpham
                                    ON chitietdonhang.idsanpham = sanpham.id
                                LEFT JOIN danhmucsanpham
                                    ON sanpham.id_DanhMuc = danhmucsanpham.id_DanhMuc
                                WHERE chitietdonhang.iddonhang = ?
                            ";

                            $stmtDetail = $conn->prepare($sqlDetail);
                            $stmtDetail->bind_param("i", $idDonHang);
                            $stmtDetail->execute();
                            $detailResult = $stmtDetail->get_result();
                            ?>

                            <tr>

                                <td>
                                    <span class="order-id-badge">#<?= $row['idDonHang'] ?></span>
                                </td>

                                <td class="text-secondary fw-semibold">
                                    <?= date("d/m/Y H:i", strtotime($row['ngaydathang'])) ?>
                                </td>

                                <td class="text-start">
                                    <?php if ($detailResult->num_rows > 0): ?>

                                        <?php while ($sp = $detailResult->fetch_assoc()): ?>

                                            <div class="order-item-capsule shadow-2xs">

                                                <div class="d-flex align-items-center gap-3">

                                                    <img src="./assets/img/<?= htmlspecialchars($sp['Anh']) ?>"
                                                         class="order-item-img shadow-2xs"
                                                         alt="<?= htmlspecialchars($sp['Ten']) ?>">

                                                    <div class="flex-grow-1 min-w-0">

                                                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.95rem;">
                                                            <?= htmlspecialchars($sp['Ten']) ?>
                                                        </div>

                                                        <div class="d-flex flex-wrap align-items-center mt-1" style="font-size: 0.8rem; color: #64748b; column-gap: 12px; row-gap: 4px;">
                                                            <span>Bộ sưu tập: <strong><?= htmlspecialchars($sp['Ten_DanhMuc'] ?? 'Không rõ') ?></strong></span>
                                                            <span class="text-slate-300">|</span>
                                                            <span>Số lượng: <strong><?= $sp['soluong'] ?></strong></span>
                                                        </div>

                                                    </div>

                                                    <div class="text-end fw-bold text-danger fs-6 ps-2">
                                                        <?= number_format($sp['gia'], 0, ',', '.') ?>đ
                                                    </div>

                                                </div>

                                            </div>

                                        <?php endwhile; ?>

                                    <?php else: ?>

                                        <span class="text-danger fw-bold"><i class="fa-solid fa-circle-exclamation me-2"></i>Không tìm thấy sản phẩm</span>

                                    <?php endif; ?>
                                </td>

                                <td class="fw-extrabold text-danger fs-5">
                                    <?= number_format($row['tongtien'], 0, ',', '.') ?>đ
                                </td>

                                <td>
                                    <?= hienThiTrangThai($row['trangthai']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="text-center rounded-4 border-0 p-5 bg-light mt-2">
                <i class="fa-solid fa-receipt fa-3x mb-3 text-secondary opacity-60"></i>
                <h4 class="fw-bold text-dark">Lịch sử đơn hàng trống!</h4>
                <p class="text-secondary mb-4">Bạn chưa thực hiện bất kỳ giao dịch mua sắm nào tại hệ thống của chúng tôi.</p>
                <a href="sanpham.php" class="btn btn-primary rounded-pill btn-lg px-5 fw-bold shadow-sm fs-6">
                    Khám phá ngay <i class="fa-solid fa-arrow-right-long ms-1.5"></i>
                </a>
            </div>

        <?php endif; ?>

    </div>

</div>

<?php include "./assets/layout/footer/index.php"; ?>

<script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>

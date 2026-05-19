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

/* ================= HÀM HIỂN THỊ TRẠNG THÁI ================= */

function hienThiTrangThai($status)
{
    switch ($status) {

        case 0:
            return '<span class="badge bg-warning text-dark">Đã đặt hàng</span>';

        case 1:
            return '<span class="badge bg-primary">Đã xác nhận</span>';

        case 2:
            return '<span class="badge bg-info text-dark">Đang giao</span>';

        case 3:
            return '<span class="badge bg-success">Hoàn thành</span>';

        case 4:
            return '<span class="badge bg-danger">Đã hủy</span>';

        default:
            return '<span class="badge bg-secondary">Không rõ</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Đơn hàng của tôi</title>

    <link href="./assets/bootstrap/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="./assets/fonts/css/all.min.css">

    <style>

        body {
            background-color: #f5f5f5;
        }

        .page-title {
            font-weight: bold;
        }

        .table-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .order-id {
            font-weight: bold;
            color: #0d6efd;
        }

        .product-item {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .product-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

    </style>

</head>

<body>

<?php include "./assets/layout/header/index.php"; ?>

<div class="container mt-5 pt-5">

    <div class="table-container">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2 class="page-title text-primary mb-0">

                <i class="fa-solid fa-bag-shopping me-2"></i>

                Đơn hàng của tôi

            </h2>

            <a href="index.php"
               class="btn btn-outline-secondary">

                <i class="fa-solid fa-arrow-left me-1"></i>

                Quay lại

            </a>

        </div>

        <?php if (!empty($orders)): ?>

            <div class="table-responsive">

                <table class="table table-bordered table-hover text-center align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>Mã đơn hàng</th>

                            <th>Ngày đặt</th>

                            <th>Sản phẩm</th>

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

        <td class="order-id">
            #<?= $row['idDonHang'] ?>
        </td>

        <td>
            <?= date(
                "d/m/Y H:i",
                strtotime($row['ngaydathang'])
            ) ?>
        </td>

        <td>
            <?php if ($detailResult->num_rows > 0): ?>

                <?php while ($sp = $detailResult->fetch_assoc()): ?>

                    <div class="border rounded p-2 mb-2 text-start">

                        <div class="d-flex align-items-center">

                            <img
                                src="./assets/img/<?= htmlspecialchars($sp['Anh']) ?>"
                                width="60"
                                class="me-2 rounded"
                                alt="<?= htmlspecialchars($sp['Ten']) ?>"
                            >

                            <div>

                                <div class="fw-bold">
                                    <?= htmlspecialchars($sp['Ten']) ?>
                                </div>

                                <small>
                                    Danh mục: <?= htmlspecialchars($sp['Ten_DanhMuc'] ?? 'Không rõ') ?>
                                </small>

                                <br>

                                <small>
                                    SL: <?= $sp['soluong'] ?>
                                </small>

                                <br>

                                <small class="text-danger">
                                    <?= number_format(
                                        $sp['gia'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?> VNĐ
                                </small>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <span class="text-danger">
                    Không có sản phẩm
                </span>

            <?php endif; ?>
        </td>

        <td class="fw-bold text-danger">
            <?= number_format(
                $row['tongtien'],
                0,
                ',',
                '.'
            ) ?> VNĐ
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

            <div class="alert alert-info text-center">

                <h5 class="mb-3">

                    Bạn chưa có đơn hàng nào.

                </h5>

                <a href="sanpham.php"
                   class="btn btn-primary">

                    Mua sắm ngay

                </a>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php include "./assets/layout/footer/index.php"; ?>

<script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>

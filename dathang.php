<?php
session_start();
include_once "./connect_DB/connect_db.php";

if (!isset($_SESSION['idtk'])) {
    die("Bạn cần đăng nhập để thanh toán.");
}

$conn = connectData();
$mes = "";

/* ================= LẤY ID USER ================= */

$idtk = $_SESSION['idtk'];

$sqlUser = "
    SELECT iduser, Ten_user, email, sdt, diachi
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
$profileName = $userData['Ten_user'] ?? '';
$profileEmail = $userData['email'] ?? '';
$profilePhone = $userData['sdt'] ?? '';
$profileAddress = $userData['diachi'] ?? '';

/* ================= LẤY GIỎ HÀNG ================= */

$sql = "
    SELECT 
        gh.*,
        sp.Ten,
        sp.Gia,
        sp.Anh,
        sp.soluong AS tonkho
    FROM giohang gh
    JOIN sanpham sp 
        ON gh.idsanpham = sp.id
    WHERE gh.iduser = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $iduser);

$stmt->execute();

$result = $stmt->get_result();

$items = $result->fetch_all(MYSQLI_ASSOC);

/* ================= TÍNH TỔNG TIỀN ================= */

$total = 0;

foreach ($items as $item) {
    $total += $item['Gia'] * $item['soluong'];
}

/* ================= ĐẶT HÀNG ================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['dathang'])) {

    if (empty($items)) {
        $mes = "Giỏ hàng trống, vui lòng thêm sản phẩm trước khi đặt hàng.";
    } else {

    $hoten = trim($_POST['hoten']);
    $email = trim($_POST['email']);
    $sdt = trim($_POST['sdt']);
    $diachi = trim($_POST['diachi']);

    if (
        empty($hoten) ||
        empty($email) ||
        empty($sdt) ||
        empty($diachi)
    ) {

        $mes = "Vui lòng nhập đầy đủ thông tin.";

    } else {

        $conn->begin_transaction();

        try {

            /* ================= TẠO ĐƠN HÀNG ================= */

            $hasShippingColumns = $conn->query("SHOW COLUMNS FROM donhang LIKE 'hoten'")->num_rows > 0;

            if ($hasShippingColumns) {
                $sqlInsertDonHang = "
                    INSERT INTO donhang
                    (
                        idKhach,
                        hoten,
                        email,
                        sodienthoai,
                        diachi,
                        tongtien,
                        trangthai,
                        ngaydathang
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, 0, NOW())
                ";

                $stmtDonHang = $conn->prepare($sqlInsertDonHang);
                $stmtDonHang->bind_param(
                    "issssd",
                    $iduser,
                    $hoten,
                    $email,
                    $sdt,
                    $diachi,
                    $total
                );
            } else {
                $sqlInsertDonHang = "
                    INSERT INTO donhang
                    (
                        idKhach,
                        tongtien,
                        trangthai,
                        ngaydathang
                    )
                    VALUES
                    (?, ?, 0, NOW())
                ";

                $stmtDonHang = $conn->prepare($sqlInsertDonHang);
                $stmtDonHang->bind_param(
                    "id",
                    $iduser,
                    $total
                );
            }

            $stmtDonHang->execute();

            $iddonhang = $conn->insert_id;

            /* ================= CHI TIẾT ĐƠN HÀNG ================= */

            foreach ($items as $item) {

                $idsp = $item['idsanpham'];
                $soluong = $item['soluong'];
                $gia = $item['Gia'];

                /* KIỂM TRA TỒN KHO */

                if ($soluong > $item['tonkho']) {
                    throw new Exception("Sản phẩm không đủ tồn kho.");
                }

                $sqlDetail = "
                    INSERT INTO chitietdonhang
                    (
                        iddonhang,
                        idsanpham,
                        soluong,
                        gia
                    )
                    VALUES (?, ?, ?, ?)
                ";

                $stmtDetail = $conn->prepare($sqlDetail);

                $stmtDetail->bind_param(
                    "iiid",
                    $iddonhang,
                    $idsp,
                    $soluong,
                    $gia
                );

                $stmtDetail->execute();

                /* ================= TRỪ TỒN KHO ================= */

                $sqlStock = "
                    UPDATE sanpham
                    SET soluong = soluong - ?
                    WHERE id = ?
                ";

                $stmtStock = $conn->prepare($sqlStock);

                $stmtStock->bind_param(
                    "ii",
                    $soluong,
                    $idsp
                );

                $stmtStock->execute();
            }

            /* ================= XÓA GIỎ HÀNG ================= */

            $sqlClear = "
                DELETE FROM giohang
                WHERE iduser = ?
            ";

            $stmtClear = $conn->prepare($sqlClear);

            $stmtClear->bind_param("i", $iduser);

            $stmtClear->execute();

            $conn->commit();

            $_SESSION['last_order_id'] = $iddonhang;

            header("Location: donhang.php");

            exit();

        } catch (Exception $e) {

            $conn->rollback();

            $mes = $e->getMessage();
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
    <title>Thanh toán</title>

    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <style>

        .container {
            max-width: 1000px;
        }

        h2 {
            font-weight: bold;
        }

        .card {
            background-color: #f8f9fa;
            border-radius: 12px;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .product-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
        }

        .fixed-profile-field {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            background-color: #e9ecef;
            color: #212529;
            cursor: not-allowed;
        }

    </style>
</head>

<body>

<?php include "./assets/layout/header/index.php"; ?>

<div class="container mt-5 pt-5">

    <div class="row g-4">

        <!-- FORM ĐẶT HÀNG -->
        <div class="col-md-5">

            <div class="card shadow-sm p-4">

                <h2 class="mb-4">
                    Thông tin đặt hàng
                </h2>

                <?php if (!empty($mes)) : ?>

                    <div class="alert alert-warning">
                        <?= htmlspecialchars($mes) ?>
                    </div>

                <?php endif; ?>

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">
                            Họ và tên
                        </label>

                        <input type="text"
                               name="hoten"
                               class="form-control"
                               value="<?= htmlspecialchars($profileName) ?>"
                               readonly
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Email
                        </label>

                        <input type="email"
                               name="email"
                               class="form-control"
                               value="<?= htmlspecialchars($profileEmail) ?>"
                               readonly
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Số điện thoại
                        </label>

                        <input type="tel"
                               name="sdt"
                               class="form-control"
                               value="<?= htmlspecialchars($profilePhone) ?>"
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            Địa chỉ giao hàng
                        </label>

                        <textarea name="diachi"
                                  class="form-control"
                                  rows="3"
                                  required><?= htmlspecialchars($profileAddress) ?></textarea>
                    </div>

                    <button type="submit"
                            name="dathang"
                            class="btn btn-warning w-100 fw-bold py-2">

                        Xác nhận đặt hàng

                    </button>

                </form>

            </div>

        </div>

        <!-- GIỎ HÀNG -->
        <div class="col-md-7">

            <div class="card shadow-sm p-4">

                <h2 class="mb-4">
                    Sản phẩm thanh toán
                </h2>

                <?php if (!empty($items)) : ?>

                    <table class="table table-bordered text-center">

                        <thead class="table-dark">

                            <tr>
                                <th>Ảnh</th>
                                <th>Tên</th>
                                <th>Giá</th>
                                <th>SL</th>
                                <th>Tổng</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($items as $item): ?>

                                <?php
                                $subtotal = $item['Gia'] * $item['soluong'];
                                ?>

                                <tr>

                                    <td>
                                        <img src="./assets/img/<?= htmlspecialchars($item['Anh']) ?>"
                                             class="product-img rounded">
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($item['Ten']) ?>
                                    </td>

                                    <td>
                                        <?= number_format($item['Gia'], 0, ',', '.') ?> VNĐ
                                    </td>

                                    <td>
                                        <?= $item['soluong'] ?>
                                    </td>

                                    <td class="fw-bold text-danger">
                                        <?= number_format($subtotal, 0, ',', '.') ?> VNĐ
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                    <div class="text-end mt-3">

                        <h4>
                            Tổng tiền:
                            <span class="text-danger fw-bold">
                                <?= number_format($total, 0, ',', '.') ?> VNĐ
                            </span>
                        </h4>

                    </div>

                <?php else : ?>

                    <div class="alert alert-info text-center">

                        Giỏ hàng trống.

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<?php include "./assets/layout/footer/index.php"; ?>

<script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
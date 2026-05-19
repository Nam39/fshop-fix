<?php
session_start();
include_once "./connect_DB/connect_db.php";

if (!isset($_SESSION['idtk'])) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Thanh toán | UNIQ</title>
        <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="./assets/fonts/css/all.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f8f9fa;
            }
            .login-prompt-container {
                margin-top: 140px;
                margin-bottom: 80px;
            }
            .glass-card {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(15px);
                border: 1px solid rgba(255, 255, 255, 0.25);
                border-radius: 20px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
                padding: 40px;
            }
            .icon-circle {
                width: 80px;
                height: 80px;
                background-color: rgba(255, 193, 7, 0.1);
                color: #ffc107;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
            }
        </style>
    </head>
    <body>
        <?php include "./assets/layout/header/index.php"; ?>

        <main class="container login-prompt-container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="glass-card">
                        <div class="icon-circle">
                            <i class="fa-solid fa-lock fa-2x"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-3">🔑 Yêu cầu Đăng nhập</h3>
                        <p class="text-secondary mb-4">Để tiến hành thanh toán đơn hàng và áp dụng mã giảm giá ưu đãi, vui lòng đăng nhập tài khoản của bạn.</p>
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <a href="login.php" class="btn btn-warning btn-lg px-4 rounded-pill fw-bold text-dark"><i class="fa-solid fa-right-to-bracket me-2"></i>Đăng nhập ngay</a>
                            <a href="signup.php" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">Tạo tài khoản</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php include "./assets/layout/footer/index.php"; ?>
        <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
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

            /* ================= XÁC THỰC VOUCHER PHÍA SERVER ================= */
            $id_voucher = null;
            $giam_gia = 0;
            if (!empty($_POST['applied_voucher_id'])) {
                $v_id = (int)$_POST['applied_voucher_id'];
                $v_stmt = $conn->prepare("SELECT * FROM voucher WHERE id_voucher = ? AND trang_thai = 1 AND ngay_het_han >= CURDATE() AND so_luong > 0");
                $v_stmt->bind_param("i", $v_id);
                $v_stmt->execute();
                $v_res = $v_stmt->get_result();
                if ($v_res->num_rows > 0) {
                    $v_data = $v_res->fetch_assoc();
                    $id_voucher = $v_data['id_voucher'];
                    if ($v_data['loai_giam'] == 0) {
                        $giam_gia = $v_data['giam_gia'];
                        if ($giam_gia > $total) {
                            $giam_gia = $total;
                        }
                    } else {
                        $giam_gia = round(($total * $v_data['giam_gia']) / 100);
                    }
                }
                $v_stmt->close();
            }
            $final_total = $total - $giam_gia;
            if ($final_total < 0) {
                $final_total = 0;
            }

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
                        ngaydathang,
                        id_voucher,
                        giam_gia
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, 0, NOW(), ?, ?)
                ";

                $stmtDonHang = $conn->prepare($sqlInsertDonHang);
                $stmtDonHang->bind_param(
                    "issssdii",
                    $iduser,
                    $hoten,
                    $email,
                    $sdt,
                    $diachi,
                    $final_total,
                    $id_voucher,
                    $giam_gia
                );
            } else {
                $sqlInsertDonHang = "
                    INSERT INTO donhang
                    (
                        idKhach,
                        tongtien,
                        trangthai,
                        ngaydathang,
                        id_voucher,
                        giam_gia
                    )
                    VALUES
                    (?, ?, 0, NOW(), ?, ?)
                ";

                $stmtDonHang = $conn->prepare($sqlInsertDonHang);
                $stmtDonHang->bind_param(
                    "idii",
                    $iduser,
                    $final_total,
                    $id_voucher,
                    $giam_gia
                );
            }

            $stmtDonHang->execute();

            $iddonhang = $conn->insert_id;

            /* ================= GIẢM SỐ LƯỢNG VOUCHER ================= */
            if ($id_voucher !== null) {
                $stmtUpdateVoucher = $conn->prepare("UPDATE voucher SET so_luong = so_luong - 1 WHERE id_voucher = ?");
                $stmtUpdateVoucher->bind_param("i", $id_voucher);
                $stmtUpdateVoucher->execute();
                $stmtUpdateVoucher->close();
            }

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
                    <input type="hidden" name="applied_voucher_id" id="appliedVoucherIdInput" value="">

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

                    <!-- VOUCHER INPUT SECTION -->
                    <div class="border-top pt-3 mt-3">
                        <label class="form-label fw-bold text-dark"><i class="fa-solid fa-ticket text-danger me-1"></i>Mã giảm giá (Voucher)</label>
                        <div class="input-group">
                            <input type="text" id="voucherCodeInput" class="form-control" placeholder="Ví dụ: FSHOP20K, SALE10">
                            <button type="button" id="applyVoucherBtn" class="btn btn-outline-primary fw-bold">Áp dụng</button>
                        </div>
                        <div id="voucherMessage" class="small mt-2 d-none"></div>
                    </div>

                    <!-- ORDER TOTALS SUMMARY -->
                    <div class="text-end mt-4 border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary">Tạm tính:</span>
                            <span class="fw-semibold text-dark"><?= number_format($total, 0, ',', '.') ?> VNĐ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 d-none" id="discountSummaryRow">
                            <span class="text-secondary">Giảm giá (<span id="appliedVoucherCodeText" class="text-primary font-monospace fw-bold"></span>):</span>
                            <span class="text-success fw-semibold">- <span id="discountAmountText">0</span> VNĐ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 border-top pt-2">
                            <h4 class="mb-0 text-dark fw-bold">Tổng thanh toán:</h4>
                            <h4 class="mb-0 text-danger fw-bold"><span id="finalTotalText"><?= number_format($total, 0, ',', '.') ?></span> VNĐ</h4>
                        </div>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const originalTotal = <?= $total ?>;
    
    $("#applyVoucherBtn").click(function() {
        const code = $("#voucherCodeInput").val().trim();
        const msgDiv = $("#voucherMessage");
        
        if (code === "") {
            msgDiv.removeClass("d-none text-success").addClass("text-danger").html("Vui lòng nhập mã giảm giá!");
            return;
        }
        
        $("#applyVoucherBtn").prop("disabled", true).html("<span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span>");
        
        $.ajax({
            url: "ap_dung_voucher.php",
            type: "POST",
            data: {
                code: code,
                tong_tien: originalTotal
            },
            dataType: "json",
            success: function(res) {
                $("#applyVoucherBtn").prop("disabled", false).html("Áp dụng");
                
                if (res.success) {
                    msgDiv.removeClass("d-none text-danger").addClass("text-success").html("<i class='fa-solid fa-circle-check'></i> " + res.message);
                    
                    // Set inputs
                    $("#appliedVoucherIdInput").val(res.id_voucher);
                    
                    // Update layout
                    $("#appliedVoucherCodeText").text(res.ma_code);
                    $("#discountAmountText").text(new Intl.NumberFormat('vi-VN').format(res.giam_gia));
                    $("#discountSummaryRow").removeClass("d-none");
                    $("#finalTotalText").text(new Intl.NumberFormat('vi-VN').format(res.tong_tien_moi));
                } else {
                    msgDiv.removeClass("d-none text-success").addClass("text-danger").html("<i class='fa-solid fa-circle-xmark'></i> " + res.message);
                    
                    // Reset inputs & layout
                    $("#appliedVoucherIdInput").val("");
                    $("#discountSummaryRow").addClass("d-none");
                    $("#finalTotalText").text(new Intl.NumberFormat('vi-VN').format(originalTotal));
                }
            },
            error: function() {
                $("#applyVoucherBtn").prop("disabled", false).html("Áp dụng");
                msgDiv.removeClass("d-none text-success").addClass("text-danger").html("Đã xảy ra lỗi kết nối, vui lòng thử lại!");
            }
        });
    });
});
</script>

</body>
</html>
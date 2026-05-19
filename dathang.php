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
    <title>Thanh toán | UNIQ</title>

    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/index.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --dark-color: #0f172a;
            --card-radius: 20px;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #334155;
        }

        .checkout-container {
            margin-top: 130px;
            margin-bottom: 80px;
        }

        .checkout-card {
            background-color: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--card-radius);
            padding: 30px;
        }

        .checkout-card h4 {
            color: var(--dark-color);
            font-weight: 800;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 12px;
            font-size: 1.25rem;
        }

        .checkout-table th {
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .checkout-table td {
            padding: 14px 8px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .product-img {
            width: 58px;
            height: 58px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .form-control-custom {
            border-radius: 12px;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        .form-control-readonly {
            background-color: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }

        /* Voucher layout box */
        .voucher-dashed-box {
            background-color: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 16px;
            padding: 18px;
        }

        @media (max-width: 768px) {
            .checkout-container {
                margin-top: 100px;
                margin-bottom: 40px;
            }
        }
    </style>
</head>

<body>

<?php include "./assets/layout/header/index.php"; ?>

<div class="container checkout-container">

    <div class="row g-4">

        <!-- LEFT SIDEBAR: ORDER FORM -->
        <div class="col-lg-5">

            <div class="checkout-card shadow-sm">

                <h4 class="mb-4">
                    <i class="fa-regular fa-address-card text-primary me-2"></i>Thông tin nhận hàng
                </h4>

                <?php if (!empty($mes)) : ?>
                    <div class="alert alert-danger border-0 rounded-4 shadow-sm p-3 mb-4 fw-bold">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($mes) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="applied_voucher_id" id="appliedVoucherIdInput" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Họ và tên <small class="text-muted">(Tài khoản)</small></label>
                        <div class="position-relative">
                            <input type="text"
                                   name="hoten"
                                   class="form-control form-control-custom form-control-readonly px-3 pe-5"
                                   value="<?= htmlspecialchars($profileName) ?>"
                                   readonly
                                   required>
                            <i class="fa-solid fa-lock position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Địa chỉ Email <small class="text-muted">(Tài khoản)</small></label>
                        <div class="position-relative">
                            <input type="email"
                                   name="email"
                                   class="form-control form-control-custom form-control-readonly px-3 pe-5"
                                   value="<?= htmlspecialchars($profileEmail) ?>"
                                   readonly
                                   required>
                            <i class="fa-solid fa-lock position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Số điện thoại liên hệ</label>
                        <input type="tel"
                               name="sdt"
                               class="form-control form-control-custom"
                               value="<?= htmlspecialchars($profilePhone) ?>"
                               placeholder="Nhập số điện thoại nhận hàng..."
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Địa chỉ nhận hàng chi tiết</label>
                        <textarea name="diachi"
                                  class="form-control form-control-custom"
                                  rows="3"
                                  placeholder="Nhập địa chỉ nhà, tên đường, phường/xã, quận/huyện..."
                                  required><?= htmlspecialchars($profileAddress) ?></textarea>
                    </div>

                    <button type="submit"
                            name="dathang"
                            class="btn btn-primary w-100 rounded-pill btn-lg fw-bold py-2.5 shadow-sm fs-6">
                        Xác nhận đặt hàng <i class="fa-solid fa-circle-check ms-1.5"></i>
                    </button>

                </form>

            </div>

        </div>

        <!-- RIGHT SIDEBAR: CART PRODUCTS -->
        <div class="col-lg-7">

            <div class="checkout-card shadow-sm">

                <h4 class="mb-4">
                    <i class="fa-solid fa-box-open text-primary me-2"></i>Sản phẩm thanh toán
                </h4>

                <?php if (!empty($items)) : ?>

                    <div class="table-responsive">
                        <table class="table checkout-table align-middle m-0">
                            <thead>
                                <tr>
                                    <th>Ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">Tổng cộng</th>
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
                                                 class="product-img shadow-sm" alt="Thumbnail">
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark text-truncate d-inline-block" style="max-width: 180px;"><?= htmlspecialchars($item['Ten']) ?></span>
                                        </td>
                                        <td class="text-center fw-medium text-secondary">
                                            <?= number_format($item['Gia'], 0, ',', '.') ?>đ
                                        </td>
                                        <td class="text-center fw-bold text-dark">
                                            <?= $item['soluong'] ?>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= number_format($subtotal, 0, ',', '.') ?>đ
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- VOUCHER INPUT SECTION -->
                    <div class="voucher-dashed-box mt-4">
                        <label class="form-label fw-bold text-dark mb-2"><i class="fa-solid fa-ticket text-danger me-2"></i>Mã giảm giá (Voucher)</label>
                        <div class="input-group">
                            <input type="text" id="voucherCodeInput" class="form-control form-control-custom rounded-start-pill bg-white px-3" placeholder="Ví dụ: UNIQ20K, SHIRT10">
                            <button type="button" id="applyVoucherBtn" class="btn btn-outline-primary rounded-end-pill px-4 fw-bold">Áp dụng</button>
                        </div>
                        <div id="voucherMessage" class="small mt-2.5 fw-bold d-none"></div>
                    </div>

                    <!-- ORDER TOTALS SUMMARY -->
                    <div class="mt-4 border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary fw-semibold">Tạm tính đơn hàng:</span>
                            <span class="fw-bold text-dark"><?= number_format($total, 0, ',', '.') ?>đ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 d-none" id="discountSummaryRow">
                            <span class="text-secondary fw-semibold">Giảm giá (<span id="appliedVoucherCodeText" class="text-primary font-monospace fw-extrabold"></span>):</span>
                            <span class="text-success fw-bold">- <span id="discountAmountText">0</span>đ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
                            <h4 class="mb-0 text-dark fw-extrabold fs-5">Thành tiền:</h4>
                            <h4 class="mb-0 text-danger fw-extrabold fs-4"><span id="finalTotalText"><?= number_format($total, 0, ',', '.') ?></span>đ</h4>
                        </div>
                    </div>

                <?php else : ?>

                    <div class="alert alert-info text-center rounded-4 border-0 p-4">
                        <i class="fa-solid fa-circle-info fa-2x mb-3 text-secondary"></i>
                        <h5 class="fw-bold text-dark">Giỏ hàng thanh toán trống!</h5>
                        <p class="text-secondary mb-0">Vui lòng chọn thêm sản phẩm trước khi tiến hành đặt hàng.</p>
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
            msgDiv.removeClass("d-none text-success").addClass("text-danger").html("<i class='fa-solid fa-circle-xmark me-1'></i> Vui lòng nhập mã giảm giá!");
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
                    msgDiv.removeClass("d-none text-danger").addClass("text-success").html("<i class='fa-solid fa-circle-check me-2'></i> " + res.message);
                    
                    // Set inputs
                    $("#appliedVoucherIdInput").val(res.id_voucher);
                    
                    // Update layout
                    $("#appliedVoucherCodeText").text(res.ma_code);
                    $("#discountAmountText").text(new Intl.NumberFormat('vi-VN').format(res.giam_gia));
                    $("#discountSummaryRow").removeClass("d-none");
                    $("#finalTotalText").text(new Intl.NumberFormat('vi-VN').format(res.tong_tien_moi));
                } else {
                    msgDiv.removeClass("d-none text-success").addClass("text-danger").html("<i class='fa-solid fa-circle-xmark me-2'></i> " + res.message);
                    
                    // Reset inputs & layout
                    $("#appliedVoucherIdInput").val("");
                    $("#discountSummaryRow").addClass("d-none");
                    $("#finalTotalText").text(new Intl.NumberFormat('vi-VN').format(originalTotal));
                }
            },
            error: function() {
                $("#applyVoucherBtn").prop("disabled", false).html("Áp dụng");
                msgDiv.removeClass("d-none text-success").addClass("text-danger").html("<i class='fa-solid fa-circle-xmark me-2'></i> Đã xảy ra lỗi kết nối, vui lòng thử lại!");
            }
        });
    });
});
</script>

</body>
</html>
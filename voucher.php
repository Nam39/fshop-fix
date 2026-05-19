<?php
session_start();
include "./connect_DB/connect_db.php";
$conn = connectData();

// Fetch active and non-expired vouchers with positive quantity
$sql = "
    SELECT * 
    FROM voucher 
    WHERE trang_thai = 1 
      AND ngay_het_han >= CURDATE() 
      AND so_luong > 0 
    ORDER BY id_voucher DESC
";
$result = $conn->query($sql);
$vouchers = [];
if ($result) {
    $vouchers = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ưu đãi & Voucher | UNIQ</title>
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/fonts/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .voucher-header {
            margin-top: 110px;
            margin-bottom: 40px;
        }

        /* Beautiful ticket card styling */
        .voucher-ticket {
            display: flex;
            background: linear-gradient(135deg, #ffffff, #f1f5f9);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
            min-height: 150px;
            position: relative;
        }

        .voucher-ticket:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: rgba(13, 110, 253, 0.3);
        }

        .voucher-left {
            flex: 0 0 130px;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 15px;
            text-align: center;
            border-right: 2px dashed #cbd5e1;
            position: relative;
        }

        /* realistic ticket punch holes */
        .voucher-left::before,
        .voucher-left::after {
            content: "";
            position: absolute;
            right: -10px;
            width: 20px;
            height: 20px;
            background-color: #f8f9fa;
            border-radius: 50%;
            z-index: 10;
        }
        .voucher-left::before {
            top: -10px;
        }
        .voucher-left::after {
            bottom: -10px;
        }

        .voucher-right {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: #ffffff;
        }

        .voucher-code-badge {
            background-color: #f1f5f9;
            border: 1px dashed #cbd5e1;
            color: #0f172a;
            font-family: monospace;
            font-size: 1.15rem;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: bold;
            letter-spacing: 1px;
            display: inline-block;
        }

        .btn-copy {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border: none;
            font-weight: bold;
            transition: all 0.2s;
            border-radius: 20px;
        }

        .btn-copy:hover {
            background: #0d6efd;
            color: white;
        }

        .toast-container-custom {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>
</head>
<body>

    <?php include "./assets/layout/header/index.php"; ?>

    <main class="container my-5">
        <div class="voucher-header text-center">
            <h2 class="fw-bold text-dark"><i class="fa-solid fa-ticket text-danger me-2"></i>KHO VOUCHER KHUYẾN MÃI</h2>
            <p class="text-secondary max-width-500 mx-auto">Nhận mã giảm giá độc quyền để sở hữu những sản phẩm thời trang cao cấp với giá ưu đãi cực sốc!</p>
        </div>

        <div class="row">
            <?php if (!empty($vouchers)): ?>
                <?php foreach ($vouchers as $voucher): ?>
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="voucher-ticket">
                            <!-- Left: Discount Value -->
                            <div class="voucher-left">
                                <i class="fa-solid fa-gift fa-2x mb-2 text-warning"></i>
                                <?php if ($voucher['loai_giam'] == 0): ?>
                                    <h4 class="fw-bold mb-0"><?= number_format($voucher['giam_gia'] / 1000) ?>K</h4>
                                    <span class="small opacity-75">GIẢM TIỀN</span>
                                <?php else: ?>
                                    <h4 class="fw-bold mb-0"><?= $voucher['giam_gia'] ?>%</h4>
                                    <span class="small opacity-75">GIẢM GIÁ</span>
                                <?php endif; ?>
                            </div>

                            <!-- Right: Voucher Details -->
                            <div class="voucher-right">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="fw-bold text-dark mb-0">
                                            <?= $voucher['loai_giam'] == 0 ? 'Giảm ngay ' . number_format($voucher['giam_gia']) . 'đ' : 'Giảm ngay ' . $voucher['giam_gia'] . '% tổng hóa đơn' ?>
                                        </h5>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">Còn <?= $voucher['so_luong'] ?> lượt</span>
                                    </div>
                                    <p class="text-secondary small mb-3">Áp dụng cho mọi đơn hàng khi mua sắm tại hệ thống UNIQ.</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="voucher-code-badge"><?= htmlspecialchars($voucher['ma_code']) ?></span>
                                    </div>
                                    <button class="btn btn-copy btn-sm px-3 py-1.5 copy-btn" data-code="<?= htmlspecialchars($voucher['ma_code']) ?>">
                                        <i class="fa-regular fa-copy me-1"></i> Sao chép mã
                                    </button>
                                </div>
                                <div class="border-top pt-2 mt-2">
                                    <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i>Hết hạn ngày: <b><?= date('d/m/Y', strtotime($voucher['ngay_het_han'])) ?></b></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center my-5">
                    <div class="p-5 bg-white rounded-3 shadow-sm">
                        <i class="fa-solid fa-ticket-simple fa-4x text-muted mb-3"></i>
                        <h4 class="text-secondary fw-bold">Hiện chưa có voucher khuyến mãi nào</h4>
                        <p class="text-muted">Chúng tôi đang cập nhật các chương trình ưu đãi mới, hãy quay lại sau nhé!</p>
                        <a href="index.php" class="btn btn-primary rounded-pill mt-3 px-4">Tiếp tục mua sắm</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Custom Toast Container for beautiful Copy notification -->
    <div class="toast-container-custom" id="toastContainer"></div>

    <?php include "./assets/layout/footer/index.php"; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Copy to clipboard handler
        $(".copy-btn").click(function() {
            const code = $(this).data("code");
            const btn = $(this);
            
            navigator.clipboard.writeText(code).then(() => {
                // Show a beautiful toast notification
                const toastHtml = `
                    <div class="toast show align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 10px; backdrop-filter: blur(10px);">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fa-solid fa-circle-check me-2"></i> Đã sao chép mã <b>${code}</b> thành công!
                            </div>
                        </div>
                    </div>
                `;
                
                const toastEl = $(toastHtml).appendTo("#toastContainer");
                
                // Change button text temporarily
                btn.html('<i class="fa-solid fa-check me-1"></i> Đã chép').addClass("btn-success text-white").removeClass("btn-copy");
                
                // Revert button after 2 seconds
                setTimeout(() => {
                    btn.html('<i class="fa-regular fa-copy me-1"></i> Sao chép mã').removeClass("btn-success text-white").addClass("btn-copy");
                }, 2000);
                
                // Fadeout and remove toast
                setTimeout(() => {
                    toastEl.fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 2500);
            }).catch(err => {
                alert("Không thể sao chép mã, vui lòng copy thủ công: " + code);
            });
        });
    });
    </script>
</body>
</html>

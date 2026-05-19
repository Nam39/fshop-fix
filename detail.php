<?php

require_once "./connect_DB/connect_db.php";

$conn = connectData();

session_start();


if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "SELECT * FROM sanpham WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "<p>Sản phẩm không tồn tại!</p>";
        exit();
    }
} else {
    echo "<p>Không có sản phẩm nào được chọn!</p>";
    exit();
}


if (isset($_POST['themvaogio'])) {
    $product_id = $product['id'];
    $product_name = $product['Ten'];
    $product_price = $product['Gia'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['soluong'] += 1;
    } else {
        $_SESSION['cart'][$product_id] = [
            'Ten' => $product_name,
            'Gia' => $product_price,
            'SoLuong' => 1
        ];
    }

    echo "<p style='color:green;'>Sản phẩm đã được thêm vào giỏ hàng!</p>";
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['Ten']) ?> | UNIQ</title>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #fafbfc;
            color: #334155;
        }

        a {
            text-decoration: none;
            transition: all 0.2s;
        }

        /* Product Gallery Box */
        .gallery-box {
            background-color: #ffffff;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            padding: 24px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
            transition: all 0.3s ease;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 480px;
        }

        .gallery-box:hover {
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        }

        .product-image-hero {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            border-radius: 16px;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .gallery-box:hover .product-image-hero {
            transform: scale(1.04);
        }

        /* Info column */
        .product-info-panel {
            padding-left: 20px;
        }

        .product-title {
            font-weight: 800;
            color: #0f172a;
            font-size: 2.2rem;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }

        .mono-sku {
            font-family: monospace;
            font-weight: 700;
            font-size: 0.9rem;
            color: #64748b;
            background-color: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
        }

        .price-badge-block {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 24px;
            display: inline-block;
        }

        .price-val {
            font-size: 2.25rem;
            font-weight: 800;
            color: #0d6efd;
            letter-spacing: -0.02em;
        }

        .price-unit {
            font-size: 1.3rem;
            font-weight: 700;
            margin-left: 4px;
        }

        .description-card {
            border-left: 3px solid #0d6efd;
            padding-left: 16px;
            margin-top: 1.5rem;
            margin-bottom: 2rem;
        }

        .description-text {
            color: #475569;
            font-size: 1rem;
            line-height: 1.8;
            text-align: justify;
        }

        /* Custom Qty Box */
        .qty-label {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.95rem;
        }

        .qty-capsule {
            display: flex;
            align-items: center;
            background-color: #f1f5f9;
            border-radius: 50px;
            padding: 4px;
            width: 140px;
            justify-content: space-between;
            border: 1px solid #cbd5e1;
        }

        .btn-qty-control {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background-color: #ffffff;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.05);
            transition: all 0.2s;
        }

        .btn-qty-control:hover:not(:disabled) {
            background-color: #0f172a;
            color: #ffffff;
            transform: scale(1.05);
        }

        .btn-qty-control:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .qty-input-custom {
            border: none !important;
            background: transparent !important;
            width: 50px;
            text-align: center;
            font-weight: 800;
            color: #0f172a;
            font-size: 1.05rem;
            box-shadow: none !important;
            outline: none !important;
            pointer-events: none;
        }

        .btn-add-cart-custom {
            border-radius: 50px;
            padding: 14px 40px;
            font-weight: 700;
            font-size: 1.05rem;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-add-cart-custom:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.35);
        }

        .btn-back-custom {
            border-radius: 50px;
            padding: 14px 28px;
            font-weight: 700;
            font-size: 1.05rem;
            transition: all 0.2s;
        }

        .btn-back-custom:hover {
            background-color: #f1f5f9;
        }

        /* Glassmorphic Live Toast */
        .toast-custom {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
        }

        .pt--detail {
            padding-top: 100px;
            padding-bottom: 100px;
        }
    </style>
</head>

<body>

    <!-- SITE HEADER -->
    <?php include "./assets/layout/header/index.php"; ?>

    <!-- MAIN WRAPPER -->
    <div class="container pt--detail">
        <div class="row g-5">
            
            <!-- LEFT COLUMN: PHOTO SHOWCASE -->
            <div class="col-md-6 mb-4">
                <div class="gallery-box">
                    <img src="./assets/img/<?= htmlspecialchars($product['Anh']) ?>" class="product-image-hero" alt="<?= htmlspecialchars($product['Ten']) ?>" id="mainImage">
                </div>
            </div>

            <!-- RIGHT COLUMN: PRODUCT INFO & PURCHASE -->
            <div class="col-md-6">
                <div class="product-info-panel">
                    
                    <!-- Category Collection Capsule -->
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold mb-3 d-inline-block text-uppercase" style="font-size: 0.725rem; letter-spacing: 0.08em;">
                        <i class="fa-solid fa-gem me-1"></i> BST Cao Cấp
                    </span>

                    <!-- Product Name -->
                    <h1 class="product-title mb-2"><?= htmlspecialchars($product['Ten']) ?></h1>
                    
                    <!-- Monospace Item Code -->
                    <div class="mb-3">
                        <span class="text-secondary small">Mã sản phẩm:</span>
                        <span class="mono-sku">#<?= $product['id'] ?></span>
                    </div>

                    <!-- Dynamic Stock Registry -->
                    <?php
                    $stock_qty = (int)$product['soluong'];
                    if ($stock_qty > 0) {
                        echo '<div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-success-subtle border border-success-subtle text-success fw-bold small mb-4">
                                <i class="fa-solid fa-circle-check fs-6"></i> Còn hàng (' . $stock_qty . ' sản phẩm)
                              </div>';
                    } else {
                        echo '<div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-danger-subtle border border-danger-subtle text-danger fw-bold small mb-4">
                                <i class="fa-solid fa-circle-xmark fs-6"></i> Hết hàng
                              </div>';
                    }
                    ?>

                    <br>

                    <!-- Price badge block -->
                    <div class="price-badge-block mb-4">
                        <span class="price-val">
                            <?= number_format($product['Gia'], 0, ',', '.') ?>
                            <span class="price-unit">VNĐ</span>
                        </span>
                    </div>

                    <!-- Editorial Description Block -->
                    <div class="description-card">
                        <h5 class="fw-bold text-dark mb-2">Mô tả sản phẩm</h5>
                        <p class="description-text">
                            <?= nl2br(htmlspecialchars($product['MoTa'])) ?>
                        </p>
                    </div>

                    <!-- TRANSACTION FORM -->
                    <form action="themvaogio.php" method="POST" id="purchaseForm">
                        <input type="hidden" name="idsanpham" value="<?= $product['id'] ?>">
                        
                        <!-- Dynamic Quantity Counter Block -->
                        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                            <span class="qty-label">Số lượng:</span>
                            <div class="qty-capsule">
                                <button class="btn-qty-control btn-qty-minus" type="button" <?= $stock_qty <= 0 ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" class="form-control qty-input-custom" id="soluong" name="soluong" value="1" min="1" max="<?= $stock_qty ?>" readonly>
                                <button class="btn-qty-control btn-qty-plus" type="button" <?= $stock_qty <= 0 ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- CTA Actions pills -->
                        <div class="d-flex flex-wrap gap-3">
                            <button type="submit" class="btn btn-primary btn-add-cart-custom d-flex align-items-center gap-2" <?= $stock_qty <= 0 ? 'disabled' : '' ?>>
                                <i class="fa-solid fa-cart-shopping"></i> Thêm vào giỏ hàng
                            </button>

                            <a href="./index.php" class="btn btn-outline-secondary btn-back-custom d-flex align-items-center gap-2">
                                <i class="fa-solid fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

    <!-- ERROR TOAST REGISTER -->
    <?php if (isset($_GET['error'])): ?>
        <div class="position-fixed top-0 end-0 p-4" style="z-index: 9999;">
            <div id="liveToast" class="toast toast-custom show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-danger text-white border-0 py-2.5 px-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <strong class="me-auto">Thông báo từ hệ thống</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body p-3 text-secondary-emphasis fw-semibold">
                    <?php
                    switch ($_GET['error']) {
                        case 'overstock':
                            echo 'Không thể đặt thêm: Số lượng bạn chọn vượt quá lượng tồn kho còn lại của shop.';
                            break;
                        case 'notloggedin':
                            echo 'Bạn cần đăng nhập tài khoản khách hàng để có thể thêm sản phẩm vào giỏ hàng.';
                            break;
                        case 'notfound':
                            echo 'Sản phẩm này đã ngừng bán hoặc không tồn tại.';
                            break;
                        default:
                            echo 'Đã xảy ra sự cố ngoài ý muốn. Vui lòng thử lại sau ít phút.';
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- SITE FOOTER -->
    <?php include "./assets/layout/footer/index.php"; ?>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Custom Counter Button Listeners
        const btnMinus = document.querySelector('.btn-qty-minus');
        const btnPlus = document.querySelector('.btn-qty-plus');
        const qtyInput = document.getElementById('soluong');

        if (btnMinus && btnPlus && qtyInput) {
            btnMinus.addEventListener('click', function() {
                let currentVal = parseInt(qtyInput.value) || 1;
                if (currentVal > 1) {
                    qtyInput.value = currentVal - 1;
                }
            });

            btnPlus.addEventListener('click', function() {
                let currentVal = parseInt(qtyInput.value) || 1;
                let maxLimit = parseInt(qtyInput.getAttribute('max')) || 9999;
                if (currentVal < maxLimit) {
                    qtyInput.value = currentVal + 1;
                }
            });
        }
    </script>
</body>

</html>
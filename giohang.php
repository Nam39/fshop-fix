<?php
session_start();
include "./connect_DB/connect_db.php";

$conn = connectData();
$mes = "";

// 1. GUEST GATEWAY BANNER
if (!isset($_SESSION['idtk'])) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Giỏ hàng | UNIQ</title>
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
                background-color: rgba(13, 110, 253, 0.1);
                color: #0d6efd;
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
                        <p class="text-secondary mb-4">Để xem các sản phẩm trong giỏ hàng và tiến hành đặt hàng thanh toán, vui lòng đăng nhập tài khoản của bạn.</p>
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <a href="login.php" class="btn btn-primary btn-lg px-4 rounded-pill"><i class="fa-solid fa-right-to-bracket me-2"></i>Đăng nhập ngay</a>
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

// 2. LOGGED-IN USERS CART PROCESSING
$idtk = $_SESSION['idtk'];
$stmt = $conn->prepare("SELECT iduser FROM users WHERE idtk = ?");
$stmt->bind_param("i", $idtk);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Không tìm thấy người dùng.");
}
$row = $result->fetch_assoc();
$iduser = $row['iduser'];

// Update Quantity Handler
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_qty'])) {
    $idsanpham = intval($_POST['product_id']);
    $soluong = max(1, intval($_POST['soluong']));

    $stmtCheck = $conn->prepare("SELECT soluong FROM sanpham WHERE id = ?");
    $stmtCheck->bind_param("i", $idsanpham);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $row = $resultCheck->fetch_assoc();
    $soluong_tonkho = $row['soluong'];

    if ($soluong > $soluong_tonkho) {
        $mes = "Không đủ hàng tồn kho. Chỉ còn lại {$soluong_tonkho} sản phẩm.";
    } else {
        $stmt = $conn->prepare("UPDATE giohang SET soluong = ? WHERE iduser = ? AND idsanpham = ?");
        $stmt->bind_param("iii", $soluong, $iduser, $idsanpham);
        $stmt->execute();
        header("Location: giohang.php");
        exit();
    }
}

// Remove Single Item Handler
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM giohang WHERE iduser = ? AND idsanpham = ?");
    $stmt->bind_param("ii", $iduser, $id);
    $stmt->execute();
    header("Location: giohang.php");
    exit();
}

// Clear Cart Handler
if (isset($_GET['clear'])) {
    $stmt = $conn->prepare("DELETE FROM giohang WHERE iduser = ?");
    $stmt->bind_param("i", $iduser);
    $stmt->execute();
    header("Location: giohang.php");
    exit();
}

// Retrieve Cart Items
$sql = "SELECT gh.*, sp.Ten, sp.Gia, sp.Anh FROM giohang gh
        JOIN sanpham sp ON gh.idsanpham = sp.id
        WHERE gh.iduser = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $iduser);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng | UNIQ</title>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
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

        .main-container {
            margin-top: 140px;
            margin-bottom: 80px;
        }

        /* Cart Product Table and Custom row styling */
        .cart-table th {
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #cbd5e1;
            padding: 16px 12px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .cart-table td {
            padding: 20px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Product image border and scale */
        .cart-img-box {
            width: 76px;
            height: 76px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.25s ease;
        }

        .cart-img-box:hover img {
            transform: scale(1.06);
        }

        /* Order Summary Card */
        .summary-card {
            background-color: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--card-radius);
            padding: 24px;
            position: sticky;
            top: 120px;
        }

        /* Micro Qty Buttons */
        .qty-btn-custom {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f1f5f9;
            color: #334155;
            border: none;
            border-radius: 50%;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .qty-btn-custom:hover {
            background-color: #cbd5e1;
            color: #0f172a;
        }

        /* Empty state glassmorphism */
        .empty-cart-box {
            background-color: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--card-radius);
            padding: 60px 40px;
        }

        @media (max-width: 768px) {
            .main-container {
                margin-top: 100px;
                margin-bottom: 40px;
            }
            .cart-table th {
                display: none;
            }
            .cart-table td {
                display: block;
                text-align: center !important;
                padding: 10px 0;
                border-bottom: none;
            }
            .cart-table tr {
                display: block;
                border-bottom: 1px solid #cbd5e1;
                padding-bottom: 15px;
                margin-bottom: 15px;
            }
            .cart-img-box {
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>
    <!-- HEADER LAYOUT -->
    <?php include "./assets/layout/header/index.php" ?>

    <main class="container main-container">
        <!-- Error Alerts -->
        <?php if (!empty($mes)) : ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 text-center fw-bold">
                <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($mes) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($items)) : ?>
            <h3 class="fw-extrabold text-dark mb-4"><i class="fa-solid fa-cart-shopping text-primary me-2.5"></i>Giỏ hàng của bạn</h3>
            
            <div class="row g-4">
                <!-- Left: Products List Grid (col-lg-8) -->
                <div class="col-lg-8">
                    <div class="bg-white rounded-4 shadow-sm border border-light-subtle p-3 p-md-4">
                        <div class="table-responsive">
                            <table class="table cart-table align-middle m-0">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Tên</th>
                                        <th class="text-center">Đơn giá</th>
                                        <th class="text-center">Số lượng</th>
                                        <th class="text-center">Tổng tiền</th>
                                        <th class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 0;
                                    foreach ($items as $item):
                                        $subtotal = $item['Gia'] * $item['soluong'];
                                        $total += $subtotal;
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="cart-img-box shadow-sm">
                                                    <img src="./assets/img/<?= htmlspecialchars($item['Anh']) ?>" alt="<?= htmlspecialchars($item['Ten']) ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark text-truncate d-inline-block" style="max-width: 180px;"><?= htmlspecialchars($item['Ten']) ?></span>
                                            </td>
                                            <td class="text-center fw-bold text-secondary">
                                                <?= number_format($item['Gia'], 0, ',', '.') ?>đ
                                            </td>
                                            <td>
                                                <!-- Pill Qty Form -->
                                                <form method="POST" action="giohang.php" class="d-flex align-items-center justify-content-center update-form m-0 bg-light rounded-pill p-1 border" style="max-width: 110px; margin: 0 auto !important;">
                                                    <input type="hidden" name="product_id" value="<?= $item['idsanpham'] ?>">
                                                    
                                                    <button type="button" class="qty-btn-custom qty-btn" data-type="minus"><i class="fa-solid fa-minus fs-6" style="font-size: 0.65rem !important;"></i></button>
                                                    <input type="number" name="soluong" value="<?= $item['soluong'] ?>" min="1" class="form-control border-0 bg-transparent text-center p-0 fw-bold" style="width: 32px; box-shadow: none; font-size: 0.9rem;" readonly>
                                                    <button type="button" class="qty-btn-custom qty-btn" data-type="plus"><i class="fa-solid fa-plus fs-6" style="font-size: 0.65rem !important;"></i></button>
                                                    
                                                    <input type="hidden" name="update_qty" value="1">
                                                </form>
                                            </td>
                                            <td class="text-center fw-bold text-danger">
                                                <?= number_format($subtotal, 0, ',', '.') ?>đ
                                            </td>
                                            <td class="text-center">
                                                <a href="giohang.php?remove=<?= $item['idsanpham'] ?>" 
                                                   class="btn btn-outline-danger btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" 
                                                   style="width: 36px; height: 36px; padding: 0;"
                                                   onclick="return confirm('Xóa sản phẩm này khỏi giỏ hàng?');">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                            <a href="sanpham.php" class="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-semibold"><i class="fa-solid fa-arrow-left me-2"></i>Tiếp tục mua sắm</a>
                            <a href="giohang.php?clear=true" class="btn btn-outline-danger rounded-pill px-4 btn-sm fw-semibold" onclick="return confirm('Bạn thực sự muốn dọn sạch toàn bộ giỏ hàng?');"><i class="fa-solid fa-eraser me-2"></i>Xóa giỏ hàng</a>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary & Checkout Sidebar (col-lg-4) -->
                <div class="col-lg-4">
                    <div class="summary-card shadow-sm border border-light-subtle">
                        <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom"><i class="fa-solid fa-receipt text-primary me-2"></i>Tóm tắt đơn hàng</h5>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary fw-medium">Tổng tiền hàng:</span>
                            <span class="fw-bold text-dark"><?= number_format($total, 0, ',', '.') ?>đ</span>
                        </div>
                        
                        <!-- Shipping threshold alert -->
                        <div class="alert <?= $total >= 500000 ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' ?> border-0 rounded-4 small p-3 mb-4">
                            <?php if ($total >= 500000): ?>
                                <i class="fa-solid fa-circle-check me-2"></i>Đơn hàng đạt trên 500k, bạn được <strong>Miễn phí vận chuyển</strong>!
                            <?php else: ?>
                                <i class="fa-solid fa-circle-info me-2"></i>Mua thêm <strong><?= number_format(500000 - $total, 0, ',', '.') ?>đ</strong> để nhận voucher <strong>Free Shipping</strong>!
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4 border-top pt-3">
                            <span class="fs-5 fw-extrabold text-dark">Thành tiền:</span>
                            <span class="fs-4 fw-extrabold text-danger"><?= number_format($total, 0, ',', '.') ?>đ</span>
                        </div>

                        <a href="dathang.php" class="btn btn-primary w-100 rounded-pill btn-lg fw-bold d-flex align-items-center justify-content-center py-2.5 fs-6 shadow-sm">
                            Tiến hành đặt hàng <i class="fa-solid fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

        <?php else : ?>
            <!-- 3. EMPTY STATE FALLBACK VIEW -->
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="empty-cart-box shadow-sm border border-light-subtle">
                        <div class="mb-4">
                            <span class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="fa-solid fa-bag-shopping fa-3x" style="opacity: 0.6;"></i>
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Giỏ hàng của bạn đang trống!</h4>
                        <p class="text-secondary mb-4 mx-auto" style="max-width: 320px;">Vui lòng quay lại cửa hàng, tìm kiếm các mẫu trang phục thời trang mới và lấp đầy giỏ hàng của bạn nhé.</p>
                        <a href="./sanpham.php" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm"><i class="fa-solid fa-bag-shopping me-2"></i>Mua sắm ngay</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- FOOTER LAYOUT -->
    <?php include "./assets/layout/footer/index.php" ?>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Handle pill quantity adjustments
            document.querySelectorAll('.qty-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    const input = form.querySelector('input[name="soluong"]');
                    let currentQty = parseInt(input.value);
                    const type = this.dataset.type;

                    if (type === 'minus' && currentQty > 1) {
                        input.value = currentQty - 1;
                        form.submit();
                    } else if (type === 'plus') {
                        input.value = currentQty + 1;
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>

</html>
<?php
$conn->close();
?>
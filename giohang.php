<?php
session_start();
include "./connect_DB/connect_db.php";
$messl = "";

// if (!isset($_SESSION['idtk'])) {
//     $messl = "Bạn cần đăng nhập để xem giỏ hàng.";
// }
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

$conn = connectData();
$mes = "";

// Lấy iduser từ idtk
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
        $mes = "Số lượng vượt quá tồn kho. Chỉ còn lại {$soluong_tonkho} sản phẩm.";
    } else {
        $stmt = $conn->prepare("UPDATE giohang SET soluong = ? WHERE iduser = ? AND idsanpham = ?");
        $stmt->bind_param("iii", $soluong, $iduser, $idsanpham);
        $stmt->execute();
        header("Location: giohang.php");
        exit();
    }
}

if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM giohang WHERE iduser = ? AND idsanpham = ?");
    $stmt->bind_param("ii", $iduser, $id);
    $stmt->execute();
    header("Location: giohang.php");
    exit();
}

if (isset($_GET['clear'])) {
    $stmt = $conn->prepare("DELETE FROM giohang WHERE iduser = ?");
    $stmt->bind_param("i", $iduser);
    $stmt->execute();
    header("Location: giohang.php");
    exit();
}

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
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media (max-width: 576px) {

            td,
            th {
                font-size: 13px;
                padding: 6px;
            }

            .qty-btn {
                width: 28px;
                height: 28px;
                font-size: 14px;
            }

            .form-control {
                font-size: 14px;
                height: 30px;
            }
        }
    </style>

</head>

<body>

    <div class="container mt-5 pt-5">
        <?php include "./assets/layout/header/index.php" ?>
        <h2 class="text-center mb-5 mt-4">Giỏ hàng của bạn</h2>
        <?php if (!empty($mes)) : ?>
            <div class="alert alert-warning text-center"><?= htmlspecialchars($mes) ?></div>
        <?php endif; ?>
        <!-- <?php if (!empty($messl)) : ?>
            <div class="alert alert-warning text-center"><?= htmlspecialchars($messl) ?></div>
        <?php endif; ?> -->

        <?php if (!empty($items)) : ?>
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                        <th>Hành động</th>
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
                            <td><img src="./assets/img/<?= htmlspecialchars($item['Anh']) ?>" width="60" class="img-fluid">
                            </td>
                            <td><?= htmlspecialchars($item['Ten']) ?></td>
                            <td><?= number_format($item['Gia'], 0, ',', '.') ?> VNĐ</td>
                            <td>
                                <form method="POST" action="giohang.php" class="d-flex flex-nowrap justify-content-center align-items-center update-form">
                                    <input type="hidden" name="product_id" value="<?= $item['idsanpham'] ?>">
                                    <button type="button" class="btn btn-outline-secondary btn-sm qty-btn" data-type="minus">-</button>
                                    <input type="number" name="soluong" value="<?= $item['soluong'] ?>" min="1" class="form-control text-center mx-1" style="width: 60px;" y>
                                    <button type="button" class="btn btn-outline-secondary btn-sm qty-btn" data-type="plus">+</button>
                                    <input type="hidden" name="update_qty" value="1">
                                </form>
                            </td>
                            <td><?= number_format($subtotal, 0, ',', '.') ?> VNĐ</td>
                            <td>
                                <a href="giohang.php?remove=<?= $item['idsanpham'] ?>" class="btn btn-danger btn-sm">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h4 class="text-end">Tổng tiền: <span class="text-danger"><?= number_format($total, 0, ',', '.') ?> VNĐ</span></h4>

            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-outline-primary">← Tiếp tục mua sắm</a>
                <div>
                    <a href="giohang.php?clear=true" class="btn btn-outline-danger">Xóa giỏ hàng</a>
                    <a href="dathang.php" class="btn btn-success">Đặt hàng</a>
                </div>
            </div>
        <?php else : ?>
            <div class="alert alert-info text-center">Giỏ hàng trống.</div>
            <div class="text-center">
                <a href="./sanpham.php" class="btn btn-primary">Quay lại mua sắm</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include "./assets/layout/footer/index.php" ?>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('input[name="soluong"]').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.closest('form').submit();
                }
            });

            input.addEventListener('blur', function() {
                const form = this.closest('form');
                if (parseInt(this.value) < 1 || isNaN(parseInt(this.value))) {
                    this.value = 1;
                }
                form.submit();
            });
        });


        document.querySelectorAll('.qty-btn').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('form');
                const input = form.querySelector('input[name="soluong"]');
                let currentQty = parseInt(input.value);
                const type = this.dataset.type;

                if (type === 'minus' && currentQty > 1) {
                    input.value = currentQty - 1;
                } else if (type === 'plus') {
                    input.value = currentQty + 1;
                }

                form.submit();
            });
        });
    </script>
</body>

</html>
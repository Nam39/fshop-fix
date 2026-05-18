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
    <title>Chi tiết sản phẩm</title>
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">

    <style>
        a {
            text-decoration: none;
        }

        .user-img {
            position: relative;
        }

        .user-img:hover .box {
            display: block;
        }

        .avatar-img {
            border: 1px solid rgba(0, 0, 0);
        }

        .box {
            z-index: 1;
            position: absolute;
            top: 4;
            right: 50;
            background-color: white;
            width: 140px;
            height: 30vh;
            display: none;
            box-shadow: 0 0 1px 2px rgba(0, 0, 0, 0.4);
        }

        .box-name {
            text-align: center;
            font-weight: 500;
            font-size: 24px;
            margin: 4px 0;
        }

        .logoutbtn {
            color: red;
            text-decoration: none;
        }

        .product-image {
            width: 400px;
            max-height: 300px;
            object-fit: cover;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.3s ease;
        }

        .thumbnail:hover,
        .thumbnail.active {
            opacity: 1;
        }

        .pt--6 {
            padding-top: 60px;
        }
    </style>
</head>

<body>

    <?php
    include "./assets/layout/header/index.php"
    ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 mb-4 pt--6">
                <div class="d-flex align-items-center justify-content-center mb-4">
                    <img src="./assets/img/<?= $product['Anh'] ?>" class="img-fluid rounded mb-3 product-image " id="mainImage">
                </div>
                <div class="d-flex justify-content-between">
                    <img src="./assets/img/giay2.jpg?>" alt="Thumbnail 1" class="thumbnail rounded active" onclick="changeImage(event, this.src)">
                    <img src="./assets/img/<?= $product['Anh'] ?>" alt="Thumbnail 2" class="thumbnail rounded" onclick="changeImage(event, this.src)">
                    <img src="./assets/img/<?= $product['Anh'] ?>" alt="Thumbnail 3" class="thumbnail rounded" onclick="changeImage(event, this.src)">
                    <img src="./assets/img/<?= $product['Anh'] ?>" alt="Thumbnail 4" class="thumbnail rounded" onclick="changeImage(event, this.src)">
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-md-6 mt-5">
                <h2 class="mb-3"><?= $product['Ten'] ?></h2>

                <div class="mb-3">
                    <span class="h4 me-2"><?= number_format($product['Gia'], 0, ',', '.') ?> VNĐ</span>
                </div>
                <!-- <div class="mb-3">
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-half text-warning"></i>
                    <span class="ms-2">4.5 (120 reviews)</span>
                </div> -->
                <p class="mb-4"><?= $product['MoTa'] ?></p>
                <!-- <div class="mb-4">
                    <h5>Color:</h5>
                    <div class="btn-group" role="group" aria-label="Color selection">
                        <input type="radio" class="btn-check" name="color" id="black" autocomplete="off" checked>
                        <label class="btn btn-outline-dark" for="black">Black</label>
                        <input type="radio" class="btn-check" name="color" id="silver" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="silver">Silver</label>
                        <input type="radio" class="btn-check" name="color" id="blue" autocomplete="off">
                        <label class="btn btn-outline-primary" for="blue">Blue</label>
                    </div>
                </div> -->

                <div class="d-flex align-items-center">
                    <form action="themvaogio.php" method="POST">
                        <input type="hidden" name="idsanpham" value="<?= $product['id'] ?>">
                        <div class="mb-4">
                            <label for="soluong" class="form-label">Số Lượng:</label>
                            <input type="number" class="form-control border border-dark" id="soluong" name="soluong" value="1" min="1" style="width: 80px;">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg mt-2 icon-link icon-link-hover" style="--bs-icon-link-transform: translate3d(0, -.125rem, 0);">
                            <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                        </button>

                        <button class="btn btn-outline-secondary btn-lg mb-2 mt-3">
                            <a href="./index.php" class="text-decoration-none">Quay lai</a>
                        </button>
                    </form>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                        <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-header bg-danger text-white">
                                <strong class="me-auto">Thông báo</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body">
                                <?php
                                switch ($_GET['error']) {
                                    case 'overstock':
                                        echo 'Không thể thêm vào giỏ hàng: Số lượng vượt quá tồn kho.';
                                        break;
                                    case 'notloggedin':
                                        echo 'Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.';
                                        break;
                                    case 'notfound':
                                        echo 'Sản phẩm không tồn tại.';
                                        break;
                                    default:
                                        echo 'Đã xảy ra lỗi. Vui lòng thử lại.';
                                        break;
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>


            </div>
        </div>
    </div>

    <?php
    include "./assets/layout/footer/index.php"
    ?>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeImage(event, src) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
            event.target.classList.add('active');
        }
    </script>
</body>

</html>
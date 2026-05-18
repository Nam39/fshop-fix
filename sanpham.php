<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "./connect_DB/connect_db.php";
$conn = connectData();

/* ================= USER ================= */

if (isset($_SESSION['idtk'])) {
    $idtk = $_SESSION['idtk'];

    $sql = "SELECT Ten_user, Anh_user 
            FROM users 
            WHERE idtk = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idtk);
    $stmt->execute();

    $userInfo = $stmt->get_result()->fetch_assoc();

    if ($userInfo) {
        $_SESSION['Ten_user'] = $userInfo['Ten_user'];
        $_SESSION['Anh_user'] = $userInfo['Anh_user'];
    }
}

/* ================= PHÂN TRANG ================= */

$limit = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* ================= DANH MỤC ================= */

$currentCategory = isset($_GET['danhmuc']) ? (int)$_GET['danhmuc'] : 0;

$categories = [];

$sqlDanhMuc = "
    SELECT 
        dm.id_DanhMuc,
        dm.Ten_DanhMuc,
        COUNT(sp.id) AS total
    FROM danhmucsanpham dm
    LEFT JOIN sanpham sp 
        ON dm.id_DanhMuc = sp.id_DanhMuc
    GROUP BY dm.id_DanhMuc, dm.Ten_DanhMuc
";

$danhMucResult = $conn->query($sqlDanhMuc);

while ($row = $danhMucResult->fetch_assoc()) {
    $categories[] = $row;
}

/* ================= TÌM KIẾM ================= */

$search = isset($_GET['query']) ? trim($_GET['query']) : "";

/* ================= LẤY SẢN PHẨM ================= */

if (!empty($search)) {

    // Đếm sản phẩm tìm kiếm
    $countSql = "
        SELECT COUNT(*) AS total
        FROM sanpham
        WHERE Ten LIKE ?
        OR MoTa LIKE ?
    ";

    $countStmt = $conn->prepare($countSql);

    $search_param = "%$search%";

    $countStmt->bind_param("ss", $search_param, $search_param);
    $countStmt->execute();

    $totalProducts = $countStmt->get_result()->fetch_assoc()['total'];

    $totalPages = ceil($totalProducts / $limit);

    // Lấy dữ liệu
    $sql = "
        SELECT *
        FROM sanpham
        WHERE Ten LIKE ?
        OR MoTa LIKE ?
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssii",
        $search_param,
        $search_param,
        $limit,
        $offset
    );

    $stmt->execute();

    $result = $stmt->get_result();

} elseif ($currentCategory > 0) {

    // Đếm sản phẩm theo danh mục
    $countSql = "
        SELECT COUNT(*) AS total
        FROM sanpham
        WHERE id_DanhMuc = ?
    ";

    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("i", $currentCategory);
    $countStmt->execute();

    $totalProducts = $countStmt->get_result()->fetch_assoc()['total'];

    $totalPages = ceil($totalProducts / $limit);

    // Lấy sản phẩm theo danh mục
    $sql = "
        SELECT *
        FROM sanpham
        WHERE id_DanhMuc = ?
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "iii",
        $currentCategory,
        $limit,
        $offset
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    // Tổng sản phẩm
    $countSql = "SELECT COUNT(*) AS total FROM sanpham";

    $totalResult = $conn->query($countSql);

    $totalProducts = $totalResult->fetch_assoc()['total'];

    $totalPages = ceil($totalProducts / $limit);

    // Lấy tất cả sản phẩm
    $sql = "
        SELECT *
        FROM sanpham
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ii", $limit, $offset);

    $stmt->execute();

    $result = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thương Mại Điện Tử</title>
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/index.css" rel="stylesheet">
    <style>
        html a {
            text-decoration: none;
            color: #333;
        }

        .nav {
            z-index: 10;
            top: 0;
            left: 0;
            right: 0;
            padding: 20px;
        }

        .user-img:hover .box {
            display: block;
        }

        .avatar-img {
            border: 1px solid rgba(0, 0, 0, 0.3);
        }

        .box {
            margin-top: 3px;
            z-index: 100;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: #fff;
            width: 180px;
            border-radius: 8px;
            display: none;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            padding: 10px 0;
        }

        .box-name {
            font-weight: 500;
            font-size: 18px;
            margin: 4px 0;
        }

        .box a {
            display: block;
            padding: 8px 15px;
            font-size: 14px;
            color: #333;
            transition: background-color 0.2s;
        }

        .box a:hover {
            background-color: #f1f1f1;
        }

        .logoutbtn:hover {
            color: red;
        }

        .aff {
            position: relative;
        }

        .aff-child::after {
            content: "";
            position: absolute;
            left: 0;
            top: 14px;
            background-color: transparent;
            width: 100%;
            height: 30px;
        }

        .dropdown-divider {
            margin: 0.3rem 0;
            border-top: 1px solid rgb(65, 67, 75);
        }
        .active-category {
    background: #0d6efd;
    border-radius: 6px;
}

.active-category a,
.active-category span {
    color: white !important;
}
    </style>
</head>

<body>
    <div class="main">

        <?php
        include "./assets/layout/header/index.php"
        ?>

        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow position-fixed nav">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary" href="./">UNIQ</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link active" href="./index.php">Trang chủ</a></li>
                        <li class="nav-item"><a class="nav-link active" href="./sanpham.php">Sản phẩm</a></li>
                        <li class="nav-item"><a class="nav-link" href="./giohang.php"><i class="fa-solid fa-cart-shopping text-secondary mx-1"></i>Giỏ hàng <span class="badge bg-danger"></span></a></li>
                    </ul>

                    <form class="d-flex me-3">
                        <input class="form-control me-2  border border-dark" type="search" name="query" placeholder="Tìm kiếm sản phẩm...">
                        <button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>

                    <div class="ms-auto">
                        <?php if (isset($_SESSION['idtk'])): ?>
                            <div class="user-img aff d-flex align-items-center justify-content-center position-relative">
                                <img src="./assets/img/<?= $_SESSION['Anh_user'] ?>" alt="Avatar" class="rounded-circle avatar-img" width="40" height="40">
                                <p class="box-name px-2 d-flex align-items-center mb-0 ms-2">
                                    <?= htmlspecialchars($_SESSION['Ten_user']) ?>
                                    <i class="fa-solid fa-sort-down mx-1 mb-1"></i>
                                </p>

                                <div class="box text-start">
                                    <a href="./thongtinuser.php">Thông tin chi tiết</a>
                                    <a href="./donhang.php">Đơn hàng của tôi</a>
                                    <a href="./doimatkhau.php">Đổi mật khẩu</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="./logout.php" class="logoutbtn" onclick="return confirm('Bạn có muốn đăng xuất không?');">Đăng xuất</a>
                                </div>

                                <div class="aff-child"></div>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Đăng nhập</a>
                            <a href="signup.php" class="btn btn-secondary">Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
        </nav>


        <div id="myCarousel" class="carousel slide bg-dark mt-4 mb-4" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>

            <div class="carousel-inner text-center">
                <div class="carousel-item active">
                    <img src="./assets/img/cat-item1.jpg" class="d-block mx-auto" style="height: 500px; object-fit: cover;" alt="Slide 1">
                </div>
                <div class="carousel-item">
                    <img src="./assets/img/cat-item2.jpg" class="d-block mx-auto" style="height: 500px; object-fit: cover;" alt="Slide 2">
                </div>
                <div class="carousel-item">
                    <img src="./assets/img/cat-item3.jpg" class="d-block mx-auto" style="height: 500px; object-fit: cover;" alt="Slide 3">
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        <div class="container mt-4">
            <div class="row">
            <div class="col-md-2">
    <p class="text-title">Danh mục</p>

    <ul class="list-group list-cus">

        <!-- TẤT CẢ -->
        <li class="list-item d-flex justify-content-between 
            <?= $currentCategory == 0 ? 'active-category' : '' ?>">

            <a href="sanpham.php">Tất cả</a>
        </li>

        <?php foreach ($categories as $dm): ?>

            <li class="list-item d-flex justify-content-between
                <?= $currentCategory == $dm['id_DanhMuc'] ? 'active-category' : '' ?>">

                <a href="?danhmuc=<?= $dm['id_DanhMuc'] ?>">
                    <?= htmlspecialchars($dm['Ten_DanhMuc']) ?>
                </a>

                <span>(<?= $dm['total'] ?>)</span>
            </li>

        <?php endforeach; ?>

    </ul>
</div>

                <div class="col-md-10">
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                        ?>
                                <div class="col">
                                    <div class="card h-100 box-sca">
                                        <a href="./detail.php?id=<?= $row['id'] ?>">
                                            <img src="./assets/img/<?= $row['Anh'] ?>" class="card-img-top mt-2" alt="Hình ảnh sản phẩm">
                                        </a>
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($row['Ten']) ?></h5>
                                            <p class="card-text description-clamp"><?= htmlspecialchars($row['MoTa']) ?></p>
                                            <p>Giá: <?= number_format($row['Gia'], 0, ',', '.') ?> <b>VNĐ</b></p>
                                            <div class="d-flex align-items-center gap-2 flex-nowrap">
                                                <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-detail text-nowrap">
                                                    Chi tiết sản phẩm
                                                </a>
                                                <form action="themvaogio.php" method="POST" class="m-0">
                                                    <input type="hidden" name="idsanpham" value="<?= $row['id'] ?>">
                                                    <button class="btn btn-success d-flex justify-content-center align-items-center cart-btn">
                                                        <i class="fa-solid fa-cart-plus"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo "<p>Không tìm thấy kết quả nào.</p>";
                        }

                        $conn->close();
                        ?>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-center">

<?php for ($i = 1; $i <= $totalPages; $i++): ?>

    <?php
    $link = "?page=$i";

    if ($currentCategory > 0) {
        $link .= "&danhmuc=$currentCategory";
    }

    if (!empty($search)) {
        $link .= "&query=" . urlencode($search);
    }
    ?>

    <a href="<?= $link ?>"
       class="btn mx-1 <?= $page == $i ? 'btn-primary' : 'btn-outline-primary' ?>">
        <?= $i ?>
    </a>

<?php endfor; ?>

</div>
        </div>

        <?php include "./assets/layout/footer/index.php" ?>
    </div>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
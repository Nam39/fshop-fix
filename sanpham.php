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
    $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
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
    $stmt->bind_param("iii", $currentCategory, $limit, $offset);
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
    <title>Sản Phẩm | UNIQ</title>
    
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
            --hover-shadow: 0 15px 35px rgba(13, 110, 253, 0.08);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #334155;
        }

        /* Banner styling */
        .hero-carousel {
            margin-top: 88px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        }

        .hero-carousel img {
            width: 100%;
            height: 480px;
            object-fit: cover;
        }

        /* Category List Sidebar Styles */
        .sidebar-card {
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--card-radius);
            background: #ffffff;
            padding: 20px;
        }

        .category-link {
            transition: all 0.25s ease;
            font-size: 0.95rem;
            color: #475569 !important;
            font-weight: 500;
        }

        .category-link:hover {
            background-color: #f1f5f9;
            color: #0f172a !important;
            transform: translateX(4px);
        }

        .category-link.active-category {
            background-color: var(--primary-color) !important;
            color: #ffffff !important;
        }

        /* Custom Product Card Styles */
        .product-card {
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--card-radius);
            background: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--hover-shadow);
            border-color: rgba(13, 110, 253, 0.2);
        }

        .product-card:hover .product-img-zoom {
            transform: scale(1.08);
        }

        /* Pagination custom style */
        .page-link-custom {
            width: 40px !important;
            height: 40px !important;
            font-size: 0.95rem !important;
            transition: all 0.2s;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: 600 !important;
            padding: 0 !important;
            line-height: 1 !important;
            text-align: center !important;
        }

        @media (max-width: 768px) {
            .hero-carousel {
                margin-top: 80px;
                border-radius: 12px;
            }

            .hero-carousel img {
                height: 260px;
            }
            
            .sidebar-card {
                margin-bottom: 25px;
            }
        }
    </style>
</head>

<body>
    <div class="main">
        <!-- HEADER LAYOUT -->
        <?php include "./assets/layout/header/index.php" ?>

        <!-- 1. HERO CAROUSEL BANNER -->
        <section id="myCarousel" class="carousel slide bg-dark hero-carousel container" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>

            <div class="carousel-inner text-center">
                <div class="carousel-item active">
                    <img src="./assets/img/cat-item1.jpg" class="d-block mx-auto" alt="Slide 1">
                </div>
                <div class="carousel-item">
                    <img src="./assets/img/cat-item2.jpg" class="d-block mx-auto" alt="Slide 2">
                </div>
                <div class="carousel-item">
                    <img src="./assets/img/cat-item3.jpg" class="d-block mx-auto" alt="Slide 3">
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
        </section>

        <!-- 2. MAIN CATALOG BODY -->
        <div class="container mt-5">
            <div class="row">
                <!-- Left Sidebar: Category Filters -->
                <div class="col-lg-3 col-md-4">
                    <div class="sidebar-card shadow-sm">
                        <h5 class="fw-bold mb-3 text-dark border-bottom pb-2" style="font-size: 1.05rem;"><i class="fa-solid fa-list-ul text-primary me-2"></i>Danh mục</h5>
                        <ul class="nav flex-column gap-2" style="padding: 0;">
                            <li class="nav-item">
                                <a href="sanpham.php" class="nav-link category-link d-flex justify-content-between align-items-center rounded-pill px-3 py-2 <?= $currentCategory == 0 ? 'active-category text-white' : '' ?>">
                                    <span>📂 Tất cả sản phẩm</span>
                                </a>
                            </li>
                            <?php foreach ($categories as $dm): 
                                $isActive = ($currentCategory == $dm['id_DanhMuc']);
                            ?>
                                <li class="nav-item">
                                    <a href="?danhmuc=<?= $dm['id_DanhMuc'] ?>" class="nav-link category-link d-flex justify-content-between align-items-center rounded-pill px-3 py-2 <?= $isActive ? 'active-category text-white' : '' ?>">
                                        <span class="text-truncate"><?= htmlspecialchars($dm['Ten_DanhMuc']) ?></span>
                                        <span class="badge rounded-pill <?= $isActive ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary' ?>"><?= $dm['total'] ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Right Body: Grid Products Listing -->
                <div class="col-lg-9 col-md-8">
                    <!-- Dynamic Search / Filter Information Header Banner -->
                    <?php if (!empty($search)): ?>
                        <div class="alert bg-primary bg-opacity-10 border-0 rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between">
                            <span class="fw-semibold text-dark"><i class="fa-solid fa-magnifying-glass text-primary me-2"></i>Kết quả tìm kiếm cho: "<strong class="text-primary"><?= htmlspecialchars($search) ?></strong>"</span>
                            <span class="badge bg-primary px-3 py-2 rounded-pill"><?= $totalProducts ?> sản phẩm</span>
                        </div>
                    <?php elseif ($currentCategory > 0): 
                        $catName = "";
                        foreach ($categories as $dm) {
                            if ($dm['id_DanhMuc'] == $currentCategory) {
                                $catName = $dm['Ten_DanhMuc'];
                                break;
                            }
                        }
                    ?>
                        <div class="alert bg-primary bg-opacity-10 border-0 rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between">
                            <span class="fw-semibold text-dark"><i class="fa-solid fa-folder-open text-primary me-2"></i>Danh mục: <strong class="text-primary"><?= htmlspecialchars($catName) ?></strong></span>
                            <span class="badge bg-primary px-3 py-2 rounded-pill"><?= $totalProducts ?> sản phẩm</span>
                        </div>
                    <?php endif; ?>

                    <!-- Products Grid -->
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="col">
                                    <div class="card h-100 product-card shadow-sm">
                                        <a href="./detail.php?id=<?= $row['id'] ?>" class="overflow-hidden position-relative d-block" style="border-radius: 14px 14px 0 0; height: 200px;">
                                            <img src="./assets/img/<?= $row['Anh'] ?>" class="product-img-zoom w-100 h-100" style="object-fit: cover; transition: transform 0.3s ease;" alt="Hình ảnh sản phẩm">
                                        </a>
                                        <div class="card-body d-flex flex-column justify-content-between p-3">
                                            <div>
                                                <h6 class="card-title fw-bold text-dark text-truncate mb-1"><?= htmlspecialchars($row['Ten']) ?></h6>
                                                <p class="card-text text-secondary small description-clamp mb-2"><?= htmlspecialchars($row['MoTa']) ?></p>
                                            </div>
                                            <div>
                                                <p class="text-danger fw-extrabold mb-3" style="font-size: 1.05rem;"><?= number_format($row['Gia'], 0, ',', '.') ?> <span style="font-size: 0.8rem;">VNĐ</span></p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-dark flex-grow-1 rounded-pill fw-bold text-nowrap py-2" style="font-size: 0.8rem;">
                                                        Chi tiết
                                                    </a>
                                                    <form action="themvaogio.php" method="POST" class="m-0">
                                                        <input type="hidden" name="idsanpham" value="<?= $row['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-success d-flex justify-content-center align-items-center rounded-circle" style="width: 36px; height: 36px; flex-shrink: 0; padding: 0;">
                                                            <i class="fa-solid fa-cart-plus"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 my-5 text-center w-100">
                                <div class="p-5 bg-white rounded-4 shadow-sm border border-light-subtle">
                                    <i class="fa-solid fa-magnifying-glass fa-3x text-muted mb-3"></i>
                                    <h5 class="text-secondary fw-bold">Không tìm thấy sản phẩm nào</h5>
                                    <p class="text-muted mb-0">Vui lòng thử tìm kiếm với từ khóa khác hoặc chuyển danh mục sản phẩm.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 3. PREMIUM PILL PAGINATION -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-5 d-flex justify-content-center" aria-label="Product navigation">
                    <ul class="pagination gap-2 border-0 m-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): 
                            $link = "?page=$i";
                            if ($currentCategory > 0) {
                                $link .= "&danhmuc=$currentCategory";
                            }
                            if (!empty($search)) {
                                $link .= "&query=" . urlencode($search);
                            }
                            $isCurrent = ($page == $i);
                        ?>
                            <li class="page-item <?= $isCurrent ? 'active' : '' ?>">
                                <a class="page-link page-link-custom border-light-subtle shadow-sm <?= $isCurrent ? 'bg-primary text-white border-primary' : 'bg-white text-secondary hover-bg-light' ?>" href="<?= $link ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

        <!-- FOOTER LAYOUT -->
        <?php include "./assets/layout/footer/index.php" ?>
    </div>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
$conn->close();
?>
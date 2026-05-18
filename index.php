<?php
session_start();
include "./connect_DB/connect_db.php";

$conn = connectData();

$categories = [];
$categoryResult = $conn->query("SELECT * FROM danhmucsanpham ORDER BY id_DanhMuc ASC LIMIT 4");
if ($categoryResult) {
    $categories = $categoryResult->fetch_all(MYSQLI_ASSOC);
}

$latestProducts = [];
$productResult = $conn->query("SELECT * FROM sanpham ORDER BY id DESC LIMIT 10");
if ($productResult) {
    $latestProducts = $productResult->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ | UNIQ</title>
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/fonts/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --card-radius: 16px;
            --soft-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
        }

        body {
            background: #fff;
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color);
        }

        .hero-carousel {
            margin-top: 88px;
        }

        .hero-carousel img {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .section-heading p {
            max-width: 520px;
        }

        .category-card,
        .product-card {
            border: 1px solid #e7e7e7;
            border-radius: var(--card-radius);
            background: #fff;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .category-card:hover,
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--soft-shadow);
            border-color: rgba(0, 123, 255, 0.25);
        }

        .category-card {
            min-height: 130px;
        }

        .category-card i {
            color: var(--primary-color);
        }

        .product-card {
            height: 100%;
            padding: 12px;
        }

        .product-card img {
            height: 180px;
            object-fit: cover;
        }

        .product-marquee {
            overflow: hidden;
            position: relative;
            padding: 10px 0 24px;
        }

        .product-marquee::before,
        .product-marquee::after {
            content: "";
            position: absolute;
            top: 0;
            width: 72px;
            height: 100%;
            z-index: 2;
            pointer-events: none;
        }

        .product-marquee::before {
            left: 0;
            background: linear-gradient(90deg, #fff 0%, rgba(255, 255, 255, 0) 100%);
        }

        .product-marquee::after {
            right: 0;
            background: linear-gradient(270deg, #fff 0%, rgba(255, 255, 255, 0) 100%);
        }

        .product-marquee-track {
            display: flex;
            gap: 1.5rem;
            width: max-content;
            animation: productMarquee 36s linear infinite;
        }

        .product-marquee:hover .product-marquee-track {
            animation-play-state: paused;
        }

        .marquee-product {
            flex: 0 0 260px;
            max-width: 260px;
        }

        @keyframes productMarquee {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        @media (max-width: 768px) {
            .hero-carousel img {
                height: 320px;
            }

            .section-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .product-marquee::before,
            .product-marquee::after {
                width: 32px;
            }

            .marquee-product {
                flex-basis: 220px;
                max-width: 220px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .product-marquee-track {
                animation: none;
                overflow-x: auto;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include "./assets/layout/header/index.php"; ?>

    <main>
        <section id="myCarousel" class="carousel slide bg-dark mb-4 hero-carousel" data-bs-ride="carousel" aria-label="Banner trang chủ">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>

            <div class="carousel-inner text-center">
                <div class="carousel-item active">
                    <img src="./assets/img/post-large-image1.jpg" class="d-block mx-auto" alt="Bộ sưu tập thời trang mới">
                </div>
                <div class="carousel-item">
                    <img src="./assets/img/post-large-image3.jpg" class="d-block mx-auto" alt="Sản phẩm nổi bật của UNIQ">
                </div>
                <div class="carousel-item">
                    <img src="./assets/img/post-large-image2.jpg" class="d-block mx-auto" alt="Ưu đãi mua sắm mới nhất">
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

        <section class="container mt-5" aria-labelledby="category-title">
            <div class="section-heading">
                <div>
                    <h3 id="category-title" class="mb-1">Danh mục sản phẩm</h3>
                    <p class="text-muted mb-0">Chọn nhanh danh mục bạn quan tâm để xem các sản phẩm phù hợp.</p>
                </div>
                <a href="sanpham.php" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
            </div>

            <?php if (!empty($categories)): ?>
                <div class="row g-3">
                    <?php foreach ($categories as $category): ?>
                        <div class="col-6 col-md-3">
                            <a href="sanpham.php?danhmuc=<?= $category['id_DanhMuc'] ?>" class="text-decoration-none text-dark">
                                <article class="card category-card text-center h-100">
                                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                        <i class="fas fa-tag fa-2x mb-3"></i>
                                        <h5 class="card-title mb-0"><?= htmlspecialchars($category['Ten_DanhMuc']) ?></h5>
                                    </div>
                                </article>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Chưa có danh mục sản phẩm để hiển thị.</div>
            <?php endif; ?>
        </section>

        <section class="container mt-5" aria-labelledby="latest-products-title">
            <div class="section-heading">
                <div>
                    <h3 id="latest-products-title" class="mb-1">Sản phẩm mới nhất</h3>
                    <p class="text-muted mb-0">Sản phẩm được sắp xếp từ mới đến cũ và tự động lướt từ phải sang trái.</p>
                </div>
                <a href="sanpham.php" class="btn btn-primary btn-sm">Mua sắm ngay</a>
            </div>

            <?php if (!empty($latestProducts)): ?>
                <div class="product-marquee" aria-label="Danh sách sản phẩm mới nhất lướt ngang">
                    <div class="product-marquee-track">
                        <?php for ($loop = 0; $loop < 2; $loop++): ?>
                            <?php foreach ($latestProducts as $product): ?>
                                <div class="marquee-product">
                                    <article class="product-card mb-4">
                                        <img src="./assets/img/<?= htmlspecialchars($product['Anh']) ?>" class="w-100 rounded mb-3" alt="<?= htmlspecialchars($product['Ten']) ?>">
                                        <h5 class="text-truncate mb-2"><?= htmlspecialchars($product['Ten']) ?></h5>
                                        <p class="text-danger fw-semibold mb-3"><?= number_format($product['Gia'], 0, ',', '.') ?> VNĐ</p>
                                        <a href="detail.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary w-100">Xem chi tiết</a>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Chưa có sản phẩm để hiển thị.</div>
            <?php endif; ?>
        </section>
    </main>

    <?php include "./assets/layout/footer/index.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

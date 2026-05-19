<?php
session_start();
include "./connect_DB/connect_db.php";

$conn = connectData();

// Fetch active categories (up to 4)
$categories = [];
$categoryResult = $conn->query("SELECT * FROM danhmucsanpham ORDER BY id_DanhMuc ASC LIMIT 4");
if ($categoryResult) {
    $categories = $categoryResult->fetch_all(MYSQLI_ASSOC);
}

// Fetch latest products
$latestProducts = [];
$productResult = $conn->query("SELECT * FROM sanpham ORDER BY id DESC LIMIT 10");
if ($productResult) {
    $latestProducts = $productResult->fetch_all(MYSQLI_ASSOC);
}

// Fetch active vouchers for home promo highlight
$promoVouchers = [];
$promoResult = $conn->query("SELECT * FROM voucher WHERE trang_thai = 1 AND ngay_het_han >= CURDATE() AND so_luong > 0 ORDER BY id_voucher DESC LIMIT 2");
if ($promoResult) {
    $promoVouchers = $promoResult->fetch_all(MYSQLI_ASSOC);
}

// Helper function to map category names to gorgeous icons and gradients
function getCategoryIcon($catName) {
    $name = mb_strtolower($catName, 'UTF-8');
    if (strpos($name, 'áo') !== false || strpos($name, 'quần') !== false || strpos($name, 'thời trang') !== false || strpos($name, 'nam') !== false || strpos($name, 'nữ') !== false) {
        return ['icon' => 'fa-solid fa-shirt', 'color' => 'linear-gradient(135deg, #0d6efd, #0dcaf0)'];
    }
    if (strpos($name, 'giày') !== false || strpos($name, 'dép') !== false || strpos($name, 'sản phẩm') !== false) {
        return ['icon' => 'fa-solid fa-shoe-prints', 'color' => 'linear-gradient(135deg, #6f42c1, #d63384)'];
    }
    if (strpos($name, 'phụ kiện') !== false || strpos($name, 'kính') !== false || strpos($name, 'đồng hồ') !== false || strpos($name, 'ví') !== false) {
        return ['icon' => 'fa-solid fa-clock', 'color' => 'linear-gradient(135deg, #fd7e14, #ffc107)'];
    }
    return ['icon' => 'fa-solid fa-tags', 'color' => 'linear-gradient(135deg, #198754, #20c997)'];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ | UNIQ</title>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/fonts/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --dark-color: #0f172a;
            --card-radius: 20px;
            --soft-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            --hover-shadow: 0 15px 35px rgba(13, 110, 253, 0.08);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #f8fafc;
            color: #334155;
        }

        .navbar-brand {
            font-weight: 800;
            color: var(--primary-color);
            letter-spacing: 0.5px;
        }

        /* Hero Carousel styling */
        .hero-carousel {
            margin-top: 88px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--soft-shadow);
        }

        .hero-carousel img {
            width: 100%;
            height: 520px;
            object-fit: cover;
            filter: brightness(0.95);
        }

        /* Section Headings */
        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .section-heading h3 {
            font-weight: 700;
            color: var(--dark-color);
        }

        /* Cards and Hover effects */
        .category-card {
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--card-radius);
            background: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .category-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--hover-shadow);
            border-color: rgba(13, 110, 253, 0.2);
        }

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

        /* Product Marquee Horizontal Slide */
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
            width: 80px;
            height: 100%;
            z-index: 2;
            pointer-events: none;
        }

        .product-marquee::before {
            left: 0;
            background: linear-gradient(90deg, #f8fafc 0%, rgba(248, 250, 252, 0) 100%);
        }

        .product-marquee::after {
            right: 0;
            background: linear-gradient(270deg, #f8fafc 0%, rgba(248, 250, 252, 0) 100%);
        }

        .product-marquee-track {
            display: flex;
            gap: 1.75rem;
            width: max-content;
            animation: productMarquee 38s linear infinite;
        }

        .product-marquee:hover .product-marquee-track {
            animation-play-state: paused;
        }

        .marquee-product {
            flex: 0 0 270px;
            max-width: 270px;
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
            .hero-carousel {
                margin-top: 80px;
                border-radius: 12px;
            }

            .hero-carousel img {
                height: 300px;
            }

            .section-heading {
                align-items: flex-start;
                flex-direction: column;
                gap: 0.75rem;
            }

            .product-marquee::before,
            .product-marquee::after {
                width: 32px;
            }

            .marquee-product {
                flex-basis: 230px;
                max-width: 230px;
            }
        }
    </style>
</head>

<body>
    <!-- HEADER LAYOUT -->
    <?php include "./assets/layout/header/index.php"; ?>

    <main class="container py-3">
        <!-- 1. HERO CAROUSEL -->
        <section id="myCarousel" class="carousel slide bg-dark hero-carousel" data-bs-ride="carousel" aria-label="Banner trang chủ">
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

        <!-- 2. TRUST ADVANTAGES BADGES -->
        <section class="mt-5">
            <div class="row g-3 justify-content-center">
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center p-3 rounded-4 bg-white shadow-sm border border-light-subtle h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="fa-solid fa-truck-fast fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Vận Chuyển Nhanh</h6>
                            <span class="text-secondary small">Đơn hàng từ 500.000đ</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center p-3 rounded-4 bg-white shadow-sm border border-light-subtle h-100">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="fa-solid fa-arrows-rotate fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Đổi Trả Dễ Dàng</h6>
                            <span class="text-secondary small">Miễn phí trong 7 ngày</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center p-3 rounded-4 bg-white shadow-sm border border-light-subtle h-100">
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="fa-solid fa-headset fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Tư Vấn Tận Tâm</h6>
                            <span class="text-secondary small">Hỗ trợ trực tuyến 24/7</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center p-3 rounded-4 bg-white shadow-sm border border-light-subtle h-100">
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="fa-solid fa-shield-halved fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Thanh Toán An Toàn</h6>
                            <span class="text-secondary small">Bảo mật giao dịch 100%</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. CATEGORIES SECTION -->
        <section class="mt-5" aria-labelledby="category-title">
            <div class="section-heading">
                <div>
                    <h3 id="category-title" class="mb-1"><i class="fa-solid fa-border-all text-primary me-2"></i>Danh mục sản phẩm</h3>
                    <p class="text-secondary mb-0">Khám phá các bộ sưu tập thời trang được yêu thích nhất.</p>
                </div>
                <a href="sanpham.php" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-bold">Xem tất cả</a>
            </div>

            <?php if (!empty($categories)): ?>
                <div class="row g-3">
                    <?php foreach ($categories as $category): 
                        $catDetails = getCategoryIcon($category['Ten_DanhMuc']);
                    ?>
                        <div class="col-6 col-md-3">
                            <a href="sanpham.php?danhmuc=<?= $category['id_DanhMuc'] ?>" class="text-decoration-none">
                                <article class="card category-card text-center h-100">
                                    <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                                        <div class="rounded-circle mb-3 d-flex align-items-center justify-content-center text-white" style="width: 60px; height: 60px; background: <?= $catDetails['color'] ?>; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                                            <i class="<?= $catDetails['icon'] ?> fs-4"></i>
                                        </div>
                                        <h6 class="card-title mb-0 fw-bold text-dark"><?= htmlspecialchars($category['Ten_DanhMuc']) ?></h6>
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

        <!-- 4. DYNAMIC VOUCHER HIGHLIGHTS BANNER -->
        <?php if (!empty($promoVouchers)): ?>
        <section class="mt-5">
            <div class="p-4 p-md-5 rounded-4 text-white shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
                <div class="position-absolute" style="top: -50px; right: -50px; width: 220px; height: 220px; background: rgba(13, 110, 253, 0.18); filter: blur(60px); border-radius: 50%;"></div>
                <div class="position-absolute" style="bottom: -50px; left: -50px; width: 180px; height: 180px; background: rgba(214, 51, 132, 0.12); filter: blur(50px); border-radius: 50%;"></div>
                
                <div class="row align-items-center position-relative z-1">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <span class="badge bg-danger mb-3 px-3 py-2 fs-6 rounded-pill"><i class="fa-solid fa-fire-flame-simple me-2"></i>SIÊU ƯU ĐÃI ĐỘC QUYỀN</span>
                        <h2 class="fw-bold mb-2 text-white">Săn Voucher Giảm Giá Cực Sốc!</h2>
                        <p class="text-secondary mb-0">Thu thập các mã giảm giá đặc biệt ngay để nhận ưu đãi chiết khấu trực tiếp trên đơn hàng mua sắm thời trang.</p>
                    </div>
                    <div class="col-lg-6">
                        <div class="row g-3">
                            <?php foreach ($promoVouchers as $pv): ?>
                                <div class="col-md-6">
                                    <div class="bg-white text-dark rounded-4 p-3 d-flex flex-column justify-content-between position-relative shadow-sm border border-secondary-subtle" style="min-height: 125px;">
                                        <!-- miniature punched holes -->
                                        <div class="position-absolute bg-dark rounded-circle" style="left: -8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; background-color: #151e2e;"></div>
                                        <div class="position-absolute bg-dark rounded-circle" style="right: -8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; background-color: #151e2e;"></div>
                                        
                                        <div class="d-flex justify-content-between align-items-start mb-2 px-2">
                                            <div>
                                                <h5 class="fw-extrabold mb-0 text-danger" style="font-size: 1.25rem;">
                                                    <?= $pv['loai_giam'] == 0 ? 'Giảm ' . number_format($pv['giam_gia']/1000) . 'K' : 'Giảm ' . $pv['giam_gia'] . '%' ?>
                                                </h5>
                                                <span class="text-secondary small">Mã: <strong class="font-monospace text-primary fs-6"><?= htmlspecialchars($pv['ma_code']) ?></strong></span>
                                            </div>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small px-2 py-0.5" style="font-size: 0.75rem;">Còn <?= $pv['so_luong'] ?> lượt</span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-2 px-2 border-top pt-2">
                                            <span class="text-secondary small font-monospace">Hạn: <?= date('d/m', strtotime($pv['ngay_het_han'])) ?></span>
                                            <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 py-1 copy-home-btn" data-code="<?= htmlspecialchars($pv['ma_code']) ?>">
                                                <i class="fa-regular fa-copy me-1"></i> Lưu mã
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- 5. LATEST PRODUCTS SHOWCASE -->
        <section class="mt-5 mb-5" aria-labelledby="latest-products-title">
            <div class="section-heading">
                <div>
                    <h3 id="latest-products-title" class="mb-1"><i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i>Sản phẩm mới nhất</h3>
                    <p class="text-secondary mb-0">Hàng mới về tay, nhanh tay sở hữu những trang phục sành điệu nhất.</p>
                </div>
                <a href="sanpham.php" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold">Mua sắm ngay</a>
            </div>

            <?php if (!empty($latestProducts)): ?>
                <div class="product-marquee" aria-label="Danh sách sản phẩm mới nhất lướt ngang">
                    <div class="product-marquee-track">
                        <?php for ($loop = 0; $loop < 2; $loop++): ?>
                            <?php foreach ($latestProducts as $product): ?>
                                <div class="marquee-product">
                                    <article class="product-card mb-4">
                                        <div class="position-relative overflow-hidden" style="border-radius: 14px; margin: 10px 10px 0;">
                                            <img src="./assets/img/<?= htmlspecialchars($product['Anh']) ?>" class="w-100 product-img-zoom mb-0" alt="<?= htmlspecialchars($product['Ten']) ?>" style="height: 200px; object-fit: cover; transition: transform 0.35s ease;">
                                            <span class="badge bg-primary position-absolute top-0 start-0 m-2 px-2.5 py-1.5 rounded-pill small fw-semibold"><i class="fa-solid fa-fire me-1"></i>New</span>
                                        </div>
                                        <div class="p-3">
                                            <h6 class="text-truncate mb-2 fw-bold text-dark"><?= htmlspecialchars($product['Ten']) ?></h6>
                                            <p class="text-danger fw-bold mb-3 fs-6"><?= number_format($product['Gia'], 0, ',', '.') ?> VNĐ</p>
                                            <a href="detail.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-dark w-100 rounded-pill fw-bold">Xem chi tiết</a>
                                        </div>
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

    <!-- TOAST NOTIFICATION CONTAINER -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11000">
        <div id="copyToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 10px; backdrop-filter: blur(10px);">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    <i class="fa-solid fa-circle-check me-2"></i> Đã sao chép mã giảm giá thành công!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- FOOTER LAYOUT -->
    <?php include "./assets/layout/footer/index.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Copy coupon directly to clipboard from Homepage
            document.querySelectorAll('.copy-home-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const code = this.getAttribute('data-code');
                    navigator.clipboard.writeText(code).then(() => {
                        // Trigger Bootstrap toast
                        const toastEl = document.getElementById('copyToast');
                        if (toastEl) {
                            const toast = new bootstrap.Toast(toastEl);
                            toast.show();
                        }
                        
                        // Micro interaction text transition
                        const originalHtml = this.innerHTML;
                        this.innerHTML = '<i class="fa-solid fa-check"></i> Đã lưu';
                        this.classList.remove('btn-dark');
                        this.classList.add('btn-success');
                        
                        setTimeout(() => {
                            this.innerHTML = originalHtml;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-dark');
                        }, 2000);
                    }).catch(err => {
                        alert("Không thể sao chép mã, vui lòng chép tay: " + code);
                    });
                });
            });
        });
    </script>
</body>

</html>

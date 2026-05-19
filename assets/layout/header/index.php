<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../../connect_DB/connect_db.php";
$conn = connectData();

$cart_count = 0;

if (isset($_SESSION['idtk'])) {
    $idtk = $_SESSION['idtk'];
    
    // Fetch user info
    $sql_user_header = "SELECT Ten_user, Anh_user FROM users WHERE idtk = ?";
    $stmt_user_header = $conn->prepare($sql_user_header);
    $stmt_user_header->bind_param("i", $idtk);
    $stmt_user_header->execute();
    $result_user_header = $stmt_user_header->get_result();
    $userInfo = $result_user_header->fetch_assoc();

    if ($userInfo) {
        $_SESSION['Ten_user'] = $userInfo['Ten_user'];
        $_SESSION['Anh_user'] = $userInfo['Anh_user'];
    }
    
    // Proactively count current user's cart items
    $sql_cart_count = "SELECT SUM(gh.soluong) AS total_items FROM giohang gh JOIN users u ON gh.iduser = u.iduser WHERE u.idtk = ?";
    $stmt_cart = $conn->prepare($sql_cart_count);
    if ($stmt_cart) {
        $stmt_cart->bind_param("i", $idtk);
        $stmt_cart->execute();
        $res_cart = $stmt_cart->get_result()->fetch_assoc();
        if ($res_cart && $res_cart['total_items'] > 0) {
            $cart_count = intval($res_cart['total_items']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/fonts/css/all.min.css" rel="stylesheet">

    <style>
        html a {
            text-decoration: none;
            color: #333;
        }
        
        /* Modern Glassmorphic Nav */
        .custom-navbar {
            z-index: 10000;
            top: 0;
            left: 0;
            right: 0;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02) !important;
        }

        .nav-link-item {
            font-weight: 500;
            color: #475569 !important;
            transition: all 0.2s ease;
            border-radius: 8px;
            padding: 8px 16px !important;
        }
        
        .nav-link-item:hover {
            color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.05);
        }

        .nav-link-item.active {
            color: #0d6efd !important;
            font-weight: 600;
        }

        .avatar-img {
            border: 2px solid rgba(13, 110, 253, 0.2);
            transition: transform 0.2s ease;
            object-fit: cover;
        }

        .user-img:hover .avatar-img {
            transform: scale(1.05);
        }

        /* Animated Dropdown Menu */
        .user-img:hover .box {
            display: block;
        }

        .box {
            margin-top: 10px;
            z-index: 100000;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: #ffffff;
            width: 220px;
            border-radius: 16px;
            display: none;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
            border: 1px solid rgba(226, 232, 240, 0.8);
            padding: 8px 0;
            height: auto !important;
            animation: dropdownFadeIn 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .box-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #1e293b;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .box-name:hover {
            color: #0d6efd;
        }

        .box a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            font-size: 0.9rem;
            color: #475569;
            font-weight: 500;
            transition: all 0.2s;
        }

        .box a:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .logoutbtn {
            color: #ef4444 !important;
        }

        .logoutbtn:hover {
            background-color: #fef2f2 !important;
        }

        .aff {
            position: relative;
        }

        /* Invisible bridge to prevent pointer flicker */
        .aff-child {
            position: absolute;
            left: 0;
            bottom: -15px;
            width: 100%;
            height: 20px;
            background: transparent;
        }

        .dropdown-divider {
            margin: 6px 0;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
        }

        @media (max-width: 991px) {
            .custom-navbar {
                padding: 12px 16px;
            }
            
            .box {
                position: static;
                width: 100%;
                box-shadow: none;
                border: none;
                background-color: rgba(248, 250, 252, 0.8);
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm position-fixed custom-navbar">
        <div class="container">
            <a class="navbar-brand fw-extrabold text-primary d-flex align-items-center" href="./index.php" style="font-size: 1.5rem;">
                <i class="fa-solid fa-gem me-2"></i>UNIQ<span class="text-dark">.</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link nav-link-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="./index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-item <?= basename($_SERVER['PHP_SELF']) == 'sanpham.php' ? 'active' : '' ?>" href="./sanpham.php">Sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-item text-danger fw-bold d-flex align-items-center" href="./voucher.php">
                            <i class="fa-solid fa-ticket me-2 fa-bounce"></i>Voucher
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-item d-flex align-items-center" href="./giohang.php">
                            <i class="fa-solid fa-cart-shopping me-2 text-secondary"></i>Giỏ hàng 
                            <?php if ($cart_count > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-2 d-inline-flex align-items-center justify-content-center" style="font-size: 0.72rem; min-width: 18px; height: 18px; padding: 0 5px; line-height: 1;"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>

                <!-- High-end search input with magnifying glass -->
                <form action="./sanpham.php" method="GET" class="d-flex me-lg-3 my-2 my-lg-0">
                    <div class="input-group">
                        <input class="form-control border-light-subtle rounded-start-pill bg-light bg-opacity-50 px-3" style="width: 220px; font-size: 0.9rem;" type="search" name="query" placeholder="Tìm sản phẩm..." aria-label="Search">
                        <button class="btn btn-primary rounded-end-pill px-3" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </form>

                <div class="d-flex align-items-center mt-2 mt-lg-0">
                    <?php if (isset($_SESSION['idtk'])): ?>
                        <div class="user-img aff d-flex align-items-center position-relative">
                            <img src="./assets/img/<?= $_SESSION['Anh_user'] ?>" alt="Avatar" class="rounded-circle avatar-img shadow-sm" width="38" height="38">
                            <p class="box-name px-2 d-flex align-items-center mb-0 ms-1 text-secondary">
                                <?= htmlspecialchars($_SESSION['Ten_user']) ?>
                                <i class="fa-solid fa-angle-down ms-1.5 small text-muted"></i>
                            </p>

                            <div class="box text-start">
                                <a href="./thongtinuser.php"><i class="fa-regular fa-user text-primary"></i>Thông tin cá nhân</a>
                                <a href="./donhang.php"><i class="fa-solid fa-box text-success"></i>Đơn hàng của tôi</a>
                                <a href="./doimatkhau.php"><i class="fa-solid fa-key text-warning"></i>Đổi mật khẩu</a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="logoutbtn" onclick="return confirm('Bạn có muốn đăng xuất khỏi tài khoản không?');">
                                    <i class="fa-solid fa-right-from-bracket"></i>Đăng xuất
                                </a>
                            </div>

                            <div class="aff-child"></div>
                        </div>
                    <?php else: ?>
                        <div class="gap-2 d-flex">
                            <a href="login.php" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-bold">Đăng nhập</a>
                            <a href="signup.php" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold">Đăng ký</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</body>
</html>
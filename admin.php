<?php
session_start();

include_once 'connect_DB/connect_db.php';
include_once 'assets/function/search/index.php';

if (!isset($_SESSION['idtk'])) {
    header("Location: ../login.php");
}

$conn = connectData();
if (isset($_SESSION['idtk'])) {
    $idtk = $_SESSION['idtk'];
    $sql = "SELECT Ten_user, Anh_user FROM users WHERE idtk = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idtk);
    $stmt->execute();
    $result = $stmt->get_result();
    $userInfo = $result->fetch_assoc();

    if ($userInfo) {
        $_SESSION['Ten_user'] = $userInfo['Ten_user'];
        $_SESSION['Anh_user'] = $userInfo['Anh_user'];
    }
}

$page_num = $_GET['p'] ?? 1;
$result = search($conn, 'taikhoan', ['username'], 'idtk', 10, $page_num);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --navbar-height: 56px;
            --sidebar-width: 220px;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: #0d6efd;
            color: white;
            padding: 1rem;
            z-index: 1030;
            transition: transform 0.3s ease;
        }

        .sidebar h4 {
            margin-bottom: 2rem;
        }

        .sidebar a {
            color: white;
            display: block;
            padding: 0.5rem 0;
            text-decoration: none;
        }

        .menu-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        /* Navbar */
        .navbar-custom {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--navbar-height);
            background-color: #0b5ed7;
            z-index: 1020;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            color: white;
        }

        /* Main content */
        .main-content {
            margin-top: calc(var(--navbar-height) + 1rem);
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .card-title {
            font-size: 1rem;
        }

        .chart-box {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            height: 300px;
        }

        .navbar-custom,
        .main-content {
            transition: all 0.3s ease;

        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1040;
                position: fixed;
            }

            /* Khi có class active thì hiện sidebar */
            .sidebar.active {
                transform: translateX(0);
            }
        }

        
        
    </style>
</head>

<body>

    <div class="sidebar" id="sidebar">
        <h4 class="text-center"><a href="./admin.php">Admin</a></h4>

        <hr class="bg-light">
        <a href="?page=qltk" class="menu-link"><i class="fa-solid fa-user me-1"></i>Tài khoản</a>
        <a href="?page=qLsp" class="menu-link"><i class="fa-brands fa-product-hunt me-1"></i>Quản lý sản phẩm</a>
        <a href="?page=qlnd" class="menu-link"><i class="fa-solid fa-user me-1"></i>Người dùng</a>
        <a href="?page=qldh" class="menu-link"><i class="fas fa-box me-1"></i>Đơn hàng</a>
        <a href="?page=qlvc" class="menu-link"><i class="fa-solid fa-ticket me-1"></i>Quản lý Voucher</a>
    </div>

    <div class="navbar-custom">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <button class="btn btn-outline-light me-3" id="toggleSidebar">☰</button>
            <div class="d-flex align-items-center">

                <i class="fa-solid fa-bell text-white me-3"></i>
                <?php if (isset($_SESSION['idtk'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle d-flex align-items-center text-white text-decoration-none p-0 border-0"
                            type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="./assets/img/<?= $_SESSION['Anh_user'] ?>" alt="Avatar" class="rounded-circle avatar-img me-2" width="40" height="40">
                            <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['Ten_user']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="./thongtin.php">Thông tin</a></li>
                            <li><a class="dropdown-item" href="#">Cài đặt</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php" onclick="return confirm('Bạn có muốn đăng xuất không?');">Đăng xuất</a></li>
                        </ul>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="container-fluid">
            <?php
            $page = $_GET['page'] ?? 'qltk';
            include $page . '.php';
            ?>
        </div>
    </div>


    <!-- Bootstrap JS -->
    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleButton = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const navbar = document.querySelector('.navbar-custom');
        const mainContent = document.querySelector('.main-content');

        toggleButton.addEventListener('click', function() {
            if (sidebar.classList.contains('d-none')) {
                sidebar.classList.remove('d-none');
                navbar.style.left = 'var(--sidebar-width)';
                mainContent.style.marginLeft = 'var(--sidebar-width)';
            } else {
                sidebar.classList.add('d-none');
                navbar.style.left = '0';
                mainContent.style.marginLeft = '0';
            }
        });
    </script>

</body>



</html>
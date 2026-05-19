<?php
session_start();

include_once 'connect_DB/connect_db.php';
include_once 'assets/function/search/index.php';

if (!isset($_SESSION['idtk']) || !isset($_SESSION['roleId']) || $_SESSION['roleId'] != 1) {
    header("Location: login.php");
    exit();
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

$active_page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | UNIQ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --navbar-height: 70px;
            --sidebar-width: 250px;
            --primary-color: #0d6efd;
            --dark-bg: #0f172a;
            --light-bg: #f8fafc;
            --border-color: rgba(226, 232, 240, 0.8);
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background-color: var(--light-bg);
            color: #334155;
        }

        /* Luxury Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: var(--dark-bg);
            color: #94a3b8;
            padding: 1.5rem 1.2rem;
            z-index: 1030;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .brand-logo {
            background-color: var(--primary-color);
            color: #ffffff;
            border-radius: 10px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        }

        .brand-name {
            font-weight: 800;
            color: #ffffff;
            font-size: 1.25rem;
            letter-spacing: 0.05em;
        }

        .menu-heading {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #475569;
            margin-bottom: 0.75rem;
            padding-left: 0.5rem;
        }

        .menu-link {
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 6px;
        }

        .menu-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            transform: translateX(4px);
        }

        .menu-link.active {
            background-color: var(--primary-color);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.25);
        }

        .menu-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Glassmorphic Navbar */
        .navbar-custom {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--navbar-height);
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            z-index: 1020;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Navbar Elements */
        .btn-toggle-custom {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            transition: all 0.2s;
        }

        .btn-toggle-custom:hover {
            background: #f1f5f9;
            color: #0f172a;
            transform: scale(1.05);
        }

        .notify-bell {
            font-size: 1.2rem;
            color: #64748b;
            cursor: pointer;
            transition: color 0.2s;
            position: relative;
        }

        .notify-bell:hover {
            color: #0f172a;
        }

        .avatar-img-custom {
            border: 2px solid #ffffff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
            object-fit: cover;
            border-radius: 50%;
        }

        .user-name-custom {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Custom Dropdown Menu */
        .dropdown-menu-custom {
            border-radius: 16px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            padding: 8px;
            min-width: 200px;
        }

        .dropdown-item-custom {
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 600;
            color: #475569;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dropdown-item-custom:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .dropdown-item-custom.text-danger:hover {
            background-color: rgba(239, 68, 68, 0.05);
        }

        /* Main Content container */
        .main-content {
            margin-top: var(--navbar-height);
            margin-left: var(--sidebar-width);
            padding: 2.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: calc(100vh - var(--navbar-height));
        }

        /* Collapsed Sidebar states */
        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .navbar-custom.wide {
            left: 0;
        }

        .main-content.wide {
            margin-left: 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .navbar-custom {
                left: 0;
                padding: 0 1rem;
            }

            .main-content {
                margin-left: 0;
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR WRAPPER -->
    <div class="sidebar" id="sidebar">
        
        <!-- BRAND BANNER -->
        <div class="sidebar-brand d-flex align-items-center gap-2">
            <div class="brand-logo">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="d-flex flex-column">
                <span class="brand-name">UNIQ</span>
                <span class="text-uppercase fw-bold text-primary" style="font-size: 0.65rem; letter-spacing: 0.15em;">Control Panel</span>
            </div>
        </div>

        <div class="menu-heading">Danh mục quản lý</div>

        <!-- NAVIGATION LINKS -->
        <a href="?page=dashboard" class="menu-link <?= $active_page === 'dashboard' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-line"></i> Tổng quan
        </a>
        <a href="?page=qltk" class="menu-link <?= $active_page === 'qltk' ? 'active' : '' ?>">
            <i class="fa-solid fa-users-gear"></i> Tài khoản
        </a>
        <a href="?page=qLsp" class="menu-link <?= $active_page === 'qLsp' ? 'active' : '' ?>">
            <i class="fa-solid fa-shirt"></i> Sản phẩm
        </a>
        <a href="?page=qlnd" class="menu-link <?= $active_page === 'qlnd' ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i> Khách hàng
        </a>
        <a href="?page=qldh" class="menu-link <?= $active_page === 'qldh' ? 'active' : '' ?>">
            <i class="fa-solid fa-boxes-packing"></i> Đơn hàng
        </a>
        <a href="?page=qlvc" class="menu-link <?= $active_page === 'qlvc' ? 'active' : '' ?>">
            <i class="fa-solid fa-ticket"></i> Khuyến mãi
        </a>
    </div>

    <!-- NAVBAR HEADER -->
    <div class="navbar-custom" id="navbar">
        <div class="container-fluid d-flex justify-content-between align-items-center p-0">
            
            <button class="btn btn-toggle-custom" id="toggleSidebar">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="d-flex align-items-center">
                <i class="fa-solid fa-bell notify-bell me-4"></i>
                
                <?php if (isset($_SESSION['idtk'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle d-flex align-items-center text-decoration-none p-0 border-0"
                            type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="./assets/img/<?= htmlspecialchars($_SESSION['Anh_user']) ?>" alt="Avatar" class="avatar-img-custom me-2" width="40" height="40">
                            <span class="d-none d-md-inline user-name-custom"><?= htmlspecialchars($_SESSION['Ten_user']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom shadow" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="./index.php" target="_blank">
                                    <i class="fa-solid fa-store text-secondary"></i> Xem cửa hàng
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom text-danger fw-bold" href="logout.php" onclick="return confirm('Bạn có muốn đăng xuất không?');">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- CONTENT WRAPPER -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid p-0">
            <?php
            $page = $_GET['page'] ?? 'dashboard';
            include $page . '.php';
            ?>
        </div>
    </div>

    <script>
        const toggleButton = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const navbar = document.getElementById('navbar');
        const mainContent = document.getElementById('mainContent');

        toggleButton.addEventListener('click', function() {
            // Check desktop width collapsible or mobile modal-like popup
            if (window.innerWidth > 768) {
                sidebar.classList.toggle('collapsed');
                navbar.classList.toggle('wide');
                mainContent.classList.toggle('wide');
            } else {
                sidebar.classList.toggle('active');
            }
        });
        
        // Auto-close sidebar on mobile if clicked outside
        document.addEventListener('click', function(event) {
            const isClickInside = sidebar.contains(event.target) || toggleButton.contains(event.target);
            if (!isClickInside && sidebar.classList.contains('active') && window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        });
    </script>

</body>
</html>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "./connect_DB/connect_db.php";
$conn = connectData();

if (isset($_SESSION['idtk'])) {
    $idtk = $_SESSION['idtk'];
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
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

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

        .aff-child {
            pointer-events: none;
        }

        .aff-child::after {
            content: "";
            position: absolute;
            left: 0;
            top: 14px;
            background-color: transparent;
            width: 100%;
            height: 30px;
            pointer-events: none;
        }

        .dropdown-divider {
            margin: 0.3rem 0;
            border-top: 1px solid rgb(65, 67, 75);
        }
    </style>

</head>

<body>
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

                <form action="./sanpham.php" method="GET" class="d-flex me-3">
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
                                <a href="logout.php" class="logoutbtn" onclick="return confirm('Bạn có muốn đăng xuất không?');">Đăng xuất</a>
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


</body>

</html>
<?php
include "./connect_DB/connect_db.php";

$conn = connectData();

$limit = 12;
$page = isset($_GET['p']) && is_numeric($_GET['p']) && $_GET['p'] > 0 ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

$search = '';
$whereClause = '';
$params = [];
$types = '';
$queryString = '';

if (!empty($_GET['queryidnd'])) {
    $search = trim($_GET['queryidnd']);
    $whereClause = "WHERE id LIKE ?";
    $params = ["%$search%"];
    $types = "s";
    $queryString = '&queryid=' . urlencode($search);
}

$countSql = "SELECT COUNT(*) AS total FROM donhang $whereClause";
$stmt = $conn->prepare($countSql);
if (!empty($whereClause)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultCount = $stmt->get_result();
$totalRow = $resultCount->fetch_assoc();
$tongdonhang = $totalRow['total'];
$totalPages = ceil($tongdonhang / $limit);

$sql = "SELECT * FROM donhang $whereClause LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
if (!empty($whereClause)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mb-5 mt-4">
        <h2 class="text-center mb-4">Quản lý đơn hàng</h2>

        <!-- Search -->
        <div class="row g-2 mb-4">
            <div class="mb-3 ">
                <div class="d-flex flex-wrap">
                    <!-- <div class="col-12 col-md-6 mb-2">
                        <form action="" method="GET" class="d-flex me-3" style="max-width: 400px;">
                            <input type="text" name="query" class="form-control me-2" placeholder="Tìm kiếm..." style="width: 250px;">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </form>
                    </div> -->
                    <div class="col-12 col-md-6">
                        <form action="" method="GET" class="d-flex" style="max-width: 400px;">
                            <input type="text" name="queryidnd" class="form-control me-2" placeholder="Tìm kiếm theo ID..." style="width: 150px;">
                            <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Tìm theo ID</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <!-- User Table -->
        <div class="table-responsive">
            <table class="table table-hover border align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>ID khach hang</th>
                        <th>Ngay dat hang</th>
                        <th>Trang thai</th>
                        <th>Sửa</th>
                        <th>Xóa</th>

                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['idDonHang'] ?></td>
                                <td><?= $row['idKhach'] ?></td>
                                <td><?= $row['ngaydathang'] ?></td>
                                <td><?= $row['trangthai'] ?></td>
                                <td><a href="./ad/suasanpham.php?id=<?= $row['idDonHang'] ?>" class="btn btn-warning btn-sm">Sửa</a></td>
                                    
                                <td><a href="./ad/xoadonhang.php?idDonHang=<?= $row['idDonHang'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa không?');">Xóa</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">Không tìm thấy người dùng nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        include "./assets/layout/navigation/navigation.php"
        ?>
    </div>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
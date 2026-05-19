<?php
include "./connect_DB/connect_db.php";

$conn = connectData();

$sql = "SELECT * FROM danhmucsanpham";
$result = $conn->query($sql);

$limit = 8;
$page = isset($_GET['p']) && is_numeric($_GET['p']) && $_GET['p'] > 0 ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

if (isset($_GET['queryid']) && !empty($_GET['queryid'])) {
    $search = trim($_GET['queryid']);
    
    // Calculate total pages for this search query
    $countSql = "SELECT COUNT(*) AS total FROM sanpham WHERE Ten LIKE ?";
    $stmtCount = $conn->prepare($countSql);
    $search_param = "%" . $search . "%";
    $stmtCount->bind_param("s", $search_param);
    $stmtCount->execute();
    $totalRow = $stmtCount->get_result()->fetch_assoc();
    $totalProducts = $totalRow['total'];
    $stmtCount->close();

    $sql = "SELECT * FROM sanpham WHERE Ten LIKE ? LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Normal total count
    $totalQuery = "SELECT COUNT(*) AS total FROM sanpham";
    $totalResult = $conn->query($totalQuery);
    $totalRow = $totalResult->fetch_assoc();
    $totalProducts = $totalRow['total'];

    $sql = "SELECT * FROM sanpham LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
}
$totalPages = ceil($totalProducts / $limit);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mb-5 mt-4">
        <h2 class="text-center mb-4">Quản lý sản phẩm</h2>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <form action="" method="GET" class="d-flex align-items-center" style="max-width: 550px;">
                    <input type="hidden" name="page" value="qLsp">
                    <input type="text" name="queryid" class="form-control me-2" placeholder="Tìm theo tên sản phẩm..." style="width: 200px;" value="<?= isset($_GET['queryid']) ? htmlspecialchars($_GET['queryid']) : '' ?>">
                    <button type="submit" class="btn btn-secondary me-2"><i class="fa-solid fa-magnifying-glass"></i> Tìm theo tên</button>
                    <a href="admin.php?page=qLsp" class="btn btn-outline-secondary"><i class="fa-solid fa-arrows-rotate"></i> Làm mới</a>
                </form>
            </div>
            <div>
                <a href="./ad/themsanpham.php" class="btn btn-success">
                    <i class="fa-solid fa-circle-plus"></i> Thêm sản phẩm
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover border align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Mô Tả</th>
                        <th>Số Lượng</th>
                        <th>Ảnh</th>
                        <th>Giá</th>
                        <th>ID Danh Mục</th>
                        <th>Sửa</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['Ten'] ?></td>
                                <td><?= $row['MoTa'] ?></td>
                                <td><?= $row['soluong'] ?></td>
                                <td><img src="./assets/img/<?= $row['Anh'] ?>" class="img-fluid" style="width: 60px;"></td>
                                <td><?= number_format($row['Gia'], 0, ',', '.') ?> VNĐ</td>
                                <td><?= $row['id_DanhMuc'] ?></td>
                                <td><a href="./ad/suasanpham.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Sửa</a></td>
                                <td><a href="./ad/xoasanpham.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa không?');">Xóa</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">Không tìm thấy sản phẩm nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <?php
        include "./assets/layout/navigation/navigation.php"
        ?>
    </div>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
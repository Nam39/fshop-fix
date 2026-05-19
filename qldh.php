<?php
include "./connect_DB/connect_db.php";

$conn = connectData();

$limit = 8;
$page = isset($_GET['p']) && is_numeric($_GET['p']) && $_GET['p'] > 0 ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

$search = '';
$whereClause = '';
$params = [];
$types = '';
$queryString = '';

if (!empty($_GET['queryidnd'])) {
    $search = trim($_GET['queryidnd']);
    $whereClause = "WHERE idDonHang LIKE ?";
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

$sql = "
    SELECT dh.*, u.Ten_user 
    FROM donhang dh 
    LEFT JOIN users u ON dh.idKhach = u.iduser 
    $whereClause 
    LIMIT $limit OFFSET $offset
";
$stmt = $conn->prepare($sql);
if (!empty($whereClause)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

function getStatusBadge($status) {
    switch ($status) {
        case 0:
            return '<span class="badge bg-secondary px-2 py-1">Đã đặt hàng</span>';
        case 1:
            return '<span class="badge bg-info text-dark px-2 py-1">Đã xác nhận</span>';
        case 2:
            return '<span class="badge bg-warning text-dark px-2 py-1">Đang giao</span>';
        case 3:
            return '<span class="badge bg-success px-2 py-1">Hoàn thành</span>';
        case 4:
            return '<span class="badge bg-danger px-2 py-1">Đã hủy</span>';
        default:
            return '<span class="badge bg-dark px-2 py-1">Không rõ</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
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
                    <div class="col-12 col-md-6">
                        <form action="" method="GET" class="d-flex" style="max-width: 450px;">
                            <input type="hidden" name="page" value="qldh">
                            <input type="text" name="queryidnd" class="form-control me-2" placeholder="Tìm theo mã đơn hàng..." style="width: 200px;">
                            <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Tìm mã ĐH</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <!-- Order Table -->
        <div class="table-responsive">
            <table class="table table-hover border align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Mã Đơn Hàng</th>
                        <th>Khách Hàng</th>
                        <th>Ngày Đặt Hàng</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Chi tiết / Sửa</th>
                        <th>Hủy / Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php 
                        $i = 1;
                        while ($row = $result->fetch_assoc()): 
                            $stt = $offset + $i;
                            $customer_display = !empty($row['Ten_user']) ? htmlspecialchars($row['Ten_user']) . ' (#' . $row['idKhach'] . ')' : 'Khách hàng #' . $row['idKhach'];
                        ?>
                            <tr>
                                <td><?= $stt ?></td>
                                <td><strong>#<?= $row['idDonHang'] ?></strong></td>
                                <td><?= $customer_display ?></td>
                                <td><?= htmlspecialchars($row['ngaydathang']) ?></td>
                                <td class="fw-bold text-danger"><?= number_format($row['tongtien'], 0, ',', '.') ?> VNĐ</td>
                                <td><?= getStatusBadge($row['trangthai']) ?></td>
                                <td><a href="./ad/suadonhang.php?id=<?= $row['idDonHang'] ?>" class="btn btn-warning btn-sm"><i class="fa-solid fa-pen-to-square"></i> Xem/Sửa</a></td>
                                <td><a href="./ad/xoadonhang.php?idDonHang=<?= $row['idDonHang'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này không?');"><i class="fa-solid fa-trash"></i> Xóa</a></td>
                            </tr>
                        <?php 
                            $i++;
                        endwhile; 
                        ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Không tìm thấy đơn hàng nào.</td>
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
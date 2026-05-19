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
            return '<span class="badge rounded-pill px-3 py-1.5 fw-bold text-warning border border-warning-subtle bg-warning-subtle">Chờ xử lý</span>';
        case 1:
            return '<span class="badge rounded-pill px-3 py-1.5 fw-bold text-primary border border-primary-subtle bg-primary-subtle">Đã xác nhận</span>';
        case 2:
            return '<span class="badge rounded-pill px-3 py-1.5 fw-bold text-info border border-info-subtle bg-info-subtle">Đang giao hàng</span>';
        case 3:
            return '<span class="badge rounded-pill px-3 py-1.5 fw-bold text-success border border-success-subtle bg-success-subtle">Đã hoàn thành</span>';
        case 4:
            return '<span class="badge rounded-pill px-3 py-1.5 fw-bold text-danger border border-danger-subtle bg-danger-subtle">Đã hủy bỏ</span>';
        default:
            return '<span class="badge rounded-pill px-3 py-1.5 fw-bold text-secondary border border-secondary-subtle bg-secondary-subtle">Không rõ</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng | UNIQ</title>
    
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
            --card-radius: 24px;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #334155;
        }

        .admin-container {
            margin-top: 50px;
            margin-bottom: 80px;
        }

        .admin-card {
            background: #ffffff;
            padding: 40px;
            border-radius: var(--card-radius);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .admin-title {
            font-weight: 800;
            color: var(--dark-color);
            font-size: 1.5rem;
        }

        .search-bar-custom {
            border-radius: 12px;
            padding: 10px 16px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .search-bar-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08);
        }

        /* Dashboard Table */
        .admin-table th {
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            padding: 14px 10px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .admin-table td {
            padding: 16px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }

        .mono-badge {
            font-family: monospace;
            font-weight: 700;
            font-size: 0.9rem;
            color: #475569;
            background-color: #f1f5f9;
            padding: 4px 8px;
            border-radius: 6px;
        }

        @media (max-width: 768px) {
            .admin-card {
                padding: 24px 16px;
            }
        }
    </style>
</head>

<body>
    
    <div class="container admin-container">

        <div class="admin-card shadow-sm">

            <!-- HEADER ACTIONS -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
                
                <h2 class="admin-title mb-0 d-flex align-items-center">
                    <i class="fa-solid fa-boxes-packing text-primary me-2"></i>Quản lý đơn hàng
                </h2>

                <div>
                    <a href="admin.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-arrow-left-long"></i> Trở về Tổng quan
                    </a>
                </div>

            </div>

            <!-- SEARCH AND FILTER PANEL -->
            <div class="mb-4 bg-light rounded-3 p-3">
                <form action="" method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="page" value="qldh">
                    <div class="col-sm-5 col-md-4">
                        <input type="text" name="queryidnd" class="form-control search-bar-custom" placeholder="Tìm theo mã đơn hàng..." value="<?= isset($_GET['queryidnd']) ? htmlspecialchars($_GET['queryidnd']) : '' ?>">
                    </div>
                    <div class="col-sm-auto d-flex gap-2">
                        <button type="submit" class="btn btn-secondary rounded-pill px-4 fw-bold"><i class="fa-solid fa-magnifying-glass me-2"></i>Tìm kiếm</button>
                        <a href="admin.php?page=qldh" class="btn btn-outline-secondary rounded-pill px-4 fw-bold"><i class="fa-solid fa-arrows-rotate me-2"></i>Làm mới</a>
                    </div>
                </form>
            </div>

            <!-- TABLE DATAGRID -->
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle text-center m-0">
                    
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã đơn hàng</th>
                            <th style="text-align: left;">Khách hàng</th>
                            <th>Ngày đặt hàng</th>
                            <th>Tổng thanh toán</th>
                            <th>Trạng thái</th>
                            <th style="width: 120px;">Chi tiết / Sửa</th>
                            <th style="width: 100px;">Hủy / Xóa</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php 
                            $i = 1;
                            while ($row = $result->fetch_assoc()): 
                                $stt = $offset + $i;
                                $customer_display = !empty($row['Ten_user']) ? htmlspecialchars($row['Ten_user']) . ' (#' . $row['idKhach'] . ')' : 'Khách hàng #' . $row['idKhach'];
                            ?>
                                <tr>
                                    <td>
                                        <span class="text-secondary small"><?= $stt ?></span>
                                    </td>
                                    <td>
                                        <span class="mono-badge">#<?= $row['idDonHang'] ?></span>
                                    </td>
                                    <td class="fw-bold text-dark text-start"><?= $customer_display ?></td>
                                    <td class="text-secondary small"><?= date("d/m/Y H:i", strtotime($row['ngaydathang'])) ?></td>
                                    <td class="fw-bold text-primary"><?= number_format($row['tongtien'], 0, ',', '.') ?> VNĐ</td>
                                    <td><?= getStatusBadge($row['trangthai']) ?></td>
                                    <td>
                                        <a href="./ad/suadonhang.php?id=<?= $row['idDonHang'] ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2">
                                            <i class="fa-solid fa-pen-to-square"></i> Xem/Sửa
                                        </a>
                                    </td>
                                    <td>
                                        <a href="./ad/xoadonhang.php?idDonHang=<?= $row['idDonHang'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2" onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này không?');">
                                            <i class="fa-solid fa-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                $i++;
                            endwhile; 
                            ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-circle-exclamation fa-2x mb-3 text-secondary opacity-50"></i>
                                    <h5 class="mb-0 text-slate-400">Không tìm thấy đơn hàng nào phù hợp.</h5>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

            <!-- PAGINATION BLOCK -->
            <?php include "./assets/layout/navigation/navigation.php" ?>

        </div>

    </div>

</body>

</html>
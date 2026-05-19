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
    <title>Quản lý sản phẩm | UNIQ</title>
    
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

        .mota-col {
            max-width: 240px;
            white-space: normal !important;
            word-wrap: break-word;
            text-align: left;
            font-size: 0.88rem;
            color: #64748b;
            line-height: 1.5;
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

        .product-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
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
                    <i class="fa-solid fa-shirt text-primary me-2"></i>Quản lý sản phẩm
                </h2>

                <div class="d-flex gap-2">
                    <a href="./ad/themsanpham.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-plus"></i> Thêm sản phẩm
                    </a>
                    <a href="admin.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-arrow-left-long"></i> Trở về Tổng quan
                    </a>
                </div>

            </div>

            <!-- SEARCH AND FILTER PANEL -->
            <div class="mb-4 bg-light rounded-3 p-3">
                <form action="" method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="page" value="qLsp">
                    <div class="col-sm-5 col-md-4">
                        <input type="text" name="queryid" class="form-control search-bar-custom" placeholder="Tìm theo tên sản phẩm..." value="<?= isset($_GET['queryid']) ? htmlspecialchars($_GET['queryid']) : '' ?>">
                    </div>
                    <div class="col-sm-auto d-flex gap-2">
                        <button type="submit" class="btn btn-secondary rounded-pill px-4 fw-bold"><i class="fa-solid fa-magnifying-glass me-2"></i>Tìm kiếm</button>
                        <a href="admin.php?page=qLsp" class="btn btn-outline-secondary rounded-pill px-4 fw-bold"><i class="fa-solid fa-arrows-rotate me-2"></i>Làm mới</a>
                    </div>
                </form>
            </div>

            <!-- TABLE DATAGRID -->
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle text-center m-0">
                    
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th style="text-align: left;">Tên sản phẩm</th>
                            <th style="text-align: left;">Mô tả sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Ảnh</th>
                            <th>Đơn giá</th>
                            <th>Danh mục</th>
                            <th style="width: 100px;">Sửa</th>
                            <th style="width: 100px;">Xóa</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="mono-badge">#<?= $row['id'] ?></span>
                                    </td>
                                    <td class="fw-bold text-dark text-start" style="white-space: normal; max-width: 180px;"><?= htmlspecialchars($row['Ten']) ?></td>
                                    <td class="mota-col"><?= htmlspecialchars($row['MoTa']) ?></td>
                                    <td class="fw-bold"><?= $row['soluong'] ?></td>
                                    <td>
                                        <img src="./assets/img/<?= htmlspecialchars($row['Anh']) ?>" class="product-thumb" alt="Product">
                                    </td>
                                    <td class="fw-bold text-primary"><?= number_format($row['Gia'], 0, ',', '.') ?> VNĐ</td>
                                    <td>
                                        <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-1.5 fw-bold border border-primary-subtle">Danh mục #<?= $row['id_DanhMuc'] ?></span>
                                    </td>
                                    <td>
                                        <a href="./ad/suasanpham.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </a>
                                    </td>
                                    <td>
                                        <a href="./ad/xoasanpham.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2" onclick="return confirm('Bạn có chắc muốn xóa không?');">
                                            <i class="fa-solid fa-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-circle-exclamation fa-2x mb-3 text-secondary opacity-50"></i>
                                    <h5 class="mb-0 text-slate-400">Không tìm thấy sản phẩm nào phù hợp.</h5>
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
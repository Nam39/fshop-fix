<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once 'connect_DB/connect_db.php';
$conn = connectData();

// Access control (Admin only)
if (!isset($_SESSION['idtk'])) {
    header("Location: login.php");
    exit();
}

$mess = "";
$messType = "success";

// ================= HANDLE ACTIONS =================

// 1. ADD VOUCHER
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_voucher'])) {
    $code = strtoupper(trim($_POST['ma_code']));
    $giam_gia = (int)$_POST['giam_gia'];
    $loai_giam = (int)$_POST['loai_giam'];
    $so_luong = (int)$_POST['so_luong'];
    $ngay_het_han = $_POST['ngay_het_han'];

    if (empty($code) || $giam_gia <= 0 || $so_luong < 0 || empty($ngay_het_han)) {
        $mess = "Vui lòng nhập đầy đủ thông tin hợp lệ!";
        $messType = "danger";
    } else {
        // Check duplicate code
        $checkStmt = $conn->prepare("SELECT id_voucher FROM voucher WHERE ma_code = ?");
        $checkStmt->bind_param("s", $code);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $mess = "Mã voucher này đã tồn tại!";
            $messType = "danger";
        } else {
            $insertStmt = $conn->prepare("INSERT INTO voucher (ma_code, giam_gia, loai_giam, so_luong, ngay_het_han, trang_thai) VALUES (?, ?, ?, ?, ?, 1)");
            $insertStmt->bind_param("siiis", $code, $giam_gia, $loai_giam, $so_luong, $ngay_het_han);
            if ($insertStmt->execute()) {
                $mess = "Thêm voucher thành công!";
                $messType = "success";
            } else {
                $mess = "Lỗi hệ thống khi thêm voucher!";
                $messType = "danger";
            }
            $insertStmt->close();
        }
        $checkStmt->close();
    }
}

// 2. EDIT VOUCHER
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_voucher'])) {
    $id_voucher = (int)$_POST['id_voucher'];
    $code = strtoupper(trim($_POST['ma_code']));
    $giam_gia = (int)$_POST['giam_gia'];
    $loai_giam = (int)$_POST['loai_giam'];
    $so_luong = (int)$_POST['so_luong'];
    $ngay_het_han = $_POST['ngay_het_han'];
    $trang_thai = (int)$_POST['trang_thai'];

    if (empty($code) || $giam_gia <= 0 || $so_luong < 0 || empty($ngay_het_han)) {
        $mess = "Vui lòng nhập đầy đủ thông tin hợp lệ!";
        $messType = "danger";
    } else {
        // Check duplicate code excluding current ID
        $checkStmt = $conn->prepare("SELECT id_voucher FROM voucher WHERE ma_code = ? AND id_voucher != ?");
        $checkStmt->bind_param("si", $code, $id_voucher);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $mess = "Mã voucher này đã được sử dụng ở voucher khác!";
            $messType = "danger";
        } else {
            $updateStmt = $conn->prepare("UPDATE voucher SET ma_code = ?, giam_gia = ?, loai_giam = ?, so_luong = ?, ngay_het_han = ?, trang_thai = ? WHERE id_voucher = ?");
            $updateStmt->bind_param("siiisii", $code, $giam_gia, $loai_giam, $so_luong, $ngay_het_han, $trang_thai, $id_voucher);
            if ($updateStmt->execute()) {
                $mess = "Cập nhật voucher thành công!";
                $messType = "success";
            } else {
                $mess = "Lỗi khi cập nhật voucher!";
                $messType = "danger";
            }
            $updateStmt->close();
        }
        $checkStmt->close();
    }
}

// 3. TOGGLE STATUS
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $new_status = (int)$_GET['toggle_status'];
    $stmt = $conn->prepare("UPDATE voucher SET trang_thai = ? WHERE id_voucher = ?");
    $stmt->bind_param("ii", $new_status, $id);
    if ($stmt->execute()) {
        $mess = "Thay đổi trạng thái voucher thành công!";
        $messType = "success";
    }
    $stmt->close();
}

// 4. DELETE VOUCHER
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM voucher WHERE id_voucher = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mess = "Xóa voucher thành công!";
        $messType = "success";
    } else {
        $mess = "Không thể xóa voucher này!";
        $messType = "danger";
    }
    $stmt->close();
}


// ================= PAGINATION & SEARCH =================
$limit = 8;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['queryid']) ? trim($_GET['queryid']) : '';

if ($search !== '') {
    $countQuery = "SELECT COUNT(*) as total FROM voucher WHERE ma_code LIKE ?";
    $stmt = $conn->prepare($countQuery);
    $searchWildcard = "%" . $search . "%";
    $stmt->bind_param("s", $searchWildcard);
    $stmt->execute();
    $totalRows = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $selectQuery = "SELECT * FROM voucher WHERE ma_code LIKE ? ORDER BY id_voucher DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($selectQuery);
    $stmt->bind_param("sii", $searchWildcard, $limit, $offset);
    $stmt->execute();
    $vouchersResult = $stmt->get_result();
    $stmt->close();
} else {
    $countQuery = "SELECT COUNT(*) as total FROM voucher";
    $totalRows = $conn->query($countQuery)->fetch_assoc()['total'];

    $selectQuery = "SELECT * FROM voucher ORDER BY id_voucher DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($selectQuery);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $vouchersResult = $stmt->get_result();
    $stmt->close();
}

$totalPages = ceil($totalRows / $limit);
if ($totalPages < 1) $totalPages = 1;
?>

<head>
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<style>
    .admin-card {
        background: #ffffff;
        padding: 40px;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        font-family: 'Outfit', sans-serif;
    }

    .admin-title {
        font-weight: 800;
        color: #0f172a;
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
        border-color: #0d6efd;
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

    .coupon-tag {
        font-family: monospace;
        font-weight: 800;
        font-size: 1rem;
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.07);
        padding: 6px 12px;
        border: 1px dashed rgba(13, 110, 253, 0.3);
        border-radius: 8px;
        letter-spacing: 0.05em;
    }

    /* Status badge anchors matching qltk */
    .status-badge-link {
        display: inline-block;
        white-space: nowrap;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .status-badge-link:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    /* Modals styling */
    .premium-modal .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 15px 40px rgba(15, 23, 42, 0.12);
        padding: 10px;
        font-family: 'Outfit', sans-serif;
    }

    .premium-modal .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 15px 20px 10px 20px;
    }

    .premium-modal .modal-title {
        font-weight: 800;
        color: #0f172a;
    }

    .premium-modal .form-control, .premium-modal .form-select {
        border-radius: 12px;
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
    }

    .premium-modal .form-control:focus, .premium-modal .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08);
    }

    .premium-modal .modal-footer {
        border-top: none;
        padding: 10px 20px 20px 20px;
    }

    .btn-pill-save {
        border-radius: 50px;
        padding: 10px 24px;
        font-weight: 700;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-pill-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    }

    .btn-pill-close {
        border-radius: 50px;
        padding: 10px 24px;
        font-weight: 600;
        border: 1px solid #cbd5e1;
        background: transparent;
        color: #64748b;
        transition: all 0.2s ease;
    }

    .btn-pill-close:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
</style>

<div class="container mb-5 mt-4">
    
    <div class="admin-card shadow-sm">
        
        <!-- HEADER ACTIONS -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
            <h2 class="admin-title mb-0 d-flex align-items-center">
                <i class="fa-solid fa-ticket text-primary me-2"></i>Quản lý mã giảm giá (Voucher)
            </h2>
            <div class="d-flex gap-2">
                <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
                    <i class="fa-solid fa-circle-plus"></i> Thêm mới
                </button>
                <a href="admin.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2">
                    <i class="fa-solid fa-arrow-left-long"></i> Trở về Tổng quan
                </a>
            </div>
        </div>

        <!-- SERVER STATUS MESSAGE -->
        <?php if (!empty($mess)): ?>
            <div class="alert alert-<?= $messType == 'danger' ? 'danger' : 'success' ?> alert-dismissible fade show rounded-3 px-3 py-2.5 mb-4" role="alert">
                <i class="fa-solid <?= $messType == 'danger' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> me-2"></i>
                <?= htmlspecialchars($mess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- SEARCH BAR -->
        <div class="mb-4 bg-light rounded-3 p-3">
            <form action="" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="page" value="qlvc">
                <div class="col-sm-5 col-md-4">
                    <input type="text" name="queryid" class="form-control search-bar-custom" placeholder="Tìm theo mã voucher..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-sm-auto d-flex gap-2">
                    <button type="submit" class="btn btn-secondary rounded-pill px-4 fw-bold"><i class="fa-solid fa-magnifying-glass me-2"></i>Tìm kiếm</button>
                    <a href="admin.php?page=qlvc" class="btn btn-outline-secondary rounded-pill px-4 fw-bold"><i class="fa-solid fa-arrows-rotate me-2"></i>Làm mới</a>
                </div>
            </form>
        </div>

        <!-- TABLE GRID -->
        <div class="table-responsive">
            <table class="table admin-table table-hover align-middle text-center m-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mã Code</th>
                        <th>Mức Giảm</th>
                        <th>Loại Giảm</th>
                        <th>Số Lượng</th>
                        <th>Ngày Hết Hạn</th>
                        <th>Trạng Thái</th>
                        <th style="width: 100px;">Sửa</th>
                        <th style="width: 100px;">Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($vouchersResult && $vouchersResult->num_rows > 0): ?>
                        <?php while ($row = $vouchersResult->fetch_assoc()): 
                            $is_expired = strtotime($row['ngay_het_han']) < strtotime(date('Y-m-d'));
                            $is_out_of_stock = $row['so_luong'] <= 0;
                        ?>
                            <tr class="<?= $is_expired ? 'table-warning text-decoration-line-through' : '' ?>">
                                <td>
                                    <span class="mono-badge">#<?= $row['id_voucher'] ?></span>
                                </td>
                                <td>
                                    <span class="coupon-tag"><?= htmlspecialchars($row['ma_code']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">
                                        <?= $row['loai_giam'] == 0 ? number_format($row['giam_gia'], 0, ',', '.') . 'đ' : $row['giam_gia'] . '%' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-1.5 fw-bold <?= $row['loai_giam'] == 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-info-subtle text-info border border-info-subtle' ?>">
                                        <?= $row['loai_giam'] == 0 ? 'Số tiền cố định' : 'Phần trăm (%)' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-1.5 fw-bold <?= $is_out_of_stock ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' ?>">
                                        <?= $row['so_luong'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?= $is_expired ? 'text-danger fw-bold' : 'text-secondary small' ?>">
                                        <i class="fa-regular <?= $is_expired ? 'fa-circle-xmark text-danger' : 'fa-clock text-secondary' ?> me-1"></i>
                                        <?= date('d/m/Y', strtotime($row['ngay_het_han'])) ?>
                                        <?= $is_expired ? ' (Hết hạn)' : '' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="admin.php?page=qlvc&toggle_status=<?= $row['trang_thai'] == 1 ? 0 : 1 ?>&id=<?= $row['id_voucher'] ?>"
                                       class="status-badge-link badge rounded-pill px-3 py-1.5 fw-bold <?= $row['trang_thai'] == 1 ? 'text-success border border-success-subtle bg-success-subtle' : 'text-danger border border-danger-subtle bg-danger-subtle' ?>"
                                       onclick="return confirm('Bạn có muốn đổi trạng thái voucher này?')">
                                        <?= $row['trang_thai'] == 1 ? 'Hoạt động' : 'Tạm khóa' ?>
                                    </a>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold edit-voucher-btn d-inline-flex align-items-center gap-2" 
                                            data-id="<?= $row['id_voucher'] ?>"
                                            data-code="<?= htmlspecialchars($row['ma_code']) ?>"
                                            data-val="<?= $row['giam_gia'] ?>"
                                            data-type="<?= $row['loai_giam'] ?>"
                                            data-qty="<?= $row['so_luong'] ?>"
                                            data-exp="<?= $row['ngay_het_han'] ?>"
                                            data-status="<?= $row['trang_thai'] ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editVoucherModal">
                                        <i class="fa-solid fa-pen-to-square"></i> Sửa
                                    </button>
                                </td>
                                <td>
                                    <a href="admin.php?page=qlvc&delete_id=<?= $row['id_voucher'] ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa voucher này vĩnh viễn?');">
                                        <i class="fa-solid fa-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fa-solid fa-circle-exclamation fa-2x mb-3 text-secondary opacity-50"></i>
                                <h5 class="mb-0 text-slate-400">Không tìm thấy mã giảm giá nào phù hợp.</h5>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Render Pagination -->
        <?php include "./assets/layout/navigation/navigation.php" ?>
        
    </div>

</div>

<!-- ================= ADD VOUCHER MODAL ================= -->
<div class="modal fade premium-modal" id="addVoucherModal" tabindex="-1" aria-labelledby="addVoucherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center" id="addVoucherModalLabel">
                        <i class="fa-solid fa-circle-plus text-primary me-2"></i>Thêm Mã Giảm Giá Mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ma_code" class="form-label form-label-custom">Mã Voucher <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase fw-bold" name="ma_code" id="ma_code" placeholder="Ví dụ: SALE20K, VIP10" style="text-transform: uppercase;" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="loai_giam" class="form-label form-label-custom">Loại Giảm Giá</label>
                            <select class="form-select fw-semibold" name="loai_giam" id="loai_giam" required>
                                <option value="0">Số tiền cố định (đ)</option>
                                <option value="1">Phần trăm (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="giam_gia" class="form-label form-label-custom">Mức Giảm <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="giam_gia" id="giam_gia" min="1" placeholder="Nhập số tiền hoặc %" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="so_luong" class="form-label form-label-custom">Số Lượng <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="so_luong" id="so_luong" min="0" value="10" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ngay_het_han" class="form-label form-label-custom">Ngày Hết Hạn <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="ngay_het_han" id="ngay_het_han" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-pill-close" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" name="add_voucher" class="btn btn-primary btn-pill-save">Tạo mã giảm giá</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= EDIT VOUCHER MODAL ================= -->
<div class="modal fade premium-modal" id="editVoucherModal" tabindex="-1" aria-labelledby="editVoucherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id_voucher" id="edit_id_voucher">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center" id="editVoucherModalLabel">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Mã Giảm Giá
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_ma_code" class="form-label form-label-custom">Mã Voucher <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase fw-bold" name="ma_code" id="edit_ma_code" style="text-transform: uppercase;" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_loai_giam" class="form-label form-label-custom">Loại Giảm Giá</label>
                            <select class="form-select fw-semibold" name="loai_giam" id="edit_loai_giam" required>
                                <option value="0">Số tiền cố định (đ)</option>
                                <option value="1">Phần trăm (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_giam_gia" class="form-label form-label-custom">Mức Giảm <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="giam_gia" id="edit_giam_gia" min="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_so_luong" class="form-label form-label-custom">Số Lượng <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="so_luong" id="edit_so_luong" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_ngay_het_han" class="form-label form-label-custom">Ngày Hết Hạn <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="ngay_het_han" id="edit_ngay_het_han" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_trang_thai" class="form-label form-label-custom">Trạng Thái</label>
                        <select class="form-select fw-semibold" name="trang_thai" id="edit_trang_thai" required>
                            <option value="1">Hoạt động</option>
                            <option value="0">Tạm khóa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-pill-close" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" name="edit_voucher" class="btn btn-primary btn-pill-save">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // When click on Sửa button, load data to Edit Modal
    $(".edit-voucher-btn").click(function() {
        const id = $(this).data("id");
        const code = $(this).data("code");
        const val = $(this).data("val");
        const type = $(this).data("type");
        const qty = $(this).data("qty");
        const exp = $(this).data("exp");
        const status = $(this).data("status");
 
        $("#edit_id_voucher").val(id);
        $("#edit_ma_code").val(code);
        $("#edit_giam_gia").val(val);
        $("#edit_loai_giam").val(type);
        $("#edit_so_luong").val(qty);
        $("#edit_ngay_het_han").val(exp);
        $("#edit_trang_thai").val(status);
    });
});
</script>

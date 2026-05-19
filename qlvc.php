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

<div class="container mb-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>🎟️ Quản lý Mã giảm giá (Voucher)</h3>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
            <i class="fa-solid fa-plus"></i> Thêm mới
        </button>
    </div>

    <?php if (!empty($mess)): ?>
        <div class="alert alert-<?= $messType ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-2 mb-4">
        <div class="col-12">
            <form action="" method="GET" class="d-flex align-items-center" style="max-width: 550px;">
                <input type="hidden" name="page" value="qlvc">
                <input type="text" name="queryid" class="form-control me-2" placeholder="Tìm theo mã..." style="width: 200px;" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-secondary me-2"><i class="fa-solid fa-magnifying-glass"></i> Tìm theo mã</button>
                <a href="admin.php?page=qlvc" class="btn btn-outline-secondary"><i class="fa-solid fa-arrows-rotate"></i> Làm mới</a>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover border align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Mã Code</th>
                    <th>Mức Giảm</th>
                    <th>Loại Giảm</th>
                    <th>Số Lượng</th>
                    <th>Ngày Hết Hạn</th>
                    <th>Trạng Thái</th>
                    <th>Sửa</th>
                    <th>Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($vouchersResult && $vouchersResult->num_rows > 0): ?>
                    <?php while ($row = $vouchersResult->fetch_assoc()): 
                        $is_expired = strtotime($row['ngay_het_han']) < strtotime(date('Y-m-d'));
                        $is_out_of_stock = $row['so_luong'] <= 0;
                    ?>
                        <tr class="<?= $is_expired ? 'table-warning text-decoration-line-through' : '' ?>">
                            <td><?= $row['id_voucher'] ?></td>
                            <td><strong class="text-primary"><?= htmlspecialchars($row['ma_code']) ?></strong></td>
                            <td>
                                <strong>
                                    <?= $row['loai_giam'] == 0 ? number_format($row['giam_gia']) . 'đ' : $row['giam_gia'] . '%' ?>
                                </strong>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <?= $row['loai_giam'] == 0 ? 'Số tiền cố định' : 'Phần trăm (%)' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $is_out_of_stock ? 'bg-danger' : 'bg-primary' ?>">
                                    <?= $row['so_luong'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="<?= $is_expired ? 'text-danger fw-bold' : '' ?>">
                                    <?= date('d/m/Y', strtotime($row['ngay_het_han'])) ?>
                                    <?= $is_expired ? ' (Hết hạn)' : '' ?>
                                </span>
                            </td>
                            <td>
                                <a href="admin.php?page=qlvc&toggle_status=<?= $row['trang_thai'] == 1 ? 0 : 1 ?>&id=<?= $row['id_voucher'] ?>"
                                   class="btn btn-sm <?= $row['trang_thai'] == 1 ? 'btn-success' : 'btn-secondary' ?>"
                                   onclick="return confirm('Bạn có muốn đổi trạng thái voucher này?')">
                                    <?= $row['trang_thai'] == 1 ? 'Hoạt động' : 'Tạm khóa' ?>
                                </a>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-voucher-btn" 
                                        data-id="<?= $row['id_voucher'] ?>"
                                        data-code="<?= htmlspecialchars($row['ma_code']) ?>"
                                        data-val="<?= $row['giam_gia'] ?>"
                                        data-type="<?= $row['loai_giam'] ?>"
                                        data-qty="<?= $row['so_luong'] ?>"
                                        data-exp="<?= $row['ngay_het_han'] ?>"
                                        data-status="<?= $row['trang_thai'] ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editVoucherModal">
                                    Sửa
                                </button>
                            </td>
                            <td>
                                <a href="admin.php?page=qlvc&delete_id=<?= $row['id_voucher'] ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa voucher này vĩnh viễn?');">
                                    Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">Không tìm thấy mã giảm giá nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Render Pagination -->
    <?php include "./assets/layout/navigation/navigation.php" ?>
</div>

<!-- ================= ADD VOUCHER MODAL ================= -->
<div class="modal fade" id="addVoucherModal" tabindex="-1" aria-labelledby="addVoucherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addVoucherModalLabel"><i class="fa-solid fa-plus-circle text-success me-2"></i>Thêm Mã Giảm Giá Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ma_code" class="form-label fw-bold">Mã Voucher</label>
                        <input type="text" class="form-control" name="ma_code" id="ma_code" placeholder="Ví dụ: SALE20K, VIP10" style="text-transform: uppercase;" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="loai_giam" class="form-label fw-bold">Loại Giảm Giá</label>
                            <select class="form-select" name="loai_giam" id="loai_giam" required>
                                <option value="0">Số tiền cố định (đ)</option>
                                <option value="1">Phần trăm (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="giam_gia" class="form-label fw-bold">Mức Giảm</label>
                            <input type="number" class="form-control" name="giam_gia" id="giam_gia" min="1" placeholder="Nhập số tiền hoặc %" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="so_luong" class="form-label fw-bold">Số Lượng</label>
                            <input type="number" class="form-control" name="so_luong" id="so_luong" min="0" value="10" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ngay_het_han" class="form-label fw-bold">Ngày Hết Hạn</label>
                            <input type="date" class="form-control" name="ngay_het_han" id="ngay_het_han" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" name="add_voucher" class="btn btn-success">Thêm Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= EDIT VOUCHER MODAL ================= -->
<div class="modal fade" id="editVoucherModal" tabindex="-1" aria-labelledby="editVoucherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id_voucher" id="edit_id_voucher">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editVoucherModalLabel"><i class="fa-solid fa-edit me-2"></i>Chỉnh Sửa Mã Giảm Giá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_ma_code" class="form-label fw-bold">Mã Voucher</label>
                        <input type="text" class="form-control" name="ma_code" id="edit_ma_code" style="text-transform: uppercase;" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_loai_giam" class="form-label fw-bold">Loại Giảm Giá</label>
                            <select class="form-select" name="loai_giam" id="edit_loai_giam" required>
                                <option value="0">Số tiền cố định (đ)</option>
                                <option value="1">Phần trăm (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_giam_gia" class="form-label fw-bold">Mức Giảm</label>
                            <input type="number" class="form-control" name="giam_gia" id="edit_giam_gia" min="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_so_luong" class="form-label fw-bold">Số Lượng</label>
                            <input type="number" class="form-control" name="so_luong" id="edit_so_luong" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_ngay_het_han" class="form-label fw-bold">Ngày Hết Hạn</label>
                            <input type="date" class="form-control" name="ngay_het_han" id="edit_ngay_het_han" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_trang_thai" class="form-label fw-bold">Trạng Thái</label>
                        <select class="form-select" name="trang_thai" id="edit_trang_thai" required>
                            <option value="1">Hoạt động</option>
                            <option value="0">Tạm khóa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" name="edit_voucher" class="btn btn-primary">Lưu Thay Đổi</button>
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

<?php
include "./connect_DB/connect_db.php";

$conn = connectData();
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $currentStatus = isset($_GET['currentStatus']) ? (int)$_GET['currentStatus'] : null;

    $stmt = $conn->prepare("SELECT trangthai FROM taikhoan WHERE idtk = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultStatus = $stmt->get_result()->fetch_assoc();
    $current = $resultStatus['trangthai'];

    if ($currentStatus !== null) {
        $current = $currentStatus;
    }

    $newStatus = ($current == 1) ? 0 : 1;
    $stmtUpdate = $conn->prepare("UPDATE taikhoan SET trangthai = ? WHERE idtk = ?");
    $stmtUpdate->bind_param("ii", $newStatus, $id);
    $stmtUpdate->execute();

    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode(['status' => 'success', 'newStatus' => $newStatus, 'id' => $id]);
        exit;
    }

    echo "<script>window.location.href='qltk.php';</script>";
    exit;
}

$limit = 8;
$page = isset($_GET['p']) && is_numeric($_GET['p']) && $_GET['p'] > 0 ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

if (isset($_GET['queryid']) && !empty($_GET['queryid'])) {
    $search = trim($_GET['queryid']);
    
    // Calculate total pages for this search query
    $countSql = "SELECT COUNT(*) AS total FROM taikhoan WHERE username LIKE ?";
    $stmtCount = $conn->prepare($countSql);
    $search_param = "%" . $search . "%";
    $stmtCount->bind_param("s", $search_param);
    $stmtCount->execute();
    $totalRow = $stmtCount->get_result()->fetch_assoc();
    $totalProducts = $totalRow['total'];
    $stmtCount->close();

    $sql = "
        SELECT tk.*, r.Ten 
        FROM taikhoan tk 
        LEFT JOIN role r ON tk.roleId = r.roleId 
        WHERE tk.username LIKE ? 
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Normal total count
    $totalQuery = "SELECT COUNT(*) AS total FROM taikhoan";
    $totalResult = $conn->query($totalQuery);
    $totalRow = $totalResult->fetch_assoc();
    $totalProducts = $totalRow['total'];

    $sql = "
        SELECT tk.*, r.Ten
        FROM taikhoan tk 
        LEFT JOIN role r ON tk.roleId = r.roleId 
        LIMIT $limit OFFSET $offset
    ";
    $result = $conn->query($sql);
}
$totalPages = ceil($totalProducts / $limit);
?>



<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản</title>
    <link rel="stylesheet" href="./assets/fonts/css/all.min.css">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mb-5 mt-4">
        <h2 class="text-center mb-4">Quản lý tài khoản</h2>
        <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📋 Quản lý Tài khoản</h3>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAccountModal">
        <i class="fa-solid fa-plus"></i> Thêm mới
    </button>
</div>
        <div class="row g-2 mb-4">
            <div class="">
                <div class="d-flex flex-wrap">
                    <div class="col-12 col-md-6">
                        <form action="" method="GET" class="d-flex" style="max-width: 450px;">
                            <input type="hidden" name="page" value="qltk">
                            <input type="text" name="queryid" class="form-control me-2" placeholder="Tìm theo tên..." style="width: 200px;">
                            <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Tìm theo tên</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover border align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID tài khoản</th>
                        <th>Tên</th>
                        <th>Mật khẩu</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Thời gian tạo</th>
                        <th>Sửa</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['idtk'] ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['password']) ?></td>
                                <td><?= $row['Ten'] ?? 'Không rõ' ?></td>
                                <td>
                                    <a href="#"
                                        class="btn btn-sm toggle-status <?= $row['trangthai'] == 1 ? 'btn-success' : 'btn-secondary' ?>"
                                        data-id="<?= $row['idtk'] ?>"
                                        data-status="<?= $row['trangthai'] ?>"
                                        id="status-<?= $row['idtk'] ?>"
                                        onclick="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái tài khoản này?')">
                                        <?= $row['trangthai'] == 1 ? 'Đang hoạt động' : 'Bị khóa' ?>
                                    </a>
                                </td>

                                <td><?= $row['thoigiantao'] ?></td>
                                <td><a href="./ad/suataikhoan.php?id=<?= $row['idtk'] ?>" class="btn btn-warning btn-sm">Sửa</a></td>
                                
                                <td><a href="./ad/xoatk.php?idtk=<?= $row['idtk'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa không?');">Xóa</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Không tìm thấy tài khoản nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>



        <?php include "./assets/layout/navigation/navigation.php" ?>
    </div>

    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Khi click vào nút thay đổi trạng thái
            $(".toggle-status").click(function(e) {
                e.preventDefault(); // Ngăn không cho reload trang

                var id = $(this).data("id"); // Lấy ID từ data-id
                var currentStatus = $(this).data("status"); // Lấy trạng thái hiện tại

                $.ajax({
                    url: 'qltk.php', // Gọi lại chính trang này
                    method: 'GET',
                    data: {
                        toggle: 1, // Để xác định yêu cầu thay đổi trạng thái
                        id: id, // Gửi ID của tài khoản
                        currentStatus: currentStatus // Gửi trạng thái hiện tại
                    },
                    success: function(response) {
                        // Sau khi thay đổi trạng thái thành công
                        if (response.status == 'success') {
                            // Cập nhật lại nút trạng thái
                            var newStatus = response.newStatus == 1 ? 'Đang hoạt động' : 'Bị khóa';
                            var newClass = response.newStatus == 1 ? 'btn-success' : 'btn-secondary';

                            // Cập nhật lại nội dung và class
                            $("#status-" + id).text(newStatus).removeClass('btn-success btn-secondary').addClass(newClass);
                            // Cập nhật data-status của nút
                            $(".toggle-status[data-id='" + id + "']").data('status', response.newStatus);
                        }
                    }
                });
            });
        });
    </script>

<!-- Modal Thêm Tài Khoản -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formAddAccount" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Thêm Tài Khoản Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Alert Messages -->
                    <div id="addAlert" class="alert d-none" role="alert"></div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="Ten_user" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="1">✅ Hoạt động</option>
                                <option value="0">❌ Khóa</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ảnh đại diện</label>
                            <input type="file" name="Anh_user" class="form-control" accept="image/*">
                            <small class="text-muted">Chỉ chấp nhận: jpg, jpeg, png, gif</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="note" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitAdd">
                        <span class="spinner-border spinner-border-sm d-none" id="addSpinner"></span>
                        💾 Lưu tài khoản
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('formAddAccount').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnSubmitAdd');
    const spinner = document.getElementById('addSpinner');
    const alertBox = document.getElementById('addAlert');
    
    // Disable button & show spinner
    btn.disabled = true;
    spinner.classList.remove('d-none');
    alertBox.classList.add('d-none');
    
    const formData = new FormData(this);
    formData.append('action', 'add_account');
    
    try {
        const response = await fetch('assets/function/add/handle_add.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
            
            // Reset form & close modal after 1.5s
            setTimeout(() => {
                document.getElementById('formAddAccount').reset();
                const modal = bootstrap.Modal.getInstance(document.getElementById('addAccountModal'));
                modal.hide();
                location.reload(); // Reload to show new data
            }, 1500);
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = result.errors.map(err => `• ${err}`).join('<br>');
            alertBox.classList.remove('d-none');
        }
    } catch (error) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = 'Lỗi kết nối: ' + error.message;
        alertBox.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        spinner.classList.add('d-none');
    }
});
</script>
</body>

</html>
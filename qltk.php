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

    echo "<script>window.location.href='admin.php?page=qltk';</script>";
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
    <title>Quản lý tài khoản | UNIQ</title>
    
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

        .status-pill {
            display: inline-block;
            white-space: nowrap;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .status-pill-active {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .status-pill-active:hover {
            background-color: #bbf7d0;
            color: #15803d;
        }

        .status-pill-blocked {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .status-pill-blocked:hover {
            background-color: #fecaca;
            color: #b91c1c;
        }

        /* Modal styling */
        .modal-content-custom {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-header-custom {
            border-bottom: 1px solid #f1f5f9;
            padding: 24px;
        }

        .modal-footer-custom {
            border-top: 1px solid #f1f5f9;
            padding: 20px 24px;
        }

        .modal-body-custom {
            padding: 24px;
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
                    <i class="fa-solid fa-users-gear text-primary me-2"></i>Quản lý tài khoản
                </h2>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                        <i class="fa-solid fa-plus"></i> Thêm mới
                    </button>
                    <a href="admin.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-arrow-left-long"></i> Trở về Tổng quan
                    </a>
                </div>

            </div>

            <!-- SEARCH AND FILTER PANEL -->
            <div class="mb-4 bg-light rounded-3 p-3">
                <form action="" method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="page" value="qltk">
                    <div class="col-sm-5 col-md-4">
                        <input type="text" name="queryid" class="form-control search-bar-custom" placeholder="Tìm theo tên..." value="<?= isset($_GET['queryid']) ? htmlspecialchars($_GET['queryid']) : '' ?>">
                    </div>
                    <div class="col-sm-auto d-flex gap-2">
                        <button type="submit" class="btn btn-secondary rounded-pill px-4 fw-bold"><i class="fa-solid fa-magnifying-glass me-2"></i>Tìm kiếm</button>
                        <a href="admin.php?page=qltk" class="btn btn-outline-secondary rounded-pill px-4 fw-bold"><i class="fa-solid fa-arrows-rotate me-2"></i>Làm mới</a>
                    </div>
                </form>
            </div>

            <!-- TABLE DATAGRID -->
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle text-center m-0">
                    
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên tài khoản</th>
                            <th>Mật khẩu</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Thời gian tạo</th>
                            <th style="width: 100px;">Sửa</th>
                            <th style="width: 100px;">Xóa</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="mono-badge">#<?= $row['idtk'] ?></span>
                                    </td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($row['username']) ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars($row['password']) ?></td>
                                    <td>
                                        <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-1.5 fw-bold border border-primary-subtle"><?= $row['Ten'] ?? 'Không rõ' ?></span>
                                    </td>
                                    <td>
                                        <a href="#"
                                            class="status-pill toggle-status <?= $row['trangthai'] == 1 ? 'status-pill-active' : 'status-pill-blocked' ?>"
                                            data-id="<?= $row['idtk'] ?>"
                                            data-status="<?= $row['trangthai'] ?>"
                                            id="status-<?= $row['idtk'] ?>"
                                            onclick="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái tài khoản này?')">
                                            <?= $row['trangthai'] == 1 ? 'Đang hoạt động' : 'Bị khóa' ?>
                                        </a>
                                    </td>
                                    <td class="text-secondary small"><?= date("d/m/Y H:i", strtotime($row['thoigiantao'])) ?></td>
                                    <td>
                                        <a href="./ad/suataikhoan.php?id=<?= $row['idtk'] ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </a>
                                    </td>
                                    <td>
                                        <a href="./ad/xoatk.php?idtk=<?= $row['idtk'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2" onclick="return confirm('Bạn có chắc muốn xóa không?');">
                                            <i class="fa-solid fa-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-circle-exclamation fa-2x mb-3 text-secondary opacity-50"></i>
                                    <h5 class="mb-0 text-slate-400">Không tìm thấy tài khoản nào phù hợp.</h5>
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

    <!-- Modal Thêm Tài Khoản -->
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-custom">
                
                <form id="formAddAccount" method="POST" enctype="multipart/form-data">
                    
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title fw-extrabold text-dark d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-user-plus text-primary me-2"></i>Thêm tài khoản mới
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body modal-body-custom">
                        <!-- Alert Messages -->
                        <div id="addAlert" class="alert d-none" role="alert"></div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="Ten_user" class="form-control search-bar-custom bg-white" placeholder="Ví dụ: Nguyễn Văn A" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control search-bar-custom bg-white" placeholder="Tên đăng nhập tài khoản..." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control search-bar-custom bg-white" placeholder="Tối thiểu 6 ký tự..." required minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control search-bar-custom bg-white" placeholder="name@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control search-bar-custom bg-white" placeholder="Nhập SĐT của tài khoản...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark mb-1">Trạng thái mặc định</label>
                                <select name="status" class="form-select search-bar-custom bg-white">
                                    <option value="1">✅ Hoạt động</option>
                                    <option value="0">❌ Khóa</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark mb-1">Ảnh đại diện</label>
                                <input type="file" name="Anh_user" class="form-control search-bar-custom bg-white" accept="image/*">
                                <div class="form-text text-muted mt-1 small">Chỉ chấp nhận các định dạng tệp ảnh thông thường: jpg, jpeg, png, gif</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark mb-1">Ghi chú quản trị</label>
                                <textarea name="note" class="form-control search-bar-custom bg-white" rows="2" placeholder="Nhập ghi chú thêm cho tài khoản này..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer modal-footer-custom">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Đóng lại</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnSubmitAdd">
                            <span class="spinner-border spinner-border-sm d-none me-2" id="addSpinner"></span>
                            💾 Lưu tài khoản
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

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
                            
                            if (response.newStatus == 1) {
                                $("#status-" + id).text(newStatus)
                                    .removeClass('status-pill-blocked')
                                    .addClass('status-pill-active');
                            } else {
                                $("#status-" + id).text(newStatus)
                                    .removeClass('status-pill-active')
                                    .addClass('status-pill-blocked');
                            }
                            
                            // Cập nhật data-status của nút
                            $(".toggle-status[data-id='" + id + "']").data('status', response.newStatus);
                        }
                    }
                });
            });
        });
    </script>

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
                alertBox.className = 'alert alert-success border-0 rounded-3 p-3 mb-4 fw-semibold';
                alertBox.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i>' + result.message;
                alertBox.classList.remove('d-none');
                
                // Reset form & close modal after 1.5s
                setTimeout(() => {
                    document.getElementById('formAddAccount').reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addAccountModal'));
                    modal.hide();
                    location.reload(); // Reload to show new data
                }, 1500);
            } else {
                alertBox.className = 'alert alert-danger border-0 rounded-3 p-3 mb-4 fw-semibold';
                alertBox.innerHTML = result.errors.map(err => `• ${err}`).join('<br>');
                alertBox.classList.remove('d-none');
            }
        } catch (error) {
            alertBox.className = 'alert alert-danger border-0 rounded-3 p-3 mb-4 fw-semibold';
            alertBox.textContent = 'Lỗi kết nối máy chủ: ' + error.message;
            alertBox.classList.remove('d-none');
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    });
    </script>
</body>

</html>
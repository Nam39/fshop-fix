<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM donhang WHERE idDonHang = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if (!$row) {
        echo "Đơn hàng không tồn tại!";
        exit();
    }

    // Fetch order details (items)
    $sql_items = "
        SELECT ct.*, sp.Ten, sp.Anh, sp.Gia
        FROM chitietdonhang ct
        JOIN sanpham sp ON ct.idsanpham = sp.id
        WHERE ct.idDonHang = $id
    ";
    $result_items = $conn->query($sql_items);
} else {
    echo "ID không hợp lệ!";
    exit();
}

$statuses = [
    0 => "Đã đặt hàng",
    1 => "Đã xác nhận",
    2 => "Đang giao",
    3 => "Hoàn thành",
    4 => "Đã hủy"
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trangthai = (int)$_POST['trangthai'];

    if (array_key_exists($trangthai, $statuses)) {
        $sql_update = "UPDATE donhang SET trangthai = ? WHERE idDonHang = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $trangthai, $id);
        if ($stmt_update->execute()) {
            header("Location: ../admin.php?page=qldh");
            exit();
        } else {
            $error = "Lỗi cập nhật: " . $conn->error;
        }
    } else {
        $error = "Trạng thái không hợp lệ!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết và Sửa đơn hàng</title>
    <link href=".././assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 mb-5" style="max-width: 800px;">
        <div class="card bg-body-secondary shadow">
            <div class="card-header bg-dark text-white text-center">
                <h2>Chi tiết & Sửa trạng thái đơn hàng</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Mã Đơn Hàng:</strong> #<?= $row['idDonHang'] ?></p>
                        <p><strong>Mã Khách Hàng:</strong> #<?= $row['idKhach'] ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p><strong>Ngày Đặt Hàng:</strong> <?= htmlspecialchars($row['ngaydathang']) ?></p>
                    </div>
                </div>

                <h4 class="mb-3">Danh sách sản phẩm đã đặt</h4>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered bg-white text-center align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $tongTien = 0;
                            if ($result_items && $result_items->num_rows > 0): 
                                while ($item = $result_items->fetch_assoc()): 
                                    $thanhTien = $item['soluong'] * $item['Gia'];
                                    $tongTien += $thanhTien;
                            ?>
                                <tr>
                                    <td><img src="../assets/img/<?= htmlspecialchars($item['Anh']) ?>" style="width: 50px;" class="img-fluid rounded"></td>
                                    <td class="text-start"><?= htmlspecialchars($item['Ten']) ?></td>
                                    <td><?= number_format($item['Gia'], 0, ',', '.') ?> VNĐ</td>
                                    <td><?= $item['soluong'] ?></td>
                                    <td class="fw-bold"><?= number_format($thanhTien, 0, ',', '.') ?> VNĐ</td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="5" class="text-muted">Không tìm thấy chi tiết sản phẩm.</td>
                                </tr>
                            <?php endif; ?>
                            <tr class="table-light">
                                <td colspan="4" class="text-end fw-bold">Tổng cộng:</td>
                                <td class="fw-bold text-danger fs-5"><?= number_format($tongTien, 0, ',', '.') ?> VNĐ</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form method="POST">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Cập nhật trạng thái đơn hàng</label>
                        <select name="trangthai" class="form-select border border-dark-subtle" required>
                            <?php foreach ($statuses as $val => $label) { ?>
                                <option value="<?= $val ?>" <?= ($val == $row['trangthai']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success px-4">Lưu thay đổi</button>
                        <a href=".././admin.php?page=qldh" class="btn btn-primary px-4">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

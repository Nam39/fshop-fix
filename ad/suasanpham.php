<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM sanpham WHERE id = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
}

$sql_danhmuc = "SELECT * FROM danhmucsanpham";
$result_danhmuc = $conn->query($sql_danhmuc);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten = $_POST['Ten'];
    $mota = $_POST['MoTa'];
    $soluong = $_POST['SoLuong'];
    $gia = $_POST['Gia'];
    $danhmuc = $_POST['id_DanhMuc'];

    $anhMoi = $_FILES['Anh']['name'];
    $anhCu = $row['Anh'];

    if (!empty($anhMoi)) {
        $target_dir = "../assets/img/";
        $target_file = $target_dir . basename($anhMoi);
        move_uploaded_file($_FILES["Anh"]["tmp_name"], $target_file);
        $anh = $anhMoi;
    } else {
        $anh = $anhCu;
    }

    $sql = "UPDATE sanpham SET Ten='$ten', MoTa='$mota', SoLuong='$soluong', Gia='$gia', Anh='$anh', id_DanhMuc='$danhmuc' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../admin.php");
        exit();
    } else {
        echo "Lỗi cập nhật: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa sản phẩm</title>
    <link href=".././assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="card bg-body-secondary">
            <div class="card-header bg-dark">
                <h2 class="text-white text-center">Sửa sản phẩm</h2>
            </div>

            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="">id</label>
                        <input type="text" name="id" value="<?= $row['id'] ?>" class="form-control" disabled>
                    </div>
                    <div class="form-group pt-3">
                        <label for="">Tên sản phẩm</label>
                        <input type="text" name="Ten" value="<?= $row['Ten'] ?>" class="form-control" required>
                    </div>
                    <img src="../assets/img/<?= $row['Anh'] ?>" alt="Ảnh hiện tại" style="width: 100px; height: auto;" class="mt-4">

                    <div class="form-group pt-3 pb-1">
                        <label for="">Ảnh mới (bỏ trống nếu không thay)</label>
                        <input type="file" name="Anh" class="form-control">
                    </div>
                    <div class="form-group pt-3">
                        <label for="">Mô Tả sản phẩm</label>
                        <input type="text" name="MoTa" value="<?= $row['MoTa'] ?>" class="form-control" required>
                    </div>
                    <div class="form-group pt-3">
                        <label for="">Gía sản phẩm</label>
                        <input type="number" name="Gia" value="<?= $row['Gia'] ?>" class="form-control" required>
                    </div>
                    <div class="form-group pt-3">
                        <label for="">Số lượng sản phẩm</label>
                        <input type="number" name="SoLuong" value="<?= $row['soluong'] ?>" class="form-control" required>
                    </div>

                    <!-- Danh mục sản phẩm -->
                    <div class="form-group">
                        <label for="">Loại sản phẩm</label>
                        <select name="id_DanhMuc" class="form-control border border-dark-subtle">
                            <?php while ($danhmuc = $result_danhmuc->fetch_assoc()) { ?>
                                <option value="<?php echo $danhmuc['id_DanhMuc']; ?>"
                                    <?php echo ($danhmuc['id_DanhMuc'] == $row['id_DanhMuc']) ? 'selected' : ''; ?>>
                                    <?php echo $danhmuc['Ten_DanhMuc']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success mt-4 mb-4 px-4"> Sửa </button>
                        <a href=".././admin.php" class="btn btn-primary mt-4 mb-4 px-4">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
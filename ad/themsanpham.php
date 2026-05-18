<?php
include ".././connect_DB/connect_db.php";

$conn = connectData();

$sql = "SELECT * FROM danhmucsanpham";
$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Ten = $_POST['Ten'] ?? '';
    $MoTa = $_POST['MoTa'] ?? '';
    $Gia = $_POST['Gia'] ?? 0;
    $SoLuong = $_POST['soluong'] ?? 0;
    $id_DanhMuc = $_POST['id_DanhMuc'] ?? 0;

    $Anh = "";
    if (isset($_FILES["Anh"]) && $_FILES["Anh"]["error"] == 0) {
        $target_dir = ".././assets/img/";
        $target_file = $target_dir . basename($_FILES["Anh"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "png", "jpeg", "gif", "WEBP"];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["Anh"]["tmp_name"], $target_file)) {
                $Anh = basename($_FILES["Anh"]["name"]);
            } else {
                echo "Lỗi tải ảnh lên!";
                exit();
            }
        } else {
            exit();
        }
    }

    $result = $conn->query("SELECT COUNT(*) as total FROM sanpham");
    $row = $result->fetch_assoc();
    $total = $row['total'];
    $stt = $total + 1;

    if ($total == 0) {
        $conn->query("ALTER TABLE sanpham AUTO_INCREMENT = 1");
    }

    // Check if Gia and SoLuong are greater than zero
    if ($Gia <= 0) {
        echo "Giá sản phẩm phải lớn hơn 0!";
        exit();
    }

    if ($SoLuong <= 0) {
        echo "Số lượng sản phẩm phải lớn hơn 0!";
        exit();
    }

    // Check if all other fields are filled
    if (!empty($Ten) && !empty($MoTa) && $Gia > 0 && $SoLuong > 0 && !empty($Anh) && $id_DanhMuc > 0) {
        $sql = "INSERT INTO sanpham (`Ten`, `MoTa`, `Gia`, `soluong`, `Anh`, `id_DanhMuc`)
                VALUES ('$Ten', '$MoTa', '$Gia', '$SoLuong', '$Anh', '$id_DanhMuc')";

        if ($conn->query($sql) === TRUE) {
            header("Location: .././admin.php");
            exit();
        } else {
            echo "Lỗi thêm sản phẩm: " . $conn->error;
        }
    } else {
        echo "Vui lòng nhập đầy đủ thông tin!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sản Phẩm</title>
    <link href=".././assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="card bg-body-secondary">
            <div class="card-header bg-dark">
                <h2 class="text-white text-center">Thêm sản phẩm</h2>
            </div>

            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="">Tên sản phẩm</label>
                        <input type="text" name="Ten" class="form-control border border-dark-subtle" required>
                    </div>
                    <div class="form-group">
                        <label for="">Mô tả sản phẩm</label>
                        <input type="text" name="MoTa" class="form-control border border-dark-subtle" required>
                    </div>
                    <div class="form-group">
                        <label for="">Giá sản phẩm</label>
                        <input type="number" name="Gia" class="form-control border border-dark-subtle" required>
                    </div>
                    <div class="form-group">
                        <label for="">Số lượng sản phẩm</label>
                        <input type="number" name="soluong" class="form-control border border-dark-subtle" required>
                    </div>

                    <div class="form-group pt-3 pb-3">
                        <label for="">Ảnh sản phẩm</label>
                        <input type="file" name="Anh" class="form-control border border-dark-subtle" required>
                    </div>

                    <div class="form-group">
                        <label for="">Loại sản phẩm</label>
                        <select name="id_DanhMuc" class="form-control border border-dark-subtle" required>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <option value="<?= $row['id_DanhMuc'] ?>"><?= $row['Ten_DanhMuc'] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success mt-4 mb-4 px-4"> Thêm </button>
                        <a href=".././admin.php" class="btn btn-primary mt-4 mb-4 px-4">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="./assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
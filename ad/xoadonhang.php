<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['idDonHang'])) {
    $id = $_GET['idDonHang'];

    $sql1 = "DELETE FROM chitietdonhang WHERE idDonHang = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $id);
    $result = $stmt1->execute();

    if ($result) {
        $sql = "DELETE FROM donhang WHERE idDonHang = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // $conn->query("SET @num = 0");
            // $conn->query("UPDATE donhang
            // SET idDonHang = @num := @num + 1");

            // $conn->query("ALTER TABLE donhang
            // AUTO_INCREMENT = 1");

            header("Location: .././admin.php");
            exit();
        } else {
            echo "Lỗi khi xóa: " . $conn->error;
        }
    }

    $stmt1->close();
    $conn->close();
}

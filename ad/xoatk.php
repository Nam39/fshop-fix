<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['idtk'])) {
    $id = $_GET['idtk'];

    $sql = "DELETE FROM taikhoan WHERE idtk = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $conn->query("SET @num = 0");
        $conn->query("UPDATE taikhoan SET idtk = @num := @num + 1");

        $conn->query("ALTER TABLE taikhoan AUTO_INCREMENT = 1");

        header("Location: .././admin.php");
        exit();
    } else {
        echo "Lỗi khi xóa: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
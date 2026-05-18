<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['iduser'])) {
    $id = $_GET['iduser'];

    $sql = "DELETE FROM users WHERE iduser = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $conn->query("SET @num = 0");
        $conn->query("UPDATE users SET iduser = @num := @num + 1");

        $conn->query("ALTER TABLE users AUTO_INCREMENT = 1");

        echo "Xóa thành công!";
    } else {
        echo "Lỗi khi xóa: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
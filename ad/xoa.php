<?php
function delete($table, $idColumn)
{
    include ".././connect_DB/connect_db.php";
    $conn = connectData();

    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        $sql = "DELETE FROM $table WHERE $idColumn = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: .././admin.php?page=qLsp");
            exit();
        } else {
            echo "Lỗi khi xóa: " . $conn->error;
        }

        $stmt->close();
        $conn->close();
    }
}

<?php
function sua($tenbang, $truong_can_sua, $redirect, $ids)
{
    include "../connect_DB/connect_db.php";
    $conn = connectData();

    if (!isset($_GET['$ids'])) {
        echo "Không có ID được chọn để sửa.";
        return;
    }

    $id = intval($_GET['$ids']);

    $sql = "SELECT * FROM $tenbang WHERE $ids = $id";
    $result = $conn->query($sql);
    if (!$result || $result->num_rows == 0) {
        echo "Không tìm thấy dữ liệu để sửa.";
        return;
    }

    $row = $result->fetch_assoc();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $data_update = [];

        foreach ($truong_can_sua as $truong) {
            if ($truong === 'Anh' && isset($_FILES['Anh']) && $_FILES['Anh']['error'] == 0) {
                $target_dir = "../assets/img/";
                $target_file = $target_dir . basename($_FILES['Anh']['name']);
                move_uploaded_file($_FILES["Anh"]["tmp_name"], $target_file);
                $data_update['Anh'] = basename($_FILES['Anh']['name']);
            } elseif (isset($_POST[$truong])) {
                $data_update[$truong] = $_POST[$truong];
            } else {
                $data_update[$truong] = $row[$truong];
            }
        }

        // Build câu lệnh UPDATE
        $set_parts = [];
        foreach ($data_update as $key => $val) {
            $escaped_val = $conn->real_escape_string($val);
            $set_parts[] = "$key = '$escaped_val'";
        }

        $sql_update = "UPDATE $tenbang SET " . implode(", ", $set_parts) . " WHERE id = $id";

        if ($conn->query($sql_update) === TRUE) {
            header("Location: $redirect");
            exit();
        } else {
            echo "Lỗi cập nhật: " . $conn->error;
        }
    }

    return $row;
}

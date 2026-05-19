<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['idtk'])) {
    $id = (int)$_GET['idtk'];

    // Start database transaction
    $conn->begin_transaction();

    try {
        // Get the associated iduser first
        $stmt_get = $conn->prepare("SELECT iduser FROM users WHERE idtk = ?");
        $stmt_get->bind_param("i", $id);
        if (!$stmt_get->execute()) {
            throw new Exception($stmt_get->error);
        }
        $res = $stmt_get->get_result()->fetch_assoc();
        $iduser = $res ? (int)$res['iduser'] : null;
        $stmt_get->close();

        if ($iduser) {
            // 1. Delete from chitietdonhang first (since it depends on donhang)
            $stmt_ct = $conn->prepare("DELETE FROM chitietdonhang WHERE iddonhang IN (SELECT idDonHang FROM donhang WHERE idKhach = ?)");
            $stmt_ct->bind_param("i", $iduser);
            if (!$stmt_ct->execute()) {
                throw new Exception($stmt_ct->error);
            }
            $stmt_ct->close();

            // 2. Delete from donhang
            $stmt_dh = $conn->prepare("DELETE FROM donhang WHERE idKhach = ?");
            $stmt_dh->bind_param("i", $iduser);
            if (!$stmt_dh->execute()) {
                throw new Exception($stmt_dh->error);
            }
            $stmt_dh->close();

            // 3. Delete from giohang
            $stmt_gio = $conn->prepare("DELETE FROM giohang WHERE iduser = ?");
            $stmt_gio->bind_param("i", $iduser);
            if (!$stmt_gio->execute()) {
                throw new Exception($stmt_gio->error);
            }
            $stmt_gio->close();

            // 4. Delete from users profile
            $stmt_user = $conn->prepare("DELETE FROM users WHERE idtk = ?");
            $stmt_user->bind_param("i", $id);
            if (!$stmt_user->execute()) {
                throw new Exception($stmt_user->error);
            }
            $stmt_user->close();
        }

        // 5. Delete from taikhoan account credentials
        $stmt_tk = $conn->prepare("DELETE FROM taikhoan WHERE idtk = ?");
        $stmt_tk->bind_param("i", $id);
        if (!$stmt_tk->execute()) {
            throw new Exception($stmt_tk->error);
        }
        $stmt_tk->close();

        // Commit transaction
        $conn->commit();
        header("Location: .././admin.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Lỗi khi xóa: " . $e->getMessage();
    }

    $conn->close();
}
?>
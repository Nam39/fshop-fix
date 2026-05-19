<?php
include ".././connect_DB/connect_db.php";
$conn = connectData();

if (isset($_GET['iduser'])) {
    $id = (int)$_GET['iduser'];

    // Get idtk associated with this user first
    $stmt_get = $conn->prepare("SELECT idtk FROM users WHERE iduser = ?");
    $stmt_get->bind_param("i", $id);
    if (!$stmt_get->execute()) {
        echo "Lỗi khi truy vấn: " . $stmt_get->error;
        exit();
    }
    $res = $stmt_get->get_result()->fetch_assoc();
    $idtk = $res ? (int)$res['idtk'] : null;
    $stmt_get->close();

    // Start database transaction
    $conn->begin_transaction();

    try {
        // 1. Delete from chitietdonhang first (since it depends on donhang)
        $stmt_ct = $conn->prepare("DELETE FROM chitietdonhang WHERE iddonhang IN (SELECT idDonHang FROM donhang WHERE idKhach = ?)");
        $stmt_ct->bind_param("i", $id);
        if (!$stmt_ct->execute()) {
            throw new Exception($stmt_ct->error);
        }
        $stmt_ct->close();

        // 2. Delete from donhang
        $stmt_dh = $conn->prepare("DELETE FROM donhang WHERE idKhach = ?");
        $stmt_dh->bind_param("i", $id);
        if (!$stmt_dh->execute()) {
            throw new Exception($stmt_dh->error);
        }
        $stmt_dh->close();

        // 3. Delete from giohang first
        $stmt_gio = $conn->prepare("DELETE FROM giohang WHERE iduser = ?");
        $stmt_gio->bind_param("i", $id);
        if (!$stmt_gio->execute()) {
            throw new Exception($stmt_gio->error);
        }
        $stmt_gio->close();

        // 4. Delete from users profile
        $stmt_user = $conn->prepare("DELETE FROM users WHERE iduser = ?");
        $stmt_user->bind_param("i", $id);
        if (!$stmt_user->execute()) {
            throw new Exception($stmt_user->error);
        }
        $stmt_user->close();

        // 5. Delete from taikhoan account credentials if exists
        if ($idtk) {
            $stmt_tk = $conn->prepare("DELETE FROM taikhoan WHERE idtk = ?");
            $stmt_tk->bind_param("i", $idtk);
            if (!$stmt_tk->execute()) {
                throw new Exception($stmt_tk->error);
            }
            $stmt_tk->close();
        }

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
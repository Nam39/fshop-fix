<?php
session_start();
include "./connect_DB/connect_db.php";

// Kiểm tra đăng nhập
if (!isset($_SESSION['idtk'])) {
    header("Location: details.php?error=notloggedin");
    exit();
}

$conn = connectData();
$idtk = $_SESSION['idtk'];

// Truy vấn iduser từ bảng users dựa vào idtk
$stmt = $conn->prepare("SELECT iduser FROM users WHERE idtk = ?");
$stmt->bind_param("i", $idtk);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Không tìm thấy người dùng tương ứng với tài khoản này.");
}

$row = $result->fetch_assoc();
$iduser = $row['iduser'];

// Lấy dữ liệu sản phẩm
$idsanpham = isset($_POST['idsanpham']) ? intval($_POST['idsanpham']) : 0;
$soluong = isset($_POST['soluong']) ? intval($_POST['soluong']) : 1;

// Kiểm tra tồn kho
$stmt = $conn->prepare("SELECT soluong FROM sanpham WHERE id = ?");
$stmt->bind_param("i", $idsanpham);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: details.php?error=notfound");
    exit();
}

$row = $result->fetch_assoc();
$soluong_tonkho = intval($row['soluong']);

// Kiểm tra giỏ hàng hiện có
$stmt = $conn->prepare("SELECT soluong FROM giohang WHERE iduser = ? AND idsanpham = ?");
$stmt->bind_param("ii", $iduser, $idsanpham);
$stmt->execute();
$result = $stmt->get_result();

$soluong_hientai = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $soluong_hientai = intval($row['soluong']);
}

$tong_soluong = $soluong_hientai + $soluong;

// Kiểm tra vượt tồn kho
if ($tong_soluong > $soluong_tonkho) {
    header("Location: detail.php?id=$idsanpham&error=overstock");
    exit();
}

// Thêm hoặc cập nhật giỏ hàng
if ($soluong_hientai > 0) {
    $stmt = $conn->prepare("UPDATE giohang SET soluong = soluong + ? WHERE iduser = ? AND idsanpham = ?");
    $stmt->bind_param("iii", $soluong, $iduser, $idsanpham);
} else {
    $stmt = $conn->prepare("INSERT INTO giohang (iduser, idsanpham, soluong) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $iduser, $idsanpham, $soluong);
}

$stmt->execute();

// Chuyển hướng sau khi thêm thành công
header("Location: giohang.php");
exit();

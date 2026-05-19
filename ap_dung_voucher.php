<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
include_once "./connect_DB/connect_db.php";

if (!isset($_SESSION['idtk'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để áp dụng mã giảm giá.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Yêu cầu không hợp lệ!'
    ]);
    exit();
}

$conn = connectData();

$code = strtoupper(trim($_POST['code'] ?? ''));
$tong_tien = (int)($_POST['tong_tien'] ?? 0);

if (empty($code)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập mã giảm giá!'
    ]);
    exit();
}

// Query voucher details
$stmt = $conn->prepare("SELECT * FROM voucher WHERE ma_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Mã giảm giá không tồn tại!'
    ]);
    $stmt->close();
    exit();
}

$voucher = $result->fetch_assoc();
$stmt->close();

// 1. Check status
if ($voucher['trang_thai'] != 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Mã giảm giá này đã tạm ngưng hoạt động!'
    ]);
    exit();
}

// 2. Check quantity
if ($voucher['so_luong'] <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Mã giảm giá này đã hết lượt sử dụng!'
    ]);
    exit();
}

// 3. Check expiration
$today = date('Y-m-d');
if (strtotime($voucher['ngay_het_han']) < strtotime($today)) {
    echo json_encode([
        'success' => false,
        'message' => 'Mã giảm giá này đã hết hạn sử dụng!'
    ]);
    exit();
}

// 4. Calculate discount
$giam_gia = 0;
if ($voucher['loai_giam'] == 0) {
    // Fixed amount discount
    $giam_gia = $voucher['giam_gia'];
    if ($giam_gia > $tong_tien) {
        $giam_gia = $tong_tien; // Discount cannot exceed total price
    }
} else {
    // Percentage discount
    $giam_gia = round(($tong_tien * $voucher['giam_gia']) / 100);
}

$tong_tien_moi = $tong_tien - $giam_gia;
if ($tong_tien_moi < 0) {
    $tong_tien_moi = 0;
}

echo json_encode([
    'success' => true,
    'message' => 'Áp dụng mã giảm giá thành công!',
    'id_voucher' => $voucher['id_voucher'],
    'ma_code' => $voucher['ma_code'],
    'giam_gia' => $giam_gia,
    'tong_tien_moi' => $tong_tien_moi
]);
$conn->close();
?>

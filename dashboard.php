<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once 'connect_DB/connect_db.php';
$conn = connectData();

// Access control check
if (!isset($_SESSION['idtk']) || !isset($_SESSION['roleId']) || $_SESSION['roleId'] != 1) {
    header("Location: login.php");
    exit();
}

// ----------------- FETCH DYNAMIC METRICS -----------------

// 1. Total Completed Revenue (Trạng thái = 3: Hoàn thành)
$revenue_query = "SELECT SUM(tongtien) AS total_revenue FROM donhang WHERE trangthai = 3";
$revenue_result = $conn->query($revenue_query);
$revenue_row = $revenue_result->fetch_assoc();
$total_revenue = $revenue_row['total_revenue'] ?? 0;

// 2. Total Orders count
$orders_query = "SELECT COUNT(*) AS total_orders FROM donhang";
$orders_result = $conn->query($orders_query);
$orders_row = $orders_result->fetch_assoc();
$total_orders = $orders_row['total_orders'] ?? 0;

// 3. Total Products count
$products_query = "SELECT COUNT(*) AS total_products FROM sanpham";
$products_result = $conn->query($products_query);
$products_row = $products_result->fetch_assoc();
$total_products = $products_row['total_products'] ?? 0;

// 4. Total Customers count (roleId != 1 represent customers usually, or total users)
$users_query = "SELECT COUNT(*) AS total_users FROM users";
$users_result = $conn->query($users_query);
$users_row = $users_result->fetch_assoc();
$total_users = $users_row['total_users'] ?? 0;


// ----------------- MONTHLY SALES QUERY FOR LINE CHART -----------------
// We fetch monthly aggregated sales of completed orders for the current year
$current_year = date('Y');
$monthly_sales_query = "
    SELECT MONTH(ngaydathang) AS month_num, SUM(tongtien) AS sales 
    FROM donhang 
    WHERE trangthai = 3 AND YEAR(ngaydathang) = ?
    GROUP BY MONTH(ngaydathang)
    ORDER BY MONTH(ngaydathang)
";
$stmt = $conn->prepare($monthly_sales_query);
$stmt->bind_param("i", $current_year);
$stmt->execute();
$sales_result = $stmt->get_result();

$monthly_data = array_fill(1, 12, 0); // Initialize all 12 months to 0
while ($row = $sales_result->fetch_assoc()) {
    $monthly_data[(int)$row['month_num']] = (double)$row['sales'];
}
$stmt->close();

// Convert to simple JSON array for JS chart
$js_monthly_sales = json_encode(array_values($monthly_data));


// ----------------- STATUS BREAKDOWN FOR Doughnut CHART -----------------
$status_query = "SELECT trangthai, COUNT(*) AS count FROM donhang GROUP BY trangthai";
$status_result = $conn->query($status_query);
$status_counts = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0];
while ($row = $status_result->fetch_assoc()) {
    $status_counts[(int)$row['trangthai']] = (int)$row['count'];
}
$js_status_counts = json_encode(array_values($status_counts));


// ----------------- FETCH 5 RECENT ORDERS -----------------
$recent_orders_query = "
    SELECT dh.*, u.Ten_user 
    FROM donhang dh
    LEFT JOIN users u ON dh.idKhach = u.iduser
    ORDER BY dh.idDonHang DESC
    LIMIT 5
";
$recent_orders = $conn->query($recent_orders_query);

// Helper function for status badges
function getDashboardStatusBadge($status) {
    switch ($status) {
        case 0:
            return '<span class="badge rounded-pill px-2.5 py-1.5 fw-bold text-warning border border-warning-subtle bg-warning-subtle">Chờ xử lý</span>';
        case 1:
            return '<span class="badge rounded-pill px-2.5 py-1.5 fw-bold text-primary border border-primary-subtle bg-primary-subtle">Đã xác nhận</span>';
        case 2:
            return '<span class="badge rounded-pill px-2.5 py-1.5 fw-bold text-info border border-info-subtle bg-info-subtle">Đang giao</span>';
        case 3:
            return '<span class="badge rounded-pill px-2.5 py-1.5 fw-bold text-success border border-success-subtle bg-success-subtle">Hoàn thành</span>';
        case 4:
            return '<span class="badge rounded-pill px-2.5 py-1.5 fw-bold text-danger border border-danger-subtle bg-danger-subtle">Đã hủy</span>';
        default:
            return '<span class="badge rounded-pill px-2.5 py-1.5 fw-bold text-secondary border border-secondary-subtle bg-secondary-subtle">Không rõ</span>';
    }
}
?>

<head>
    <!-- Outfit Font and Chart.js library -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<style>
    .dashboard-container {
        font-family: 'Outfit', sans-serif;
    }

    .dashboard-title-bar {
        margin-bottom: 2rem;
    }

    .dashboard-title {
        font-weight: 800;
        color: #0f172a;
        font-size: 1.6rem;
    }

    /* Metric Cards */
    .metric-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
    }

    .metric-label {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 8px;
    }

    .metric-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0;
    }

    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    /* Color styles for icons */
    .icon-revenue { background-color: rgba(16, 185, 129, 0.1); color: #10b981; }
    .icon-orders { background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .icon-products { background-color: rgba(99, 102, 241, 0.1); color: #6366f1; }
    .icon-users { background-color: rgba(168, 85, 247, 0.1); color: #a855f7; }

    /* Visual Blocks */
    .visual-block {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        padding: 24px;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
        height: 100%;
    }

    .block-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1.5rem;
        padding-bottom: 10px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Monospace ID badge */
    .mono-badge {
        font-family: monospace;
        font-weight: 700;
        font-size: 0.9rem;
        color: #475569;
        background-color: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .table-recent th {
        font-weight: 700;
        color: #64748b;
        font-size: 0.8rem;
        text-transform: uppercase;
        border-bottom: 2px solid #f1f5f9;
        padding: 12px 10px;
    }

    .table-recent td {
        padding: 14px 10px;
        vertical-align: middle;
        font-size: 0.9rem;
        border-bottom: 1px solid #f8fafc;
    }
</style>

<div class="dashboard-container mb-5">
    
    <!-- HEADER INTRO -->
    <div class="dashboard-title-bar d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h2 class="dashboard-title mb-1">
                👋 Xin chào, <?= htmlspecialchars($_SESSION['Ten_user'] ?? 'Quản trị viên') ?>
            </h2>
            <p class="text-secondary small mb-0">Hôm nay là <?= date('d/m/Y') ?>. Dưới đây là thông số hoạt động của cửa hàng thời trang UNIQ.</p>
        </div>
        <a href="admin.php?page=qldh" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
            <i class="fa-solid fa-cart-shopping me-2"></i> Xử lý đơn hàng
        </a>
    </div>

    <!-- METRICS GRID -->
    <div class="row g-4 mb-4">
        <!-- 1. Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div>
                    <div class="metric-label">Tổng doanh thu</div>
                    <h3 class="metric-value"><?= number_format($total_revenue, 0, ',', '.') ?>đ</h3>
                </div>
                <div class="metric-icon icon-revenue">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
        </div>

        <!-- 2. Orders -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div>
                    <div class="metric-label">Tổng đơn hàng</div>
                    <h3 class="metric-value"><?= number_format($total_orders) ?></h3>
                </div>
                <div class="metric-icon icon-orders">
                    <i class="fa-solid fa-cubes"></i>
                </div>
            </div>
        </div>

        <!-- 3. Products -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div>
                    <div class="metric-label">Sản phẩm hiện có</div>
                    <h3 class="metric-value"><?= number_format($total_products) ?></h3>
                </div>
                <div class="metric-icon icon-products">
                    <i class="fa-solid fa-shirt"></i>
                </div>
            </div>
        </div>

        <!-- 4. Customers -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div>
                    <div class="metric-label">Khách hàng đăng ký</div>
                    <h3 class="metric-value"><?= number_format($total_users) ?></h3>
                </div>
                <div class="metric-icon icon-users">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- CHARTS SECTION -->
    <div class="row g-4 mb-4">
        <!-- Revenue line chart -->
        <div class="col-lg-7">
            <div class="visual-block">
                <h5 class="block-title"><i class="fa-solid fa-chart-line text-primary"></i> Biểu đồ doanh thu năm <?= $current_year ?> (VNĐ)</h5>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Status breakdown Doughnut chart -->
        <div class="col-lg-5">
            <div class="visual-block">
                <h5 class="block-title"><i class="fa-solid fa-chart-pie text-indigo"></i> Tỷ lệ trạng thái đơn hàng</h5>
                <div style="position: relative; height: 320px; width: 100%; display: flex; align-items: center; justify-content: center;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT TRANSACTIONS TABLE -->
    <div class="row">
        <div class="col-12">
            <div class="visual-block">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="m-0 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-receipt text-secondary"></i> Hóa đơn giao dịch mới nhất
                    </h5>
                    <a href="admin.php?page=qldh" class="text-primary fw-bold text-decoration-none small">
                        Xem tất cả <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-recent text-center align-middle m-0">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th style="text-align: left;">Khách hàng</th>
                                <th>Ngày mua</th>
                                <th>Tổng thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="mono-badge">#<?= $order['idDonHang'] ?></span>
                                        </td>
                                        <td class="text-start fw-semibold text-dark">
                                            <?= !empty($order['Ten_user']) ? htmlspecialchars($order['Ten_user']) : 'Khách hàng #' . $order['idKhach'] ?>
                                        </td>
                                        <td class="text-secondary small">
                                            <?= date('d/m/Y H:i', strtotime($order['ngaydathang'])) ?>
                                        </td>
                                        <td class="fw-bold text-primary">
                                            <?= number_format($order['tongtien'], 0, ',', '.') ?>đ
                                        </td>
                                        <td>
                                            <?= getDashboardStatusBadge($order['trangthai']) ?>
                                        </td>
                                        <td>
                                            <a href="./ad/suadonhang.php?id=<?= $order['idDonHang'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                                Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-muted py-4">Chưa có giao dịch phát sinh.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- CHARTS LOGIC SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Monthly Revenue Area Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    
    // Create gradient fill
    const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
    revenueGradient.addColorStop(0, 'rgba(13, 110, 253, 0.35)');
    revenueGradient.addColorStop(1, 'rgba(13, 110, 253, 0.00)');

    const monthlySalesData = <?= $js_monthly_sales ?>;
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
            datasets: [{
                label: 'Doanh thu',
                data: monthlySalesData,
                borderColor: '#0d6efd',
                borderWidth: 3,
                backgroundColor: revenueGradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#0d6efd',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        callback: function(value) {
                            return value >= 1000000 ? (value / 1000000) + 'M' : value;
                        },
                        color: '#64748b',
                        font: { family: 'Outfit', weight: '600' }
                    },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#64748b',
                        font: { family: 'Outfit', weight: '600' }
                    }
                }
            }
        }
    });

    // 2. Order Status Doughnut Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const orderCounts = <?= $js_status_counts ?>;

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Chờ xử lý', 'Đã xác nhận', 'Đang giao', 'Hoàn thành', 'Đã hủy'],
            datasets: [{
                data: orderCounts,
                backgroundColor: [
                    'rgba(245, 158, 11, 0.85)', // Amber / Warning
                    'rgba(79, 70, 229, 0.85)',  // Indigo / Primary
                    'rgba(14, 165, 233, 0.85)',  // Sky Blue / Info
                    'rgba(16, 185, 129, 0.85)',  // Mint Green / Success
                    'rgba(239, 68, 68, 0.85)'    // Rose / Danger
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: { family: 'Outfit', weight: '600', size: 12 },
                        color: '#475569'
                    }
                }
            },
            cutout: '68%'
        }
    });
});
</script>

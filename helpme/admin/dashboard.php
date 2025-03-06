<?php
require_once 'check_login.php';
require_once '../config/db.php';

// ดึงสถิติต่างๆ
$stats = [
    'total_bookings' => $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'],
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_flights' => $conn->query("SELECT COUNT(*) as count FROM flights")->fetch_assoc()['count'],
    'total_revenue' => $conn->query("SELECT SUM(total_amount) as sum FROM bookings WHERE payment_status = 'completed'")->fetch_assoc()['sum']
];

// ดึงการจองล่าสุด
$recent_bookings = $conn->query("
    SELECT b.*, u.username, f.flight_number 
    FROM bookings b 
    JOIN users u ON b.user_id = u.user_id 
    JOIN flights f ON b.flight_id = f.flight_id 
    ORDER BY b.booking_date DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EliteTix</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="dashboard.php">
                                <i class='bx bxs-dashboard'></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="flights.php">
                                <i class='bx bxs-plane'></i> จัดการเที่ยวบิน
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="airports.php">
                                <i class='bx bxs-buildings'></i> จัดการสนามบิน
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="airlines.php">
                                <i class='bx bxs-plane-take-off'></i> จัดการสายการบิน
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="logout.php">
                                <i class='bx bxs-log-out'></i> ออกจากระบบ
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">การจองทั้งหมด</h5>
                                <h2><?php echo number_format($stats['total_bookings']); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">รายได้รวม</h5>
                                <h2>฿<?php echo number_format($stats['total_revenue'], 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">ผู้ใช้ทั้งหมด</h5>
                                <h2><?php echo number_format($stats['total_users']); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">เที่ยวบินทั้งหมด</h5>
                                <h2><?php echo number_format($stats['total_flights']); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">การจองล่าสุด</h5>
                        <a href="all_bookings.php" class="btn btn-primary">
                            <i class='bx bx-list-ul'></i> ดูการจองทั้งหมด
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>เลขที่การจอง</th>
                                        <th>ผู้ใช้</th>
                                        <th>เที่ยวบิน</th>
                                        <th>วันที่จอง</th>
                                        <th>สถานะ</th>
                                        <th>ราคารวม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $booking['booking_number']; ?></td>
                                            <td><?php echo $booking['username']; ?></td>
                                            <td><?php echo $booking['flight_number']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($booking['booking_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                                        echo $booking['status'] == 'confirmed' ? 'success' : ($booking['status'] == 'pending' ? 'warning' : ($booking['status'] == 'cancelled' ? 'danger' : 'info'));
                                                                        ?>">
                                                    <?php echo $booking['status']; ?>
                                                </span>
                                            </td>
                                            <td>฿<?php echo number_format($booking['total_amount'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
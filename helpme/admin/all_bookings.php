<?php
require_once 'check_login.php';
require_once '../config/db.php';

$all_bookings = $conn->query("
    SELECT b.*, u.username, f.flight_number 
    FROM bookings b 
    JOIN users u ON b.user_id = u.user_id 
    JOIN flights f ON b.flight_id = f.flight_id 
    ORDER BY b.booking_date DESC
");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การจองทั้งหมด - EliteTix</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid mt-4">
        <h2>การจองทั้งหมด</h2>
        <a href="dashboard.php" class="btn btn-secondary mb-3">
            <i class='bx bx-arrow-back'></i> กลับไปหน้า Dashboard
        </a>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
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
                            <?php while($booking = $all_bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $booking['booking_number']; ?></td>
                                <td><?php echo $booking['username']; ?></td>
                                <td><?php echo $booking['flight_number']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($booking['booking_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $booking['status'] == 'confirmed' ? 'success' : 
                                            ($booking['status'] == 'pending' ? 'warning' : 
                                            ($booking['status'] == 'cancelled' ? 'danger' : 'info')); 
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
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
include 'config/db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ดึงข้อมูลการจองทั้งหมดของผู้ใช้
$sql = "SELECT b.*, f.flight_number, f.departure_time, f.arrival_time,
        a1.airport_name as origin_airport, a2.airport_name as destination_airport,
        air.airline_name
        FROM bookings b
        JOIN flights f ON b.flight_id = f.flight_id
        JOIN airports a1 ON f.origin_airport = a1.airport_id
        JOIN airports a2 ON f.destination_airport = a2.airport_id
        JOIN airlines air ON f.airline_id = air.airline_id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ประวัติการจอง - EliteTix</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Prompt', sans-serif;
        }

        .container {
            max-width: 1200px;
        }

        .page-header {
            background: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .booking-list {
            margin-top: 30px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            background: white;
            margin-bottom: 25px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-body {
            padding: 25px;
        }

        .card-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .flight-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .badge {
            font-size: 0.9em;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }

        .route-arrow {
            color: #6c757d;
            margin: 0 10px;
            font-size: 1.2em;
        }

        .price-tag {
            font-size: 1.3em;
            color: #2c3e50;
            font-weight: 600;
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 10px;
            display: inline-block;
        }

        .airline-logo {
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }

        .info-label {
            color: #6c757d;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .info-value {
            color: #2c3e50;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <a href="index.php" class="btn btn-primary mb-3">
            <i class="fas fa-arrow-left me-2"></i>กลับไปหน้าหลัก
        </a>

        <div class="page-header">
            <h2><i class="fas fa-history me-2"></i>ประวัติการจอง</h2>
            <p class="mb-0">รายการจองทั้งหมดของคุณ</p>
        </div>

        <div class="booking-list">
            <?php foreach ($bookings as $booking): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-ticket-alt me-2"></i>
                            เลขที่การจอง: <?php echo $booking['booking_number']; ?>
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="flight-info">
                                    <div class="mb-3">
                                        <span class="info-label">สายการบิน</span>
                                        <div class="info-value">
                                            <i class="fas fa-plane me-2"></i>
                                            <?php echo $booking['airline_name']; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="info-label">เที่ยวบิน</span>
                                        <div class="info-value">
                                            <i class="fas fa-hashtag me-2"></i>
                                            <?php echo $booking['flight_number']; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="info-label">เส้นทาง</span>
                                        <div class="info-value">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <?php echo $booking['origin_airport']; ?>
                                            <i class="fas fa-long-arrow-alt-right route-arrow"></i>
                                            <?php echo $booking['destination_airport']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="flight-info">
                                    <div class="mb-3">
                                        <span class="info-label">วันที่จอง</span>
                                        <div class="info-value">
                                            <i class="far fa-calendar-alt me-2"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($booking['booking_date'])); ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="info-label">สถานะการจอง</span>
                                        <div>
                                            <span class="badge bg-<?php echo getStatusColor($booking['status']); ?>">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <?php echo getStatusText($booking['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="info-label">สถานะการชำระเงิน</span>
                                        <div>
                                            <span class="badge bg-<?php echo getPaymentStatusColor($booking['payment_status']); ?>">
                                                <i class="fas fa-money-bill-wave me-1"></i>
                                                <?php echo getPaymentStatusText($booking['payment_status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <?php if ($booking['status'] != 'cancelled' && $booking['payment_status'] == 'completed'): ?>
                                <a href="booking_confirmation.php?booking_id=<?php echo $booking['booking_id']; ?>"
                                    class="btn btn-success me-2">
                                    <i class="fas fa-download me-2"></i>ดาวน์โหลดตั๋ว
                                </a>
                            <?php endif; ?>
                            <div class="price-tag">
                                <i class="fas fa-tags me-2"></i>
                                ยอดรวม: <?php echo number_format($booking['total_amount'], 2); ?> บาท
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
function getStatusText($status)
{
    $statusMap = [
        'pending' => 'รอดำเนินการ',
        'confirmed' => 'ยืนยันแล้ว',
        'checked_in' => 'เช็คอินแล้ว',
        'completed' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก'
    ];
    return $statusMap[$status] ?? $status;
}

function getStatusColor($status)
{
    $colorMap = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'checked_in' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger'
    ];
    return $colorMap[$status] ?? 'secondary';
}

function getPaymentStatusText($status)
{
    $statusMap = [
        'pending' => 'รอชำระเงิน',
        'processing' => 'กำลังดำเนินการ',
        'completed' => 'ชำระเงินแล้ว',
        'failed' => 'ชำระเงินไม่สำเร็จ',
        'refunded' => 'คืนเงินแล้ว'
    ];
    return $statusMap[$status] ?? $status;
}

function getPaymentStatusColor($status)
{
    $colorMap = [
        'pending' => 'warning',
        'processing' => 'info',
        'completed' => 'success',
        'failed' => 'danger',
        'refunded' => 'secondary'
    ];
    return $colorMap[$status] ?? 'secondary';
}
?>
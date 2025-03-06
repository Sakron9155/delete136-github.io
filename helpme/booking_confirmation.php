<?php
include 'config/db.php';

// รับ booking_id จาก URL
$booking_id = $_GET['booking_id'];

// ดึงข้อมูลการจอง รายละเอียดเที่ยวบิน และข้อมูลผู้โดยสาร
$sql = "SELECT b.*, f.flight_number, f.departure_time, f.arrival_time,
        a1.airport_name as origin_name, a1.airport_code as origin_code,
        a2.airport_name as destination_name, a2.airport_code as destination_code,
        al.airline_name, al.airline_code,
        p.payment_method, p.transaction_id
        FROM bookings b
        JOIN flights f ON b.flight_id = f.flight_id
        JOIN airports a1 ON f.origin_airport = a1.airport_id
        JOIN airports a2 ON f.destination_airport = a2.airport_id
        JOIN airlines al ON f.airline_id = al.airline_id
        JOIN payments p ON b.booking_id = p.booking_id
        WHERE b.booking_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

// ดึงข้อมูลผู้โดยสารและที่นั่ง
$sql = "SELECT bp.*, s.seat_number, sc.class_name
        FROM booking_passengers bp
        JOIN seats s ON bp.seat_id = s.seat_id
        JOIN seat_classes sc ON s.class_id = sc.class_id
        WHERE bp.booking_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$passengers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/confirmation.css">
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .confirmation-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .success-message {
            text-align: center;
            margin-bottom: 40px;
        }

        .success-icon {
            background: #4CAF50;
            color: white;
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            font-size: 40px;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        }

        .flight-info {
            border-bottom: 2px solid #eee;
            padding: 20px 0;
            margin-bottom: 20px;
        }

        .route-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 30px 0;
        }

        .flight-path {
            flex-grow: 1;
            text-align: center;
            position: relative;
        }

        .plane-icon {
            font-size: 24px;
            color: #2196F3;
            animation: fly 3s infinite;
        }

        .passenger-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #2196F3;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-success {
            background: #4CAF50;
            color: white;
        }

        .home-button {
            background: #2196F3;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        @keyframes fly {
            0% {
                transform: translateX(-50px);
            }

            50% {
                transform: translateX(50px);
            }

            100% {
                transform: translateX(-50px);
            }
        }

        .airline-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .airline-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .payment-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .total-amount {
            font-size: 1.2em;
            color: #2196F3;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="confirmation-page">
            <div class="success-message">
                <div class="success-icon animate__animated animate__bounceIn">✓</div>
                <h1 class="animate__animated animate__fadeInDown">การจองสำเร็จ!</h1>
                <p class="animate__animated animate__fadeInUp">ขอบคุณที่ใช้บริการ EliteTix</p>
            </div>

            <div class="booking-details glass-effect">
                <div class="booking-header">
                    <h2><i class="fas fa-ticket-alt"></i> รายละเอียดการจอง</h2>
                    <p class="booking-number">รหัสการจอง: <?php echo $booking['booking_number']; ?></p>
                </div>

                <div class="flight-info">
                    <div class="airline-info">
                        <img src="<?php echo $booking['logo_path']; ?>" alt="<?php echo $booking['airline_name']; ?>" class="airline-logo">
                        <h3><?php echo $booking['airline_name']; ?> (<?php echo $booking['flight_number']; ?>)</h3>
                    </div>

                    <div class="route-info">
                        <div class="origin">
                            <h4><?php echo $booking['origin_code']; ?></h4>
                            <p><?php echo $booking['origin_name']; ?></p>
                            <p class="time"><?php echo date('H:i', strtotime($booking['departure_time'])); ?></p>
                        </div>

                        <div class="flight-path">
                            <span class="plane-icon"><i class="fas fa-plane"></i></span>
                        </div>

                        <div class="destination">
                            <h4><?php echo $booking['destination_code']; ?></h4>
                            <p><?php echo $booking['destination_name']; ?></p>
                            <p class="time"><?php echo date('H:i', strtotime($booking['arrival_time'])); ?></p>
                        </div>
                    </div>

                    <div class="date-info">
                        <p><i class="far fa-calendar-alt"></i> วันที่: <?php echo date('d/m/Y', strtotime($booking['departure_time'])); ?></p>
                    </div>
                </div>

                <div class="passengers-info">
                    <h3><i class="fas fa-users"></i> ข้อมูลผู้โดยสาร</h3>
                    <?php foreach ($passengers as $passenger): ?>
                        <div class="passenger-card">
                            <div class="passenger-details">
                                <p class="passenger-name"><i class="fas fa-user"></i> <?php echo $passenger['first_name'] . ' ' . $passenger['last_name']; ?></p>
                                <p class="seat-info"><i class="fas fa-chair"></i> ที่นั่ง: <?php echo $passenger['seat_number']; ?> (<?php echo $passenger['class_name']; ?>)</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="payment-info">
                    <h3><i class="fas fa-credit-card"></i> ข้อมูลการชำระเงิน</h3>
                    <p><i class="fas fa-money-bill-wave"></i> วิธีการชำระเงิน: <?php echo $booking['payment_method']; ?></p>
                    <p><i class="fas fa-receipt"></i> เลขที่ธุรกรรม: <?php echo $booking['transaction_id']; ?></p>
                    <p class="total-amount"><i class="fas fa-tags"></i> ยอดรวมทั้งสิ้น: <?php echo number_format($booking['total_amount'], 2); ?> บาท</p>
                </div>

                <div class="action-buttons">
                    <a href="print_ticket.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-success" target="_blank">
                        <i class="fas fa-print"></i> พิมพ์ตั๋ว
                    </a>
                    <a href="index.php" class="home-button">
                        <i class="fas fa-home"></i> กลับหน้าหลัก
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
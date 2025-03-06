<?php
include 'config/db.php';

$booking_id = $_GET['booking_id'];

// ใช้ query เดียวกับที่มีใน booking_confirmation.php
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

// ดึงข้อมูลผู้โดยสาร
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
    <title>E-Ticket - <?php echo $booking['booking_number']; ?></title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        @media print {
            body {
                width: 21cm;
                height: 29.7cm;
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
        
        body {
            font-family: 'Prompt', sans-serif;
            line-height: 1.6;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .ticket-container {
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 40px;
            max-width: 800px;
            margin: 20px auto;
        }
        
        .ticket-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px dashed #e0e0e0;
            margin-bottom: 30px;
        }
        
        .company-logo {
            width: 150px;
            margin-bottom: 15px;
        }
        
        .booking-number {
            font-size: 24px;
            color: #2196F3;
            font-weight: 600;
            margin: 10px 0;
        }
        
        .flight-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .flight-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 20px 0;
            position: relative;
        }
        
        .flight-route::after {
            content: '✈️';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .passenger-info {
            background: #fff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .payment-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .total-amount {
            font-size: 24px;
            color: #2196F3;
            text-align: right;
            padding: 10px 0;
            border-top: 2px dashed #e0e0e0;
            margin-top: 15px;
        }
        
        .barcode {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            border-top: 2px dashed #e0e0e0;
        }
        
        .barcode img {
            max-width: 300px;
        }
        
        .print-button {
            background: #2196F3;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            margin: 20px auto;
            transition: background 0.3s;
        }
        
        .print-button:hover {
            background: #1976D2;
        }
        
        .footer {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px dashed #e0e0e0;
        }

        .qr-code {
            text-align: center;
            margin: 20px 0;
        }

        .ticket-notice {
            font-size: 12px;
            color: #666;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">พิมพ์ตั๋ว</button>
    
    <div class="ticket-container">
        <div class="ticket-header">
            <img src="images/logo.png" alt="EliteTix" class="company-logo">
            <h1>E-Ticket & ใบเสร็จรับเงิน</h1>
            <div class="booking-number">Booking Ref: <?php echo $booking['booking_number']; ?></div>
            <p>ออกเมื่อ: <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <div class="flight-route">
            <div class="origin">
                <h3><?php echo $booking['origin_code']; ?></h3>
                <p><?php echo $booking['origin_name']; ?></p>
                <p><?php echo date('H:i', strtotime($booking['departure_time'])); ?></p>
            </div>
            <div class="destination">
                <h3><?php echo $booking['destination_code']; ?></h3>
                <p><?php echo $booking['destination_name']; ?></p>
                <p><?php echo date('H:i', strtotime($booking['arrival_time'])); ?></p>
            </div>
        </div>

        <div class="flight-details">
            <div>
                <h3><?php echo $booking['airline_name']; ?></h3>
                <p>เที่ยวบิน: <?php echo $booking['flight_number']; ?></p>
            </div>
            <div>
                <h3>วันที่เดินทาง</h3>
                <p><?php echo date('d/m/Y', strtotime($booking['departure_time'])); ?></p>
            </div>
            <div>
                <h3>สถานะ</h3>
                <p style="color: #4CAF50;">ยืนยันแล้ว</p>
            </div>
        </div>

        <h3>ผู้โดยสาร</h3>
        <?php foreach ($passengers as $passenger): ?>
        <div class="passenger-info">
            <p><strong>ชื่อ-นามสกุล:</strong> <?php echo $passenger['first_name'] . ' ' . $passenger['last_name']; ?></p>
            <p><strong>ที่นั่ง:</strong> <?php echo $passenger['seat_number']; ?> | <strong>ชั้น:</strong> <?php echo $passenger['class_name']; ?></p>
        </div>
        <?php endforeach; ?>

        <div class="payment-info">
            <h3>รายละเอียดการชำระเงิน</h3>
            <table style="width: 100%;">
                <tr>
                    <td>วิธีการชำระเงิน:</td>
                    <td><?php echo $booking['payment_method']; ?></td>
                </tr>
                <tr>
                    <td>เลขที่ธุรกรรม:</td>
                    <td><?php echo $booking['transaction_id']; ?></td>
                </tr>
            </table>
            <div class="total-amount">
                <strong>ยอดรวมทั้งสิ้น:</strong> <?php echo number_format($booking['total_amount'], 2); ?> บาท
            </div>
        </div>

        <div class="qr-code">
            <!-- เพิ่ม QR Code สำหรับการเช็คอิน -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $booking['booking_number']; ?>" alt="QR Code">
        </div>

        <div class="footer">
            <p>ขอบคุณที่เลือกใช้บริการ EliteTix</p>
            <p>เอกสารนี้เป็นใบเสร็จรับเงิน/ใบกำกับภาษีอย่างย่อ</p>
        </div>

        <div class="ticket-notice">
            กรุณาแสดงเอกสารนี้พร้อมบัตรประจำตัวประชาชนเมื่อทำการเช็คอิน
        </div>
    </div>
</body>
</html>

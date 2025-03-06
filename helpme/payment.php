<?php
include 'config/db.php';

// รับ booking_id จาก URL
$booking_id = $_GET['booking_id'];

// ดึงข้อมูลการจองและราคาที่นั่ง
$sql = "SELECT b.*, f.flight_number, f.departure_time, 
        a1.airport_name as origin_name, 
        a2.airport_name as destination_name,
        al.airline_name,
        (SELECT SUM(sc.price_markup) 
         FROM booking_passengers bp
         JOIN seats s ON bp.seat_id = s.seat_id
         JOIN seat_classes sc ON s.class_id = sc.class_id
         WHERE bp.booking_id = b.booking_id) as total_seat_markup
        FROM bookings b
        JOIN flights f ON b.flight_id = f.flight_id
        JOIN airports a1 ON f.origin_airport = a1.airport_id
        JOIN airports a2 ON f.destination_airport = a2.airport_id
        JOIN airlines al ON f.airline_id = al.airline_id
        WHERE b.booking_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

// คำนวณราคารวม
$base_price = $booking['total_amount'];
$seat_markup = $booking['total_seat_markup'] ?? 0;
$total_price = $base_price + $seat_markup;
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน - EliteTix</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/payment.css">
</head>
<style>
    body {
        font-family: 'Prompt', sans-serif;
        background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
        min-height: 100vh;
        margin: 0;
        padding: 20px;
        color: white;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .payment-section {
        padding: 40px 0;
        animation: fadeIn 1s ease;
    }

    .page-title {
        text-align: center;
        font-size: 2.5em;
        margin-bottom: 40px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: bounceIn 1s ease;
    }

    .booking-summary {
        margin-bottom: 30px;
        transform: translateY(0);
        transition: transform 0.3s ease;
    }

    .booking-summary:hover {
        transform: translateY(-5px);
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }

    .glass-effect:hover {
        background: rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }

    .section-title {
        font-size: 1.8em;
        margin-bottom: 25px;
        position: relative;
        padding-bottom: 10px;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, #fff, transparent);
    }

    .booking-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .booking-icon {
        font-size: 2em;
        animation: bounce 2s infinite;
    }

    .flight-info {
        background: rgba(255, 255, 255, 0.1);
        padding: 20px;
        border-radius: 15px;
        margin: 20px 0;
    }

    .flight-info p {
        margin: 15px 0;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: transform 0.3s ease;
    }

    .flight-info p:hover {
        transform: translateX(10px);
    }

    .icon {
        font-size: 1.2em;
        opacity: 0.9;
    }

    .price-breakdown {
        margin-top: 30px;
        padding: 20px;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 15px;
    }

    .price-breakdown p {
        display: flex;
        justify-content: space-between;
        margin: 12px 0;
        padding: 10px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .price-breakdown p:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .total-amount {
        font-size: 1.4em;
        font-weight: bold;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid rgba(255, 255, 255, 0.2);
        animation: pulse 2s infinite;
    }

    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin: 30px 0;
    }

    .payment-method {
        background: rgba(255, 255, 255, 0.1);
        padding: 25px;
        border-radius: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .payment-method:hover {
        transform: translateY(-10px) scale(1.02);
        background: rgba(255, 255, 255, 0.2);
    }

    .payment-method::before {
        content: '';
        position: absolute;
        top: -100%;
        left: -100%;
        width: 300%;
        height: 300%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: all 0.6s ease;
    }

    .payment-method:hover::before {
        top: 100%;
        left: 100%;
    }

    .payment-icon {
        width: 80px;
        height: 80px;
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }

    .payment-method:hover .payment-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .gradient-button {
        background: linear-gradient(45deg, #FF512F, #DD2476);
        color: white;
        border: none;
        padding: 18px 35px;
        border-radius: 30px;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        margin-top: 30px;
        font-size: 1.2em;
        position: relative;
        overflow: hidden;
    }

    .gradient-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(221, 36, 118, 0.4);
    }

    .gradient-button::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.3s ease;
    }

    .gradient-button:hover::after {
        left: 100%;
        top: 100%;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.8;
        }

        100% {
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<body>

    <section class="payment-section">
        <div class="container">
            <h2 class="page-title">ชำระเงิน</h2>

            <div class="booking-summary animate__animated animate__fadeInUp">
                <h3 class="section-title">สรุปรายการจอง</h3>
                <div class="booking-details glass-effect">
                    <div class="booking-header">
                        <div class="booking-icon">✈️</div>
                        <p class="booking-ref">รหัสการจอง: <?php echo $booking['booking_reference']; ?></p>
                    </div>

                    <div class="flight-info">
                        <p class="airline"><span class="icon">🛫</span> <?php echo $booking['airline_name'] . ' ' . $booking['flight_number']; ?></p>
                        <p class="route"><span class="icon">🗺️</span> <?php echo $booking['origin_name'] . ' → ' . $booking['destination_name']; ?></p>
                        <p class="date"><span class="icon">📅</span> <?php echo date('d/m/Y', strtotime($booking['departure_time'])); ?></p>
                        <p class="passengers"><span class="icon">👥</span> <?php echo $booking['total_passengers']; ?> ท่าน</p>
                    </div>

                    <div class="price-breakdown">
                        <p class="base-price">ราคาตั๋วพื้นฐาน: <span><?php echo number_format($base_price, 2); ?> บาท</span></p>
                        <p class="seat-price">ราคาที่นั่งเพิ่มเติม: <span><?php echo number_format($seat_markup, 2); ?> บาท</span></p>
                        <p class="total-amount">ยอดชำระทั้งหมด: <span class="price"><?php echo number_format($total_price, 2); ?> บาท</span></p>
                    </div>
                </div>
            </div>

            <div class="payment-form glass-effect animate__animated animate__fadeInUp animate__delay-1s">
                <h3 class="section-title">เลือกวิธีชำระเงิน</h3>
                <form action="process_payment.php" method="POST" class="animated-form">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    <input type="hidden" name="total_amount" value="<?php echo $total_price; ?>">

                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="qr_payment" value="qr_payment" required>
                            <label for="qr_payment">
                                <img src="images/qr-code.png" alt="QR Payment" class="payment-icon">
                                <span>QR Payment</span>
                            </label>
                        </div>

                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                            <label for="bank_transfer">
                                <img src="images/bank-transfer.png" alt="Bank Transfer" class="payment-icon">
                                <span>โอนเงินผ่านธนาคาร</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="gradient-button">
                        <span class="button-icon">💳</span>
                        ชำระเงิน <?php echo number_format($total_price, 2); ?> บาท
                    </button>
                </form>
            </div>
        </div>
    </section>

</body>

</html>
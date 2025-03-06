<?php
session_start();
include 'config/db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// รับค่า flight_id และจำนวนผู้โดยสาร
$flight_id = $_GET['flight_id'];
$passengers = $_GET['passengers'];

// ดึงข้อมูลเที่ยวบิน
$sql = "SELECT f.*, 
        a1.airport_name as origin_name, 
        a2.airport_name as destination_name,
        al.airline_name,
        al.logo_path,
        f.base_price
        FROM flights f
        JOIN airports a1 ON f.origin_airport = a1.airport_id
        JOIN airports a2 ON f.destination_airport = a2.airport_id
        JOIN airlines al ON f.airline_id = al.airline_id
        WHERE f.flight_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $flight_id);
$stmt->execute();
$flight = $stmt->get_result()->fetch_assoc();

// คำนวณราคารวมเบื้องต้น
$total_price = $flight['base_price'] * $passengers;
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จองตั๋วเครื่องบิน - EliteTix</title>
    <link rel="stylesheet" href="css/booking.css">
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #6c63ff;
            --accent-color: #ff6b6b;
            --background-gradient: linear-gradient(135deg, #f6f9fc 0%, #e3eeff 100%);
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: var(--background-gradient);
            margin: 0;
            min-height: 100vh;
        }

        .booking-section {
            padding: 3rem;
            animation: fadeIn 1s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            backdrop-filter: blur(10px);
        }

        .booking-title {
            color: #2c3e50;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 1rem;
        }

        .booking-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }

        .flight-card {
            display: flex;
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .flight-card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .airline-logo {
            width: 120px;
            height: auto;
            object-fit: contain;
            margin-right: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .flight-details {
            flex: 1;
        }

        .flight-name {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .route {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            font-size: 1.2rem;
        }

        .arrow {
            margin: 0 1.5rem;
            color: var(--primary-color);
            font-size: 1.5rem;
            animation: fly 2s infinite;
        }

        .time {
            display: flex;
            gap: 2rem;
            margin: 1rem 0;
        }

        .time-item {
            background: #f8f9fa;
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .price-info {
            margin-top: 1.5rem;
            padding: 1rem;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
            color: white;
            font-weight: 600;
            text-align: right;
        }

        .passenger-info {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            animation: slideUp 0.5s ease forwards;
            transition: all 0.3s ease;
        }

        .passenger-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.2);
            background: white;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 1.2rem 3rem;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            display: block;
            margin: 2rem auto;
            cursor: pointer;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 20px rgba(74, 144, 226, 0.3);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fly {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(10px);
            }
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            animation: slideIn 0.5s ease;
        }

        .alert-danger {
            background: #ffe5e5;
            border-left: 4px solid var(--accent-color);
            color: #d63031;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .error {
            border-color: var(--accent-color);
            animation: shake 0.5s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }
    </style>
</head>

<body>
    <section class="booking-section">
        <div class="container">
            <h2 class="booking-title">กรอกข้อมูลผู้โดยสาร</h2>

            <?php if (isset($_SESSION['booking_error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    echo $_SESSION['booking_error'];
                    unset($_SESSION['booking_error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="flight-info">
                <div class="flight-card">
                    <img src="<?php echo $flight['logo_path']; ?>" alt="<?php echo $flight['airline_name']; ?>" class="airline-logo">
                    <div class="flight-details">
                        <h3 class="flight-name"><?php echo $flight['airline_name']; ?> - <?php echo $flight['flight_number']; ?></h3>
                        <div class="route">
                            <span class="airport"><?php echo $flight['origin_name']; ?></span>
                            <span class="arrow">✈</span>
                            <span class="airport"><?php echo $flight['destination_name']; ?></span>
                        </div>
                        <div class="time">
                            <div class="time-item">
                                <span class="time-label">เวลาออก:</span>
                                <span class="time-value"><?php echo date('H:i', strtotime($flight['departure_time'])); ?></span>
                            </div>
                            <div class="time-item">
                                <span class="time-label">เวลาถึง:</span>
                                <span class="time-value"><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></span>
                            </div>
                        </div>
                        <div class="price-info">
                            <span class="price-label">ราคารวม: </span>
                            <span class="price-value"><?php echo number_format($total_price, 2); ?> บาท</span>
                        </div>
                    </div>
                </div>
            </div>

            <form action="process_passengers.php" method="POST" class="booking-form" id="bookingForm" onsubmit="return validateForm()">
                <input type="hidden" name="form_token" value="<?php echo md5(uniqid()); ?>">
                <input type="hidden" name="flight_id" value="<?php echo $flight_id; ?>">
                <input type="hidden" name="passengers_count" value="<?php echo $passengers; ?>">

                <?php for ($i = 0; $i < $passengers; $i++): ?>
                    <div class="passenger-info">
                        <h4>ผู้โดยสารคนที่ <?php echo $i + 1; ?></h4>
                        <div class="form-group">
                            <label>ชื่อ:</label>
                            <input type="text" name="passenger[<?php echo $i; ?>][firstname]" required
                                class="form-control" placeholder="กรุณากรอกชื่อ">
                        </div>
                        <div class="form-group">
                            <label>นามสกุล:</label>
                            <input type="text" name="passenger[<?php echo $i; ?>][lastname]" required
                                class="form-control" placeholder="กรุณากรอกนามสกุล">
                        </div>
                        <div class="form-group">
                            <label>หมายเลขพาสปอร์ต:</label>
                            <input type="text" name="passenger[<?php echo $i; ?>][passport_number]" required
                                class="form-control" placeholder="กรุณากรอกหมายเลขพาสปอร์ต">
                        </div>
                        <div class="form-group">
                            <label>คำขอพิเศษ:</label>
                            <textarea name="passenger[<?php echo $i; ?>][special_requests]"
                                class="form-control" placeholder="ระบุคำขอพิเศษ เช่น อาหารพิเศษ หรือความช่วยเหลือที่ต้องการ"></textarea>
                        </div>
                    </div>
                <?php endfor; ?>

                <div class="button-group">
                    <button type="button" class="btn btn-primary" onclick="window.history.back()">ย้อนกลับ</button>
                    <button type="submit" class="btn btn-primary">ดำเนินการต่อ</button>
                </div>

            </form>
        </div>
    </section>

    <script>
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('error');
                } else {
                    input.classList.remove('error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            }
        });

        function validateForm() {
            return true;
        }
    </script>
</body>

</html>
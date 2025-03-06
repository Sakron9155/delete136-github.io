<?php
include 'config/db.php';

// รับค่าจากฟอร์มค้นหา
$origin = $_GET['origin'];
$destination = $_GET['destination'];
$departure_date = $_GET['departure_date'];
$airline = isset($_GET['airline']) ? $_GET['airline'] : '';
$passengers = $_GET['passengers'];

// สร้าง SQL query
$sql = "SELECT 
    f.flight_id,
    f.flight_number,
    f.departure_time,
    f.arrival_time,
    f.base_price,
    f.status,
    a1.airport_name as origin_airport,
    a1.airport_code as origin_code,
    a2.airport_name as destination_airport,
    a2.airport_code as destination_code,
    al.airline_name,
    al.airline_code,
    al.logo_path,
    (SELECT COUNT(*) FROM seats s 
     WHERE s.flight_id = f.flight_id 
     AND s.status = 'available') as available_seats
FROM flights f
JOIN airports a1 ON f.origin_airport = a1.airport_id
JOIN airports a2 ON f.destination_airport = a2.airport_id
JOIN airlines al ON f.airline_id = al.airline_id
WHERE f.origin_airport = ? 
AND f.destination_airport = ?
AND DATE(f.departure_time) = ?";

if (!empty($airline)) {
    $sql .= " AND f.airline_id = ?";
}

$sql .= " AND f.status != 'cancelled'
ORDER BY f.departure_time";

// เตรียม statement
$stmt = $conn->prepare($sql);

// ผูก parameters
if (!empty($airline)) {
    $stmt->bind_param("isss", $origin, $destination, $departure_date, $airline);
} else {
    $stmt->bind_param("iss", $origin, $destination, $departure_date);
}

// ประมวลผล query
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลการค้นหาเที่ยวบิน - EliteTix</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #f6f8fb 0%, #e9f0f7 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem;
        }

        h2 {
            color: #1a237e;
            text-align: center;
            margin-bottom: 2.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            padding-bottom: 20px;
            font-size: 2.2rem;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 4px;
        }

        .search-info-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 25px;
            padding: 2.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 2.5rem;
            transform: translateY(0);
            transition: all 0.4s ease;
        }

        .search-info-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.18);
        }

        .flight-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 25px;
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
            transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            overflow: hidden;
        }

        .flight-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(52, 152, 219, 0.15));
            opacity: 0;
            transition: all 0.4s ease;
        }

        .flight-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.25);
        }

        .flight-card:hover::before {
            opacity: 1;
        }

        .airline-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-right: 2rem;
            filter: drop-shadow(0 6px 8px rgba(0, 0, 0, 0.15));
            transition: transform 0.4s ease;
        }

        .airline-logo:hover {
            transform: scale(1.15) rotate(8deg);
        }

        .time-info {
            position: relative;
            padding: 2.5rem 0;
        }

        .duration-line {
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            position: relative;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(52, 152, 219, 0.2);
            margin: 20px 0;
        }

        .duration-line i {
            background: white;
            padding: 12px;
            border-radius: 50%;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
            position: absolute;
            top: -20px;
            /* ปรับตำแหน่งให้อยู่เหนือเส้น */
            left: 0;
            animation: flyPlane 10s linear infinite;
        }

        @keyframes flyPlane {
            0% {
                transform: translateX(0) rotate(0deg);
                left: 0;
            }

            100% {
                transform: translateX(100%) rotate(0deg);
                left: calc(100% - 40px);
            }
        }



        .duration-line i:hover {
            transform: translateY(-8px) scale(1.15);
            color: #3498db;
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
        }

        .book-button {
            background: linear-gradient(45deg, #3498db, #2ecc71);
            color: white;
            padding: 1.2rem 3rem;
            border-radius: 35px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .duration-line::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shine 2s infinite linear;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .book-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(52, 152, 219, 0.4);
        }

        .book-button:hover::before {
            opacity: 1;
        }

        .price-amount {
            font-size: 2rem;
            font-weight: 600;
            background: linear-gradient(45deg, #2ecc71, #3498db);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            padding: 8px 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-12px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .pulse {
            animation: float 4s ease-in-out infinite;
        }

        .seats-available {
            background: rgba(52, 152, 219, 0.15);
            padding: 12px 25px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: all 0.4s ease;
            font-size: 1.1rem;
        }

        .seats-available:hover {
            background: rgba(52, 152, 219, 0.25);
            transform: scale(1.08);
        }

        .highlight {
            color: #e74c3c;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            font-size: 1.2rem;
        }

        .no-flights-content {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 25px;
            padding: 4rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .no-flights-content i {
            font-size: 5rem;
            background: linear-gradient(45deg, #3498db, #2ecc71);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
        }

        .airline-details {
            margin-top: 1rem;
        }

        .airline-name {
            font-size: 1.3rem;
            font-weight: 500;
            color: #2c3e50;
        }

        .flight-number {
            background: rgba(52, 152, 219, 0.1);
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.9rem;
            color: #3498db;
            margin-left: 10px;
        }

        .time {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .airport-code {
            font-size: 1.2rem;
            font-weight: 500;
            color: #3498db;
            margin: 8px 0;
        }

        .airport-name {
            color: #7f8c8d;
            font-size: 1rem;
        }

        .duration-time {
            text-align: center;
            margin-top: 1rem;
            font-size: 1.1rem;
            color: #7f8c8d;
        }

        .price-label {
            display: block;
            color: #7f8c8d;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>

    <section class="search-results">
        <div class="container">
            <div class="back-button-container" style="margin-bottom: 20px;">
                <a href="search.php" class="book-button" style="display: inline-block;">
                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                </a>
            </div>
            <h2 class="animate__animated animate__fadeIn">✈️ ผลการค้นหาเที่ยวบิน</h2>
            <div class="search-summary animate__animated animate__fadeInUp">
                <div class="search-info-card">
                    <p>
                        <i class="fas fa-plane-departure"></i> เที่ยวบินจาก: <strong><?php echo $_GET['origin']; ?></strong>
                        <i class="fas fa-plane-arrival"></i> ไปยัง: <strong><?php echo $_GET['destination']; ?></strong><br>
                        <i class="far fa-calendar-alt"></i> วันที่: <strong><?php echo date('d/m/Y', strtotime($departure_date)); ?></strong><br>
                        <i class="fas fa-users"></i> ผู้โดยสาร: <strong><?php echo $passengers; ?> ท่าน</strong>
                    </p>
                </div>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="flights-container animate__animated animate__fadeInUp">
                    <?php while ($flight = $result->fetch_assoc()): ?>
                        <div class="flight-card">
                            <div class="airline-info">
                                <img src="<?php echo $flight['logo_path']; ?>" alt="<?php echo $flight['airline_name']; ?>" class="airline-logo pulse">
                                <div class="airline-details">
                                    <span class="airline-name"><?php echo $flight['airline_name']; ?></span>
                                    <span class="flight-number badge"><?php echo $flight['flight_number']; ?></span>
                                </div>
                            </div>

                            <div class="flight-details">
                                <div class="time-info">
                                    <div class="departure">
                                        <div class="time"><?php echo date('H:i', strtotime($flight['departure_time'])); ?></div>
                                        <div class="airport-code"><?php echo $flight['origin_code']; ?></div>
                                        <div class="airport-name"><?php echo $flight['origin_airport']; ?></div>
                                    </div>
                                    <div class="duration">
                                        <div class="duration-line">
                                            <i class="fas fa-plane"></i>
                                        </div>
                                        <div class="duration-time">
                                            <?php
                                            $duration = strtotime($flight['arrival_time']) - strtotime($flight['departure_time']);
                                            echo gmdate('H ชม. i น.', $duration);
                                            ?>
                                        </div>
                                    </div>
                                    <div class="arrival">
                                        <div class="time"><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></div>
                                        <div class="airport-code"><?php echo $flight['destination_code']; ?></div>
                                        <div class="airport-name"><?php echo $flight['destination_airport']; ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="price-info">
                                <div class="seats-available">
                                    <i class="fas fa-chair"></i>
                                    ที่นั่งว่าง: <span class="highlight"><?php echo $flight['available_seats']; ?></span> ที่นั่ง
                                </div>
                                <div class="price">
                                    <span class="price-label">ราคาเริ่มต้น</span>
                                    <span class="price-amount">฿<?php echo number_format($flight['base_price'], 2); ?></span>
                                </div>
                                <a href="booking.php?flight_id=<?php echo $flight['flight_id']; ?>&passengers=<?php echo $passengers; ?>"
                                    class="book-button">
                                    <i class="fas fa-ticket-alt"></i> จองตั๋วเลย
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-flights animate__animated animate__fadeIn">
                    <div class="no-flights-content">
                        <i class="fas fa-search-minus"></i>
                        <p>ไม่พบเที่ยวบินที่ตรงตามเงื่อนไขการค้นหา</p>
                        <a href="search.php" class="book-button">
                            <i class="fas fa-redo"></i> ค้นหาใหม่
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>
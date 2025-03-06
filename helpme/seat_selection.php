<?php
session_start();
include 'config/db.php';

// ตรวจสอบ booking_id
if (!isset($_GET['booking_id']) || !isset($_SESSION['current_booking_id']) || $_GET['booking_id'] != $_SESSION['current_booking_id']) {
    header("Location: index.php");
    exit();
}

$booking_id = $_GET['booking_id'];

// ดึงข้อมูลการจองและเที่ยวบิน
$sql = "SELECT b.*, f.flight_number, 
        a1.airport_name as origin_name, 
        a2.airport_name as destination_name,
        al.airline_name
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

// ดึงข้อมูลที่นั่งและประเภทที่นั่ง
$sql = "SELECT s.*, sc.class_name, sc.price_markup, sc.benefits
        FROM seats s
        JOIN seat_classes sc ON s.class_id = sc.class_id
        WHERE s.flight_id = ?
        ORDER BY s.seat_number";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking['flight_id']);
$stmt->execute();
$seats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ดึงข้อมูลผู้โดยสาร
$sql = "SELECT * FROM booking_passengers WHERE booking_id = ?";
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
    <title>เลือกที่นั่ง - EliteTix</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .seat-sample {
            width: 30px;
            height: 30px;
            border-radius: 8px;
        }

        .available {
            background: #4CAF50;
        }

        .occupied {
            background: #f44336;
        }

        .selected {
            background: #2196F3;
        }

        .price-summary {
            background: #2c3e50;
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.2em;
            margin: 20px 0;
        }

        .flight-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            text-align: center;
        }

        .seat-classes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .seat-class {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .seat-class:hover {
            transform: translateY(-5px);
        }

        .seat-map {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
        }

        .section-title {
            color: #2c3e50;
            font-size: 1.3em;
            margin: 20px 0;
            text-align: center;
            font-weight: 600;
        }

        .seat-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .seat {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .seat:hover {
            transform: scale(1.1);
        }

        .passenger-seat-selection {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .passenger-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        select {
            padding: 10px;
            border-radius: 8px;
            border: 2px solid #ddd;
            width: 200px;
        }

        .submit-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            cursor: pointer;
            width: 100%;
            margin-top: 30px;
            transition: transform 0.3s ease;
        }

        .submit-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .back-button {
            background: #6c757d;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            transition: transform 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            background: #5a6268;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>✈️ เลือกที่นั่ง</h2>
        <div class="seat-legend">
            <div class="legend-item">
                <div class="seat-sample available"></div>
                <span>ที่นั่งว่าง</span>
            </div>
            <div class="legend-item">
                <div class="seat-sample occupied"></div>
                <span>ที่นั่งถูกจอง</span>
            </div>
            <div class="legend-item">
                <div class="seat-sample selected"></div>
                <span>ที่นั่งที่เลือก</span>
            </div>
        </div>

        <div class="price-summary" id="total-price">
            💰 ราคาเพิ่มเติมรวม: 0 บาท
        </div>

        <div class="flight-info">
            <h3>✈️ <?php echo $booking['airline_name'] . ' ' . $booking['flight_number']; ?></h3>
            <p>🛫 <?php echo $booking['origin_name'] . ' → ' . $booking['destination_name']; ?> 🛬</p>
        </div>
        <center>
            <h4>🎫 ประเภทที่นั่ง:</h4><br>
        </center>
        <div class="seat-classes">
            <?php
            $unique_classes = array_unique(array_column($seats, 'class_id'));
            foreach ($unique_classes as $class_id) {
                $class = array_filter($seats, function ($seat) use ($class_id) {
                    return $seat['class_id'] == $class_id;
                });
                $class = reset($class);
                echo "<div class='seat-class'>";
                echo "<h5>🔹 " . $class['class_name'] . "</h5>";
                echo "<p>💵 ราคาเพิ่มเติม: " . number_format($class['price_markup']) . " บาท</p>";
                echo "<p>✨ สิทธิประโยชน์: " . $class['benefits'] . "</p>";
                echo "</div>";
            }
            ?>
        </div>
        <form action="process_seats.php" method="POST">
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">

            <div class="seat-map">
                <div class="section-title">✨ Business Class</div>
                <div class="seat-row business-section">
                    <?php foreach ($seats as $seat): ?>
                        <?php if ($seat['class_name'] == 'Business Class'): ?>
                            <div class="seat business <?php echo $seat['status'] == 'available' ? 'available' : 'occupied'; ?>"
                                data-class="<?php echo $seat['class_name']; ?>"
                                data-price="<?php echo $seat['price_markup']; ?>">
                                <input type="checkbox"
                                    name="selected_seats[]"
                                    value="<?php echo $seat['seat_id']; ?>"
                                    <?php echo $seat['status'] == 'occupied' ? 'disabled' : ''; ?>>
                                <span><?php echo $seat['seat_number']; ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="section-title">💎 First Class</div>
                <div class="seat-row premium-section">
                    <?php foreach ($seats as $seat): ?>
                        <?php if ($seat['class_name'] == 'First Class'): ?>
                            <div class="seat premium-economy <?php echo $seat['status'] == 'available' ? 'available' : 'occupied'; ?>"
                                data-class="<?php echo $seat['class_name']; ?>"
                                data-price="<?php echo $seat['price_markup']; ?>">
                                <input type="checkbox"
                                    name="selected_seats[]"
                                    value="<?php echo $seat['seat_id']; ?>"
                                    <?php echo $seat['status'] == 'occupied' ? 'disabled' : ''; ?>>
                                <span><?php echo $seat['seat_number']; ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="section-title">🌟 Economy Class</div>
                <div class="seat-row economy-section">
                    <?php foreach ($seats as $seat): ?>
                        <?php if ($seat['class_name'] == 'Economy Class'): ?>
                            <div class="seat economy <?php echo $seat['status'] == 'available' ? 'available' : 'occupied'; ?>"
                                data-class="<?php echo $seat['class_name']; ?>"
                                data-price="<?php echo $seat['price_markup']; ?>">
                                <input type="checkbox"
                                    name="selected_seats[]"
                                    value="<?php echo $seat['seat_id']; ?>"
                                    <?php echo $seat['status'] == 'occupied' ? 'disabled' : ''; ?>>
                                <span><?php echo $seat['seat_number']; ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="passenger-seat-selection">
                <h4>👥 เลือกที่นั่งสำหรับผู้โดยสาร:</h4>
                <?php foreach ($passengers as $index => $passenger): ?>
                    <div class="passenger-row">
                        <span>🧑‍✈️ <?php echo $passenger['first_name'] . ' ' . $passenger['last_name']; ?></span>
                        <select name="passenger_seats[<?php echo $passenger['passenger_id']; ?>]" required>
                            <option value="">เลือกที่นั่ง</option>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="submit-button">✅ ยืนยันที่นั่ง</button>
        </form>
        <a href="javascript:history.back()" class="back-button">↩️ ย้อนกลับ</a>
    </div>

    <script src="js/seat_selection.js"></script>
    <!-- เพิ่ม JavaScript นี้ก่อนปิด </body> -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seats = document.querySelectorAll('.seat input[type="checkbox"]');
            const passengerSelects = document.querySelectorAll('select[name^="passenger_seats"]');
            const maxSeats = <?php echo count($passengers); ?>;

            seats.forEach(seat => {
                seat.addEventListener('change', function() {
                    const selectedCount = document.querySelectorAll('.seat input[type="checkbox"]:checked').length;
                    if (selectedCount > maxSeats) {
                        this.checked = false;
                        alert(`สามารถเลือกได้สูงสุด ${maxSeats} ที่นั่ง`);
                        return;
                    }
                    updateAvailableSeats();
                });
            });

            function updateAvailableSeats() {
                const selectedSeats = Array.from(seats)
                    .filter(seat => seat.checked)
                    .map(seat => ({
                        id: seat.value,
                        number: seat.parentElement.querySelector('span').textContent
                    }));

                passengerSelects.forEach(select => {
                    const currentValue = select.value;
                    select.innerHTML = '<option value="">เลือกที่นั่ง</option>';

                    selectedSeats.forEach(seat => {
                        const option = document.createElement('option');
                        option.value = seat.id;
                        option.textContent = seat.number;
                        if (seat.id === currentValue) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                });
            }
        });
    </script>
</body>

</html>
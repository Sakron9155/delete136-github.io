<?php
include 'config/db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหาเที่ยวบิน - EliteTix</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #1a2a6c 0%, #b21f1f 50%, #fdbb2d 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            background-attachment: fixed;
            animation: skyBackground 20s ease infinite;
            background-size: 400% 400%;
        }

        @keyframes skyBackground {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .search-section {
            padding: 30px 0;
            animation: floatIn 1.5s ease-out;
        }

        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.8);
            position: relative;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #1a2a6c, #b21f1f, #fdbb2d);
            border-radius: 8px 8px 0 0;
        }

        h2 {
            color: #1a2a6c;
            text-align: center;
            font-size: 3.5em;
            margin-bottom: 40px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 25px;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .form-group:hover {
            transform: translateY(-5px);
        }

        select, input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1.1em;
            transition: all 0.3s ease;
            background-color: white;
        }

        select:focus, input:focus {
            border-color: #1a2a6c;
            box-shadow: 0 0 15px rgba(26, 42, 108, 0.2);
            outline: none;
        }

        .search-button {
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            color: white;
            padding: 20px 40px;
            border: none;
            border-radius: 15px;
            font-size: 1.3em;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            width: 100%;
            margin-top: 30px;
        }

        .search-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(26, 42, 108, 0.3);
            background: linear-gradient(45deg, #b21f1f, #1a2a6c);
        }

        label {
            color: #1a2a6c;
            font-weight: 500;
            margin-bottom: 10px;
            display: block;
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 12px 25px;
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
    </style>
</head>

<body>
    <section class="search-section animate__animated animate__fadeIn">
        <div class="container">
            <h2>ค้นหาเที่ยวบิน</h2>
            <form action="search_results.php" method="GET" class="search-form">
                <div class="form-group animate__animated animate__fadeInLeft" style="animation-delay: 0.2s;">
                    <label for="origin">สนามบินต้นทาง</label>
                    <select name="origin" id="origin" required>
                        <option value="">เลือกสนามบินต้นทาง</option>
                        <?php
                        $sql = "SELECT * FROM airports ORDER BY airport_name";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['airport_id'] . "'>" . $row['airport_name'] . " (" . $row['airport_code'] . ")</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group animate__animated animate__fadeInRight" style="animation-delay: 0.4s;">
                    <label for="destination">สนามบินปลายทาง</label>
                    <select name="destination" id="destination" required>
                        <option value="">เลือกสนามบินปลายทาง</option>
                        <?php
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['airport_id'] . "'>" . $row['airport_name'] . " (" . $row['airport_code'] . ")</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group animate__animated animate__fadeInLeft" style="animation-delay: 0.6s;">
                    <label for="departure_date">วันที่เดินทาง</label>
                    <input type="date" name="departure_date" id="departure_date" required>
                </div>

                <div class="form-group animate__animated animate__fadeInRight" style="animation-delay: 0.8s;">
                    <label for="airline">สายการบิน (ไม่บังคับ)</label>
                    <select name="airline" id="airline">
                        <option value="">ทุกสายการบิน</option>
                        <?php
                        $sql = "SELECT * FROM airlines ORDER BY airline_name";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['airline_id'] . "'>" . $row['airline_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group animate__animated animate__fadeInLeft" style="animation-delay: 1s;">
                    <label for="passengers">จำนวนผู้โดยสาร</label>
                    <input type="number" name="passengers" id="passengers" min="1" max="9" value="1" required>
                </div>

                <button type="submit" class="search-button animate__animated animate__fadeInUp" style="animation-delay: 1.2s;">ค้นหาเที่ยวบิน</button>
            </form>
            <a href="index.php" class="back-button">← ย้อนกลับ</a>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="js/search.js"></script>
    <script>
        flatpickr("#departure_date", {
            minDate: "today",
            dateFormat: "Y-m-d",
            locale: "th",
            animate: true
        });
    </script>
</body>
</html>
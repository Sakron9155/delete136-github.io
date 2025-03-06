<?php
require_once 'check_login.php';
require_once '../config/db.php';


// จัดการการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {

            case 'add':
                $flight_number = $conn->real_escape_string($_POST['flight_number']);
                $airline_id = (int)$_POST['airline_id'];
                $origin = (int)$_POST['origin_airport'];
                $destination = (int)$_POST['destination_airport'];
                $departure = $_POST['departure_time'];
                $arrival = $_POST['arrival_time'];
                $price = (float)$_POST['base_price'];
            
                $sql = "INSERT INTO flights (flight_number, airline_id, origin_airport, destination_airport, 
                        departure_time, arrival_time, base_price, status) 
                        VALUES ('$flight_number', $airline_id, $origin, $destination, 
                        '$departure', '$arrival', $price, 'scheduled')";
            
                if($conn->query($sql)) {
                    header('Location: flights.php');
                    exit();
                }
                break;


            case 'delete':
                $flight_id = (int)$_POST['flight_id'];
                $sql = "DELETE FROM flights WHERE flight_id = $flight_id";
                $conn->query($sql);
                break;

            case 'generate_advance':
                $airline_id = (int)$_POST['airline_id'];
                $start_date = $_POST['start_generate_date'];
                $end_date = $_POST['end_generate_date'];
                $origin_airport = (int)$_POST['origin_airport_generate'];
                $destination_airport = (int)$_POST['destination_airport_generate'];

                $flight_times = [
                    ['08:00:00', '09:20:00', 2500], // เที่ยวเช้า
                    ['17:00:00', '18:20:00', 2700]  // เที่ยวเย็น
                ];

                $current_date = $start_date;
                while (strtotime($current_date) <= strtotime($end_date)) {
                    $airline_code = $conn->query("SELECT airline_code FROM airlines WHERE airline_id = $airline_id")->fetch_assoc()['airline_code'];

                    foreach ($flight_times as $index => $time) {
                        // เที่ยวไป
                        $flight_number = $airline_code . (100 + $index);
                        $departure = $current_date . ' ' . $time[0];
                        $arrival = $current_date . ' ' . $time[1];

                        $sql = "INSERT INTO flights (flight_number, airline_id, origin_airport, destination_airport, 
                                            departure_time, arrival_time, base_price, status) 
                                            VALUES ('$flight_number', $airline_id, $origin_airport, 
                                            $destination_airport, '$departure', '$arrival', {$time[2]}, 'scheduled')";

                        if ($conn->query($sql)) {
                            $flight_id = $conn->insert_id;
                        }

                        // เที่ยวกลับ
                        $return_number = $airline_code . (200 + $index);
                        $return_departure = date('Y-m-d H:i:s', strtotime($departure . ' +2 hours'));
                        $return_arrival = date('Y-m-d H:i:s', strtotime($arrival . ' +2 hours'));

                        $sql = "INSERT INTO flights (flight_number, airline_id, origin_airport, destination_airport, 
                                            departure_time, arrival_time, base_price, status) 
                                            VALUES ('$return_number', $airline_id, $destination_airport, 
                                            $origin_airport, '$return_departure', '$return_arrival', {$time[2]}, 'scheduled')";

                        if ($conn->query($sql)) {
                            $flight_id = $conn->insert_id;

                        }
                    }

                    $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
                }
                break;
        }
        header('Location: flights.php');
        exit();
    }
}

// ดึงข้อมูลสายการบินและสนามบินสำหรับ dropdown
$airlines = $conn->query("SELECT * FROM airlines ORDER BY airline_name");
$airports = $conn->query("SELECT * FROM airports ORDER BY airport_name");

// ดึงข้อมูลเที่ยวบิน
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('+7 days'));

// จัดการการแบ่งหน้า
$flights_per_page = 50;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $flights_per_page;

// คำนวณจำนวนหน้าทั้งหมด
$total_flights = $conn->query("
    SELECT COUNT(*) as total FROM flights f
    WHERE DATE(f.departure_time) BETWEEN '$start_date' AND '$end_date'
")->fetch_assoc()['total'];

$total_pages = ceil($total_flights / $flights_per_page);

// ดึงข้อมูลเที่ยวบินตามการแบ่งหน้า
$flights = $conn->query("
    SELECT f.*, a.airline_name, 
    o.airport_name as origin_name, 
    d.airport_name as destination_name
    FROM flights f
    JOIN airlines a ON f.airline_id = a.airline_id
    JOIN airports o ON f.origin_airport = o.airport_id
    JOIN airports d ON f.destination_airport = d.airport_id
    WHERE DATE(f.departure_time) BETWEEN '$start_date' AND '$end_date'
    ORDER BY f.departure_time
    LIMIT $offset, $flights_per_page
");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการเที่ยวบิน - EliteTix</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
</head>

<body>
    <!-- ส่วนเนื้อหาเว็บไซต์ -->
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
                    <h1 class="h2">จัดการเที่ยวบิน</h1>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFlightModal">
                            เพิ่มเที่ยวบิน
                        </button>
                        <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#generateFlightsModal">
                            สร้างเที่ยวบินอัตโนมัติ
                        </button>
                    </div>
                </div>

                <!-- ฟิลเตอร์วันที่ -->
                <div class="mb-3">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="form-label">วันที่เริ่มต้น</label>
                            <input type="date" name="start_date" class="form-control"
                                value="<?php echo $_GET['start_date'] ?? date('Y-m-d'); ?>">
                        </div>
                        <div class="col-auto">
                            <label class="form-label">วันที่สิ้นสุด</label>
                            <input type="date" name="end_date" class="form-control"
                                value="<?php echo $_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days')); ?>">
                        </div>
                        <div class="col-auto">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">กรองข้อมูล</button>
                        </div>
                    </form>
                </div>

                <!-- ตารางแสดงเที่ยวบิน -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>รหัสเที่ยวบิน</th>
                                <th>สายการบิน</th>
                                <th>ต้นทาง</th>
                                <th>ปลายทาง</th>
                                <th>เวลาออก</th>
                                <th>เวลาถึง</th>
                                <th>ราคา</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($flight = $flights->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $flight['flight_number']; ?></td>
                                    <td><?php echo $flight['airline_name']; ?></td>
                                    <td><?php echo $flight['origin_name']; ?></td>
                                    <td><?php echo $flight['destination_name']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($flight['departure_time'])); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($flight['arrival_time'])); ?></td>
                                    <td>฿<?php echo number_format($flight['base_price'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php
                                                                echo $flight['status'] == 'scheduled' ? 'primary' : ($flight['status'] == 'boarding' ? 'warning' : ($flight['status'] == 'departed' ? 'success' : ($flight['status'] == 'cancelled' ? 'danger' : 'info')));
                                                                ?>">
                                            <?php echo $flight['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-flight"
                                            data-flight='<?php echo json_encode($flight); ?>'
                                            data-bs-toggle="modal"
                                            data-bs-target="#editFlightModal">
                                            แก้ไข
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-flight"
                                            data-flight-id="<?php echo $flight['flight_id']; ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteFlightModal">
                                            ลบ
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- การแบ่งหน้า -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Modal เพิ่มเที่ยวบิน -->
    <div class="modal fade" id="addFlightModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มเที่ยวบิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">รหัสเที่ยวบิน</label>
                            <input type="text" name="flight_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สายการบิน</label>
                            <select name="airline_id" class="form-select" required>
                                <?php
                                $airlines->data_seek(0);
                                while ($airline = $airlines->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $airline['airline_id']; ?>">
                                        <?php echo $airline['airline_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สนามบินต้นทาง</label>
                            <select name="origin_airport" class="form-select" required>
                                <?php
                                $airports->data_seek(0);
                                while ($airport = $airports->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $airport['airport_id']; ?>">
                                        <?php echo $airport['airport_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สนามบินปลายทาง</label>
                            <select name="destination_airport" class="form-select" required>
                                <?php
                                $airports->data_seek(0);
                                while ($airport = $airports->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $airport['airport_id']; ?>">
                                        <?php echo $airport['airport_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เวลาออก</label>
                            <input type="datetime-local" name="departure_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เวลาถึง</label>
                            <input type="datetime-local" name="arrival_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ราคา</label>
                            <input type="number" name="base_price" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal แก้ไขเที่ยวบิน -->
    <div class="modal fade" id="editFlightModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขเที่ยวบิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="flight_id" id="edit_flight_id">
                        <div class="mb-3">
                            <label class="form-label">รหัสเที่ยวบิน</label>
                            <input type="text" name="flight_number" id="edit_flight_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สายการบิน</label>
                            <select name="airline_id" id="edit_airline_id" class="form-select" required>
                                <?php
                                $airlines->data_seek(0);
                                while ($airline = $airlines->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $airline['airline_id']; ?>">
                                        <?php echo $airline['airline_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สนามบินต้นทาง</label>
                            <select name="origin_airport" id="edit_origin_airport" class="form-select" required>
                                <?php
                                $airports->data_seek(0);
                                while ($airport = $airports->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $airport['airport_id']; ?>">
                                        <?php echo $airport['airport_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สนามบินปลายทาง</label>
                            <select name="destination_airport" id="edit_destination_airport" class="form-select" required>
                                <?php
                                $airports->data_seek(0);
                                while ($airport = $airports->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $airport['airport_id']; ?>">
                                        <?php echo $airport['airport_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เวลาออก</label>
                            <input type="datetime-local" name="departure_time" id="edit_departure_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เวลาถึง</label>
                            <input type="datetime-local" name="arrival_time" id="edit_arrival_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ราคา</label>
                            <input type="number" name="base_price" id="edit_base_price" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สถานะ</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="scheduled">Scheduled</option>
                                <option value="boarding">Boarding</option>
                                <option value="departed">Departed</option>
                                <option value="arrived">Arrived</option>
                                <option value="delayed">Delayed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal ลบเที่ยวบิน -->
    <div class="modal fade" id="deleteFlightModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการลบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="flight_id" id="delete_flight_id">
                        <p>คุณต้องการลบเที่ยวบินนี้ใช่หรือไม่?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ยืนยันการลบ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal สร้างเที่ยวบินอัตโนมัติ -->
    <div class="modal fade" id="generateFlightsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">สร้างเที่ยวบินล่วงหน้าอัตโนมัติ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="generate_advance">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">วันที่เริ่มต้น</label>
                                <input type="date" name="start_generate_date" class="form-control" required
                                    min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">วันที่สิ้นสุด</label>
                                <input type="date" name="end_generate_date" class="form-control" required
                                    min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">สายการบิน</label>
                                <select name="airline_id" class="form-select" required>
                                    <?php
                                    $airlines->data_seek(0);
                                    while ($airline = $airlines->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $airline['airline_id']; ?>">
                                            <?php echo $airline['airline_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">สนามบินต้นทาง</label>
                                <select name="origin_airport_generate" class="form-select" required>
                                    <?php
                                    $airports->data_seek(0);
                                    while ($airport = $airports->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $airport['airport_id']; ?>">
                                            <?php echo $airport['airport_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">สนามบินปลายทาง</label>
                                <select name="destination_airport_generate" class="form-select" required>
                                    <?php
                                    $airports->data_seek(0);
                                    while ($airport = $airports->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $airport['airport_id']; ?>">
                                            <?php echo $airport['airport_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">สร้างเที่ยวบิน</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // จัดการการแก้ไขเที่ยวบิน
        document.querySelectorAll('.edit-flight').forEach(button => {
            button.addEventListener('click', function() {
                const flight = JSON.parse(this.dataset.flight);
                document.getElementById('edit_flight_id').value = flight.flight_id;
                document.getElementById('edit_flight_number').value = flight.flight_number;
                document.getElementById('edit_airline_id').value = flight.airline_id;
                document.getElementById('edit_origin_airport').value = flight.origin_airport;
                document.getElementById('edit_destination_airport').value = flight.destination_airport;
                document.getElementById('edit_departure_time').value = flight.departure_time.slice(0, 16);
                document.getElementById('edit_arrival_time').value = flight.arrival_time.slice(0, 16);
                document.getElementById('edit_base_price').value = flight.base_price;
                document.getElementById('edit_status').value = flight.status;
            });
        });

        // จัดการการลบเที่ยวบิน
        document.querySelectorAll('.delete-flight').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_flight_id').value = this.dataset.flightId;
            });
        });
    </script>
</body>

</html>
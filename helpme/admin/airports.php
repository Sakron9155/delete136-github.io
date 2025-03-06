<?php
require_once 'check_login.php';
require_once '../config/db.php';

// จัดการการอัพโหลดรูปภาพ
function uploadImage($file)
{
    $target_dir = "../uploads/airports/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return 'uploads/airports/' . $filename;
    }
    return null;
}

// จัดการการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $airport_code = $conn->real_escape_string($_POST['airport_code']);
                $airport_name = $conn->real_escape_string($_POST['airport_name']);
                $city = $conn->real_escape_string($_POST['city']);
                $country = $conn->real_escape_string($_POST['country']);

                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $image_path = uploadImage($_FILES['image']);
                }

                $sql = "INSERT INTO airports (airport_code, airport_name, city, country, image_path) 
                        VALUES ('$airport_code', '$airport_name', '$city', '$country', '$image_path')";
                $conn->query($sql);
                break;

            case 'edit':
                $airport_id = (int)$_POST['airport_id'];
                $airport_code = $conn->real_escape_string($_POST['airport_code']);
                $airport_name = $conn->real_escape_string($_POST['airport_name']);
                $city = $conn->real_escape_string($_POST['city']);
                $country = $conn->real_escape_string($_POST['country']);

                $image_sql = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $image_path = uploadImage($_FILES['image']);
                    if ($image_path) {
                        $image_sql = ", image_path = '$image_path'";
                    }
                }

                $sql = "UPDATE airports SET 
                        airport_code = '$airport_code',
                        airport_name = '$airport_name',
                        city = '$city',
                        country = '$country'
                        $image_sql
                        WHERE airport_id = $airport_id";
                $conn->query($sql);
                break;

            case 'delete':
                $airport_id = (int)$_POST['airport_id'];
                $sql = "DELETE FROM airports WHERE airport_id = $airport_id";
                $conn->query($sql);
                break;
        }
        header('Location: airports.php');
        exit();
    }
}

// ดึงข้อมูลสนามบิน
$airports = $conn->query("SELECT * FROM airports ORDER BY airport_name");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสนามบิน - EliteTix</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
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
                    <h1 class="h2">จัดการสนามบิน</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAirportModal">
                        เพิ่มสนามบิน
                    </button>
                </div>

                <!-- ตารางแสดงสนามบิน -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>รูปภาพ</th>
                                <th>รหัสสนามบิน</th>
                                <th>ชื่อสนามบิน</th>
                                <th>เมือง</th>
                                <th>ประเทศ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($airport = $airports->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if ($airport['image_path']): ?>
                                            <img src="../<?php echo $airport['image_path']; ?>"
                                                alt="<?php echo $airport['airport_name']; ?>"
                                                style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                                style="width: 50px; height: 50px;">
                                                No Image
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $airport['airport_code']; ?></td>
                                    <td><?php echo $airport['airport_name']; ?></td>
                                    <td><?php echo $airport['city']; ?></td>
                                    <td><?php echo $airport['country']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-airport"
                                            data-airport='<?php echo json_encode($airport); ?>'
                                            data-bs-toggle="modal"
                                            data-bs-target="#editAirportModal">
                                            แก้ไข
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-airport"
                                            data-airport-id="<?php echo $airport['airport_id']; ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteAirportModal">
                                            ลบ
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal เพิ่มสนามบิน -->
    <div class="modal fade" id="addAirportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มสนามบิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">รหัสสนามบิน</label>
                            <input type="text" name="airport_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ชื่อสนามบิน</label>
                            <input type="text" name="airport_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เมือง</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ประเทศ</label>
                            <input type="text" name="country" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">รูปภาพ</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
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

    <!-- Modal แก้ไขสนามบิน -->
    <div class="modal fade" id="editAirportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขสนามบิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="airport_id" id="edit_airport_id">
                        <div class="mb-3">
                            <label class="form-label">รหัสสนามบิน</label>
                            <input type="text" name="airport_code" id="edit_airport_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ชื่อสนามบิน</label>
                            <input type="text" name="airport_name" id="edit_airport_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เมือง</label>
                            <input type="text" name="city" id="edit_city" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ประเทศ</label>
                            <input type="text" name="country" id="edit_country" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">รูปภาพ</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div id="current_image" class="mt-2"></div>
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

    <!-- Modal ลบสนามบิน -->
    <div class="modal fade" id="deleteAirportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการลบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="airport_id" id="delete_airport_id">
                        <p>คุณต้องการลบสนามบินนี้ใช่หรือไม่?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ยืนยันการลบ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // จัดการการแก้ไขสนามบิน
        document.querySelectorAll('.edit-airport').forEach(button => {
            button.addEventListener('click', function() {
                const airport = JSON.parse(this.dataset.airport);
                document.getElementById('edit_airport_id').value = airport.airport_id;
                document.getElementById('edit_airport_code').value = airport.airport_code;
                document.getElementById('edit_airport_name').value = airport.airport_name;
                document.getElementById('edit_city').value = airport.city;
                document.getElementById('edit_country').value = airport.country;

                const currentImage = document.getElementById('current_image');
                if (airport.image_path) {
                    currentImage.innerHTML = `<img src="../${airport.image_path}" alt="${airport.airport_name}" style="max-width: 100px;">`;
                } else {
                    currentImage.innerHTML = 'ไม่มีรูปภาพ';
                }
            });
        });

        // จัดการการลบสนามบิน
        document.querySelectorAll('.delete-airport').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_airport_id').value = this.dataset.airportId;
            });
        });
    </script>
</body>

</html>
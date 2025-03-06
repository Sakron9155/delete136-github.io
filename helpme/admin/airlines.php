<?php
require_once 'check_login.php';
require_once '../config/db.php';

// จัดการการอัพโหลดโลโก้
function uploadLogo($file)
{
    $target_dir = "../uploads/airlines/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return 'uploads/airlines/' . $filename;
    }
    return null;
}

// จัดการการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
                // ส่วนของการเพิ่มข้อมูล
            case 'add':
                $airline_name = $conn->real_escape_string($_POST['airline_name']);
                $airline_code = $conn->real_escape_string($_POST['airline_code']);

                $logo_path = '';
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                    $logo_path = uploadLogo($_FILES['logo']);
                }

                $sql = "INSERT INTO airlines (airline_name, airline_code, logo_path) 
            VALUES ('$airline_name', '$airline_code', '$logo_path')";
                $conn->query($sql);
                break;

                // ส่วนของการแก้ไขข้อมูล
            case 'edit':
                $airline_id = (int)$_POST['airline_id'];
                $airline_name = $conn->real_escape_string($_POST['airline_name']);
                $airline_code = $conn->real_escape_string($_POST['airline_code']);

                $logo_sql = '';
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                    $logo_path = uploadLogo($_FILES['logo']);
                    if ($logo_path) {
                        $logo_sql = ", logo_path = '$logo_path'";
                    }
                }

                $sql = "UPDATE airlines SET 
            airline_name = '$airline_name',
            airline_code = '$airline_code'
            $logo_sql
            WHERE airline_id = $airline_id";
                $conn->query($sql);
                break;


            case 'delete':
                $airline_id = (int)$_POST['airline_id'];
                $sql = "DELETE FROM airlines WHERE airline_id = $airline_id";
                $conn->query($sql);
                break;
        }
        header('Location: airlines.php');
        exit();
    }
}

// ดึงข้อมูลสายการบิน
$airlines = $conn->query("SELECT * FROM airlines ORDER BY airline_name");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสายการบิน - EliteTix</title>
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
                    <h1 class="h2">จัดการสายการบิน</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAirlineModal">
                        เพิ่มสายการบิน
                    </button>
                </div>

                <!-- ตารางแสดงสายการบิน -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>โลโก้</th>
                                <th>รหัสสายการบิน</th>
                                <th>ชื่อสายการบิน</th>
                                <th>รายละเอียด</th>
                                <th>เว็บไซต์</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($airline = $airlines->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if ($airline['logo_path']): ?>
                                            <img src="../<?php echo $airline['logo_path']; ?>"
                                                alt="<?php echo $airline['airline_name']; ?>"
                                                style="width: 50px; height: 50px; object-fit: contain;">
                                        <?php else: ?>
                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                                style="width: 50px; height: 50px;">
                                                No Logo
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $airline['airline_code']; ?></td>
                                    <td><?php echo $airline['airline_name']; ?></td>
                                    <td><?php echo substr($airline['description'], 0, 100) . '...'; ?></td>
                                    <td><a href="<?php echo $airline['website']; ?>" target="_blank"><?php echo $airline['website']; ?></a></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-airline"
                                            data-airline='<?php echo json_encode($airline); ?>'
                                            data-bs-toggle="modal"
                                            data-bs-target="#editAirlineModal">
                                            แก้ไข
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-airline"
                                            data-airline-id="<?php echo $airline['airline_id']; ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteAirlineModal">
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

    <!-- Modal เพิ่มสายการบิน -->
    <div class="modal fade" id="addAirlineModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มสายการบิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <!-- ในส่วนของฟอร์มเพิ่มและแก้ไข -->
                        <div class="mb-3">
                            <label class="form-label">ชื่อสายการบิน</label>
                            <input type="text" name="airline_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">รหัสสายการบิน</label>
                            <input type="text" name="airline_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">โลโก้</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
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

    <!-- Modal แก้ไขสายการบิน -->
    <div class="modal fade" id="editAirlineModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขสายการบิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="airline_id" id="edit_airline_id">
                        <!-- ในส่วนของฟอร์มเพิ่มและแก้ไข -->
                        <div class="mb-3">
                            <label class="form-label">ชื่อสายการบิน</label>
                            <input type="text" name="airline_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">รหัสสายการบิน</label>
                            <input type="text" name="airline_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">โลโก้</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal ลบสายการบิน -->
    <div class="modal fade" id="deleteAirlineModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการลบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="airline_id" id="delete_airline_id">
                        <p>คุณต้องการลบสายการบินนี้ใช่หรือไม่?</p>
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
        // จัดการการแก้ไขสายการบิน
        document.querySelectorAll('.edit-airline').forEach(button => {
            button.addEventListener('click', function() {
                const airline = JSON.parse(this.dataset.airline);
                document.getElementById('edit_airline_id').value = airline.airline_id;
                document.getElementById('edit_airline_code').value = airline.airline_code;
                document.getElementById('edit_airline_name').value = airline.airline_name;
                document.getElementById('edit_description').value = airline.description;
                document.getElementById('edit_website').value = airline.website;

                const currentLogo = document.getElementById('current_logo');
                if (airline.logo_path) {
                    currentLogo.innerHTML = `<img src="../${airline.logo_path}" alt="${airline.airline_name}" style="max-width: 100px;">`;
                } else {
                    currentLogo.innerHTML = 'ไม่มีโลโก้';
                }
            });
        });

        // จัดการการลบสายการบิน
        document.querySelectorAll('.delete-airline').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_airline_id').value = this.dataset.airlineId;
            });
        });
    </script>
</body>

</html>
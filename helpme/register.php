<?php
session_start();
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];

    // จัดการอัพโหลดรูป
    $profile_image = 'default.jpg'; // ค่าเริ่มต้น
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = 'user_' . time() . '.' . $ext;
            $upload_path = 'uploads/profiles/' . $new_filename;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profile_image = $new_filename;
            }
        }
    }
    // ตรวจสอบว่า username ซ้ำหรือไม่
    $check_sql = "SELECT * FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows > 0) {
        $error = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
    } else {
        // แก้ไข SQL query
        $sql = "INSERT INTO users (username, password, email, first_name, last_name, phone, profile_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssss",
            $username,
            $password,
            $email,
            $first_name,
            $last_name,
            $phone,
            $profile_image
        );

        if ($stmt->execute()) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                });
            </script>";
        } else {
            $error = "เกิดข้อผิดพลาดในการลงทะเบียน";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/background.png');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .registration-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            margin: 20px;
            backdrop-filter: blur(10px);
            transform: translateY(0);
            transition: all 0.3s ease;
            animation: fadeIn 1s ease-in;
        }

        .registration-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.2);
            border-color: #007bff;
        }

        .error {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #dc3545;
            animation: shake 0.5s ease-in-out;
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 35px;
            font-weight: 700;
            font-size: 2.2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
        }

        h2:after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: linear-gradient(45deg, #007bff, #00bfff);
            margin: 15px auto 0;
            border-radius: 2px;
        }

        .btn-register {
            background: linear-gradient(45deg, #007bff, #00bfff);
            border: none;
            color: white;
            padding: 15px 0;
            width: 100%;
            border-radius: 30px;
            font-weight: 600;
            margin-top: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .btn-register:hover {
            background: linear-gradient(45deg, #0056b3, #0098cc);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.4);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
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

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #007bff;
        }

        /* เพิ่ม style สำหรับ Modal */
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }

        .modal-header {
            border-bottom: 2px solid #f0f0f0;
            background: linear-gradient(45deg, #007bff, #00bfff);
            border-radius: 20px 20px 0 0;
            padding: 20px 30px;
        }

        .modal-title {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-body p {
            font-size: 1.2rem;
            color: #2c3e50;
            margin: 0;
            text-align: center;
        }

        .modal-footer {
            border-top: none;
            padding: 20px 30px;
        }

        .modal-footer .btn-primary {
            background: linear-gradient(45deg, #007bff, #00bfff);
            border: none;
            padding: 12px 35px;
            border-radius: 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .modal-footer .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #0098cc);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.4);
        }

        .btn-close {
            color: white;
            opacity: 1;
            text-shadow: none;
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
        }

        .form-group input[type="file"] {
            padding: 8px;
            margin-bottom: 5px;
        }

        .form-group small {
            display: block;
            margin-bottom: 15px;
            color: #666;
        }
    </style>
</head>

<body>

    <div class="registration-container animate__animated animate__fadeIn">
        <h2>ลงทะเบียน</h2>

        <?php if (isset($error)): ?>
            <div class="error animate__animated animate__shakeX"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username" class="form-label">ชื่อผู้ใช้:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">รหัสผ่าน:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">อีเมล:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="first_name" class="form-label">ชื่อ:</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name" class="form-label">นามสกุล:</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">เบอร์โทรศัพท์:</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="profile_image" class="form-label">รูปโปรไฟล์:</label>
                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                <small class="text-muted">รองรับไฟล์ภาพ jpg, png, gif ขนาดไม่เกิน 2MB</small>
            </div>

            <button type="submit" class="btn btn-register">ลงทะเบียน</button>
        </form>

        <div class="login-link">
            <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
        </div>
    </div>

    <!-- Modal สมัครเสร็จสิ้น -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content animate__animated animate__fadeIn">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">แจ้งเตือน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-center">🎉 สมัครสมาชิกเสร็จสิ้น 🎉</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="window.location.href='login.php'">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
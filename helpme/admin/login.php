<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // เพิ่มการ debug
    $sql = "SELECT * FROM admins WHERE username = '$username'";
    $result = $conn->query($sql);
    $admin = $result->fetch_assoc();
    
    if ($result->num_rows > 0) {
        // ตรวจสอบรหัสผ่าน
        if ($password === '123456') {  // ตรวจสอบตรงๆ ก่อนเพื่อทดสอบ
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header('Location: dashboard.php');
            exit();
        }
    }
    $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
}
?>



<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - EliteTix</title>
    <link rel="icon" href="images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #ffd1dc;
            min-height: 100vh;
            overflow: hidden;
        }
        .sakura {
            position: absolute;
            width: 20px;
            height: 20px;
            background-image: url('../images/sakura.png');
            background-size: cover;
            pointer-events: none;
            animation: fall linear infinite;
        }
        @keyframes fall {
            0% {
                opacity: 1;
                top: -10%;
                transform: rotate(0deg) translateX(0);
            }
            100% {
                opacity: 0.7;
                top: 100%;
                transform: rotate(360deg) translateX(100px);
            }
        }
        .card {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            border: none;
            backdrop-filter: blur(10px);
        }
        .card-body {
            padding: 2.5rem;
        }
        h3 {
            color: #ff69b4;
            font-family: 'Kanit', sans-serif;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            border-radius: 15px;
            border: 2px solid #ffd1dc;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #ff69b4;
            box-shadow: 0 0 10px rgba(255, 105, 180, 0.3);
        }
        .btn-primary {
            background: linear-gradient(45deg, #ff69b4, #ffd1dc);
            border: none;
            border-radius: 15px;
            padding: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.4);
            background: linear-gradient(45deg, #ffd1dc, #ff69b4);
        }
        .alert-danger {
            background-color: rgba(255, 182, 193, 0.9);
            border: none;
            border-radius: 15px;
            color: #800000;
        }
        .form-label {
            color: #ff69b4;
            font-weight: 600;
        }
        .container {
            margin-top: 8rem !important;
        }
        .card::before {
            content: '';
            position: absolute;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 100px;
            background-image: url('https://www.transparentpng.com/thumb/sakura/pink-sakura-flowers-png-8.png');
            background-size: contain;
            background-repeat: no-repeat;
        }
        .btn-back {
            background: linear-gradient(45deg, #ffd1dc, #ff9ecd);
            border: none;
            border-radius: 15px;
            padding: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.4);
            background: linear-gradient(45deg, #ff9ecd, #ffd1dc);
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">เข้าสู่ระบบผู้ดูแล</h3>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">รหัสผ่าน</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
                        </form>
                        <a href="../index.php" class="btn btn-back w-100">กลับสู่หน้าหลัก</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function createSakura() {
            const sakura = document.createElement('div');
            sakura.className = 'sakura';
            
            const startX = Math.random() * window.innerWidth;
            sakura.style.left = startX + 'px';
            
            const size = Math.random() * 15 + 10;
            sakura.style.width = `${size}px`;
            sakura.style.height = `${size}px`;
            
            sakura.style.animationDuration = `${Math.random() * 3 + 2}s`;
            
            document.body.appendChild(sakura);

            sakura.addEventListener('animationend', () => {
                sakura.remove();
            });
        }

        setInterval(createSakura, 300);
        
        for(let i = 0; i < 20; i++) {
            setTimeout(createSakura, Math.random() * 1000);
        }
    </script>
</body>
</html>
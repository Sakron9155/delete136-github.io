<?php
session_start();
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            header("Location: index.php");
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
    <title>เข้าสู่ระบบ</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/background.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Kanit', sans-serif;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, #ff6b6b20, #4ecdc420, #45b7af20, #96e6a120);
            animation: gradient 15s ease infinite;
            transform: translateZ(0);
            background-size: 400% 400%;
            z-index: -1;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            transform: translateY(0);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 1s ease;
        }
        .login-container:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4);
        }
        .error {
            color: #dc3545;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 15px;
            background-color: rgba(220, 53, 69, 0.1);
            border: 2px solid rgba(220, 53, 69, 0.2);
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        h2 {
            color: #2d3436;
            text-align: center;
            margin-bottom: 35px;
            font-weight: 600;
            font-size: 2.5rem;
            background: linear-gradient(45deg, #4e54c8, #8f94fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeIn 1.5s ease;
        }
        .form-group {
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        .form-control {
            padding: 15px;
            border-radius: 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.4s ease;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
        }
        .form-control:focus {
            border-color: #4e54c8;
            box-shadow: 0 0 20px rgba(78, 84, 200, 0.3);
            transform: translateY(-2px);
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 10px;
            color: #2d3436;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-login {
            background: linear-gradient(45deg, #4e54c8, #8f94fb);
            border: none;
            color: white;
            padding: 16px;
            border-radius: 30px;
            width: 100%;
            font-weight: 600;
            font-size: 1.2rem;
            margin-top: 25px;
            transition: all 0.5s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            overflow: hidden;
        }
        .btn-login:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: all 0.6s;
        }
        .btn-login:hover:before {
            left: 100%;
        }
        .btn-login:hover {
            background: linear-gradient(45deg, #3f4499, #7276cc);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(78, 84, 200, 0.5);
        }
        .register-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid rgba(238, 238, 238, 0.5);
        }
        .register-link p {
            font-size: 1.1rem;
            color: #666;
        }
        .register-link a {
            color: #4e54c8;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }
        .register-link a:after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(45deg, #4e54c8, #8f94fb);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .register-link a:hover:after {
            transform: scaleX(1);
        }
        .register-link a:hover {
            color: #3f4499;
            text-shadow: 0 0 15px rgba(78, 84, 200, 0.3);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="animate__animated animate__fadeInDown">เข้าสู่ระบบ</h2>
        
        <?php if (isset($error)): ?>
            <div class="error animate__animated animate__shakeX"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <label for="username" class="form-label">ชื่อผู้ใช้:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="form-group animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                <label for="password" class="form-label">รหัสผ่าน:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-login animate__animated animate__fadeInUp" style="animation-delay: 0.6s">เข้าสู่ระบบ</button>
        </form>
        
        <div class="register-link animate__animated animate__fadeInUp" style="animation-delay: 0.8s">
            <p>ยังไม่มีบัญชี? <a href="register.php">ลงทะเบียน</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
include 'config/db.php';

$booking_id = $_POST['booking_id'];
$payment_method = $_POST['payment_method'];
$total_amount = $_POST['total_amount'];
$transaction_id = 'TXN' . date('ymd') . rand(1000, 9999);

// บันทึกข้อมูลการชำระเงิน
$sql = "INSERT INTO payments (booking_id, amount, payment_method, transaction_id) 
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("idss", 
    $booking_id, 
    $total_amount,
    $payment_method, 
    $transaction_id
);
$stmt->execute();

// อัพเดทสถานะการจอง
$sql = "UPDATE bookings SET status = 'confirmed', payment_status = 'completed', 
        total_amount = ? WHERE booking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("di", $total_amount, $booking_id);
$stmt->execute();

// ส่งต่อไปยังหน้ายืนยันการจอง
header("Location: booking_confirmation.php?booking_id=" . $booking_id);
exit();
?>

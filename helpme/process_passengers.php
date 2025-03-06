<?php
session_start();
include 'config/db.php';

$flight_id = $_POST['flight_id'];
$user_id = $_SESSION['user_id'];
$booking_number = 'BK' . date('ymd') . rand(1000, 9999);
$booking_date = date('Y-m-d H:i:s');

// คำนวณราคารวม
$sql_flight = "SELECT base_price FROM flights WHERE flight_id = ?";
$stmt_flight = $conn->prepare($sql_flight);
$stmt_flight->bind_param("i", $flight_id);
$stmt_flight->execute();
$flight_result = $stmt_flight->get_result();
$flight_data = $flight_result->fetch_assoc();
$total_amount = $flight_data['base_price'] * count($_POST['passenger']);

$conn->begin_transaction();

try {
    // บันทึกการจอง
    $sql = "INSERT INTO bookings (user_id, flight_id, booking_number, booking_date, status, payment_status, total_amount) 
            VALUES (?, ?, ?, ?, 'pending', 'pending', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissd", $user_id, $flight_id, $booking_number, $booking_date, $total_amount);
    $stmt->execute();
    
    $booking_id = $conn->insert_id;

    // บันทึกข้อมูลผู้โดยสาร
    $sql = "INSERT INTO booking_passengers (booking_id, first_name, last_name, passport_number, special_requests) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    foreach ($_POST['passenger'] as $passenger) {
        $stmt->bind_param("issss", 
            $booking_id,
            $passenger['firstname'],
            $passenger['lastname'],
            $passenger['passport_number'],
            $passenger['special_requests']
        );
        $stmt->execute();
    }

    $conn->commit();
    
    // เก็บ booking_id ใน session
    $_SESSION['current_booking_id'] = $booking_id;
    
    header("Location: seat_selection.php?booking_id=" . $booking_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['booking_error'] = "เกิดข้อผิดพลาดในการจอง: " . $e->getMessage();
    header("Location: booking.php?flight_id=" . $flight_id);
    exit();
}
?>

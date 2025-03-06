<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['current_booking_id']) || $_POST['booking_id'] != $_SESSION['current_booking_id']) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'];
    $passenger_seats = $_POST['passenger_seats'];

    $conn->begin_transaction();

    try {
        // อัพเดตสถานะที่นั่ง
        $sql = "UPDATE seats SET status = 'booked' WHERE seat_id = ?";
        $stmt = $conn->prepare($sql);

        // อัพเดตที่นั่งสำหรับผู้โดยสาร
        $update_passenger = "UPDATE booking_passengers SET seat_id = ? WHERE passenger_id = ?";
        $stmt_passenger = $conn->prepare($update_passenger);

        foreach ($passenger_seats as $passenger_id => $seat_id) {
            $stmt->bind_param("i", $seat_id);
            $stmt->execute();

            $stmt_passenger->bind_param("ii", $seat_id, $passenger_id);
            $stmt_passenger->execute();
        }

        // อัพเดตสถานะการจอง
        $update_booking = "UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?";
        $stmt_booking = $conn->prepare($update_booking);
        $stmt_booking->bind_param("i", $booking_id);
        $stmt_booking->execute();

        $conn->commit();
        header("Location: payment.php?booking_id=" . $booking_id);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['seat_error'] = "เกิดข้อผิดพลาดในการเลือกที่นั่ง: " . $e->getMessage();
        header("Location: seat_selection.php?booking_id=" . $booking_id);
        exit();
    }
}
?>

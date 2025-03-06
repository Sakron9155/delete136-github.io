<?php
include 'config/db.php';

$sql = "SELECT r.*, u.first_name, u.last_name, u.profile_image 
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        ORDER BY r.created_at DESC";
        
$result = $conn->query($sql);
$reviews = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($reviews);
